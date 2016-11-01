<?php
App::uses('GoalMember', 'Model');
App::uses('User', 'Model');

/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/05/28
 * Time: 0:36
 */
class MixpanelComponent extends CakeObject
{

    /**
     * Event Name
     */
    const TRACK_CREATE_GOAL = 'CreGoal';
    const TRACK_DELETE_GOAL = 'DelGoal';
    const TRACK_UPDATE_GOAL = 'UpdGoal';
    const TRACK_COLLABORATE_GOAL = 'Clb';
    const TRACK_WITHDRAW_COLLABORATE = "WidClb";
    const TRACK_FOLLOW_GOAL = 'FolGoal';
    const TRACK_UN_FOLLOW_GOAL = 'UnFolGoal';
    const TRACK_CREATE_KR = 'CreKR';
    const TRACK_DELETE_KR = 'DelKR';
    const TRACK_UPDATE_KR = 'UpdKR';
    const TRACK_CREATE_ACTION = 'CreAct';
    const TRACK_DELETE_ACTION = 'DelAct';
    const TRACK_UPDATE_ACTION = 'UpdAct';
    const TRACK_POST = 'Post';
    const TRACK_COMMENT = 'Comment';
    const TRACK_LIKE = 'Like';
    const TRACK_APPROVAL = 'ApvAct';
    const TRACK_EVALUATION = 'EvaAct';
    const TRACK_2SV_ENABLE = '2SVEbl';
    const TRACK_2SV_DISABLE = '2SVDbl';
    const TRACK_ACHIEVE_GOAL = 'AchieveGoal';
    const TRACK_ACHIEVE_KR = 'AchieveKR';
    const TRACK_MESSAGE = 'Message';

    /**
     * Property Values
     */
    const PROP_SHARE_CIRCLE = 'Circle';
    const PROP_SHARE_MEMBERS = 'Members';
    const PROP_SHARE_TEAM = 'Team';
    const PROP_TARGET_POST = 'Post';
    const PROP_TARGET_ACTION = 'Action';
    const PROP_TARGET_COMPLETE_KR = 'Complete KR';
    const PROP_TARGET_CREATE_GOAL = 'Create Goal';
    const PROP_TARGET_COMPLETED_GOAL = 'Complete Goal';
    const PROP_LIKE_ITSELF = 'Itself';
    const PROP_LIKE_COMMENT = 'Comment';
    const PROP_APPROVAL_MEMBER_COACH = 'Coach';
    const PROP_APPROVAL_MEMBER_MEMBER = 'Member';
    const PROP_APPROVAL_STATUS_APPROVAL_COMMENT_GOAL = 'Comment on Goal Approval';
    const PROP_APPROVAL_STATUS_APPROVAL_REVISION_REQUESTS = 'Approval-revision requests';
    const PROP_APPROVAL_STATUS_APPROVAL_EVALUABLE = 'Approval-Evaluable';
    const PROP_APPROVAL_STATUS_APPROVAL_INEVALUABLE = 'Approval-Inevaluable';
    const PROP_EVALUATION_MEMBER_SELF = 'Self';
    const PROP_EVALUATION_MEMBER_EVALUATOR = 'Evaluator';
    const PROP_EVALUATION_MEMBER_LEADER = 'Leader';

    public $name = "Mixpanel";

    /**
     * @var Mixpanel $MpOrigin
     */
    var $MpOrigin;

    /**
     * @var AppController $Controller
     */
    var $Controller;

    var $trackProperty = [];

    var $user;

    var $alreadySetUser = false;

    function initialize($controller)
    {
        $this->Controller = $controller;
        $user = $this->getUserInfo();
        if (MIXPANEL_TOKEN) {
            if (!$this->MpOrigin) {
                $this->MpOrigin = Mixpanel::getInstance(MIXPANEL_TOKEN);
            }
            if ($this->Controller->Auth->user() && !$this->alreadySetUser) {
                //mixpanelにユーザidをセット
                $this->identify($this->Controller->Auth->user('id'));
                //チームIDをセット
                $this->register('$team_id', $this->Controller->Session->read('current_team_id'));
                //性別をセット
                $this->register('$gender', $this->getGenderName());
                //言語をセット
                $this->register('$language', $user['language']);
                //タイムゾーンをセット
                $this->register('$timezone', $user['timezone']);
                $this->alreadySetUser = true;
            }
        }
    }

