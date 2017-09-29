<?php
App::uses('AppController', 'Controller');
App::uses('AppUtil', 'Util');

/**
 * Devices Controller
 *
 * @property Device $Device
 */
class DevicesController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
        $allowed_actions = ['add', 'get_version_info'];
        //アプリからのPOSTではフォーム改ざんチェック用のハッシュ生成ができない為、ここで改ざんチェックを除外指定
        // TODO: There is big security issue!!! In currentry, all clint requests are allowed!
        if (in_array($this->request->params['action'], $allowed_actions)) {
            $this->Security->validatePost = false;
            $this->Security->csrfCheck = false;
        }
    }

    /**
     * デバイス情報を追加する
     * POSTのみ受け付ける
     * 以下のフィールドを渡してあげる
     * $this->request->data['user_id'] // TODO: Security working! It should not be recieved in request param and should not be used.
     * $this->request->data['installation_id']
     * $this->request->data['current_version']
     *
     * @return CakeResponse
     */
    public function add()
    {
        $this->request->allowMethod('post');
        //TODO: After implementing mobile app force updating,
        //      have to change only from session to get user id https://jira.goalous.com/browse/GL-5949
        $user_id = $this->Auth->user('id') ?? $this->request->data['user_id'];
        $installation_id = $this->request->data['installation_id'];
        $current_version = isset($this->request->data['current_version']) ? $this->request->data['current_version'] : null;
        try {
            if (!$this->User->exists($user_id)) {
                $this->log(sprintf("user id is invalid. user_id: %s", $user_id));
                throw new RuntimeException(__('Parameters were wrong'));
            }
            $device = $this->NotifyBiz->saveDeviceInfo($user_id, $installation_id, $current_version);
            /* @var AppMeta $AppMeta */
            $AppMeta = ClassRegistry::init('AppMeta');
            $app_metas = $AppMeta->getMetas();
            if (count($app_metas) < 4) {
                $this->log('##### App Meta does not exists. ####');
                throw new RuntimeException(__('Internal Server Error'));
            }

            //バージョン情報を比較
            $key_name = $device['Device']['os_type'] == Device::OS_TYPE_IOS ? "iOS_version" : "android_version";
            $is_latest_version = "";
            $msg = __('Version Info not exists');
            if ($current_version) {
                if ($is_latest_version = version_compare($current_version, $app_metas[$key_name]) > -1) {
                    $msg = __('This device is latest version.');
                } else {
                    $msg = __('This device is not latest version.');
                }
            }

            //セットアップガイドのカウント更新
            $this->_updateSetupStatusIfNotCompleted();

            $ret_array = [
                'response' => [
                    'error'             => false,
                    'message'           => $msg,
                    'is_latest_version' => $is_latest_version,
                    'user_id'           => $user_id,
                    'installation_id'   => $installation_id,
                    'store_url'         => $device['Device']['os_type'] == Device::OS_TYPE_IOS ? $app_metas['iOS_install_url'] : $app_metas['android_install_url'],
                ]
            ];
        } catch (RuntimeException $e) {
            $this->log($e->getMessage());
            $this->log(Debugger::trace());
            $ret_array = [
                'response' => [
                    'error'             => true,
                    'message'           => $e->getMessage(),
                    'is_latest_version' => "",
                    'user_id'           => $user_id,
                    'installation_id'   => $installation_id,
                    'store_url'         => "",
                ]
            ];
        }

        return $this->_ajaxGetResponse($ret_array, JSON_UNESCAPED_UNICODE);
    }
}
