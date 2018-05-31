<?php
App::uses('AuthService', 'Service');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/05/30
 * Time: 11:35
 */
class AuthController extends ApiV2Controller
{
    public function beforeFilter()
    {
        return parent::beforeFilter();
    }

    /**
     * Login endpoint for user. Ignore restriction and authentication
     *
     * @ignoreRestriction
     * @skipAuthentication
     */
    public function login()
    {
        $return = $this->validateLogin();

        if (!empty($return)) {
            return $return;
        }

        $requestData = $this->request->data;

        $Auth = new AuthService();

        try {
            $jwt = $Auth->authenticateUser($requestData['username'], $requestData['password']);
        } catch (Exception $e) {
            return (new ApiResponse(ApiResponse::RESPONSE_INTERNAL_SERVER_ERROR))->withMessage($e->getMessage())
                                                                                 ->withExceptionTrace($e->getTrace())
                                                                                 ->getResponse();
        }

        if (empty($jwt)) {
            return (new ApiResponse(ApiResponse::RESPONSE_BAD_REQUEST))->withMessage("Username & password doesn't match")
                                                                       ->getResponse();
        }

        return (new ApiResponse(ApiResponse::RESPONSE_SUCCESS))->withBody(['jwt' => $jwt->token()])->getResponse();

    }

    /**
     * Validate all parameters before being manipulated by respective endpoint
     *
     * @return CakeResponse Return a response if validation failed
     */
    private function validateLogin()
    {
        if (!$this->request->is('post')) {
            return (new ApiResponse(ApiResponse::RESPONSE_BAD_REQUEST))->withMessage("Unsupported HTTP method")
                                                                       ->getResponse();
        }

        $validator = AuthRequestValidator::createLoginValidator();

        try {
            $validator->validate($this->request->data);
        } catch (Exception $e) {
            return (new ApiResponse(ApiResponse::RESPONSE_BAD_REQUEST))->withMessage($e->getMessage())
                                                                       ->withExceptionTrace($e->getTrace())
                                                                       ->getResponse();
        }

    }

}