    function getGenderName()
    {
        $user = $this->getUserInfo();
        $gender_types = [
            User::TYPE_GENDER_MALE    => 'male',
            User::TYPE_GENDER_FEMALE  => 'female',
            User::TYPE_GENDER_NEITHER => 'other'
        ];
        return isset($gender_types[$user['gender_type']]) ? $gender_types[$user['gender_type']] : null;
    }

    function startup()
    {

    }

    function beforeRender()
    {
    }

    function shutdown()
    {
    }

    function beforeRedirect()
    {
    }

    /**
     * ユーザ情報をセット
     */
    function setUser()
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }

        $user = $this->getUserInfo();
        //ユーザ情報をセット
        $this->MpOrigin->people->set($user['id'],
            [
                '$first_name'      => $user['first_name'],
                '$last_name'       => $user['last_name'],
                '$email'           => $user['PrimaryEmail']['email'],
                '$default_team_id' => $user['default_team_id'],
                '$language'        => $user['language'],
                '$is_admin'        => $user['admin_flg'],
                '$gender_type'     => $this->getGenderName(),
            ]
        );
    }

    function getUserInfo()
    {
        if ($this->user) {
            return $this->user;
        }
        $this->user = $this->Controller->Auth->user();
        return $this->user;
    }

    /**
     * @param      $track_type
     * @param      $goal_id
     * @param null $kr_id
     * @param null $action_id
     */
    function trackGoal($track_type, $goal_id, $kr_id = null, $action_id = null)
    {
        $property = [
            '$goal_id'              => $goal_id,
            '$goal_owner_type'      => null,
            '$goal_approval_status' => null,
            '$img_file_count'       => 0,
            '$video_file_count'     => 0,
            '$doc_file_count'       => 0,
        ];

        if ($track_type != self::TRACK_FOLLOW_GOAL && $track_type != self::TRACK_UN_FOLLOW_GOAL) {
            $user_id = $this->Controller->Auth->user('id');
            $team_id = $this->Controller->Session->read('current_team_id');

            $goalMember = $this->Controller->Goal->GoalMember->getGoalMember($team_id, $user_id, $goal_id);
            if (empty($goalMember)) {
                $goalMember = $this->Controller->Goal->GoalMember->getGoalMember($team_id, $user_id, $goal_id, false);
            }
            if (isset($goalMember['GoalMember']['type'])) {
                $property['$goal_owner_type'] = $goalMember['GoalMember']['type'] == GoalMember::TYPE_OWNER ? 'L' : 'C';
            }

            $approval_status = [
                GoalMember::APPROVAL_STATUS_NEW           => "Pending approval",
                GoalMember::APPROVAL_STATUS_REAPPLICATION => "Evaluable",
                GoalMember::APPROVAL_STATUS_DONE          => "Not evaluable",
                GoalMember::APPROVAL_STATUS_WITHDRAWN     => "Pending modification",
            ];
            if (isset($goalMember['GoalMember']['approval_status'])) {
                $property['$goal_approval_status'] = $approval_status[$goalMember['GoalMember']['approval_status']];
            }
        }
        if ($kr_id) {
            $property['$kr_id'] = $kr_id;
        }
        if ($action_id) {
            $property['$action_id'] = $action_id;
        }
        if ($track_type == self::TRACK_CREATE_ACTION) {
            /** @var AttachedFile $AttachedFile */
            $AttachedFile = ClassRegistry::init('AttachedFile');
            $property['$img_file_count'] = $AttachedFile->getCountOfAttachedFiles($action_id,
                $AttachedFile::TYPE_MODEL_ACTION_RESULT,
                $AttachedFile::TYPE_FILE_IMG);
            $property['$video_file_count'] = $AttachedFile->getCountOfAttachedFiles($action_id,
                $AttachedFile::TYPE_MODEL_ACTION_RESULT,
                $AttachedFile::TYPE_FILE_VIDEO);
            $property['$doc_file_count'] = $AttachedFile->getCountOfAttachedFiles($action_id,
                $AttachedFile::TYPE_MODEL_ACTION_RESULT,
                $AttachedFile::TYPE_FILE_DOC);

        }
        $this->track($track_type, $property);
    }

    function trackPost($post_id, $share_type)
    {
        /** @var AttachedFile $AttachedFile */
        $AttachedFile = ClassRegistry::init('AttachedFile');
        $img_file_count = $AttachedFile->getCountOfAttachedFiles($post_id,
            $AttachedFile::TYPE_MODEL_POST,
            $AttachedFile::TYPE_FILE_IMG);
        $video_file_count = $AttachedFile->getCountOfAttachedFiles($post_id,
            $AttachedFile::TYPE_MODEL_POST,
            $AttachedFile::TYPE_FILE_VIDEO);
        $doc_file_count = $AttachedFile->getCountOfAttachedFiles($post_id,
            $AttachedFile::TYPE_MODEL_POST,
            $AttachedFile::TYPE_FILE_DOC);
        $this->track(self::TRACK_POST, [
            '$share_type'       => $share_type,
            '$post_id'          => $post_id,
            '$img_file_count'   => $img_file_count,
            '$video_file_count' => $video_file_count,
            '$doc_file_count'   => $doc_file_count,
        ]);
    }

    function trackComment($post_type, $comment_id)
    {
        $target_type = $this->getTargetTypeByPostType($post_type);
        /** @var AttachedFile $AttachedFile */
        $AttachedFile = ClassRegistry::init('AttachedFile');
        $img_file_count = $AttachedFile->getCountOfAttachedFiles($comment_id,
            $AttachedFile::TYPE_MODEL_COMMENT,
            $AttachedFile::TYPE_FILE_IMG);
        $video_file_count = $AttachedFile->getCountOfAttachedFiles($comment_id,
            $AttachedFile::TYPE_MODEL_COMMENT,
            $AttachedFile::TYPE_FILE_VIDEO);
        $doc_file_count = $AttachedFile->getCountOfAttachedFiles($comment_id,
            $AttachedFile::TYPE_MODEL_COMMENT,
            $AttachedFile::TYPE_FILE_DOC);

        $this->track(self::TRACK_COMMENT, [
            '$target_type'      => $target_type,
            '$img_file_count'   => $img_file_count,
            '$video_file_count' => $video_file_count,
            '$doc_file_count'   => $doc_file_count,
        ]);
    }

    function trackLike($post_type)
    {
        $target_type = $this->getTargetTypeByPostType($post_type);
        $this->track(self::TRACK_LIKE, ['$target_type' => $target_type]);
    }

    function trackMessage($post_id)
    {
        $this->track(self::TRACK_MESSAGE, ['$post_id' => $post_id]);
    }

    /**
     * @param $approval_type
     * @param $approval_member_type
     * @param $goal_id
     */
    function trackApproval($approval_type, $approval_member_type, $goal_id)
    {
        $property = [
            '$approval_type'        => $approval_type,
            '$approval_member_type' => $approval_member_type,
            '$goal_id'              => $goal_id,
        ];
        $this->track(self::TRACK_APPROVAL, $property);
    }

    /**
     * @param $member_type
     */
    function trackEvaluation($member_type)
    {
        $property = [
            '$evaluation_member_type' => $member_type,
        ];
        $this->track(self::TRACK_EVALUATION, $property);
    }

    function track2SV($track_type)
    {
        $this->track($track_type);
    }

    function getTargetTypeByPostType($post_type)
    {
        $mixpanel_prop_name = null;
        switch ($post_type) {
            case Post::TYPE_NORMAL:
                $mixpanel_prop_name = MixpanelComponent::PROP_TARGET_POST;
                break;
            case Post::TYPE_ACTION:
                $mixpanel_prop_name = MixpanelComponent::PROP_TARGET_ACTION;
                break;
            case Post::TYPE_KR_COMPLETE:
                $mixpanel_prop_name = MixpanelComponent::PROP_TARGET_COMPLETE_KR;
                break;
            case Post::TYPE_CREATE_GOAL:
                $mixpanel_prop_name = MixpanelComponent::PROP_TARGET_CREATE_GOAL;
                break;
            case Post::TYPE_GOAL_COMPLETE:
                $mixpanel_prop_name = MixpanelComponent::PROP_TARGET_COMPLETED_GOAL;
                break;
        }
        return $mixpanel_prop_name;
    }

    /**
     * Add an array representing a message to be sent to Mixpanel to the in-memory queue.
     *
     * @param array $message
     */
    public function enqueue($message = [])
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->enqueue($message);
    }

    /**
     * Add an array representing a list of messages to be sent to Mixpanel to a queue.
     *
     * @param array $messages
     */
    public function enqueueAll($messages = [])
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->enqueueAll($messages);
    }

    /**
     * Flush the events queue
     *
     * @param int $desired_batch_size
     */
    public function flush($desired_batch_size = 50)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->flush($desired_batch_size);
    }

    /**
     * Empty the events queue
     */
    public function reset()
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->reset();
    }

    /**
     * Identify the user you want to associate to tracked events
     *
     * @param string|int $user_id
     */
    public function identify($user_id)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->identify($user_id);
    }

    /**
     * Track an event defined by $event associated with metadata defined by $properties
     *
     * @param string $event
     * @param array  $properties
     */
    public function track($event, $properties = [])
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->track($event, $properties);
    }

    /**
     * Register a property to be sent with every event.
     * If the property has already been registered, it will be
     * overwritten. NOTE: Registered properties are only persisted for the life of the Mixpanel class instance.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function register($property, $value)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->register($property, $value);
    }

    /**
     * Register multiple properties to be sent with every event.
     * If any of the properties have already been registered,
     * they will be overwritten. NOTE: Registered properties are only persisted for the life of the Mixpanel class
     * instance.
     *
     * @param array $props_and_vals
     */
    public function registerAll($props_and_vals = [])
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->registerAll($props_and_vals);
    }

    /**
     * Register a property to be sent with every event.
     * If the property has already been registered, it will NOT be
     * overwritten. NOTE: Registered properties are only persisted for the life of the Mixpanel class instance.
     *
     * @param $property
     * @param $value
     */
    public function registerOnce($property, $value)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->registerOnce($property, $value);
    }

    /**
     * Register multiple properties to be sent with every event.
     * If any of the properties have already been registered,
     * they will NOT be overwritten. NOTE: Registered properties are only persisted for the life of the Mixpanel class
     * instance.
     *
     * @param array $props_and_vals
     */
    public function registerAllOnce($props_and_vals = [])
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->registerAllOnce($props_and_vals);
    }

    /**
     * Un-register an property to be sent with every event.
     *
     * @param string $property
     */
    public function unregister($property)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->unregister($property);
    }

    /**
     * Un-register a list of properties to be sent with every event.
     *
     * @param array $properties
     */
    public function unregisterAll($properties)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->unregisterAll($properties);
    }

    /**
     * Get a property that is set to be sent with every event
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($property)
    {
        if (!MIXPANEL_TOKEN) {
            return null;
        }
        return $this->MpOrigin->getProperty($property);
    }

    /**
     * Alias an existing id with a different unique id. This is helpful when you want to associate a generated id
     * (such as a session id) to a user id or username.
     *
     * @param string|int $original_id
     * @param string|int $new_id
     */
    public function createAlias($original_id, $new_id)
    {
        if (!MIXPANEL_TOKEN) {
            return;
        }
        $this->MpOrigin->createAlias($original_id, $new_id);
    }

}
