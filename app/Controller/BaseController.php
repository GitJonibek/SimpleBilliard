<?php
App::uses('Controller', 'Controller');

/**
 * Application level Controller
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 9/6/16
 * Time: 16:00
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @property SessionComponent   $Session
 * @property SecurityComponent  $Security
 * @property AuthComponent      $Auth
 * @property NotifyBizComponent $NotifyBiz
 * @property GlEmailComponent   $GlEmail
 * @property MixpanelComponent  $Mixpanel
 * @property User               $User
 * @property Post               $Post
 * @property Goal               $Goal
 * @property Team               $Team
 * @property GlRedis            $GlRedis
 */
class BaseController extends Controller
{
    public $components = [
        'Session',
        'Security' => [
            'csrfUseOnce' => false,
            'csrfExpires' => '+24 hour'
        ],
        'Auth'     => [
            'flash' => [
                'element' => 'alert',
                'key'     => 'auth',
                'params'  => ['plugin' => 'BoostCake', 'class' => 'alert-error']
            ]
        ],
        'NotifyBiz',
        'GlEmail',
        'Mixpanel',
    ];

    public $uses = [
        'User',
        'Post',
        'Goal',
        'Team',
        'GlRedis',
    ];

    public $my_uid = null;
    public $current_team_id = null;
    public $current_term_id = null;
    public $next_term_id = null;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->_mergeParent = 'BaseController';
    }

    function beforeFilter()
    {
        parent::beforeFilter();
        $this->_setSecurity();

        if ($this->Auth->user()) {
            $this->current_team_id = $this->Session->read('current_team_id');
            $this->my_uid = $this->Auth->user('id');
        }
    }

    public function _setSecurity()
    {
        // sslの判定をHTTP_X_FORWARDED_PROTOに変更
        $this->request->addDetector('ssl', ['env' => 'HTTP_X_FORWARDED_PROTO', 'value' => 'https']);
        //サーバー環境のみSSLを強制
        if (ENV_NAME != "local") {
            $this->Security->blackHoleCallback = 'forceSSL';
            $this->Security->requireSecure();
        }
    }

    /**
     * アプリケーション全体の言語設定
     */
    public function _setAppLanguage()
    {
        //言語設定済かつ自動言語フラグが設定されていない場合は、言語設定を適用。それ以外はブラウザ判定
        if ($this->Auth->user() && $this->Auth->user('language') && !$this->Auth->user('auto_language_flg')) {
            Configure::write('Config.language', $this->Auth->user('language'));
            $this
                ->set('is_not_use_local_name', $this->User->isNotUseLocalName($this->Auth->user('language')));
        } else {
            $lang = $this->Lang->getLanguage();
            $this->set('is_not_use_local_name', $this->User->isNotUseLocalName($lang));
        }
    }

    function updateSetupStatusIfNotCompleted()
    {
        $setup_guide_is_completed = $this->Auth->user('setup_complete_flg');
        if ($setup_guide_is_completed) {
            return true;
        }

        $user_id = $this->Auth->user('id');
        $this->GlRedis->deleteSetupGuideStatus($user_id);
        $status_from_mysql = $this->User->generateSetupGuideStatusDict($user_id);
        if ($this->calcSetupRestCount($status_from_mysql) === 0) {
            $this->User->completeSetupGuide($user_id);
            $this->_refreshAuth($this->Auth->user('id'));
            return true;
        }
        //set update time
        $status_from_mysql[GlRedis::FIELD_SETUP_LAST_UPDATE_TIME] = time();

        $this->GlRedis->saveSetupGuideStatus($user_id, $status_from_mysql);
        return true;
    }

    function calcSetupRestCount($status)
    {
        return count(User::$TYPE_SETUP_GUIDE) - count(array_filter($status));
    }

    function calcSetupCompletePercent($status)
    {
        $rest_count = $this->calcSetupRestCount($status);
        if ($rest_count <= 0) {
            return 100;
        }

        $complete_count = count(User::$TYPE_SETUP_GUIDE) - $rest_count;
        if ($complete_count === 0) {
            return 0;
        }

        return 100 - floor(($rest_count / count(User::$TYPE_SETUP_GUIDE) * 100));
    }

    /**
     * ログイン中のAuthを更新する（ユーザ情報を更新後などに実行する）
     *
     * @param $uid
     *
     * @return bool
     */
    public function _refreshAuth($uid = null)
    {
        if (!$uid) {
            $uid = $this->Auth->user('id');
        }
        //言語設定を退避
        $user_lang = $this->User->findById($uid);
        $lang = null;
        if (!empty($user_lang)) {
            $lang = $user_lang['User']['language'];
        }
        $this->Auth->logout();
        $this->User->resetLocalNames();
        $this->User->me['language'] = $lang;
        $this->User->recursive = 0;
        $user_buff = $this->User->findById($uid);
        $this->User->recursive = -1;
        unset($user_buff['User']['password']);
        $user_buff = array_merge(['User' => []], $user_buff);
        //配列を整形（Userの中に他の関連データを配置）
        $user = [];
        $associations = [];
        foreach ($user_buff as $key => $val) {
            if ($key == 'User') {
                $user[$key] = $val;
            } else {
                $associations[$key] = $val;
            }
        }
        if (isset($user['User'])) {
            $user['User'] = array_merge($user['User'], $associations);
        }
        $this->User->me = $user['User'];
        $res = $this->Auth->login($user['User']);
        return $res;
    }

    /**
     * @param $goal_id
     * @param $notify_type
     */
    function _sendNotifyToCoach($model_id, $notify_type)
    {
        $coach_id = $this->Team->TeamMember->getCoachId($this->Auth->user('id'),
            $this->Session->read('current_team_id'));
        if (!$coach_id) {
            return;
        }
        $this->NotifyBiz->execSendNotify($notify_type, $model_id, null, $coach_id);
    }

    /**
     * コーチーに通知
     *
     * @param $goalMemberId
     * @param $notifyType
     */
    function _sendNotifyToCoachee($goalMemberId, $notifyType)
    {
        $goalMember = $this->Goal->GoalMember->findById($goalMemberId);
        if (!Hash::get($goalMember, 'GoalMember')) {
            return;
        }
        $this->NotifyBiz->execSendNotify($notifyType,
            $goalMember['GoalMember']['goal_id'],
            null,
            $goalMember['GoalMember']['user_id']
        );
    }

}
