<?php
App::uses('ApiController', 'Controller/Api');

class DevicesController extends  ApiController
{
    public $components = array('RequestHandler');

    public function beforeFilter()
    {
        // Do not call parent class to prevent the validation of
        // authenticated users. The API should allow request even if
        // the users is not logged in
        //parent::beforeFilter();
        $this->Auth->allow();
        $this->Security->validatePost = false;
        $this->Security->csrfCheck = false;
    }

    /**
     * Accept a JSON with device information
     * Format:
     * {
     *    "installationId": "string",
     *    "version": "string"
     * }
     * 
     * @return CakeResponse
     */
    public function post()
    {
        $userId = $this->Auth->user('id');
        $requestJsonData = $this->request->input("json_decode", true);

        // Validate parameters
        if (empty($requestJsonData['installationId']) ||
            empty($requestJsonData['version'])) {
            return $this->_getResponseBadFail('Invalid Parameters');
        }
        $installationId = $requestJsonData['installationId'];
        $version = $requestJsonData['version'];

        // User not logged
        if ($userId === null) {
            // remove device info
            /** @var Device $Device */
            $Device = ClassRegistry::init('Device');

            if (!$Device->softDeleteAll(['Device.installation_id' => $installationId], false)) {
                CakeLog::error("Failed to delete installation_id: $installationId");
                return $this->_getResponseInternalServerError();
            }
            return $this->_getResponseSuccess();
        }

       // Check the request user
        if (!$this->User->exists($userId)) {
            CakeLog::error(sprintf("user id is invalid. user_id: %s", $userId));
            return $this->_getResponseBadFail(__('Parameters were wrong'));
        }

        try {
            // Save the device
            $this->NotifyBiz->saveDeviceInfo($userId, $installationId, $version);

            // Update setup status
            $this->_updateSetupStatusIfNotCompleted();
        }
        catch (RuntimeException $e) {
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            return $this->_getResponseInternalServerError();
        }
        return $this->_getResponseSuccess();
    }
}
