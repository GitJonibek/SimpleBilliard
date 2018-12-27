<?php
App::import('Service', 'CommentService');
App::import('Service', 'PostService');
App::import('Service', 'PostLikeService');
App::import('Service', 'PostReadService');
App::import('Service', 'SavedPostService');
App::import('Lib/Paging', 'PagingRequest');
App::import('Service/Paging', 'CommentPagingService');
App::import('Service/Paging', 'PostLikesPagingService');
App::import('Service/Paging', 'PostReaderPagingService');
App::uses('CircleMember', 'Model');
App::uses('Post', 'Model');
App::uses('BasePagingController', 'Controller/Api');
App::uses('PostShareCircle', 'Model');
App::uses('PostRequestValidator', 'Validator/Request/Api/V2');
App::uses('TeamMember', 'Model');
App::import('Lib/DataExtender', 'CommentExtender');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/18
 * Time: 15:00
 */

use Goalous\Exception as GlException;

class PostsController extends BasePagingController
{
    public $components = [
        'NotifyBiz',
        'GlEmail',
    ];

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

        $requestData = $this->getRequestJsonBody();
        $post['body'] = Hash::get($requestData, 'body');
        $post['type'] = (int)Hash::get($requestData, 'type');
        $post['site_info'] = Hash::get($requestData, 'site_info');

        $circleId = (int)Hash::get($requestData, 'circle_id');
        $fileIds = Hash::get($requestData, 'file_ids', []);

