<?php
App::uses('BasePagingController', 'Controller/Api');
App::import('Lib/Network/Response', 'ApiResponse');
App::import('Lib/Network/Response', 'ErrorResponse');
App::import('Service', 'CircleMemberService');
App::import('Service/Paging', 'CirclePostPagingService');
App::import('Service/Paging', 'CircleMemberPagingService');
App::uses('PagingRequest', 'Lib/Paging');
App::uses('CircleMember', 'Model');
App::uses('Circle', 'Model');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/20
 * Time: 9:41
 */

use Goalous\Exception as GlException;

class CirclesController extends BasePagingController
{
    /**
     * Get circle by Id
     *
     * @param int $circleId
     *
     * @return BaseApiResponse
     */
    public function get_detail(int $circleId)
    {
        $error = $this->validateGetCircle($circleId);

        if (!empty($error)) {
            return $error;
        }

        /** @var CircleService $CircleService */
        $CircleService = ClassRegistry::init("CircleService");

        $circle = $CircleService->get($circleId);
        return ApiResponse::ok()->withData($circle)->getResponse();
    }

    public function get_posts(int $circleId)
    {
        $error = $this->validateGetCircle($circleId);

        if (!empty($error)) {
            return $error;
        }

        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = ClassRegistry::init('CirclePostPagingService');

        try {
            $pagingRequest = $this->getPagingParameters();
        } catch (Exception $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        }

        try {
            $data = $CirclePostPagingService->getDataWithPaging(
                $pagingRequest,
                $this->getPagingLimit(),
                $this->getExtensionOptions() ?: $this->getDefaultPostExtension());
        } catch (Exception $e) {
            GoalousLog::error($e->getMessage(), $e->getTrace());
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withBody($data)->getResponse();
    }

    public function get_members(int $circleId)
    {
        $error = $this->validateGetCircle($circleId);

        if (!empty($error)) {
            return $error;
        }

        /** @var CircleMemberPagingService $CircleMemberPagingService */
        $CircleMemberPagingService = ClassRegistry::init('CircleMemberPagingService');

        try {
            $pagingRequest = $this->getPagingParameters();
        } catch (Exception $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        }

        try {
            $data = $CircleMemberPagingService->getDataWithPaging(
                $pagingRequest,
                $this->getPagingLimit(10),
                $this->getExtensionOptions() ?: $this->getDefaultMemberExtension());
        } catch (Exception $e) {
            GoalousLog::error($e->getMessage(), $e->getTrace());
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withBody($data)->getResponse();
    }

    public function post_join(int $circleId)
    {
        $error = $this->validatePostMember($circleId);

        if (!empty($error)) {
            return $error;
        }

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');
        try {
            $return = $CircleMemberService->add($this->getUserId(), $this->getTeamId(), $circleId);
            $CircleMemberService->notifyMembers(NotifySetting::TYPE_CIRCLE_USER_JOIN, $circleId, $this->getUserId(),
                $this->getTeamId());
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (GlException\GoalousConflictException $exception) {
            return ErrorResponse::resourceConflict()->withException($exception)
                                ->withMessage(__("You already joined to this circle."))->getResponse();
        } catch (Exception $exception) {
            return ErrorResponse::internalServerError()->withException($exception)->getResponse();
        }

        return ApiResponse::ok()->withData($return->toArray())->getResponse();
    }

    public function post_leave(int $circleId)
    {
        $error = $this->validatePostLeave($circleId);

        if (!empty($error)) {
            return $error;
        }

        try {
            /** @var CircleMemberService $CircleMemberService */
            $CircleMemberService = ClassRegistry::init('CircleMemberService');

            $CircleMemberService->delete($this->getUserId(), $this->getTeamId(), $circleId);
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (Exception $exception) {
            return ErrorResponse::internalServerError()->withException($exception)->getResponse();
        }

        return ApiResponse::ok()->withData(['circle_id' => $circleId, 'user_id' => $this->getUserId()])->getResponse();
    }

    /**
     * Validation for endpoint get_posts
     *
     * @param int $circleId
     *
     * @return CakeResponse|null
     */
    private function validateGetCircle(int $circleId)
    {
        if (!is_int($circleId)) {
            return ErrorResponse::badRequest()->getResponse();
        }

        /** @var Circle $Circle */
        $Circle = ClassRegistry::init("Circle");

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        //Check if circle belongs to current team & user has access to the circle
        if (!$Circle->isBelongCurrentTeam($circleId, $this->getTeamId()) ||
            ($Circle->isSecret($circleId) && !$CircleMember->isBelong($circleId, $this->getUserId(),
                    $this->getTeamId()))) {
            return ErrorResponse::forbidden()->withMessage(__("The circle dosen't exist or you don't have permission."))
                                ->getResponse();
        }

        return null;
    }

    /**
     * Validate delete member endpoint
     *
     * @param int $circleId
     *
     * @return ErrorResponse | null
     */
    private function validatePostLeave(int $circleId)
    {
        /** @var Circle $Circle */
        $Circle = ClassRegistry::init('Circle');

        if (!$Circle->exists($circleId)) {
            return ErrorResponse::notFound()->withMessage(__("This circle does not exist."))->getResponse();
        }

        return null;
    }

    public function validatePostMember(int $circleId)
    {
        /** @var Circle $Circle */
        $Circle = ClassRegistry::init("Circle");

        $condition = [
            'conditions' => [
                'Circle.id'         => $circleId,
                'Circle.public_flg' => true
            ]
        ];
        $circle = $Circle->find('first', $condition);

        if (!$Circle->isBelongCurrentTeam($circleId, $this->getTeamId()) || empty($circle)) {
            return ErrorResponse::notFound()->withMessage(__("This circle does not exist."))->getResponse();
        }

        return null;
    }

    /**
     * Default extension options for getting circle post
     *
     * @return array
     */
    private function getDefaultPostExtension()
    {
        return [
            CirclePostPagingService::EXTEND_CIRCLE,
            CirclePostPagingService::EXTEND_LIKE,
            CirclePostPagingService::EXTEND_SAVED,
            CirclePostPagingService::EXTEND_USER,
            CirclePostPagingService::EXTEND_COMMENTS,
            CirclePostPagingService::EXTEND_POST_FILE
        ];
    }

    /**
     * Default extension options for getting circle members
     *
     * @return array
     */
    private function getDefaultMemberExtension()
    {
        return [
            CircleMemberPagingService::EXTEND_USER
        ];
    }

}
