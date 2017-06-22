<?php
App::uses('AppController', 'Controller');
App::uses('Post', 'Model');
App::uses('AppUtil', 'Util');
App::import('Service', 'GoalService');
App::import('Service', 'UserService');
App::import('Service', 'TermService');

/**
 * Users Controller
 *
 * @property User           $User
 * @property Invite         $Invite
 * @property Circle         $Circle
 * @property TwoFaComponent $TwoFa
 */
class UsersController extends AppController
{
    public $uses = [
        'User',
        'Invite',
        'Circle',
    ];
    public $components = [
        'TwoFa',
    ];

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('register', 'login', 'verify', 'logout', 'password_reset', 'token_resend', 'sent_mail',
            'accept_invite', 'register_with_invite', 'registration_with_set_password', 'two_fa_auth',
            'two_fa_auth_recovery',
            'add_subscribe_email', 'ajax_validate_email');
    }

    /**
     * Common login action
     *
     * @return void
     */
    public function login()
    {
        $this->layout = LAYOUT_ONE_COLUMN;

        if ($this->Auth->user()) {
            return $this->redirect('/');
        }

        if (!$this->request->is('post')) {
            return $this->render();
        }

        //account lock check
        $ip_address = $this->request->clientIp();
        $is_account_locked = $this->GlRedis->isAccountLocked($this->request->data['User']['email'], $ip_address);
        if ($is_account_locked) {
            $this->Pnotify->outError(__("Your account is tempolary locked. It will be unlocked after %s mins.",
                ACCOUNT_LOCK_TTL / 60));
            return $this->render();
        }
        //メアド、パスの認証(セッションのストアはしていない)
        $user_info = $this->Auth->identify($this->request, $this->response);
        if (!$user_info) {
            $this->Pnotify->outError(__("Email address or Password is incorrect."));
            return $this->render();
        }
        $this->Session->write('preAuthPost', $this->request->data);

        //デバイス情報を保存する
        $user_id = $user_info['id'];
        $installation_id = $this->request->data['User']['installation_id'];
        if ($installation_id == "no_value") {
            $installation_id = null;
        }
        $app_version = $this->request->data['User']['app_version'];
        if ($app_version == "no_value") {
            $app_version = null;
        }
        if (!empty($installation_id)) {
            try {
                $this->NotifyBiz->saveDeviceInfo($user_id, $installation_id, $app_version);
                //セットアップガイドステータスの更新
                $this->updateSetupStatusIfNotCompleted();
            } catch (RuntimeException $e) {
                $this->log([
                    'where'           => 'login page',
                    'error_msg'       => $e->getMessage(),
                    'user_id'         => $user_id,
                    'installation_id' => $installation_id,
                ]);
            }
        }

        $is_2fa_auth_enabled = true;
        // 2要素認証設定OFFの場合
        // 2要素認証設定ONかつ、設定して30日以内の場合
        if ((is_null($user_info['2fa_secret']) === true) || (empty($user_info['2fa_secret']) === false
                && $this->GlRedis->isExistsDeviceHash($user_info['DefaultTeam']['id'], $user_info['id']))
        ) {
            $is_2fa_auth_enabled = false;
        }

        //２要素設定有効なら
        if ($is_2fa_auth_enabled) {
            $this->Session->write('2fa_secret', $user_info['2fa_secret']);
            $this->Session->write('user_id', $user_info['id']);
            $this->Session->write('team_id', $user_info['DefaultTeam']['id']);
            return $this->redirect(['action' => 'two_fa_auth']);
        }

        return $this->_afterAuthSessionStore();
    }

    function two_fa_auth()
    {
        if ($this->Auth->user()) {
            return $this->redirect($this->referer());
        }
        $this->layout = LAYOUT_ONE_COLUMN;
        //仮認証状態か？そうでなければエラー出してリファラリダイレクト
        $is_avail_auth = !empty($this->Session->read('preAuthPost')) ? true : false;
        if (!$is_avail_auth) {
            $this->Pnotify->outError(__("Error. Try to login again."));
            return $this->redirect(['action' => 'login']);
        }

        if (!$this->request->is('post')) {
            return $this->render();
        }

        $is_account_locked = $this->GlRedis->isTwoFaAccountLocked($this->Session->read('user_id'),
            $this->request->clientIp());
        if ($is_account_locked) {
            $this->Pnotify->outError(__("Your account is tempolary locked. It will be unlocked after %s mins.",
                ACCOUNT_LOCK_TTL / 60));
            return $this->render();
        }

        if ((empty($this->Session->read('2fa_secret')) === false && empty($this->request->data['User']['two_fa_code']) === false)
            && $this->TwoFa->verifyKey($this->Session->read('2fa_secret'),
                $this->request->data['User']['two_fa_code']) === true
        ) {
            $this->GlRedis->saveDeviceHash($this->Session->read('team_id'), $this->Session->read('user_id'));
            return $this->_afterAuthSessionStore();

        } else {
            $this->Pnotify->outError(__("Incorrect 2fa code."));
            return $this->render();
        }
    }

    /**
     * リカバリコード入力画面
     *
     * @return CakeResponse|void
     */
    function two_fa_auth_recovery()
    {
        if ($this->Auth->user()) {
            return $this->redirect($this->referer());
        }
        $this->layout = LAYOUT_ONE_COLUMN;
        //仮認証状態か？そうでなければエラー出してリファラリダイレクト
        $is_avail_auth = !empty($this->Session->read('preAuthPost')) ? true : false;
        if (!$is_avail_auth) {
            $this->Pnotify->outError(__("Error. Try to login again."));
            return $this->redirect(['action' => 'login']);
        }

        if (!$this->request->is('post')) {
            return $this->render();
        }

        $is_account_locked = $this->GlRedis->isTwoFaAccountLocked($this->Session->read('user_id'),
            $this->request->clientIp());
        if ($is_account_locked) {
            $this->Pnotify->outError(__("Your account is tempolary locked. It will be unlocked after %s mins.",
                ACCOUNT_LOCK_TTL / 60));
            return $this->render();
        }

        // 入力されたコードが利用可能なリカバリーコードか確認
        $code = str_replace(' ', '', $this->request->data['User']['recovery_code']);
        $row = $this->User->RecoveryCode->findUnusedCode($this->Session->read('user_id'), $code);
        if (!$row) {
            $this->Pnotify->outError(__("Incorrect recovery code."));
            return $this->render();
        }

        // コードを使用済にする
        $res = $this->User->RecoveryCode->useCode($row['RecoveryCode']['id']);
        if (!$res) {
            $this->Pnotify->outError(__("An error has occurred."));
            return $this->render();
        }

        $this->GlRedis->saveDeviceHash($this->Session->read('team_id'), $this->Session->read('user_id'));
        return $this->_afterAuthSessionStore();
    }

    function _afterAuthSessionStore()
    {
        $redirect_url = ($this->Session->read('Auth.redirect')) ? $this->Session->read('Auth.redirect') : "/";
        $this->request->data = $this->Session->read('preAuthPost');
        if ($this->Auth->login()) {
            $this->Session->delete('preAuthPost');
            $this->Session->delete('2fa_secret');
            $this->Session->delete('user_id');
            $this->Session->delete('team_id');
            if ($this->Session->read('referer_status') === REFERER_STATUS_INVITED_USER_EXIST) {
                $this->Session->write('referer_status', REFERER_STATUS_INVITED_USER_EXIST);
            } else {
                $this->Session->write('referer_status', REFERER_STATUS_LOGIN);
            }
            $this->_refreshAuth();
            $this->_setAfterLogin();
            $this->Pnotify->outSuccess(__("Hello %s.", $this->Auth->user('display_username')),
                ['title' => __("Succeeded to login")]);
            return $this->redirect($redirect_url);
        } else {
            $this->Pnotify->outError(__("Error. Try to login again."));
            return $this->redirect(['action' => 'login']);
        }

    }

    /**
     * Common logout action
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->Auth->user();
        foreach ($this->Session->read() as $key => $val) {
            if (in_array($key, ['Config', '_Token', 'Auth'])) {
                continue;
            }
            $this->Session->delete($key);
        }
        $this->Cookie->destroy();
        $this->Pnotify->outInfo(__("See you %s", $user['display_username']),
            ['title' => __("Logged out")]);
        return $this->redirect($this->Auth->logout());
    }

    /**
     * ユーザー登録兼チームジョイン
     * - このメソッドは未登録ユーザーがチーム招待された場合にだけ呼ばれる
     * - ここで処理するチーム招待の種類は以下二つ。
     *  - CSVによる招待(ユーザー仮登録状態)
     *  - メールによる招待
     * - この中で呼ばれる_joinTeam()メソッド内でトランザクションを張っている
     * TODO: このメソッド中のユーザー登録処理にてトランザクションが張られていないため、
     *       チームジョインが失敗した際のユーザー情報ロールバック処理をベタ書きしてしまっている。
     *       ユーザー登録/チーム参加処理のリファクタとトランザクション処理の追加実装が必要。
     *
     * @return
     */
    public function register_with_invite()
    {
        $step = isset($this->request->params['named']['step']) ? (int)$this->request->params['named']['step'] : 1;
        if (!($step === 1 or $step === 2)) {
            $this->Pnotify->outError(__('Invalid access'));
            return $this->redirect('/');
        }

        $profileTemplate = 'register_prof_with_invite';
        $passwordTemplate = 'register_password_with_invite';

        $this->layout = LAYOUT_ONE_COLUMN;

        // トークンチェック
        try {
            // トークン存在チェック
            if (!isset($this->request->params['named']['invite_token'])) {
                throw new Exception(sprintf("The invitation token is not exist. params: %s"
                    , var_export($this->request->params, true)));
            }
            //トークンが有効かチェック
            $confirmRes = $this->Invite->confirmToken($this->request->params['named']['invite_token']);
            if ($confirmRes !== true) {
                throw new Exception(sprintf("The invitation token is not available. confirmMessage: %s"
                    , var_export($confirmRes, true)));
            }
        } catch (RuntimeException $e) {
            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
            $this->Pnotify->outError(__("The invitation token is incorrect. Check your email again."));
            return $this->redirect('/');
        }

        $invite = $this->Invite->getByToken($this->request->params['named']['invite_token']);
        $team = $this->Team->findById($invite['Invite']['team_id']);
        $this->set('team_name', $team['Team']['name']);

        //batch case
        if ($user = $this->User->getUserByEmail($invite['Invite']['email'])) {
            // Set user info to view value
            $this->set('first_name', $user['User']['first_name']);
            $this->set('last_name', $user['User']['last_name']);
            $this->set('birth_day', $user['User']['birth_day']);
        }

        if (!$this->request->is('post')) {
            if ($step === 2) {
                return $this->render($passwordTemplate);
            }
            return $this->render($profileTemplate);
        }

        //Sessionに保存してパスワード入力画面に遷移
        if ($step === 1) {
            //プロフィール入力画面の場合
            //validation
            if ($this->User->validates($this->request->data)) {
                //store to session
                $this->Session->write('data', $this->request->data);
                //パスワード入力画面にリダイレクト
                return $this->redirect(
                    [
                        'action'       => 'register_with_invite',
                        'step'         => 2,
                        'invite_token' => $this->request->params['named']['invite_token']
                    ]);
            } else {
                //エラーメッセージ
                $this->Pnotify->outError(__('Failed to save data.'));
                return $this->render($profileTemplate);
            }
        }
        //パスワード入力画面の場合

        //session存在チェック
        if (!$this->Session->read('data')) {
            $this->Pnotify->outError(__('Invalid access'));
            return $this->redirect('/');
        }

        //sessionデータとpostのデータとマージ
        $data = Hash::merge($this->Session->read('data'), $this->request->data);
        //batch case
        if ($user) {
            $userId = $user['User']['id'];

            // Disabled user email validation
            // Because in batch case, email is already registered
            $email = $this->User->Email->getNotVerifiedEmail($userId);
            $emailFromEmailTable = Hash::get($email, 'Email.email');
            $emailFromInviteTable = $invite['Invite']['email'];
            if ($emailFromEmailTable === $emailFromInviteTable) {
                unset($this->User->Email->validate['email']);
            }
            // Set user info to register data
            $data['User']['id'] = $userId;
            $data['User']['no_pass_flg'] = false;
            $data['Email'][0]['Email']['id'] = $email['Email']['id'];
        }
        //email
        $data['Email'][0]['Email']['email'] = $invite['Invite']['email'];
        //タイムゾーンをセット
        if (isset($data['User']['local_date'])) {
            //ユーザのローカル環境から取得したタイムゾーンをセット
            $data['User']['timezone'] = AppUtil::getClientTimezone($data['User']['local_date']);
            //自動タイムゾーン設定フラグをoff
            $data['User']['auto_timezone_flg'] = false;
        }
        //言語を保存
        $data['User']['language'] = $this->Lang->getLanguage();
        //デフォルトチームを設定
        $data['User']['default_team_id'] = $team['Team']['id'];

        // ユーザ本登録
        if (!$this->User->userRegistration($data)) {
            return $this->render($passwordTemplate);
        }
        //ログイン
        $userId = $this->User->getLastInsertID() ? $this->User->getLastInsertID() : $userId;
        $this->_autoLogin($userId, true);
        // flash削除
        // csvによる招待のケースで_authLogin()の処理中に例外メッセージが吐かれるため、
        // 一旦ここで例外メッセージを表示させないためにFlashメッセージをremoveする
        $this->Session->delete('Message.pnotify');

        //チーム参加
        $invitedTeam = $this->_joinTeam($this->request->params['named']['invite_token']);
        if ($invitedTeam === false) {
            // HACK: _joinTeamでチーム参加処理に失敗した場合、どのチームにも所属していないユーザーが存在してしまうことになる。
            //       したがってここでuserとemailレコードを明示的に削除している。
            //       ただ本来はここですべき処理じゃない。ユーザー登録処理とチームジョイン処理でトランザクションを張るべきである。
            $this->Auth->logout();
            $this->User->delete($userId);
            $this->User->Email->deleteAll(['Email.user_id' => $userId], $cascade = false);
            $this->Pnotify->outError(__("Failed to register user. Please try again later."));
            return $this->redirect("/");
        }

        //ホーム画面でモーダル表示
        $this->Session->write('add_new_mode', MODE_NEW_PROFILE);
        //top画面に遷移
        return $this->redirect('/');
    }

    /**
     * 新規プロフィール入力
     */
    public function add_profile()
    {
        $this->layout = LAYOUT_ONE_COLUMN;

        //新規ユーザ登録モードじゃない場合は４０４
        if ($this->Session->read('add_new_mode') !== MODE_NEW_PROFILE) {
            throw new NotFoundException;
        }
        $me = $this->Auth->user();

        //ローカル名を利用している国かどうか？
        $is_not_use_local_name = $this->User->isNotUseLocalName($me['language']);

        // リクエストデータが無い場合は入力画面を表示
        if (!$this->request->is('put')) {
            $this->request->data = ['User' => $me];
            $language_name = $this->Lang->availableLanguages[$me['language']];
            $this->set(compact('me', 'is_not_use_local_name', 'language_name'));
            return $this->render();
        }

        //ローカル名の入力が無い場合は除去
        if (isset($this->request->data['LocalName'])) {
            $local_name = $this->request->data['LocalName'][0];
            if (!$local_name['first_name'] || !$local_name['last_name']) {
                unset($this->request->data['LocalName']);
            }
        }

        // プロフィールを保存
        $this->User->id = $me['id'];
        $isSavedSuccess = $this->User->saveAll($this->request->data);

        // 保存失敗
        if (!$isSavedSuccess) {
            $language_name = $this->Lang->availableLanguages[$me['language']];

            $this->set(compact('me', 'is_not_use_local_name', 'language_name'));
            return $this->render();
        }

        // 保存成功
        $this->_refreshAuth($me['id']);

        //トークン付きの場合は招待のため、ホームへ
        if (isset($this->request->params['named']['invite_token'])) {
            $this->Session->write('referer_status', REFERER_STATUS_INVITATION_NOT_EXIST);
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->redirect("/?st=" . REFERER_STATUS_INVITATION_NOT_EXIST);
        } else {
            //チーム作成ページへリダイレクト
            $this->Session->write('referer_status', REFERER_STATUS_SIGNUP);
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->redirect(['controller' => 'teams', 'action' => 'add']);
        }

    }

    /**
     * 承認メール送信後の画面
     */
    public function sent_mail()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        if ($this->Session->read('tmp_email')) {
            $this->set(['email' => $this->Session->read('tmp_email')]);
            $this->Session->delete('tmp_email');
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * Confirm email action
     *
     * @param string $token Token
     *
     * @throws RuntimeException
     * @return void
     */
    public function verify($token = null)
    {
        try {
            $user = $this->User->verifyEmail($token);
            $last_login = null;
            if ($user) {
                //ログイン済か確認
                $last_login = $user['User']['last_login'];
                //自動ログイン
                $this->_autoLogin($user['User']['id']);
            }
            if (!$last_login) {
                //ログインがされていなければ、新規ユーザなので「ようこそ」表示
                $this->Pnotify->outSuccess(__('Succeeded to register!'));
                //新規ユーザ登録時のフロー
                $this->Session->write('add_new_mode', MODE_NEW_PROFILE);
                /** @noinspection PhpInconsistentReturnPointsInspection */
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                //新規プロフィール入力画面へ
                return $this->redirect(['action' => 'add_profile']);
            } else {
                //ログインされていれば、メール追加
                $this->Pnotify->outSuccess(__('Authenticated your email address.'));
                /** @noinspection PhpInconsistentReturnPointsInspection */
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                return $this->redirect('/');
            }
        } catch (RuntimeException $e) {
            //例外の場合は、トークン再送信画面へ
            $this->Pnotify->outError($e->getMessage());
            //トークン再送メージへ
            return $this->redirect(['action' => 'token_resend']);
        }
    }

    /**
     * メールアドレス変更時の認証
     *
     * @param $token
     */
    public function change_email_verify($token)
    {
        try {
            $this->User->begin();
            $user = $this->User->verifyEmail($token, $this->Auth->user('id'));
            $this->User->changePrimaryEmail($this->Auth->user('id'), $user['Email']['id']);
        } catch (RuntimeException $e) {
            $this->User->rollback();
            //例外の場合は、トークン再送信画面へ
            $this->Pnotify->outError($e->getMessage() . "\n" . __("Please cancel changing email address and try again."));
            //トークン再送ページへ
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->redirect(['action' => 'settings']);
        }
        $this->User->commit();
        $this->_autoLogin($this->Auth->user('id'));
        $this->Pnotify->outSuccess(__("Email address is changed."));
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect(['action' => 'settings']);
    }

    /**
     * Password reset
     *
     * @param null $token
     *
     * @return CakeResponse|void
     */
    public function password_reset($token = null)
    {
        if ($this->Auth->user()) {
            throw new NotFoundException();
        }

        $this->layout = LAYOUT_ONE_COLUMN;

        if (!$token) {
            if (!$this->request->is('post')) {
                return $this->render('password_reset_request');
            }

            // Search user
            $user = $this->User->passwordResetPre($this->request->data);
            if ($user) {
                // Send mail containing token
                $this->GlEmail->sendMailPasswordReset($user['User']['id'], $user['User']['password_token']);
                $this->Pnotify->outSuccess(__("Password reset email has been sent. Please check your email."),
                    ['title' => __("Email sent.")]);
            }
            return $this->render('password_reset_request');
        }

        // Token existing case
        $user_email = $this->User->checkPasswordToken($token);

        if (!$user_email) {
            $this->Pnotify->outError(__("Password code incorrect. The validity period may have expired. Please resend email again."),
                ['title' => __("Failed to confirm code.")]);
            return $this->redirect(['action' => 'password_reset']);
        }

        if (!$this->request->is('post')) {
            return $this->render('password_reset');
        }

        $successPasswordReset = $this->User->passwordReset($user_email, $this->request->data);
        if ($successPasswordReset) {
            // Notify to user reset password
            $this->GlEmail->sendMailCompletePasswordReset($user_email['User']['id']);
            $this->Pnotify->outSuccess(__("Please login with your new password."),
                ['title' => __('Password is set.')]);
            return $this->redirect(['action' => 'login']);
        }
        return $this->render('password_reset');

    }

    public function token_resend()
    {
        if ($this->Auth->user()) {
            throw new NotFoundException();
        }
        $this->layout = LAYOUT_ONE_COLUMN;
        if ($this->request->is('post') && !empty($this->request->data)) {
            //パスワード認証情報登録成功した場合
            if ($email_user = $this->User->saveEmailToken($this->request->data['User']['email'])) {
                //メールでトークンを送信
                $this->GlEmail->sendMailEmailTokenResend($email_user['User']['id'],
                    $email_user['Email']['email_token']);
                $this->Pnotify->outSuccess(__("Confirmation has been sent to your email address."),
                    ['title' => __("Send you an email.")]);
            }
        }
    }

    /**
     * ユーザ設定
     */
    public function settings()
    {
        //ユーザデータ取得
        $me = $this->_getMyUserDataForSetting();
        if ($this->request->is('put')) {
            //キャッシュ削除
            Cache::delete($this->User->getCacheKey(CACHE_KEY_MY_NOTIFY_SETTING, true, null, false), 'user_data');
            Cache::delete($this->User->getCacheKey(CACHE_KEY_MY_PROFILE, true, null, false), 'user_data');

            // Specify update user
            $this->request->data['User']['id'] = $me['User']['id'];

            // ローカル名 更新時
            if (isset($this->request->data['LocalName'][0])) {
                // すでにレコードが存在する場合は、id をセット（update にする)
                $row = $this->User->LocalName->getName($this->Auth->user('id'),
                    $this->request->data['LocalName'][0]['language']);
                if ($row) {
                    $this->request->data['LocalName'][0]['id'] = $row['LocalName']['id'];
                }
            }

            // 通知設定 更新時
            if (isset($this->request->data['NotifySetting']['email']) &&
                isset($this->request->data['NotifySetting']['mobile'])
            ) {
                $this->request->data['NotifySetting'] =
                    array_merge($this->request->data['NotifySetting'],
                        $this->User->NotifySetting->getSettingValues('app', 'all'));
                $this->request->data['NotifySetting'] =
                    array_merge($this->request->data['NotifySetting'],
                        $this->User->NotifySetting->getSettingValues('email',
                            $this->request->data['NotifySetting']['email']));
                $this->request->data['NotifySetting'] =
                    array_merge($this->request->data['NotifySetting'],
                        $this->User->NotifySetting->getSettingValues('mobile',
                            $this->request->data['NotifySetting']['mobile']));
            }
            $this->User->id = $this->Auth->user('id');
            //ユーザー情報更新
            //チームメンバー情報を付与
            if ($this->User->saveAll($this->request->data)) {
                //ログインし直し。
                $this->_autoLogin($this->Auth->user('id'), true);
                //言語設定
                $this->_setAppLanguage();
                //セットアップガイドステータスの更新
                $this->updateSetupStatusIfNotCompleted();

                // update message search keywords by user id
                ClassRegistry::init('TopicSearchKeyword')->updateByUserId($this->Auth->user('id'));

                $this->Pnotify->outSuccess(__("Saved user setting."));
                $this->redirect('/users/settings');
            } else {
                $this->Pnotify->outError(__("Failed to save user setting."));
            }
            $me = $this->_getMyUserDataForSetting();
            // For updating header user info
            $this->set('my_prof', $this->User->getMyProf());
        }

        $this->request->data = $me;

        $this->layout = LAYOUT_TWO_COLUMN;
        //姓名の並び順をセット
        $lastFirst = in_array($me['User']['language'], $this->User->langCodeOfLastFirst);
        //言語選択
        $language_list = $this->Lang->getAvailLangList();
        //タイムゾーン
        $timezones = AppUtil::getTimezoneList();
        //ローカル名を利用している国かどうか？
        $is_not_use_local_name = $this->User->isNotUseLocalName($me['User']['language']);
        $not_verified_email = $this->User->Email->getNotVerifiedEmail($this->Auth->user('id'));
        $language_name = $this->Lang->availableLanguages[$me['User']['language']];

        // 通知設定のプルダウンデフォルト
        $this->request->data['NotifySetting']['email'] = 'all';
        $this->request->data['NotifySetting']['mobile'] = 'all';
        // 既に通知設定が保存されている場合
        foreach (['email', 'mobile'] as $notify_target) {
            foreach (array_keys(NotifySetting::$TYPE_GROUP) as $type_group) {
                $values = $this->User->NotifySetting->getSettingValues($notify_target, $type_group);
                $same = true;
                foreach ($values as $k => $v) {
                    if ($this->request->data['NotifySetting'][$k] !== $v) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    $this->request->data['NotifySetting'][$notify_target] = $type_group;
                    break;
                }
            }
        }
        $this->set(compact('me', 'is_not_use_local_name', 'lastFirst', 'language_list', 'timezones',
            'not_verified_email', 'local_name', 'language_name'));
        return $this->render();
    }

    private function _getMyUserDataForSetting()
    {
        $me = $this->User->getDetail($this->Auth->user('id'));
        unset($me['User']['password']);
        $local_name = $this->User->LocalName->getName($this->Auth->user('id'), $this->Auth->user('language'));
        if (isset($local_name['LocalName'])) {
            $me['LocalName'][0] = $local_name['LocalName'];
        }

        return $me;
    }

    /**
     * パスワード変更
     *
     * @throws NotFoundException
     */
    public function change_password()
    {
        if (!$this->request->is('put')) {
            throw new NotFoundException();
        }

        try {
            $this->User->changePassword($this->request->data);
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage(), ['title' => __("Failed to save password change.")]);
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->redirect($this->referer());
        }
        $this->Pnotify->outSuccess(__("Changed password."));

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect($this->referer());
    }

    /**
     *
     */
    public function change_email()
    {
        if (!$this->request->is('put')) {
            throw new NotFoundException();
        }

        try {
            $email_data = $this->User->addEmail($this->request->data, $this->Auth->user('id'));
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->redirect($this->referer());
        }

        $this->Pnotify->outInfo(__("Confirmation has been sent to your email address."));
        $this->GlEmail->sendMailChangeEmailVerify($this->Auth->user('id'), $email_data['Email']['email'],
            $email_data['Email']['email_token']);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect($this->referer());
    }

    /**
     * 招待に応じる
     * - 登録済みユーザの場合は、チーム参加でホームへリダイレクト
     * - 未登録ユーザの場合は、個人情報入力ページ(register_with_invite)へ
     * - この中で呼ばれる_joinTeam()メソッド内でトランザクションを張っている
     *
     * @param $token
     */
    public function accept_invite($token)
    {
        // トークンが有効かどうかチェック
        $confirmRes = $this->Invite->confirmToken($token);
        if ($confirmRes !== true) {
            $this->Pnotify->outError($confirmRes);
            return $this->redirect("/");
        }

        // メール招待かつ未登録ユーザーの場合
        if (!$this->Invite->isUser($token)) {
            $this->Session->write('referer_status', REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_EMAIL);
            return $this->redirect(['action' => 'register_with_invite', 'invite_token' => $token]);
        }

        // CSV招待かつ未(仮)登録ユーザー場合
        if ($this->Invite->isUserPreRegistered($token)) {
            $this->Session->write('referer_status', REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_CSV);
            return $this->redirect(['action' => 'register_with_invite', 'invite_token' => $token]);
        }

        // 登録済みユーザーかつ未ログインの場合はログイン画面へ
        if (!$this->Auth->user()) {
            $this->Auth->redirectUrl(['action' => 'accept_invite', $token]);
            $this->Session->write('referer_status', REFERER_STATUS_INVITED_USER_EXIST);
            return $this->redirect(['action' => 'login']);
        }

        // トークンが自分用に生成されたもうのかどうかチェック
        if (!$this->Invite->isForMe($token, $this->Auth->user('id'))) {
            $this->Pnotify->outError(__("This invitation isn't not for you."));
            return $this->redirect("/");
        }

        // ユーザーがログイン中でかつチームジョインが失敗した場合、
        // ログインしていたチームのセッションに戻す必要があるためここでチームIDを退避させる
        $loggedInTeamId = $this->Auth->user('current_team_id');
        $invitedTeam = $this->_joinTeam($token);
        if ($invitedTeam === false) {
            if ($loggedInTeamId) {
                $this->_switchTeam($loggedInTeamId);
            }
            $this->Pnotify->outError(__("Failed to join team. Please try again later."));
            return $this->redirect("/");
        }

        $this->Session->write('referer_status', REFERER_STATUS_INVITED_USER_EXIST);
        $this->Pnotify->outSuccess(__("Joined %s.", $invitedTeam['Team']['name']));
        return $this->redirect("/");
    }

    /**
     * select2のユーザ検索
     */
    function ajax_select2_get_users()
    {
        $this->_ajaxPreProcess();
        $query = $this->request->query;
        $res = ['results' => []];
        if (isset($query['term']) && !empty($query['term']) && count($query['term']) <= SELECT2_QUERY_LIMIT && isset($query['page_limit']) && !empty($query['page_limit'])) {
            $with_group = (isset($query['with_group']) && $query['with_group']);
            $res = $this->User->getUsersSelect2($query['term'], $query['page_limit'], $with_group);
        }
        return $this->_ajaxGetResponse($res);
    }

    /**
     * select2用に加工したユーザ情報を取得
     *
     * @param $userId
     *
     * @return CakeResponse|null
     */
    function ajax_select2_get_user_detail($userId)
    {
        if (empty($userId) || !is_numeric($userId)) {
            return $this->_ajaxGetResponse([]);
        }
        // ユーザ詳細情報取得
        $user = $this->User->getDetail($userId);
        if (empty($user)) {
            return $this->_ajaxGetResponse([]);
        }
        // レスポンス用にユーザ詳細情報を加工
        $res = $this->User->makeSelect2User($user);
        return $this->_ajaxGetResponse($res);
    }

    /**
     * search users for adding users in message select2
     */
    function ajax_select_add_members_on_message()
    {
        $this->_ajaxPreProcess();

        $query = $this->request->query;
        $res = ['results' => []];
        $existparameters = !empty($query['topic_id']) && !empty($query['term']) && !empty($query['page_limit']);
        if ($existparameters) {
            /** @var UserService $UserService */
            $UserService = ClassRegistry::init('UserService');
            $res['results'] = $UserService->findUsersForAddingOnTopic($query['term'], $query['page_limit'],
                $query['topic_id'], true);
        }
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_modal_2fa_register()
    {
        $this->_ajaxPreProcess();
        if ($this->Session->read('2fa_secret_key')) {
            $google_2fa_secret_key = $this->Session->read('2fa_secret_key');
        } else {
            $google_2fa_secret_key = $this->TwoFa->generateSecretKey();
            $this->Session->write('2fa_secret_key', $google_2fa_secret_key);
        }

        $url_2fa = $this->TwoFa->getQRCodeGoogleUrl(SERVICE_NAME,
            $this->Session->read('Auth.User.PrimaryEmail.email'),
            $google_2fa_secret_key);
        $this->set(compact('url_2fa'));
        $response = $this->render('User/modal_2fa_register');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function register_2fa()
    {
        $this->request->allowMethod('post');
        try {
            if (!$secret_key = $this->Session->read('2fa_secret_key')) {
                throw new RuntimeException(__("An error has occurred."));
            }
            if (!Hash::get($this->request->data, 'User.2fa_code')) {
                throw new RuntimeException(__("An error has occurred."));
            }
            if (!$this->TwoFa->verifyKey($secret_key, $this->request->data['User']['2fa_code'])) {
                throw new RuntimeException(__("The code is incorrect."));
            }
            //2要素認証コードの登録
            $this->User->id = $this->Auth->user('id');
            $this->User->saveField('2fa_secret', $secret_key);
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            return $this->redirect($this->referer());
        }
        $this->Session->delete('2fa_secret_key');
        $this->Mixpanel->track2SV(MixpanelComponent::TRACK_2SV_ENABLE);
        $this->Pnotify->outSuccess(__("Succeeded to save 2-Step Verification."));
        $this->Flash->set(null,
            ['element' => 'flash_click_event', 'params' => ['id' => 'ShowRecoveryCodeButton'], 'key' => 'click_event']);
        return $this->redirect($this->referer());
    }

    function ajax_get_modal_2fa_delete()
    {
        $this->_ajaxPreProcess();
        $response = $this->render('User/modal_2fa_delete');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function delete_2fa()
    {
        $this->request->allowMethod('post');
        $this->User->id = $this->Auth->user('id');
        $this->User->saveField('2fa_secret', null);
        $this->User->RecoveryCode->invalidateAll($this->User->id);
        if (empty($this->Auth->user('DefaultTeam.id')) === false && empty($this->Auth->user('id')) === false) {
            $this->GlRedis->deleteDeviceHash($this->Auth->user('DefaultTeam.id'), $this->Auth->user('id'));
        }
        $this->Mixpanel->track2SV(MixpanelComponent::TRACK_2SV_DISABLE);
        $this->Pnotify->outSuccess(__("Succeeded to cancel 2-Step Verification."));
        return $this->redirect($this->referer());
    }

    /**
     * リカバリコードを表示
     *
     * @return CakeResponse
     */
    function ajax_get_modal_recovery_code()
    {
        $this->_ajaxPreProcess();
        $recovery_codes = $this->User->RecoveryCode->getAll($this->Auth->user('id'));
        if (!$recovery_codes) {
            $success = $this->User->RecoveryCode->regenerate($this->Auth->user('id'));
            if (!$success) {
                throw new NotFoundException();
            }
            $recovery_codes = $this->User->RecoveryCode->getAll($this->Auth->user('id'));
        }
        $this->set('recovery_codes', $recovery_codes);
        $response = $this->render('User/modal_recovery_code');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    /**
     * リカバリコードを再生成
     */
    function ajax_regenerate_recovery_code()
    {
        $this->_ajaxPreProcess();
        $this->request->allowMethod('post');

        $success = $this->User->RecoveryCode->regenerate($this->Auth->user('id'));
        if (!$success) {
            return $this->_ajaxGetResponse([
                'error' => true,
                'msg'   => __("An error has occurred.")
            ]);
        }
        $recovery_codes = $this->User->RecoveryCode->getAll($this->Auth->user('id'));
        $codes = array_map(function ($v) {
            return $v['RecoveryCode']['code'];
        }, $recovery_codes);
        return $this->_ajaxGetResponse([
            'error' => false,
            'msg'   => __("Generated new recovery codes."),
            'codes' => $codes
        ]);
    }

    /**
     * select2のユーザ検索
     */
    function ajax_select2_get_circles_users()
    {
        $this->_ajaxPreProcess();
        $query = $this->request->query;
        $res = [];
        if (Hash::get($query, 'term') && Hash::get($query, 'page_limit') && Hash::get($query, 'circle_type')) {
            $res = $this->User->getUsersCirclesSelect2($query['term'], $query['page_limit'], $query['circle_type'],
                true);
        }
        return $this->_ajaxGetResponse($res);
    }

    /**
     * select2の非公開サークル検索
     */
    function ajax_select2_get_secret_circles()
    {
        $this->_ajaxPreProcess();
        $query = $this->request->query;
        $res = [];
        if (Hash::get($query, 'term') && Hash::get($query, 'page_limit')) {
            $res = $this->User->getSecretCirclesSelect2($query['term'], $query['page_limit']);
        }
        return $this->_ajaxGetResponse($res);
    }

    /**
     * チームに参加
     * - 一連の処理にトランザクションを使用。
     *
     * @param $token
     */
    function _joinTeam($token)
    {
        try {
            $this->User->begin();

            //トークン認証
            $confirmRes = $this->Invite->confirmToken($token);
            if ($confirmRes !== true) {
                throw new Exception(sprintf("Failed to confirm token. token:%s errorMsg: %s"
                    , var_export($token, true), $confirmRes));
            }

            $userId = $this->Auth->user('id');
            $invite = $this->Invite->verify($token, $userId);

            $inviteTeamId = Hash::get($invite, 'Invite.team_id');

            //チーム参加
            if (!$this->User->TeamMember->add($userId, $inviteTeamId)) {
                $validationErrors = $ExperimentService->validationExtract($this->User->TeamMember->validationErrors);
                throw new Exception(sprintf("Failed to confirm token. userId:%s teamId:%s validationErrors:%s"
                    , $userId, $inviteTeamId, var_export($validationErrors, true)));
            }

            //セッション更新
            $this->_refreshAuth();

            //チーム切換え
            $this->_switchTeam($inviteTeamId);

            // Circle と CircleMember の current_team_id を一時的に変更
            $currentTeamId = $this->Circle->current_team_id;
            $this->Circle->current_team_id = $inviteTeamId;
            $this->Circle->CircleMember->current_team_id = $inviteTeamId;
            $teamAllCircle = $this->Circle->getTeamAllCircle();

            // 「チーム全体」サークルに追加
            App::import('Service', 'CircleService');
            /** @var ExperimentService $ExperimentService */
            $CircleService = ClassRegistry::init('CircleService');
            $circleId = $teamAllCircle['Circle']['id'];
            if (!$CircleService->join($circleId, $userId)) {
                $validationErrors = $ExperimentService->validationExtract($this->Circle->CircleMember->validationErrors);
                throw new Exception(sprintf("Failed to join all team circle. userId:%s circleId:%s validationErrors:%s"
                    , $userId, $circleId, var_export($validationErrors, true)));
            }

            $this->Circle->current_team_id = $currentTeamId;
            $this->Circle->CircleMember->current_team_id = $currentTeamId;
        } catch (Exception $e) {
            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
            $this->User->rollback();
            return false;
        }

        $this->User->commit();

        //cache削除
        Cache::delete($this->Circle->CircleMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, null, false), 'team_info');

        //招待者に通知
        $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_USER_JOINED_TO_INVITED_TEAM, $invite['Invite']['id']);

        $invitedTeam = $this->User->TeamMember->Team->findById($inviteTeamId);
        return $invitedTeam;
    }

    public function ajax_get_user_detail($user_id)
    {
        $this->_ajaxPreProcess();
        $user_detail = $this->User->getDetail($user_id);
        return $this->_ajaxGetResponse($user_detail);
    }

    public function ajax_get_select2_circle_user_all()
    {
        $result = $this->User->getAllUsersCirclesSelect2();
        $this->_ajaxPreProcess();
        return $this->_ajaxGetResponse($result);
    }

    /**
     * メールアドレスが登録可能なものか確認
     *
     * @return CakeResponse
     */
    public function ajax_validate_email()
    {
        $this->_ajaxPreProcess();
        $email = $this->request->query('email');
        $valid = false;
        $message = '';
        if ($email) {
            // メールアドレスだけ validate
            $this->User->Email->create(['email' => $email]);
            $this->User->Email->validates(['fieldList' => ['email']]);
            if ($this->User->Email->validationErrors) {
                $message = $this->User->Email->validationErrors['email'][0];
            } else {
                $valid = true;
            }
        }
        return $this->_ajaxGetResponse([
            'valid'   => $valid,
            'message' => $message
        ]);
    }

    function view_goals()
    {
        /** @var GoalService $GoalService */
        $GoalService = ClassRegistry::init("GoalService");

        $namedParams = $this->request->params['named'];

        $userId = Hash::get($namedParams, "user_id");
        if (!$userId || !$this->_setUserPageHeaderInfo($userId)) {
            // ユーザーが存在しない
            $this->Pnotify->outError(__("Invalid screen transition."));
            return $this->redirect($this->referer());
        }
        $this->layout = LAYOUT_ONE_COLUMN;
        $pageType = Hash::get($namedParams, 'page_type');

        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        $termFilterOptions = $TermService->getFilterMenu();

        /** @var Term $Term */
        $Term = ClassRegistry::init('Term');

        /** @var Team $Team */
        $Team = ClassRegistry::init('Team');
        $team = $Team->getCurrentTeam();
        $termId = Hash::get($namedParams, 'term_id') ?? $Term->getCurrentTermId();
        // if all term is selected, start date will be team created, end date will be end date of next term
        if ($termId == $TermService::TERM_FILTER_ALL_KEY_NAME) {
            $startDate = AppUtil::dateYesterday(
                AppUtil::dateYmdLocal($team['Team']['created'],
                    $team['Team']['timezone'])
            );
            $nextTerm = $Term->getNextTermData();
            $endDate = $nextTerm['end_date'];

            $isAfterCurrentTerm = false;
        } else {
            $targetTerm = $this->Team->Term->findById($termId);
            $startDate = $targetTerm['Term']['start_date'];
            $endDate = $targetTerm['Term']['end_date'];

            $isAfterCurrentTerm = $TermService->isAfterCurrentTerm($termId);
        }

        $myGoalsCount = $this->Goal->getMyGoals(null, 1, 'count', $userId, $startDate, $endDate);
        $collaboGoalsCount = $this->Goal->getMyCollaboGoals(null, 1, 'count', $userId, $startDate, $endDate);
        $myGoalsCount += $collaboGoalsCount;
        $followGoalsCount = $this->Goal->getMyFollowedGoals(null, 1, 'count', $userId, $startDate, $endDate);

        if ($pageType == "following") {
            $goals = $this->Goal->getMyFollowedGoals(null, 1, 'all', $userId, $startDate, $endDate);
        } else {
            $goals = $this->Goal->getGoalsWithAction($userId, MY_PAGE_ACTION_NUMBER, $startDate, $endDate);
        }
        $goals = $GoalService->processGoals($goals);
        $goals = $GoalService->extendTermType($goals, $this->Auth->user('id'));
        $isMine = $userId == $this->Auth->user('id') ? true : false;
        $displayActionCount = MY_PAGE_ACTION_NUMBER;
        if ($isMine) {
            $displayActionCount--;
        }

        $termBaseUrl = Router::url([
            'controller' => 'users',
            'action'     => 'view_goals',
            'user_id'    => $userId,
            'page_type'  => $pageType
        ]);

        $myCoachingUsers = $this->User->TeamMember->getMyMembersList($this->my_uid);

        // 完了アクションが可能なゴールIDリスト
        $canCompleteGoalIds = Hash::extract(
            $this->Goal->findCanComplete($this->my_uid), '{n}.id'
        );

        $this->set([
            'term'                 => $termFilterOptions,
            'term_id'              => $termId,
            'term_base_url'        => $termBaseUrl,
            'my_goals_count'       => $myGoalsCount,
            'follow_goals_count'   => $followGoalsCount,
            'page_type'            => $pageType,
            'goals'                => $goals,
            'is_mine'              => $isMine,
            'display_action_count' => $displayActionCount,
            'my_coaching_users'    => $myCoachingUsers,
            'canCompleteGoalIds'   => $canCompleteGoalIds,
            'isAfterCurrentTerm'   => $isAfterCurrentTerm,
        ]);
        return $this->render();
    }

    /**
     * ユーザーページ 投稿一覧
     *
     * @return CakeResponse
     */
    function view_posts()
    {
        $user_id = Hash::get($this->request->params, "named.user_id");
        if (!$user_id || !$this->_setUserPageHeaderInfo($user_id)) {
            // ユーザーが存在しない
            $this->Pnotify->outError(__("Invalid screen transition."));
            return $this->redirect($this->referer());
        }
        $posts = $this->Post->get(1, POST_FEED_PAGE_ITEMS_NUMBER, null, null, [
            'user_id' => $user_id,
            'type'    => Post::TYPE_NORMAL
        ]);
        $team = $this->Team->getCurrentTeam();
        $this->set('item_created', $team['Team']['created']);
        $this->set('posts', $posts);
        $this->set('long_text', false);

        $this->layout = LAYOUT_ONE_COLUMN;
        return $this->render();
    }

    function view_actions()
    {
        $this->layout = LAYOUT_ONE_COLUMN;

        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        /** @var Term $Term */
        $Term = ClassRegistry::init('Term');
        /** @var Goal $Goal */
        $Goal = ClassRegistry::init('Goal');

        $currentTermId = $Term->getCurrentTermId();
        // make variables for requested named params.
        $namedParams = $this->request->params['named'];
        $userId = Hash::get($namedParams, "user_id");
        $pageType = Hash::get($namedParams, "page_type");
        $goalId = Hash::get($namedParams, 'goal_id');
        $termId = Hash::get($namedParams, 'term_id') ?? $currentTermId;

        // validation
        if (!$this->_validateParamsOnActionPage($userId, $pageType, $termId, $goalId)) {
            $this->Pnotify->outError(__("Invalid screen transition."));
            return $this->redirect($this->referer());
        }

        $this->_setUserPageHeaderInfo($userId);

        $myUid = $this->Auth->user('id');

        if ($userId != $myUid) {
            $canAction = false;
        } else {
            if ($goalId) {
                $canAction = $Goal->isActionable($userId, $goalId);
            } else {
                $canAction = $this->_canActionOnActionPageInTerm($userId, $termId);
            }
        }

        /** @var ActionResult $ActionResult */
        $ActionResult = ClassRegistry::init('ActionResult');
        /** @var GoalService $GoalService */
        $GoalService = ClassRegistry::init('GoalService');
        // count action
        if ($termId == $TermService::TERM_FILTER_ALL_KEY_NAME) {
            // if term = all, then user_is is key
            $actionCount = $ActionResult->getCountByUserId($userId);
        } else {
            $goalIdsInTerm = $GoalService->findIdsByTermIdUserId($termId, $userId);
            $actionCount = $ActionResult->getCountByGoalId($goalIdsInTerm);
        }
        $termFilterOptions = $TermService->getFilterMenu(true, false);
        $goalFilterOptions = $this->_getGoalFilterMenuOnActionPage($userId, $termId);

        $postCondition = $this->_getTimestampsForPostCondition($termId, $userId);
        $startTimestamp = $postCondition['startTimestamp'];
        $endTimestamp = $postCondition['endTimestamp'];
        $oldestTimestamp = $postCondition['oldestTimestamp'];

        $posts = $this->_findPostsOnActionPage($pageType, $userId, $goalId, $startTimestamp, $endTimestamp);

        $this->set('long_text', false);
        $this->set(compact(
            'posts',
            'termId',
            'goalFilterOptions',
            'termFilterOptions',
            'endTimestamp',
            'oldestTimestamp',
            'actionCount',
            'currentTermId',
            'canAction'
        ));
        return $this->render();
    }

    /**
     * @param int        $userId
     * @param int|string $termId
     *
     * @return bool
     */
    function _canActionOnActionPageInTerm(int $userId, $termId): bool
    {
        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        /** @var Term $Term */
        $Term = ClassRegistry::init('Term');
        /** @var GoalService $GoalService */
        $GoalService = ClassRegistry::init('GoalService');

        if ($userId == $this->Auth->user('id')
            && ($termId == $Term->getCurrentTermId() || $termId == $TermService::TERM_FILTER_ALL_KEY_NAME)
            && !empty($GoalService->findActionables())
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param int      $userId
     * @param int|null $termId
     *
     * @return array
     */
    function _getGoalFilterMenuOnActionPage(int $userId, $termId): array
    {
        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        /** @var GoalService $GoalService */
        $GoalService = ClassRegistry::init('GoalService');

        if ($termId == $TermService::TERM_FILTER_ALL_KEY_NAME) {
            $goalFilterOptions = $GoalService->getFilterMenu($userId, null);
        } else {
            $goalFilterOptions = $GoalService->getFilterMenu($userId, $termId);
        }
        return $goalFilterOptions;
    }

    /**
     * @param $userId
     * @param $pageType
     * @param $termId
     * @param $goalId
     *
     * @return bool
     */
    function _validateParamsOnActionPage($userId, $pageType, $termId, $goalId)
    {
        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        /** @var Term $Term */
        $Term = ClassRegistry::init('Term');
        /** @var GoalMember $GoalMember */
        $GoalMember = ClassRegistry::init('GoalMember');

        if ($this->Team->TeamMember->isActive($userId) == false) {
            // inactive user or not exists
            return false;
        }
        if (!in_array($pageType, ['list', 'image'])) {
            // $pageType is wrong
            return false;
        }
        if ($termId != $TermService::TERM_FILTER_ALL_KEY_NAME && $Term->exists($termId) == false) {
            // $termId is wrong
            return false;
        }
        if ($goalId && $GoalMember->isCollaborated($goalId, $userId) == false) {
            // $goalId is not collaborated
            return false;
        }
        return true;
    }

    /**
     * @param $termId
     * @param $userId
     *
     * @return array ['startTimestamp'=>"",'endTimestamp'=>"",'oldestTimestamp'=>""]
     */
    function _getTimestampsForPostCondition($termId, $userId): array
    {
        /** @var TermService $TermService */
        $TermService = ClassRegistry::init('TermService');
        /** @var Team $Team */
        $Team = ClassRegistry::init('Team');
        /** @var Term $Term */
        $Term = ClassRegistry::init('Term');

        if ($termId == $TermService::TERM_FILTER_ALL_KEY_NAME) {
            // if all term, start is date of team created
            $endTimestamp = REQUEST_TIMESTAMP;
            $startTimestamp = $endTimestamp - MONTH;
            $targetUser = $this->User->getDetail($userId);
            $oldestTimestamp = $targetUser['User']['created'];
        } else {
            $term = $Term->findById($termId)['Term'];
            $timezone = $Team->getTimezone();
            if ($termId == $Term->getCurrentTermId()) {
                $endTimestamp = REQUEST_TIMESTAMP;
            } else {
                $endTimestamp = AppUtil::getTimestampByTimezone(AppUtil::dateTomorrow($term['end_date']), $timezone);
            }
            $startTimestamp = $endTimestamp - MONTH;
            $oldestTimestamp = AppUtil::getTimestampByTimezone($term['start_date'], $timezone);
        }
        // $startTimestamp should be ahead of $oldestTimestamp
        if ($startTimestamp < $oldestTimestamp) {
            $startTimestamp = $oldestTimestamp;
        }

        $res = compact('startTimestamp', 'endTimestamp', 'oldestTimestamp');
        return $res;
    }

    function _findPostsOnActionPage($pageType, $userId, $goalId, $startTimestamp, $endTimestamp): array
    {
        $limit = ($pageType == 'list') ? POST_FEED_PAGE_ITEMS_NUMBER : MY_PAGE_CUBE_ACTION_IMG_NUMBER;
        $params = [
            'author_id' => $userId,
            'type'      => Post::TYPE_ACTION,
        ];
        if ($goalId) {
            $params['goal_id'] = $goalId;
        }
        $posts = $this->Post->get(1, $limit, $startTimestamp, $endTimestamp, $params);
        return $posts;
    }

    /**
     * ユーザーページ 基本情報
     *
     * @return CakeResponse
     */
    function view_info()
    {
        $user_id = Hash::get($this->request->params, "named.user_id");

        if (!$user_id || !$this->_setUserPageHeaderInfo($user_id)) {
            // ユーザーが存在しない
            $this->Pnotify->outError(__("Invalid screen transition."));
            return $this->redirect($this->referer());
        }

        $this->layout = LAYOUT_ONE_COLUMN;
        return $this->render();
    }

    function add_subscribe_email()
    {
        $this->request->allowMethod('post');
        /**
         * @var SubscribeEmail $SubscribeEmail
         */
        $SubscribeEmail = ClassRegistry::init('SubscribeEmail');
        if (!$SubscribeEmail->save($this->request->data)) {
            $this->Pnotify->outError($SubscribeEmail->validationErrors['email'][0]);
            return $this->redirect($this->referer());
        }
        $this->Pnotify->outSuccess(__('Registered email address.'));
        return $this->redirect($this->referer());
    }

    /**
     * ユーザページの上部コンテンツの表示に必要なView変数をセット
     *
     * @param $user_id
     *
     * @return bool
     */
    function _setUserPageHeaderInfo($user_id)
    {
        // ユーザー情報
        $user = $this->User->TeamMember->getByUserId($user_id);
        if (!$user) {
            // チームメンバーでない場合
            return false;
        }
        $this->set('user', $user);

        $timezone = $this->Team->getTimezone();
        // 評価期間内の投稿数
        $termStartTimestamp = AppUtil::getStartTimestampByTimezone($this->Team->Term->getCurrentTermData()['start_date'],
            $timezone);
        $termEndTimestamp = AppUtil::getEndTimestampByTimezone($this->Team->Term->getCurrentTermData()['end_date'],
            $timezone);

        $post_count = $this->Post->getCount($user_id, $termStartTimestamp, $termEndTimestamp);
        $this->set('post_count', $post_count);

        // 評価期間内のアクション数
        $action_count = $this->Goal->ActionResult->getCount($user_id, $termStartTimestamp, $termEndTimestamp);
        $this->set('action_count', $action_count);

        // 投稿に対するいいねの数
        $post_like_count = $this->Post->getLikeCountSumByUserId($user_id, $termStartTimestamp, $termEndTimestamp);
        // コメントに対するいいねの数
        $comment_like_count = $this->Post->Comment->getLikeCountSumByUserId($user_id, $termStartTimestamp,
            $termEndTimestamp);
        $this->set('like_count', $post_like_count + $comment_like_count);

        return true;
    }
}