        try {
            $res = $PostService->addCirclePost($post, $circleId, $this->getUserId(), $this->getTeamId(), $fileIds);
            $this->_notifyNewPost($res);

        } catch (InvalidArgumentException $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->withMessage(__("Failed to post."))
                ->getResponse();
        }
        return ApiResponse::ok()->withData($res->toArray())->getResponse();
    }

    /**
     * Notify new post to other members
     *
     * @param array $newPost
     */
    private function _notifyNewPost(PostEntity $newPost)
    {
        // Notify to other members
        $postedPostId = $newPost['id'];
        $notifyType = NotifySetting::TYPE_FEED_POST;

        /** @var NotifyBizComponent $NotifyBiz */
        $this->NotifyBiz->execSendNotify($notifyType, $postedPostId, null, null, $newPost['team_id'], $newPost['user_id']);

        // TODO: Realtime notification with WebSocket.
        // But to implement, we have to decide how realize WebSocket at first
        // e.g. use Pusher like old Goalous, or scratch implementing, etc
    }


    public function get_comments(int $postId)
    {
        $error = $this->validatePostAccess($postId);
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
            $this->getExtensionOptions() ?: $this->getDefaultCommentsExtension());

        return ApiResponse::ok()->withBody($result)->getResponse();
    }

    /**
     * Default extension options for getting comments
     *
     * @return array
     */
    private function getDefaultCommentsExtension()
    {
        return [
            CommentExtender::EXTEND_ALL
        ];
    }

    /**
     * Get list of the post readers
     *
     * @param int $postId
     *
     * @return BaseApiResponse
     */
    public function get_reads(int $postId)
    {
        $error = $this->validatePostAccess($postId);
        if (!empty($error)) {
            return $error;
        }

        /** @var PostReaderPagingService $PostReaderPagingService */
        $PostReaderPagingService = ClassRegistry::init("PostReaderPagingService");

        try {
            $pagingRequest = $this->getPagingParameters();
        } catch (Exception $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        }

        try {
            $result = $PostReaderPagingService->getDataWithPaging(
                $pagingRequest,
                $this->getPagingLimit(),
                $this->getExtensionOptions() ?: $this->getDefaultReaderExtension());
        } catch (Exception $e) {
            GoalousLog::error($e->getMessage(), $e->getTrace());
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withBody($result)->getResponse();
    }

    /**
     * Add readers of the post
     *
     * @param int $postId
     *
     * @return BaseApiResponse
     */
    public function post_reads()
    {

        $error = $this->validatePostRead();
        if (!empty($error)) {
            return $error;
        }

        $postsIds = Hash::get($this->getRequestJsonBody(), 'posts_ids', []);

        /** @var PostReadService $PostReadService */
        $PostReadService = ClassRegistry::init('PostReadService');

        try {
            $res = $PostReadService->multipleAdd($postsIds, $this->getUserId(), $this->getTeamId());
        } catch (InvalidArgumentException $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->withMessage(__("Failed to read post."))
                ->getResponse();
        }

        return ApiResponse::ok()->withData(["posts_ids" => $res])->getResponse();
    }

    /**
     * Default extension options for getting user that readers of the post
     *
     * @return array
     */
    private function getDefaultReaderExtension()
    {
        return [
            PostReadExtender::EXTEND_USER
        ];
    }

    /**
     * Default extension options for getting user that likes the post
     *
     * @return array
     */
    private function getDefaultLikesUserExtension()
    {
        return [
            PostLikeExtender::EXTEND_USER
        ];
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

    public function post_likes(int $postId): CakeResponse
    {
        $res = $this->validatePostAccess($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var PostLikeService $PostLikeService */
        $PostLikeService = ClassRegistry::init('PostLikeService');

        try {
            $result = $PostLikeService->add($postId, $this->getUserId(), $this->getTeamId());
        } catch (GlException\GoalousConflictException $exception) {
            return ErrorResponse::resourceConflict()->withException($exception)->getResponse();
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
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData(["post_id" => $postId])->getResponse();
    }

    /**
     * @param int $postId
     *
     * @return CakeResponse
     */
    public function delete_likes(int $postId): CakeResponse
    {
        $res = $this->validatePostAccess($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var PostLikeService $PostLikeService */
        $PostLikeService = ClassRegistry::init('PostLikeService');

        try {
            $count = $PostLikeService->delete($postId, $this->getUserId());
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }
        return ApiResponse::ok()->withData(["like_count" => $count])->getResponse();
    }

    /**
     * Get list of the user who likes the post
     *
     * @param int $postId
     *
     * @return BaseApiResponse
     */
    public function get_likes(int $postId)
    {
        $error = $this->validatePostAccess($postId);
        if (!empty($error)) {
            return $error;
        }

        /** @var PostLikesPagingService $PostLikesPagingService */
        $PostLikesPagingService = ClassRegistry::init("PostLikesPagingService");

        try {
            $pagingRequest = $this->getPagingParameters();
        } catch (Exception $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        }

        try {
            $result = $PostLikesPagingService->getDataWithPaging(
                $pagingRequest,
                $this->getPagingLimit(),
                $this->getExtensionOptions() ?: $this->getDefaultLikesUserExtension());
        } catch (Exception $e) {
            GoalousLog::error($e->getMessage(), $e->getTrace());
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withBody($result)->getResponse();
    }

    /**
     * Post save method
     *
     * @param int $postId
     *
     * @return BaseApiResponse
     */
    public function post_saves(int $postId): CakeResponse
    {
        $res = $this->validatePostAccess($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var SavedPostService $SavedPostService */
        $SavedPostService = ClassRegistry::init('SavedPostService');

        try {
            $result = $SavedPostService->add($postId, $this->getUserId(), $this->getTeamId());
        } catch (GlException\GoalousConflictException $ConflictException) {
            return ErrorResponse::resourceConflict()->withException($ConflictException)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }

        return ApiResponse::ok()->withData($result->toArray())->getResponse();
    }

    /**
     * @param int $postId
     *
     * @return CakeResponse
     */
    public function delete_saves(int $postId): CakeResponse
    {
        $res = $this->validatePostAccess($postId);

        if (!empty($res)) {
            return $res;
        }

        /** @var SavedPostService $SavedPostService */
        $SavedPostService = ClassRegistry::init('SavedPostService');

        try {
            $SavedPostService->delete($postId, $this->getUserId());
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->getResponse();
        }
        return ApiResponse::ok()->withData(["post_id" => $postId])->getResponse();
    }

    /**
     * Endpoint for saving a new comment
     *
     * @param int $postId Id of the post to comment to
     *
     * @return CakeResponse
     */
    public function post_comments(int $postId)
    {
        /* Validate user access to this post */
        $error = $this->validatePostComments($postId);

        if (!empty($error)) {
            return $error;
        }

        /** @var CommentService $CommentService */
        $CommentService = ClassRegistry::init('CommentService');

        $requestBody = $this->getRequestJsonBody();
        $commentBody['body'] = Hash::get($requestBody, 'body');
        $commentBody['site_info'] = Hash::get($requestBody, 'site_info');
        $fileIDs = Hash::get($requestBody, 'file_ids', []);

        $userId = $this->getUserId();
        $teamId = $this->getTeamId();
        try {
            $res = $CommentService->add($commentBody, $postId, $userId, $teamId, $fileIDs);
            $this->notifyNewComment($res['id'], $postId, $this->getUserId());
        } catch (GlException\GoalousNotFoundException $exception) {
            return ErrorResponse::notFound()->withException($exception)->getResponse();
        } catch (InvalidArgumentException $e) {
            return ErrorResponse::badRequest()->withException($e)->getResponse();
        } catch (Exception $e) {
            return ErrorResponse::internalServerError()->withException($e)->withMessage(__("Failed to comment."))
                ->getResponse();
        }

        /** @var CommentExtender $CommentExtender */
        $CommentExtender = ClassRegistry::init('CommentExtender');
        $comment = $res->toArray();
        $comment = $CommentExtender->extend($comment, $userId, $teamId, [CommentExtender::EXTEND_ALL]);

        return ApiResponse::ok()->withData($comment)->getResponse();
    }

    /**
     * @return CakeResponse|null
     */
    private function validatePost()
    {
        $requestBody = $this->getRequestJsonBody();

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        $circleId = (int)Hash::get($requestBody, 'circle_id');

        if (!empty($circleId) && !$CircleMember->isJoined($circleId, $this->getUserId())) {
            return ErrorResponse::forbidden()->withMessage(__("The circle doesn't exist or you don't have permission."))
                ->getResponse();
        }
        try {
            PostRequestValidator::createDefaultPostValidator()->validate($requestBody);
            PostRequestValidator::createFileUploadValidator()->validate($requestBody);
            switch ($requestBody['type']) {
                case Post::TYPE_NORMAL:
                    PostRequestValidator::createCirclePostValidator()->validate($requestBody);
                    break;
            }
        } catch (\Respect\Validation\Exceptions\AllOfException $e) {
            return ErrorResponse::badRequest()
                ->addErrorsFromValidationException($e)
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
     * Validate access to post
     *
     * @param int  $postId
     * @param bool $mustBelong Whether user must belong to the circle where post is made
     *
     * @return CakeResponse|null
     */
    private function validatePostAccess(int $postId, bool $mustBelong = false)
    {
        if (empty($postId) || !is_int($postId)) {
            return ErrorResponse::badRequest()->getResponse();
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $access = $PostService->checkUserAccessToCirclePost($this->getUserId(), $postId);
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

    /**
     * Validation function for adding / removing save from a post
     *
     * @param int $postId
     *
     * @return CakeResponse|null
     */
    private function validateSave(int $postId)
    {
        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $access = $PostService->checkUserAccessToCirclePost($this->getUserId(), $postId);
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
     * Validate get comments and readers endpoint
     *
     * @param int $postId
     *
     * @return ErrorResponse|null
     */
    private function validateAccessToPost(int $postId)
    {
        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $hasAccess = $PostService->checkUserAccessToCirclePost($this->getUserId(), $postId);
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
     * @return CakeResponse|null
     */
    private function validatePostRead()
    {
        $requestBody = $this->getRequestJsonBody();

        $postsIds = Hash::get($requestBody, 'posts_ids', []);

        try {
            PostRequestValidator::createPostReadValidator()->validate($requestBody);
        } catch (\Respect\Validation\Exceptions\AllOfException $e) {
            return ErrorResponse::badRequest()
                ->addErrorsFromValidationException($e)
                ->getResponse();
        } catch (Exception $e) {
            GoalousLog::error('Unexpected validation exception', [
                'class'   => get_class($e),
                'message' => $e,
            ]);
            return ErrorResponse::internalServerError()->getResponse();
        }

        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        try {
            $PostService->checkUserAccessToMultiplePost($this->getUserId(), $postsIds);
        } catch (GlException\GoalousNotFoundException $notFoundException) {
            return ErrorResponse::notFound()->withException($notFoundException)->getResponse();
        } catch (Exception $exception) {
            return ErrorResponse::internalServerError()->withException($exception)->getResponse();
        }

        return null;
    }

    private function validatePostComments(int $postId)
    {
        /** @var PostService $PostService */
        $PostService = ClassRegistry::init('PostService');

        $requestBody = $this->getRequestJsonBody();

        try {
            PostRequestValidator::createPostCommentValidator()->validate($requestBody);
            PostRequestValidator::createFileUploadValidator()->validate($requestBody);
        } catch (\Respect\Validation\Exceptions\AllOfException $e) {
            return ErrorResponse::badRequest()
                ->addErrorsFromValidationException($e)
                ->getResponse();
        } catch (Exception $e) {
            GoalousLog::error('Unexpected validation exception', [
                'class'   => get_class($e),
                'message' => $e,
            ]);
            return ErrorResponse::internalServerError()->getResponse();
        }

        try {
            $access = $PostService->checkUserAccessToCirclePost($this->getUserId(), $postId, true);
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

    /**
     * Send notification about new comment on a post.
     * Will notify post's author & other users who've commented on the post
     *
     * @param int   $commentId      Comment ID of the new comment
     * @param int   $postId         Post ID where the comment belongs to
     * @param int   $userId         User ID of the author of the new comment
     * @param int[] $mentionedUsers List of user IDs of mentioned users
     */
    private function notifyNewComment(int $commentId, int $postId, int $userId, array $mentionedUsers = [])
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        $type = $Post->getPostType($postId);

        switch ($type) {
            case Post::TYPE_NORMAL:
                // This notification must not be sent to those who mentioned
                // because we exlude them in NotifyBiz#execSendNotify.
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_MY_POST, $postId,
                    $commentId);
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_POST,
                    $postId, $commentId);
                //TODO Enable mention notification
//                $NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_MENTIONED_IN_COMMENT, $postId, $commentId, $mentionedUsers);
                break;
            case Post::TYPE_ACTION:
                // This notification must not be sent to those who mentioned
                // because we exlude them in NotifyBiz#execSendNotify.
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_MY_ACTION,
                    $postId,
                    $commentId);
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_ACTION,
                    $postId, $commentId);
                //TODO Enable mention notification
//                $NotifyBiz->execSendNotify(NotifySetting::TYPE_FEED_MENTIONED_IN_COMMENT, $postId, $commentId, $mentionedUsers);
                break;
            case Post::TYPE_CREATE_GOAL:
                $this->notifyUserOfGoalComment($userId, $postId);
                break;
        }
    }

    /**
     * Send notification if a Goal post is commented
     *
     * @param int $commentAuthorUserId ID of user who made the comment
     * @param int $postId              Post ID where the comment belongs to
     */
    private function notifyUserOfGoalComment(int $commentAuthorUserId, int $postId)
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        $postData = $Post->getEntity($postId);

        $postId = $postData['id'];
        $postOwnerUserId = $postData['user_id'];

        //If commenter is not post owner, send notification to owner
        if ($commentAuthorUserId !== $postOwnerUserId) {
            $this->NotifyBiz->sendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_GOAL, null, null,
                [$postOwnerUserId], $commentAuthorUserId, $postData['team_id'], $postId);
        }
        $excludedUserList = array($postOwnerUserId, $commentAuthorUserId);

        /** @var Comment $Comment */
        $Comment = ClassRegistry::init('Comment');
        $notificationReceiverUserList = $Comment->getCommentedUniqueUsersList($postId, false, $excludedUserList);

        if (!empty($notificationReceiverUserList)) {
            $this->NotifyBiz->sendNotify(NotifySetting::TYPE_FEED_COMMENTED_ON_COMMENTED_GOAL, null, null,
                $notificationReceiverUserList, $commentAuthorUserId, $postData['team_id'], $postId);
        }
    }
}
