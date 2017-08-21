<?php
/**
 * Static content controller.
 * This file will render views from views/pages/
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('AppController', 'Controller');
App::uses('PaymentSetting', 'Model');

/**
 * Static content controller
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link          http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 * @property User $User
 * @noinspection  PhpInconsistentReturnPointsInspection
 */
class PagesController extends AppController
{
    /**
     * Displays a view
     *
     * @throws NotFoundException
     * @throws Exception
     * @throws MissingViewException
     * @internal param \What $mixed page to display
     * @return $this->redirect('/') or void
     */
    public function home()
    {
        // Display lp top page if not logged in
        if (!$this->isLoggedIn()) {
            $this->layout = LAYOUT_HOMEPAGE;
            return $this->render('home');
        }

        // Define URL params for Google analytics.
        $this->_setUrlParams();

        //title_for_layoutはAppControllerで設定
        $this->set(compact('page', 'subpage'));
        $this->_setTopAllContentIfLoggedIn();

        return $this->render('logged_in_home');
    }

    public function lp()
    {
        $path = func_get_args();
        $page = $path[0];

        if ($page === 'pricing') {
            $this->_setAmountPerUser();
        }

        $this->layout = LAYOUT_HOMEPAGE;
        return $this->render(implode('/', $path));
    }

    function _setTopAllContentIfLoggedIn()
    {
        // プロフィール作成モードの場合、ビューモードに切り替え
        if ($this->Session->read('add_new_mode') === MODE_NEW_PROFILE) {
            $this->Session->delete('add_new_mode');
            $this->set('mode_view', MODE_VIEW_TUTORIAL);
        }
        // ビュー変数のセット
        $this->_setCurrentCircle();
        $this->_setFeedMoreReadUrl();
        $this->_setGoalsForTopAction();
        // 現在のチーム
        $current_team = $this->Team->getCurrentTeam();
        $this->set('item_created', isset($current_team['Team']['created']) ? $current_team['Team']['created'] : null);
        $this->set('current_team', $current_team);
        // チーム全体サークル
        $this->set('team_all_circle', $this->Team->Circle->getTeamAllCircle());
        $current_global_menu = "home";
        $feed_filter = 'all';
        $this->set(compact('feed_filter', 'current_global_menu'));
        $this->set('long_text', false);
        if ($form_type = Hash::get($this->request->params, 'common_form_type')) {
            $this->set('common_form_type', $form_type);
        } else {
            $this->set('common_form_type', 'action');
        }

        try {
            $this->set([
                'posts' => $this->Post->get(1, POST_FEED_PAGE_ITEMS_NUMBER, null, null,
                    $this->request->params)
            ]);
        } catch (RuntimeException $e) {
            $this->Notification->outError($e->getMessage());
            $this->redirect($this->referer());
        }
    }

    public function beforeFilter()
    {
        $this->_setLanguage();
        //全ページ許可
        $this->Auth->allow();

        //チームidがあった場合は許可しない
        if (isset($this->request->params['team_id'])) {
            $this->Auth->deny('display');
        }

        //切り換え可能な言語をセット
        $this->set('lang_list', $this->_getPageLanguageList());
        parent::beforeFilter();
    }

    public function _setLanguage()
    {
        // パラメータから言語をセット
        $this->set('top_lang', null);
        $lang = $this->_getLangFromParam();
        if ($lang) {
            $this->set('top_lang', $lang);
            Configure::write('Config.language', $lang);
        }
        //省略型の言語テキストをviewにセット
        $short_lang = $this->Lang->getShortLang();
        $available_lang = $this->_getPageLanguageList();
        if (!array_key_exists($short_lang, $available_lang)) {
            $short_lang = 'en';
        }
        $this->set('short_lang', $short_lang);
    }

    function _getLangFromParam()
    {
        $lang = null;
        if (isset($this->request->params['lang'])) {
            $lang = $this->request->params['lang'];
        } elseif (isset($this->request->params['named']['lang'])) {
            $lang = $this->request->params['named']['lang'];
        }
        return $lang;
    }

    /**
     * トップ用言語リスト
     */
    public function _getPageLanguageList()
    {
        $lang_list = [
            'ja' => __("Japanese"),
            'en' => __("English"),
        ];
        return $lang_list;
    }

    public function contact($type = null)
    {
        $this->layout = LAYOUT_HOMEPAGE;
        $this->set('type_options', $this->_getContactTypeOption());
        $this->set('selected_type', $type);

        if ($this->request->is('get')) {
            if (isset($this->request->params['named']['from_confirm']) &&
                $this->Session->read('contact_form_data')
            ) {
                $this->request->data['Email'] = $this->Session->read('contact_form_data');
            }
            return $this->render();
        }
        /**
         * @var Email $Email
         */
        $Email = ClassRegistry::init('Email');
        $Email->validate = $Email->contact_validate;
        $Email->set($this->request->data);
        $data = Hash::extract($this->request->data, 'Email');
        if ($Email->validates()) {
            if (empty($data['sales_people'])) {
                $data['sales_people_text'] = __('Anyone');
            } else {
                $data['sales_people_text'] = implode(', ', $data['sales_people']);
            }
            $data['want_text'] = $this->_getContactTypeOption()[$data['want']];

            $this->Session->write('contact_form_data', $data);
            $lang = $this->_getLangFromParam();
            return $this->redirect(['action' => 'contact_confirm', 'lang' => $lang]);
        }
        return $this->render();
    }

