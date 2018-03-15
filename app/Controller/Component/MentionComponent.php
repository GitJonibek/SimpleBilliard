<?php
App::uses('Component', 'Controller');
/**
 * Class MessageService
 */
class MentionComponent extends Component {
    static function extractAllIdFromMention($text) {
        preg_match_all('/%%%(.*?)%%%/m', $text, $matches);
        $result = array();
        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $match) {
                $isUser = strpos($match, 'user') === 0;
                $isCircle = strpos($match, 'circle') === 0;
                $isGroup = strpos($match, 'group') === 0;
                $replacement = '';
                if ($isUser) {
                    $replacement = 'user_';
                }else if ($isCircle) {
                    $replacement = 'circle_';
                }else if ($isGroup) {
                    $replacement = 'group_';
                }
                $result[$match] = array(
                    'id' => explode(':', str_replace($replacement, '', $match))[0],
                    'isUser' => $isUser, 
                    'isCircle' => $isCircle,
                    'isGroup' => $isGroup
                );
            }
        }
        return $result;
    }

    static function replaceAndAddNameToMention($pattern, $replacement, $subject) {
        $result = preg_replace('/%%%'.$pattern.'%%%/m', '%%%'.$pattern.':'.$replacement.'%%%', $subject);
        return $result;
    }
    public function replaceMention($text, $mentions) {
        $result = $text;
        foreach ($mentions as $mention) {
            $result = preg_replace('/%%%'.$mention.':(.*?)%%%/m', '<b><i class="mentioned-to-me"><@${1}></i></b>', $result);
        }
        $result = preg_replace('/%%%.*?:(.*?)%%%/m', '<b><i><@${1}></i></b>', $result);
        return $result;
    }
    public function getMyMentions($body, $userId, $teamId) {
        return $this->getUserList($body, $teamId, $userId, true, true);
    }
    static public function appendName($body) {
        $matches = MentionComponent::extractAllIdFromMention($body);
        if (count($matches) > 0) {
            $cache = array();
            foreach ($matches as $key => $match) {
                $replacementName = 'name';
                $model = null;
                if ($match['isUser'] === true) {
                    $model = ClassRegistry::init('PlainUser');
                    $replacementName = 'display_username';
                    $model->alias = 'User';
                }else if ($match['isCircle'] === true) {
                    $model = ClassRegistry::init('Circle');
                }else if ($match['isGroup'] === true) {
                    $model = ClassRegistry::init('Group');
                }
                if (!is_null($model)) {
                    $data = $model->findById($match['id']);
                    $obj = $data[$model->alias];
                    $replacement = $obj[$replacementName];
                    $body = MentionComponent::replaceAndAddNameToMention($key, $replacement, $body);
                }
            }
        }
        return $body;
    }
    // TODO It's too bad that $onlyMe has multiple meanings to use to extract just me and to append user/circle/group prefix.
    public function getUserList($body, $my, $me, $includeMe = false, $onlyMe = false) {
        $mentions = MentionComponent::extractAllIdFromMention($body);
        $result = array();
        
        foreach ($mentions as $key => $mention) {
            if ($mention['isUser']) {
                $userId = $mention['id'];
                if ($onlyMe && $userId != $me) continue;
                $result[] = $onlyMe ? 'user_'.$userId : $userId;
            }else if($mention['isCircle']) {
                $notifyCircles[] = $mention['id'];
            }else if ($mention['isGroup']) {
                $notifyGroups[] = $mention['id'];
            }
        }
        if (!empty($notifyCircles)) {
            foreach ($notifyCircles as $circleId) {
                $CircleMember = ClassRegistry::init('CircleMember');
                $circle_members = $CircleMember->getMembers($circleId, true);
                foreach ($circle_members as $member) {
                    $userId = $member['CircleMember']['user_id'];
                    if ($onlyMe && $userId != $me) continue;
                    if ($includeMe || $userId != $me) {
                        $result[] = $onlyMe ? 'circle_'.$circleId : $userId;
                    }
                }
            }
        }
        if (!empty($notifyGroups)) {
            foreach ($notifyGroups as $groupId) {
                $MemberGroup = ClassRegistry::init('MemberGroup');
                $group_members = $MemberGroup->getGroupMemberUserId($my, $groupId);
                foreach ($group_members as $member) {
                    $userId = $member;
                    if ($onlyMe && $userId != $me) continue;
                    if ($includeMe || $userId != $me) {
                        $result[] = $onlyMe ? 'group_'.$groupId : $userId;
                    }
                }
            }
        }
        return $result;
    }
}
