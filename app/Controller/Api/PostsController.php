<?php
App::import('Service', 'PostService');
App::import('Service', 'PostLikeService');
App::import('Lib/Paging', 'PagingRequest');
App::import('Service/Paging', 'CommentPagingService');
App::uses('CircleMember', 'Model');
App::uses('Post', 'Model');
App::uses('BasePagingController', 'Controller/Api');
App::uses('PostShareCircle', 'Model');
App::uses('PostRequestValidator', 'Validator/Request/Api/V2');
App::uses('TeamMember', 'Model');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/18
 * Time: 15:00
 */

use Goalous\Exception as GlException;

class PostsController extends BasePagingController
{

    /**
     * Endpoint for saving both circle posts and action posts
     *
     * @return CakeResponse
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
        $post['type'] = (int)Hash::get($this->getRequestJsonBody(), 'type');

        $circleId = (int)Hash::get($this->getRequestJsonBody(), 'circle_id');
        $fileIDs = Hash::get($this->getRequestJsonBody(), 'file_ids', []);

        try {
            $res = $PostService->addCirclePost($post, $circleId, $this->getUserId(), $this->getTeamId(), $fileIDs);
        } catch (InvalidArgumentException $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->withMessage(__("Failed to post."))
                                ->getResponse();
        }

        return ApiResponse::ok()->withData($res->toArray())->getResponse();
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
     * Endpoint for editing a post
     *
     * @param int $postId
     *
     * @return CakeResponse
     */
    public function put(int $postId): CakeResponse
    {
        $error = $this->validatePut($postId);

        if (!empty($error)) {
            return $error;
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        $newBody = Hash::get($this->getRequestJsonBody(), 'body');

        try {
            /** @var PostEntity $newPost */
            $newPost = $PostService->editPost($newBody, $postId);
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData($newPost->toArray())->getResponse();
    }

    public function post_like(int $postId): CakeResponse
    {
        $res = $this->validateLike($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var PostLikeService $PostLikeService */
        $PostLikeService = ClassRegistry::init('PostLikeService');

        try {
            $result = $PostLikeService->add($postId, $this->getUserId(), $this->getTeamId());
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData((empty($result)) ? [] : $result->toArray())->getResponse();
    }

    public function delete(int $postId)
    {
        $error = $this->validateDelete($postId);

        if (!empty($error)) {
            return $error;
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $PostService->softDelete($postId);
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->getResponse();
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

        /** @var PostLikeService $PostLikeService */
        $PostLikeService = ClassRegistry::init('PostLikeService');

        try {
            $count = $PostLikeService->delete($postId, $this->getUserId());

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

        try {
            $access = $PostService->checkUserAccessToPost($this->getUserId(), $postId);
        } catch (GlException\GoalousNotFoundException $notFoundException) {
            return ErrorResponse::notFound()->withException($notFoundException)->getResponse();
        } catch (Exception $exception) {
            return ErrorResponse::internalServerError()->withException($exception)->getResponse();
        }

        //Check if user belongs to a circle where the post is shared to
        if (!$access) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have permission to access this post"))
                                ->getResponse();
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
    private function validateGetComments(int $postId)
    {
        if (empty($postId) || !is_int($postId)) {
            return ErrorResponse::badRequest()->getResponse();
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $hasAccess = $PostService->checkUserAccessToPost($this->getUserId(), $postId);
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        }

        if (!$hasAccess) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have permission to access this post"))
                                ->getResponse();
        }

        return null;
    }

    /**
     * Validate deleting post endpoint
     *
     * @param int $postId
     *
     * @return ErrorResponse|null
     */
    private function validateDelete(int $postId)
    {
        if (empty($postId) || !is_int($postId)) {
            return ErrorResponse::badRequest()->getResponse();
        }
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init('TeamMember');

        if (!$Post->exists($postId)) {
            return ErrorResponse::notFound()->withMessage(__("This post doesn't exist."))->getResponse();
        }

        if (!$Post->isPostOwned($postId, $this->getUserId()) && !$TeamMember->isActiveAdmin($this->getUserId(),
                $this->getTeamId())) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have permission to access this post"))
                                ->getResponse();
        }
        return null;
    }

    /**
     * @param $postId
     *
     * @return CakeResponse| null
     */
    private function validatePut(int $postId)
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        if (!$Post->exists($postId)) {
            return ErrorResponse::notFound()->withMessage(__("This post doesn't exist."))->getResponse();
        }
        //Check whether user is the owner of the post
        if (!$Post->isPostOwned($postId, $this->getUserId())) {
            return ErrorResponse::forbidden()->withMessage(__("You don't have permission to access this post"))
                                ->getResponse();
        }

        $body = $this->getRequestJsonBody();

        try {

            PostRequestValidator::createPostEditValidator()->validate($body);

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
}