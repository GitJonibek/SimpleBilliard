<?php
App::uses('TextUtil', 'Lib/Util');
/**
 * Class MessageService
 */
class MentionComponent extends Component {
    public function replaceMention($text) {
        $result = preg_replace('/%%%.*?:(.*?)%%%/m', '<b><i><@${1}></i></b>', $text);
        return $result;
    }
    public function isMentioned($body, $userId, $teamId) {
        $users = $this->getUserList($body, $teamId, $userId, true);
        return in_array($userId, $users);
    }
    public function getUserList($body, $my, $me, $all = false) {
        $mentions = TextUtil::extractAllIdFromMention($body);
        $notifyUsers = array();
        foreach ($mentions as $key => $mention) {
            if ($mention['isUser']) {
                $notifyUsers[] = explode(':', $mention['id'])[0];
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
                    if ($all || $userId != $me) {
                        $notifyUsers[] = $userId;
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
                    if ($all || $userId != $me) {
                        $notifyUsers[] = $userId;
                    }
                }
            }
        }
        return $notifyUsers;
    }
}