    private function _getContactTypeOption()
    {
        return [
            null => __('Please select'),
            1    => __('Get more information'),
            2    => __('Get documentation'),
            3    => __('Collaborate with ISAO'),
            4    => __('Give us an interview'),
            5    => __('Others'),
        ];
    }

    public function contact_confirm()
    {
        $this->layout = LAYOUT_HOMEPAGE;
        $data = $this->Session->read('contact_form_data');
        if (empty($data)) {
            $this->Notification->outError(__('Ooops. Some problems occurred.'));
            return $this->redirect($this->referer());
        }
        $this->set(compact('data'));
        return $this->render();
    }

    public function contact_send()
    {
        $data = $this->Session->read('contact_form_data');
        if (empty($data)) {
            $this->Notification->outError(__('Ooops. Some problems occurred.'));
            return $this->redirect($this->referer());
        }
        $this->Session->delete('contact_form_data');
        //メール送信処理
        App::uses('CakeEmail', 'Network/Email');
        if (ENV_NAME === "local") {
            $config = 'default';
        } else {
            $config = 'amazon_contact';
        }

        // 送信処理
        $email = new CakeEmail($config);
        $email
            ->template('contact', 'default')
            ->viewVars(['data' => $data])
            ->emailFormat('text')
            ->to([$data['email'] => $data['email']])
            ->bcc(['contact@goalous.com' => 'contact@goalous.com'])
            ->subject(__('Goalous - Thanks for your contact.'))
            ->send();
        $lang = $this->_getLangFromParam();
        return $this->redirect([
            'controller' => 'pages',
            'action'     => 'display',
            'pagename'   => 'contact_thanks',
            'lang'       => $lang,
        ]);
    }

    public function _setUrlParams()
    {
        $parsed_referer_url = Router::parse($this->referer('/', true));
        $request_status = $this->params['url'];
        $status_from_referer = $this->_defineStatusFromReferer();

        // When parametes separated from google analitics already exists,
        // ignore redirect for google analitics.
        $reserved_params = ['notify_id', 'common_form', 'team_id', 'from'];
        foreach ($reserved_params as $param) {
            if (Hash::get($this->request->params, $param) || Hash::get($this->request->params, "named.$param")) {
                return true;
            }
        }

        if ($this->_parseParameter($request_status) !== $status_from_referer) {
            return $this->redirect("/${status_from_referer}");
        }
        $this->Session->delete('referer_status');
        return true;
    }

    public function _defineStatusFromReferer()
    {
        switch ($this->Session->read('referer_status')) {
            case REFERER_STATUS_SIGNUP_WITH_INVITING:
                return REFERER_STATUS_SIGNUP_WITH_INVITING;

            case REFERER_STATUS_SIGNUP_WITH_NOT_INVITING:
                return REFERER_STATUS_SIGNUP_WITH_NOT_INVITING;

            case REFERER_STATUS_INVITED_USER_EXIST:
                return REFERER_STATUS_INVITED_USER_EXIST;

            case REFERER_STATUS_INVITED_USER_EXIST_BY_EMAIL:
                return REFERER_STATUS_INVITED_USER_EXIST_BY_EMAIL;

            case REFERER_STATUS_INVITED_USER_EXIST_BY_CSV:
                return REFERER_STATUS_INVITED_USER_EXIST_BY_CSV;

            case REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_EMAIL:
                return REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_EMAIL;

            case REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_CSV:
                return REFERER_STATUS_INVITED_USER_NOT_EXIST_BY_CSV;

            case REFERER_STATUS_LOGIN:
                return REFERER_STATUS_LOGIN;

            // Others
            default:
                return REFERER_STATUS_DEFAULT;
        }
    }

    public function _parseParameter($parameters)
    {
        $parameters_text = '';
        $prefix = '';
        $i = 0;
        foreach ($parameters as $key => $value) {
            if ($i === 0) {
                $prefix = '?';
            } else {
                $prefix = '&';
            }
            $parameters_text .= "${prefix}${key}=${value}";
            $i++;
        }
        return $parameters_text;
    }

    function _setAmountPerUser()
    {
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init("PaymentService");

        $amountPerUser = PaymentService::AMOUNT_PER_USER_JPY;
        if ($this->isLoggedIn()) {
            $amountPerUser = $PaymentService->getAmountPerUser($this->current_team_id);
        }
        $this->set(compact('amountPerUser'));
    }
}
