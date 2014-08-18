<?php

/**
 * @author daikihirakata
 * @property LangComponent $Lang
 * @property User          $User
 * @property SendMail      $SendMail
 */

/** @noinspection PhpDocSignatureInspection */
class GlEmailComponent extends Object
{

    public $name = "GlEmail";

    public $Controller;

    public $notifi_mails = array();

    public $User;

    /**
     * @var SendMail $SendMail
     */
    public $SendMail;

    public $components = array(
        'Lang',
    );

    function initialize()
    {
    }

    function startup(&$controller)
    {
        $this->Controller = $controller;
        $this->User = ClassRegistry::init('User');
        $this->SendMail = ClassRegistry::init('SendMail');
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
     * メールにてユーザ再認証
     *
     * @param $to_uid
     * @param $email_token
     *
     * @return null
     */
    public function sendMailEmailTokenResend($to_uid, $email_token)
    {
        $url = Router::url(
                     [
                         'admin'      => false,
                         'controller' => 'users',
                         'action'     => 'verify',
                         $email_token,
                     ], true);
        $item = [
            'url'      => $url,
            'language' => Configure::read('Config.language'),
        ];
        $this->SendMail->saveMailData($to_uid, SendMail::TYPE_TMPL_TOKEN_RESEND, $item);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにてユーザ認証
     *
     * @param $to_uid
     * @param $email_token
     *
     * @return null
     */
    public function sendMailUserVerify($to_uid, $email_token)
    {
        if (!$to_uid || !$email_token) {
            return null;
        }
        $url = Router::url(
                     [
                         'admin'      => false,
                         'controller' => 'users',
                         'action'     => 'verify',
                         $email_token,
                     ], true);
        $item = [
            'url'      => $url,
            'language' => Configure::read('Config.language')
        ];
        $this->SendMail->saveMailData($to_uid, SendMail::TYPE_TMPL_ACCOUNT_VERIFY, $item);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにてメールアドレス変更に伴う認証
     *
     * @param $to_uid
     * @param $email
     * @param $email_token
     *
     * @return null
     */
    public function sendMailChangeEmailVerify($to_uid, $email, $email_token)
    {
        if (!$to_uid || !$email_token) {
            return null;
        }
        $url = Router::url(
                     [
                         'admin'      => false,
                         'controller' => 'users',
                         'action'     => 'change_email_verify',
                         $email_token,
                     ], true);
        $this->SendMail->saveMailData($to_uid, SendMail::TYPE_TMPL_CHANGE_EMAIL_VERIFY,
                                      ['url' => $url, 'to' => $email]);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにてパスワード設定完了通知
     *
     * @param $to_uid
     *
     * @return null
     */
    public function sendMailCompletePasswordReset($to_uid)
    {
        $this->SendMail->saveMailData($to_uid, SendMail::TYPE_TMPL_PASSWORD_RESET_COMPLETE);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにてパスワード再設定
     *
     * @param $to_uid
     * @param $token
     *
     * @return null
     */
    public function sendMailPasswordReset($to_uid, $token)
    {
        $url = Router::url(
                     [
                         'admin'      => false,
                         'controller' => 'users',
                         'action'     => 'password_reset',
                         $token,
                     ], true);
        $this->SendMail->saveMailData($to_uid, SendMail::TYPE_TMPL_PASSWORD_RESET, ['url' => $url]);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにて招待メールを送信
     *
     * @param array $invite_data
     * @param       $team_name
     *
     * @return bool
     */
    public function sendMailInvite($invite_data, $team_name)
    {
        if (!isset($invite_data['Invite']) || empty(($invite_data['Invite']))) {
            return false;
        }
        $invite_data = $invite_data['Invite'];
        $url = Router::url(
                     [
                         'admin'      => false,
                         'controller' => 'users',
                         'action'     => 'accept_invite',
                         $invite_data['email_token'],
                     ], true);
        $item = [
            'url'       => $url,
            'to'        => $invite_data['email'],
            'team_name' => $team_name,
            'message'   => isset($invite_data['message']) ? $invite_data['message'] : null
        ];
        $this->SendMail->saveMailData(isset($invite_data['to_user_id']) ? $invite_data['to_user_id'] : null,
                                      SendMail::TYPE_TMPL_INVITE,
                                      $item,
                                      $invite_data['from_user_id'],
                                      $invite_data['team_id']
        );
        $this->execSendMailById($this->SendMail->id);
        return true;
    }

    public function sendMailNotify($data, $send_to_users)
    {
        if (empty($data)) {
            return;
        }
        $url = Router::url($data['url_data'], true);
        $item = [
            'url' => $url,
        ];

        $this->SendMail->saveMailData(null, SendMail::TYPE_TMPL_NOTIFY, $item, $data['from_user_id'],
                                      $this->SendMail->current_team_id);
        foreach ($send_to_users as $key => $val) {
            $send_to_users[$key]['send_mail_id'] = $this->SendMail->id;
            $send_to_users[$key]['team_id'] = $this->SendMail->current_team_id;
        }
        $this->SendMail->SendMailToUser->saveAll($send_to_users);
        //メール送信を実行
        $this->execSendMailById($this->SendMail->id, "send_notify_mail_by_id");

    }

    /**
     * execコマンドにてidを元にメール送信を行う
     *
     * @param $id
     */
    public function execSendMailById($id, $method_name = "send_mail_by_id")
    {
        $set_web_env = "";
        $php = "/usr/bin/php ";
        $cake_cmd = $php . APP . "Console" . DS . "cake.php";
        $cake_app = " -app " . APP;
        $cmd = " send_mail {$method_name}";
        $cmd .= " -i " . $id;
        $cmd_end = " > /dev/null &";
        $all_cmd = $set_web_env . $cake_cmd . $cake_app . $cmd . $cmd_end;
        exec($all_cmd);
    }
}
