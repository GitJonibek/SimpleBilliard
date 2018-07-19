<?php
App::import('Service', 'PostService');
App::import('Lib/Paging', 'PagingRequest');
App::import('Service/Paging', 'CommentPagingService');
App::uses('CircleMember', 'Model');
App::uses('Post', 'Model');
App::uses('BasePagingController', 'Controller/Api');
App::uses('PostShareCircle', 'Model');
App::uses('PostRequestValidator', 'Validator/Request/Api/V2');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/18
 * Time: 15:00
 */
class PostsController extends BasePagingController
{

    /**
     * Endpoint for saving both circle posts and action posts
     *
     * @return CakeResponse|null
     */
    public function post()
    {
        $error = $this->validatePost();

        if (!empty($error)) {
            return $error;
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        $post['body'] = Hash::get($this->getRequestJsonBody(), 'body');
        $post['type'] = Hash::get($this->getRequestJsonBody(), 'type');

        $circleId = Hash::get($this->getRequestJsonBody(), 'circle_id');

        try {
            $res = $PostService->addCirclePost($post, $circleId, $this->getUserId(), $this->getTeamId());
        } catch (InvalidArgumentException $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        //If post saving failed, $res will be false
        if (empty($res)) {
            return ErrorResponse::internalServerError()->withMessage(__("Failed to post."))->getResponse();
        }

        return ApiResponse::ok()->getResponse();
    }

    public function get_comments(int $postId)
    {
        $error = $this->validateGetComments($postId);
        if (!empty($error)) {
            return $error;
        }

        /** @var CommentPagingService $CommentPagingService */
        $CommentPagingService = ClassRegistry::init("CommentPagingService");

        try {
            $pagingRequest = $this->getPagingParameters();
        } catch (Exception $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        }

        $result = $CommentPagingService->getDataWithPaging($pagingRequest, $this->getPagingLimit(),
            $this->getExtensionOptions());

        return ApiResponse::ok()->withBody($result)->getResponse();
    }

    /**
     * @param int $postId
     *
     * @return CakeResponse
     */
    public function post_like(int $postId): CakeResponse
    {
        $res = $this->validateLike($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var PostLike $PostLike */
        $PostLike = ClassRegistry::init('PostLike');

        try {
            $result = $PostLike->addPostLike($postId, $this->getUserId(), $this->getTeamId());
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData( (empty($result)) ? [] : $result->toArray())->getResponse();
    }

    /**
     * @param int $postId
     *
     * @return CakeResponse
     */
    public function delete_like(int $postId): CakeResponse
    {
        $res = $this->validateLike($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var PostLike $PostLike */
        $PostLike = ClassRegistry::init('PostLike');

        try {
            $count = $PostLike->deletePostLike($postId, $this->getUserId(), $this->getTeamId());
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData(["like_count" => $count])->getResponse();

    }

    /**
     * @return CakeResponse|null
     */
    private function validatePost()
    {
        $requestBody = $this->getRequestJsonBody();

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        $circleId = Hash::get($requestBody, 'circle_id');

        if (!empty($circleId) && !$CircleMember->isJoined($circleId, $this->getUserId())) {
            return ErrorResponse::forbidden()->withMessage(__("The circle dosen't exist or you don't have permission."))
                                ->getResponse();
        }

        try {
            PostRequestValidator::createDefaultPostValidator()->validate($requestBody);

            switch ($requestBody['type']) {
                case Post::TYPE_NORMAL:
                    PostRequestValidator::createCirclePostValidator()->validate($requestBody);
                    break;
            }
        } catch (\Respect\Validation\Exceptions\AllOfException $e) {
            return ErrorResponse::badRequest()
                                ->addErrorsFromValidationException($e)
                                ->withMessage(__('validation failed'))
                                ->getResponse();
        } catch (Exception $e) {
            GoalousLog::error('Unexpected validation exception', [
                'class'   => get_class($e),
                'message' => $e,
            ]);
            return ErrorResponse::internalServerError()->getResponse();
        }

        return null;
    }

    /**
     * Validation function for adding / removing like from a post
     *
     * @param int $postId
     *
     * @return CakeResponse|null
     */
    private function validateLike(int $postId)
    {
        if (empty($postId) || !is_int($postId)) {
            return ErrorResponse::badRequest()->getResponse();
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        //Check if user belongs to a circle where the post is shared to
        if (!$PostService->checkUserAccessToPost($this->getUserId(), $postId)) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have access to this post"))->getResponse();

        }

        return null;
    }

    /*
     * Validate get comments endpoint
     *
     * @param int $postId
     *
     * @return ErrorResponse|null
     */
    public function validateGetComments(int $postId)
    {
        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        $hasAccess = $PostService->checkUserAccessToPost($this->getUserId(), $postId);

        if (!$hasAccess) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have permission to access this post"))
                                ->getResponse();
        }

        return null;
    }
}