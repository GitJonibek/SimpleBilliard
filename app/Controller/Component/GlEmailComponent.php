<?php
App::uses('SendMail', 'Model');

/**
 * @author daikihirakata
 * @property LangComponent    $Lang
 * @property SessionComponent $Session
 * @property User             $User
 * @property SendMail         $SendMail
 */
class GlEmailComponent extends Component
{

    public $name = "GlEmail";
    public $notifi_mails = array();

    public $components = array(
        'Lang',
        'Session',
    );

    public function __construct(ComponentCollection $collection, $settings = array())
    {
        parent::__construct($collection, $settings);
    }

    public function startup(Controller $controller)
    {
        CakeSession::start();
        if (!$this->User) {
            $this->User = ClassRegistry::init('User');
        }
        if (!$this->SendMail) {
            $this->SendMail = ClassRegistry::init('SendMail');
        }
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
     * Sending a alert of expires
     *
     * @param int    $toUid
     * @param int    $teamId
     * @param string $teamName
     * @param string $expireDate
     * @param string $serviceUseStatus
     */
    public function sendMailServiceExpireAlert(
        int $toUid,
        int $teamId,
        string $teamName,
        string $expireDate,
        string $serviceUseStatus
    ) {
        $mailTemplate = null;
        switch ($serviceUseStatus) {
            case Team::SERVICE_USE_STATUS_FREE_TRIAL:
                $mailTemplate = Sendmail::TYPE_TMPL_EXPIRE_ALERT_FREE_TRIAL;
                break;
            case Team::SERVICE_USE_STATUS_READ_ONLY:
                $mailTemplate = Sendmail::TYPE_TMPL_EXPIRE_ALERT_READ_ONLY;
                break;
            case Team::SERVICE_USE_STATUS_CANNOT_USE:
                $mailTemplate = Sendmail::TYPE_TMPL_EXPIRE_ALERT_CANNOT_USE;
                break;
        }
        $url = Router::url(
            [
                'admin'      => false,
                'controller' => 'payments',
                'action'     => 'index',
                'team_id'    => $teamId,
            ], true);
        $item = compact('teamName', 'expireDate', 'url');
        $this->SendMail->saveMailData($toUid, $mailTemplate, $item, null, $teamId);
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

    /**
     * メールにてメアド認証用digit送信
     *
     * @param $code
     *
     * @return null
     */
    public function sendEmailVerifyDigit($code, $to)
    {
        $item = [
            'code'    => $code,
            'subject' => __('Confirmation Code for Goalous: %s', $code),
            'to'      => $to,
        ];
        $this->SendMail->saveMailData(null, SendMail::TYPE_TMPL_SEND_EMAIL_VERIFY_DIGIT_CODE, $item);
        $this->execSendMailById($this->SendMail->id);
    }

    /**
     * メールにてセットアップガイドメールを送信
     *
     * @param $to_user_id
     * @param $message
     * @param $url_data
     */
    public function sendMailSetup($to_user_id, $message, $url_data)
    {
        $url = Router::url($url_data, true);
        $item = [
            'url'     => $url,
            'message' => $message
        ];
        $this->SendMail->saveMailData($to_user_id,
            SendMail::TYPE_TMPL_SETUP,
            $item
        );
        $this->execSendMailById($this->SendMail->id);
    }

    public function sendMailNotify($data, $send_to_users)
    {
        if (empty($data)) {
            return;
        }
        $url_data = ['?' => ['from' => 'email']];
        if (!empty($this->SendMail->current_team_id)) {
            $url_data['?']['team_id'] = $this->SendMail->current_team_id;
        }
        if (Hash::get($data, 'url_data')) {
            $url_data = array_merge($url_data, $data['url_data']);
        }
        $url = Router::url($url_data, true);
        $item = [
            'url'       => $url,
            'type'      => $data['notify_type'],
            'count_num' => $data['count_num'],
            'item_name' => json_decode($data['item_name']),
            'model_id'  => $data['model_id'],
            'options'   => $data['options'],
        ];
        $this->SendMail->saveMailData($send_to_users, SendMail::TYPE_TMPL_NOTIFY, $item, $data['from_user_id'],
            $this->SendMail->SendMailToUser->current_team_id);
        //メール送信を実行
        $this->execSendMailById($this->SendMail->id, "send_notify_mail_by_id");

    }

    /**
     * execコマンドにてidを元にメール送信を行う
     *
     * @param        $id
     * @param string $method_name
     */
    public function execSendMailById($id, $method_name = "send_mail_by_id")
    {
        $set_web_env = "";
        $nohup = "nohup ";
        $php = '/opt/phpbrew/php/php-' . phpversion() . '/bin/php ';
        $cake_cmd = $php . APP . "Console" . DS . "cake.php";
        $cake_app = " -app " . APP;
        $cmd = " send_mail {$method_name}";
        $cmd .= " -i " . $id;
        $cmd .= " -s " . $this->Session->id();
        $cmd .= " -l " . Configure::read('Config.language');
        $cmd_end = " > /dev/null &";
        $all_cmd = $set_web_env . $nohup . $cake_cmd . $cake_app . $cmd . $cmd_end;
        exec($all_cmd);
    }
}
