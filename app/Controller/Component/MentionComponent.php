<?php
App::uses('Component', 'Controller');
/**
 * Class MentionComponent
 */
class MentionComponent extends Component {
    private static $PREFIX = '%%%';
    private static $SUFFIX = '%%%';
    private static $USER_ID_PREFIX = 'user';
    private static $CIRCLE_ID_PREFIX = 'circle';
    private static $GROUP_ID_PREFIX = 'group';
    private static $ID_DELIMITER = '_';
    private static function getMentionReg(string $pattern, string $option) {
        return '/' . self::$PREFIX . $pattern . self::$SUFFIX . '/' . $option;
    }
    /**
     * extract any kinds of ID from content.
     *
     * @param $text string content of Post/Action/Comment
     * @return array 
     */
    static function extractAllIdFromMention($text) {
        $result = array();
        if (preg_match_all(self::getMentionReg('(.*?)', 'm'), $text, $matches) && count($matches[1]) > 0) {
            foreach ($matches[1] as $match) {
                $isUser = strpos($match, self::$USER_ID_PREFIX) === 0;
                $isCircle = strpos($match, self::$CIRCLE_ID_PREFIX) === 0;
                $isGroup = strpos($match, self::$GROUP_ID_PREFIX) === 0;
                $replacement = '';
                if ($isUser) {
                    $replacement = self::$USER_ID_PREFIX.self::$ID_DELIMITER;
                }else if ($isCircle) {
                    $replacement = self::$CIRCLE_ID_PREFIX.self::$ID_DELIMITER;
                }else if ($isGroup) {
                    $replacement = self::$GROUP_ID_PREFIX.self::$ID_DELIMITER;
                }
                $result[$match] = array(
                    // $match will be like "user_1:user_name".
                    // Explode it at first and replace it with $replacement
                    // so that we can return just an ID itself.
                    'id' => explode(':', str_replace($replacement, '', $match))[0],
                    'isUser' => $isUser, 
                    'isCircle' => $isCircle,
                    'isGroup' => $isGroup
                );
            }
        }
        return $result;
    }
    /**
     * replace a mention expression with params below. 
     *
     * @param $pattern string a regular expression to replace
     * @param $replacement string a replacement to replace $pattern with
     * @param $subject string a subject to replace 
     * @return string 
     */
    static function replaceAndAddNameToMention($pattern, $replacement, $subject) {
        $result = preg_replace(self::getMentionReg($pattern, 'm'), self::$PREFIX.$pattern.':'.$replacement.self::$SUFFIX, $subject);
        return $result;
    }
    /**
     * replace all mentions in content with HTML expression.
     *
     * @param $text string the content should be replaced
     * @param $mentions array[string] mentions should be replaced
     * @return string 
     */
    public function replaceMention($text, $mentions) {
        $result = $text;
        foreach ($mentions as $mention) {
            $result = preg_replace(self::getMentionReg($mention.':(.*?)', 'm'), '<b><i class="mentioned-to-me"><@${1}></i></b>', $result);
        }
        $result = preg_replace(self::getMentionReg('.*?:(.*?)', 'm'), '<b><i><@${1}></i></b>', $result);
        return $result;
    }
    /**
     * a shortcut method to get belongings
     * 
     * @param $body string the content which can contain mentions
     * @param $userId int a userId which should be recognized as myself
     * @param $teamId int the team ID to identify the circle uniquely
     * @return array
     */
    public function getMyMentions($body, $userId, $teamId) {
        return $this->getUserList($body, $teamId, $userId, true, true);
    }
    /**
     * append the name to the ID in each mention in the content
     * 
     * @param $body string the content which can contain mentions
     * @return string
     */
    static public function appendName($body) {
        $matches = self::extractAllIdFromMention($body);
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
                    $body = self::replaceAndAddNameToMention($key, $replacement, $body);
                }
            }
        }
        return $body;
    }
    /**
     * get user id list or id list of user/circle/group which contains $userId.
     *
     * @param $body string content of Post/Action/Comment
     * @param $teamId int the team ID to identify the circle uniquely
     * @param $me int the user ID to decide to exlude or include the user itself
     * @param $includeMe boolean whether the result should include $me or not
     * @param $returnAsBelonging boolean whether the result should be user/circle/group which contains $me
     */
    public function getUserList($body, $teamId, $me, $includeMe = false, $returnAsBelonging = false) {
        $mentions = self::extractAllIdFromMention($body);
        $result = array();
        
        foreach ($mentions as $key => $mention) {
            if ($mention['isUser']) {
                $userId = $mention['id'];
                if ($returnAsBelonging && $userId != $me) continue;
                $result[] = $returnAsBelonging ? self::$USER_ID_PREFIX.self::$ID_DELIMITER.$userId : $userId;
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
                    if ($returnAsBelonging && $userId != $me) continue;
                    if ($includeMe || $userId != $me) {
                        $result[] = $returnAsBelonging ? self::$CIRCLE_ID_PREFIX.self::$ID_DELIMITER.$circleId : $userId;
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
                    if ($returnAsBelonging && $userId != $me) continue;
                    if ($includeMe || $userId != $me) {
                        $result[] = $returnAsBelonging ? self::$GROUP_ID_PREFIX.self::$ID_DELIMITER.$groupId : $userId;
                    }
                }
            }
        }
        return $result;
    }
}
