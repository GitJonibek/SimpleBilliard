<?php
App::uses('Controller', 'Core');
App::uses('AppController', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('GlEmailComponent', 'Controller/Component');
App::uses('LangComponent', 'Controller/Component');
App::uses('NotifySetting', 'Model');

/**
 * SendMailShell
 *
 * @property Team          $Team
 * @property User          $User
 * @property SendMail      $SendMail
 * @property LangComponent $LangComponent
 */
class SendMailShell extends AppShell
{

    public $uses = array(
        'Team',
        'User',
        'SendMail'
    );
    public $components;
    public $Lang;
    public $Controller;
    public $AppController;
    /**
     * 言語別のメールデータ
     *
     * @var
     */
    public $lang_data;
    /**
     * @var
     */
    public $item;

    public $init_data;

    public function startup()
    {
        parent::startup();
        if ($this->params['session_id']) {
            CakeSession::destroy();
            CakeSession::id($this->params['session_id']);
            CakeSession::start();
        }
        $this->components = new ComponentCollection();
        $this->Lang = new LangComponent($this->components);
        $this->AppController = new AppController();
        $this->components->disable('Security');
    }

    public function __destruct()
    {
        unset($this->Lang);
        unset($this->components);
        unset($this->AppController);
    }

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $commands = [
            'send_mail_by_id'        => [
                'help'   => 'SendMailのidを元にメールを送信する',
                'parser' => [
                    'options' => [
                        'id'         => ['short' => 'i', 'help' => 'SendMailのid', 'required' => true,],
                        'session_id' => ['short' => 's', 'help' => 'Session ID', 'required' => true,],
                    ]
                ]
            ],
            'send_notify_mail_by_id' => [
                'help'   => 'SendMailのidを元に通知メールを送信する',
                'parser' => [
                    'options' => [
                        'id'         => ['short' => 'i', 'help' => 'SendMailのid', 'required' => true,],
                        'session_id' => ['short' => 's', 'help' => 'Session ID', 'required' => true,],
                    ]
                ]
            ],
        ];
        $parser->addSubcommands($commands);
        return $parser;
    }

    public function main()
    {

    }

    /**
     * SendMailのidを元にメール送信を行う
     */
    public function send_mail_by_id()
    {
        try {
            $data = $this->_getDataAndProcessPreSend();
        } catch (RuntimeException $e) {
            return;
        }
        $this->item = json_decode($data['SendMail']['item'], true);
        $tmpl_type = $data['SendMail']['template_type'];
        $to_user_ids = $this->SendMail->SendMailToUser->getToUserList($data['SendMail']['id']);
        if (!empty($to_user_ids)) {
            foreach ($to_user_ids as $to_user_id) {
                $data = $this->_getLangToUserData($to_user_id);
                $options = array_merge(SendMail::$TYPE_TMPL[$tmpl_type],
                                       ['to' => (isset($data['ToUser']['PrimaryEmail']['email'])) ? $data['ToUser']['PrimaryEmail']['email'] : null]
                );
                //送信先メールアドレスが指定されていた場合
                if (isset($this->item['to'])) {
                    $options['to'] = $this->item['to'];
                }
                $viewVars = [
                    'to_user_name'   => isset($data['ToUser']['display_username']) ? $data['ToUser']['display_username'] : null,
                    'from_user_name' => (isset($data['FromUser']['display_username'])) ? $data['FromUser']['display_username'] : null,
                ];
                if (is_array($this->item)) {
                    $viewVars = array_merge($this->item, $viewVars);
                }
                $this->_sendMailItem($options, $viewVars);
                $this->SendMail->id = $data['SendMail']['id'];
                $this->SendMail->save(['sent_datetime' => REQUEST_TIMESTAMP]);
            }
        }
        else {
            $options = SendMail::$TYPE_TMPL[$tmpl_type];
            //送信先メールアドレスが指定されていた場合
            if (isset($this->item['to'])) {
                $options['to'] = $this->item['to'];
            }
            $viewVars = [
                'to_user_name'   => null,
                'from_user_name' => (isset($data['FromUser']['display_username'])) ? $data['FromUser']['display_username'] : null,
            ];
            if (is_array($this->item)) {
                $viewVars = array_merge($this->item, $viewVars);
            }
            $this->_sendMailItem($options, $viewVars);
            $this->SendMail->id = $data['SendMail']['id'];
            $this->SendMail->save(['sent_datetime' => REQUEST_TIMESTAMP]);
        }
    }

    /**
     * SendMailのidを元に通知メール送信を行う
     */
    public function send_notify_mail_by_id()
    {
        try {
            $data = $this->_getDataAndProcessPreSend();
        } catch (RuntimeException $e) {
            return;
        }
        $this->item = json_decode($data['SendMail']['item'], true);
        $to_user_ids = $this->SendMail->SendMailToUser->getToUserList($data['SendMail']['id']);

        if (isset(NotifySetting::$TYPE[$this->item['type']])) {
            $notify_option = NotifySetting::$TYPE[$this->item['type']];
        }
        else {
            $notify_option = null;
        }
        foreach ($to_user_ids as $to_user_id) {
            $data = $this->_getLangToUserData($to_user_id, true);
            $from_user_names = [];
            $from_user_names[] = isset($data['FromUser']['display_username']) ? $data['FromUser']['display_username'] : null;
            $subject = $this->User->NotifySetting->getTitle($this->item['type'],
                                                            $from_user_names,
                                                            $this->item['count_num'],
                                                            $this->item['item_name'],
                                                            array_merge($this->item['options'], [
                                                                'style'        => 'plain',
                                                                'from_user_id' => $data['SendMail']['from_user_id'],
                                                            ])
            );
            $options = [
                'to'       => $data['ToUser']['PrimaryEmail']['email'],
                'subject'  => $subject,
                'template' => isset($notify_option['mail_template']) ? $notify_option['mail_template'] : '',
                'layout'   => 'default',
            ];
            $viewVars = [
                'to_user_name'   => $data['ToUser']['display_username'],
                'from_user_name' => $data['FromUser']['display_username'],
                'url'            => $this->item['url'],
                'body_title'     => $subject,
                'body'           => $this->item['item_name'],
            ];
            $this->_sendMailItem($options, $viewVars);

        }
        $this->SendMail->id = $data['SendMail']['id'];
        $this->SendMail->save(['sent_datetime' => REQUEST_TIMESTAMP]);
    }

    private function _getDataAndProcessPreSend()
    {
        if (!$this->params['id']) {
            throw new RuntimeException();
        }
        //送信データを取得
        $data = $this->SendMail->getDetail($this->params['id']);
        if (!isset($data['SendMail'])) {
            throw new RuntimeException();
        }
        $this->init_data = $data;
        return $data;
    }

    function _getLangToUserData($to_user_id, $with_notify_from_user = false)
    {
        $data = $this->init_data;
        //ユーザデータを取得
        $to_user = $this->User->getProfileAndEmail($to_user_id);
        //言語設定
        //相手が存在するユーザなら相手の言語を採用
        if (isset($to_user['User']['language'])) {
            $lang = $to_user['User']['language'];
        }
        //相手が存在せず送信元のユーザが存在する場合は送信元ユーザの言語を採用
        elseif (isset($data['FromUser']['language'])) {
            $lang = $data['FromUser']['language'];
        }
        elseif (isset($this->item['language'])) {
            $lang = $this->item['language'];
        }
        //それ以外は英語
        else {
            $lang = "eng";
        }
        Configure::write('Config.language', $lang);
        //送信データを再取得
        $data = $this->SendMail->getDetail($this->params['id'], $lang, $with_notify_from_user, $to_user_id);
        //ToUserデータを付加
        $to_user = $this->User->getProfileAndEmail($to_user_id, $lang);
        $data['ToUser'] = $to_user['User'];
        return $data;
    }

    /**
     * @param array $options
     * @param array $viewVars
     *
     * @internal param \unknown $val
     */
    private function _sendMailItem($options, $viewVars)
    {
        // TODO: $viewVars['message']以外の場所もメール本文として使われてる可能性があるため、調査が必要。
        //       もし上記の場所を発見したら、そのテキストを_preventGarbledCharacters()に通す必要がある。文字化け回避のために。
        if (isset($viewVars['message'])) {
            $viewVars['message'] = $this->_preventGarbledCharacters($viewVars['message']);
        }
        $defaults = array(
            'subject'  => '',
            'template' => '',
            'to'       => '',
            'layout'   => 'default'
        );
        $options = array_merge($defaults, $options);
        $options['subject'] = "[" . SERVICE_NAME . "]" . $options['subject'];
        /**
         * @var CakeEmail $Email
         */
        $Email = $this->_getMailInstance();
        /** @noinspection PhpUndefinedMethodInspection */
        $Email->to($options['to'])->subject($options['subject'])
              ->template($options['template'], $options['layout'])->viewVars($viewVars)->send();
        $Email->reset();
    }

    /**
     * Returns a CakeEmail object
     *
     * @return object CakeEmail instance
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/email.html
     */
    protected function _getMailInstance()
    {
        App::uses('CakeEmail', 'Network/Email');
        if (ENV_NAME === "local") {
            return new CakeEmail('default');
        }
        else {
            return new CakeEmail('amazon');
        }
    }

    /**
     * Prevent multi-byte text garbled over 1000 byte
     *
     * @param string $bigText
     * @param int    $width
     *
     * @return string $wrappedText
     */
    private function _preventGarbledCharacters($bigText, $width = 249)
    {
        $pattern = "/(.{1,{$width}})(?:\\s|$)|(.{{$width}})/uS";
        $replace = '$1$2' . "\n";
        $wrappedText = preg_replace($pattern, $replace, $bigText);
        return $wrappedText;
    }

}
