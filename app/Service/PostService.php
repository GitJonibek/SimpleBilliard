<?php
App::import('Service', 'AppService');
App::uses('AttachedFile', 'Model');
App::import('Service', 'PostFileService');
App::import('Service', 'AttachedFileService');
App::import('Service', 'UploadService');
App::import('Lib/Storage', 'UploadedFile');
App::uses('Circle', 'Model');
App::uses('PostShareUser', 'Model');
App::uses('PostShareCircle', 'Model');
App::uses('PostDraft', 'Model');
App::uses('PostRead', 'Model');
App::uses('PostMention', 'Model');
App::uses('PostLike', 'Model');
App::uses('PostFile', 'Model');
App::uses('PostResource', 'Model');
App::uses('PostSharedLog', 'Model');
App::uses('CircleMember', 'Model');
App::uses('Post', 'Model');
App::uses('User', 'Model');
App::import('Model/Entity', 'PostEntity');
App::import('Model/Entity', 'PostFileEntity');
App::import('Model/Entity', 'CircleEntity');
App::import('Model/Entity', 'AttachedFileEntity');
App::import('Model/Entity', 'PostFileEntity');

use Goalous\Enum as Enum;
use Goalous\Enum\Model\AttachedFile\AttachedFileType as AttachedFileType;
use Goalous\Enum\Model\AttachedFile\AttachedModelType as AttachedModelType;
use Goalous\Exception as GlException;

/**
 * Class PostService
 */
class PostService extends AppService
{

    /**
     * 月のインデックスからフィードの取得期間を取得
     *
     * @param int $monthIndex
     *
     * @return array ['start'=>unixtimestamp,'end'=>unixtimestamp]
     */
    function getRangeByMonthIndex(int $monthIndex): array
    {
        $start_month_offset = $monthIndex + 1;
        $ret['end'] = strtotime("-{$monthIndex} months", REQUEST_TIMESTAMP);
        $ret['start'] = strtotime("-{$start_month_offset} months", REQUEST_TIMESTAMP);
        return $ret;
    }

    /**
     * Adding new normal post from post_draft data
     *
     * @param array $postDraft
     *
     * @return array|false
     */
    public function addNormalFromPostDraft(array $postDraft)
    {
        try {
            $this->TransactionManager->begin();
            $post = $this->addNormal(
                json_decode($postDraft['draft_data'], true),
                $postDraft['user_id'],
                $postDraft['team_id'],
                // If draft is created, post resources is also created
                []
            );

            /** @var PostResourceService $PostResourceService */
            $PostResourceService = ClassRegistry::init('PostResourceService');
            // changing post_resources.post_id = null to posts.id
            if (false === $PostResourceService->updatePostIdByPostDraftId($post['id'], $postDraft['id'])) {
                GoalousLog::error($errorMessage = 'failed updating post_resources.post_id', [
                    'posts.id'       => $post['id'],
                    'post_drafts.id' => $postDraft['id'],
                ]);
                throw new RuntimeException('Error on adding post from draft: ' . $errorMessage);
            }

            /** @var PostDraft $PostDraft */
            $PostDraft = ClassRegistry::init('PostDraft');
            $postDraft['post_id'] = $post['id'];
            if (false === $PostDraft->save($postDraft)) {
                GoalousLog::error($errorMessage = 'failed saving post_draft', [
                    'posts.id'       => $post['id'],
                    'post_drafts.id' => $postDraft['id'],
                ]);
                throw new RuntimeException('Error on adding post from draft: ' . $errorMessage);
            }

            // Post is created by PostDraft
            // Deleting PostDraft because target PostDraft ended role
            // Could not judge if delete() is succeed or not (always returning false)
            $PostDraft->delete($postDraft['id']);
            $this->TransactionManager->commit();
            return $post;
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error('failed adding post data from draft', [
                'message' => $e->getMessage(),
            ]);
            GoalousLog::error($e->getTraceAsString());
        }
        return false;
    }

    /**
     * Adding new normal post with transaction
     *
     * @param array $postData
     * @param int   $userId
     * @param int   $teamId
     * @param array $postResources
     *
     * @return array|bool If success, returns posts data array, if failed, returning false
     */
    function addNormalWithTransaction(array $postData, int $userId, int $teamId, array $postResources = [])
    {
        try {
            $this->TransactionManager->begin();
            $post = $this->addNormal(
                $postData, $userId, $teamId, $postResources
            );
            $this->TransactionManager->commit();
            return $post;
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error('failed adding post data', [
                'message'  => $e->getMessage(),
                'users.id' => $userId,
                'teams.id' => $teamId,
            ]);
            GoalousLog::error($e->getTraceAsString());
        }
        return false;
    }

    /**
     * Adding new normal post
     * Be careful, no transaction in this method
     * You should write try-catch and transaction yourself outside of this function
     *
     * @param array $postData
     * @param int   $userId
     * @param int   $teamId
     * @param array $postResources array data of post_resources
     *
     * @return array Always return inserted post data array if succeed
     *      otherwise throwing exception
     * @throws \InvalidArgumentException
     *      If passed data is invalid or not enough, throws InvalidArgumentException
     * @throws \RuntimeException
     *      If failing on adding post, this function always throws exception in any case
     * @throws (\Throwable) will not throw this, but should define here
     *      PhpStorm shows warn because \Throwable appear in codes
     */
    function addNormal(array $postData, int $userId, int $teamId, array $postResources = []): array
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');
        /** @var PostShareUser $PostShareUser */
        $PostShareUser = ClassRegistry::init('PostShareUser');
        /** @var User $User */
        $User = ClassRegistry::init('User');
        /** @var PostShareCircle $PostShareCircle */
        $PostShareCircle = ClassRegistry::init('PostShareCircle');
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');
        /** @var PostFile $PostFile */
        $PostFile = ClassRegistry::init('PostFile');
        /** @var Circle $Circle */
        $Circle = ClassRegistry::init('Circle');

        // TODO: should be fix for better system
        // Having deep dependence on each class's property(my_uid, current_team_id).
        // These property is null. If method used on the session called by "batch shell" or "non-auth routing".
        $PostFile->AttachedFile->current_team_id = $teamId;
        $PostFile->AttachedFile->my_uid = $userId;
        $PostFile->AttachedFile->PostFile->current_team_id = $teamId;
        $PostFile->AttachedFile->PostFile->my_uid = $userId;

        // Why? if using this line, the Post record increase on CakePHP fixture
        //$PostFile->AttachedFile->PostFile->Post->current_team_id = $teamId;
        //$PostFile->AttachedFile->PostFile->Post->my_uid = $userId;
        $PostFile->AttachedFile->PostFile->Post->PostShareCircle->current_team_id = $teamId;
        $PostFile->AttachedFile->PostFile->Post->PostShareCircle->my_uid = $userId;
        $PostFile->AttachedFile->PostFile->Post->PostShareUser->current_team_id = $teamId;
        $PostFile->AttachedFile->PostFile->Post->PostShareUser->my_uid = $userId;
        $User->CircleMember->current_team_id = $teamId;
        $User->CircleMember->my_uid = $userId;
        $PostShareCircle->current_team_id = $teamId;
        $PostShareCircle->my_uid = $userId;
        $PostShareCircle->Circle->current_team_id = $teamId;
        $PostShareCircle->Circle->my_uid = $userId;

        if (!isset($postData['Post']) || empty($postData['Post'])) {
            GoalousLog::error('Error on adding post: Invalid argument', [
                'users.id' => $userId,
                'teams.id' => $teamId,
                'postData' => $postData,
            ]);
            throw new InvalidArgumentException('Error on adding post: Invalid argument');
        }
        $share = null;
        if (isset($postData['Post']['share']) && !empty($postData['Post']['share'])) {
            $share = explode(",", $postData['Post']['share']);
            foreach ($share as $key => $val) {
                if (stristr($val, 'public')) {
                    $teamAllCircle = $Circle->getTeamAllCircle($teamId);
                    $share[$key] = 'circle_' . $teamAllCircle['Circle']['id'];
                }
            }
        }
        $postData['Post']['user_id'] = $userId;
        $postData['Post']['team_id'] = $teamId;
        if (!isset($postData['Post']['type'])) {
            $postData['Post']['type'] = Post::TYPE_NORMAL;
        }

        $Post->create();
        $post = $Post->save($postData, [
            'atomic' => false,
        ]);
        if (empty($post)) {
            GoalousLog::error('Error on adding post: failed post save', [
                'users.id' => $userId,
                'teams.id' => $teamId,
                'postData' => $postData,
            ]);
            throw new RuntimeException('Error on adding post: failed post save');
        }
        $postId = $post['Post']['id'];
        // If posted with attach files
        if (isset($postData['file_id']) && is_array($postData['file_id'])) {
            if (false === $PostFile->AttachedFile->saveRelatedFiles($postId,
                    AttachedFile::TYPE_MODEL_POST,
                    $postData['file_id'])
            ) {
                throw new RuntimeException('Error on adding post: failed saving related files');
            }
        }
        // Handling post resources
        foreach ($postResources as $postResource) {
            $PostResource->create();
            $postResource = $PostResource->save([
                'post_id'       => $postId,
                'post_draft_id' => null,
                // TODO: currently only resource type of video only https://jira.goalous.com/browse/GL-6601
                // need to determine what type of resource is passed from arguments
                // (maybe should wrap by class, not simple array)
                // same as in PostDraftService::createPostDraftWithResources()
                'resource_type' => Enum\Model\Post\PostResourceType::VIDEO_STREAM()->getValue(),
                'resource_id'   => $postResource['id'],
            ], [
                'atomic' => false
            ]);
            $postResource = reset($postResource);
        }

        if (!empty($share)) {
            // ユーザとサークルに分割
            $users = [];
            $circles = [];
            foreach ($share as $val) {
                // ユーザの場合
                if (stristr($val, 'user_')) {
                    $users[] = str_replace('user_', '', $val);
                } // サークルの場合
                elseif (stristr($val, 'circle_')) {
                    $circles[] = str_replace('circle_', '', $val);
                }
            }
            if ($users) {
                // Save share users
                if (false === $PostShareUser->add($postId, $users)) {
                    GoalousLog::error($errorMessage = 'failed saving post share users', [
                        'posts.id'  => $postId,
                        'users.ids' => $users,
                    ]);
                    throw new RuntimeException('Error on adding post: ' . $errorMessage);
                }
            }
            if ($circles) {
                try {
                    // Save share circles
                    if (false === $PostShareCircle->add($postId, $circles, $teamId)) {
                        GoalousLog::error($errorMessage = 'failed saving post share circles', [
                            'posts.id'    => $postId,
                            'circles.ids' => $postId,
                            'teams.id'    => $teamId,
                        ]);
                        throw new RuntimeException('Error on adding post: ' . $errorMessage);
                    }
                    // Update unread post numbers if specified sharing circle
                    if (false === $User->CircleMember->incrementUnreadCount($circles, true, $teamId)) {
                        GoalousLog::error($errorMessage = 'failed increment unread count', [
                            'circles.ids' => $postId,
                            'teams.id'    => $teamId,
                        ]);
                        throw new RuntimeException('Error on adding post: ' . $errorMessage);
                    }
                    // Update modified date if specified sharing circle
                    if (false === $User->CircleMember->updateModified($circles, $teamId)) {
                        GoalousLog::error($errorMessage = 'failed update modified of circle member', [
                            'circles.ids' => $circles,
                            'teams.id'    => $teamId,
                        ]);
                        throw new RuntimeException('Error on adding post: ' . $errorMessage);
                    }
                    // Same as above
                    if (false === $PostShareCircle->Circle->updateModified($circles)) {
                        GoalousLog::error($errorMessage = 'failed update modified of circles', [
                            'circles.ids' => $circles,
                        ]);
                        throw new RuntimeException('Error on adding post: ' . $errorMessage);
                    }
                } catch (\Throwable $e) {
                    $PostFile->AttachedFile->deleteAllRelatedFiles($postId, AttachedFile::TYPE_MODEL_POST);
                    throw $e;
                }
            }
        }

        // If attached file is specified, deleting temporary updated files
        if (isset($postData['file_id']) && is_array($postData['file_id'])) {
            /** @var GlRedis $GlRedis */
            $GlRedis = ClassRegistry::init('GlRedis');
            foreach ($postData['file_id'] as $hash) {
                $GlRedis->delPreUploadedFile($teamId, $userId, $hash);
            }
        }

        return reset($post);
    }

    /**
     * Save favorite post
     *
     * @param int $postId
     * @param int $userId
     * @param int $teamId
     *
     * @return bool
     */
    function saveItem(int $postId, int $userId, int $teamId): bool
    {
        /** @var SavedPost $SavedPost */
        $SavedPost = ClassRegistry::init("SavedPost");

        try {
            $SavedPost->create();
            $SavedPost->save([
                'post_id' => $postId,
                'user_id' => $userId,
                'team_id' => $teamId,
            ]);
        } catch (Exception $e) {
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            return false;
        }
        return true;
    }

    /**
     * Delete favorite post
     *
     * @param int $postId
     * @param int $userId
     *
     * @return bool
     */
    function deleteItem(int $postId, int $userId): bool
    {
        /** @var SavedPost $SavedPost */
        $SavedPost = ClassRegistry::init("SavedPost");

        try {
            $SavedPost->deleteAll([
                'post_id' => $postId,
                'user_id' => $userId,
            ]);
        } catch (Exception $e) {
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            return false;
        }
        return true;
    }

    /**
     * Method to save a circle post
     *
     * @param array    $postBody
     *                   ["body" => '',
     *                   "type" => ''
     *                   ]
     * @param int      $circleId
     * @param int      $userId
     * @param int      $teamId
     * @param string[] $fileIDs
     *
     * @return PostEntity Entity of saved post
     * @throws Exception
     */
    public function addCirclePost(
        array $postBody,
        int $circleId,
        int $userId,
        int $teamId,
        array $fileIDs = []
    ): PostEntity {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');
        /** @var PostShareCircle $PostShareCircle */
        $PostShareCircle = ClassRegistry::init('PostShareCircle');
        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        if (empty($postBody['body'])) {
            GoalousLog::error('Error on adding post: Invalid argument', [
                'users.id'  => $userId,
                'circle.id' => $circleId,
                'teams.id'  => $teamId,
                'postData'  => $postBody,
            ]);
            throw new InvalidArgumentException('Error on adding post: Invalid argument');
        }

        try {
            $this->TransactionManager->begin();
            $Post->create();

            $postBody['user_id'] = $userId;
            $postBody['team_id'] = $teamId;

            if ($postBody['type'] == Post::TYPE_CREATE_CIRCLE) {
                $postBody['circle_id'] = $circleId;
            } elseif (empty($postBody['type'])) {
                $postBody['type'] = Post::TYPE_NORMAL;
            }

            /** @var PostEntity $savedPost */
            $savedPost = $Post->useType()->useEntity()->save($postBody, false);

            if (empty ($savedPost)) {
                GoalousLog::error('Error on adding post: failed post save', [
                    'users.id'  => $userId,
                    'circle.id' => $circleId,
                    'teams.id'  => $teamId,
                    'postData'  => $postBody,
                ]);
                throw new RuntimeException('Error on adding post: failed post save');
            }

            $postId = $savedPost['id'];
            $postCreated = $savedPost['created'];

            //Update last_posted time
            $updateCondition = [
                'CircleMember.user_id'   => $userId,
                'CircleMember.circle_id' => $circleId
            ];

            if (!$CircleMember->updateAll(['last_posted' => $postCreated], $updateCondition)) {
                GoalousLog::error($errorMessage = 'failed updating last_posted in circle_members', [
                    'posts.id'    => $postId,
                    'circles.ids' => $circleId,
                    'teams.id'    => $teamId,
                    'users.id'    => $userId,
                ]);
                throw new RuntimeException('Error on adding post: ' . $errorMessage);
            }

            // Save share circles
            if (false === $PostShareCircle->add($postId, [$circleId], $teamId)) {
                GoalousLog::error($errorMessage = 'failed saving post share circles', [
                    'posts.id'    => $postId,
                    'circles.ids' => $circleId,
                    'teams.id'    => $teamId,
                ]);
                throw new RuntimeException('Error on adding post: ' . $errorMessage);
            }
            // Update unread post numbers if specified sharing circle
            if (false === $CircleMember->incrementUnreadCount([$circleId], true, $teamId)) {
                GoalousLog::error($errorMessage = 'failed increment unread count', [
                    'circles.ids' => $postId,
                    'teams.id'    => $teamId,
                ]);
                throw new RuntimeException('Error on adding post: ' . $errorMessage);
            }
            //Save attached files
            if (!empty($fileIDs)) {
                $this->saveFiles($postId, $userId, $teamId, $fileIDs);
            }

            $this->TransactionManager->commit();

        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            throw $e;
        }

        return $savedPost;
    }

    /**
     * Get query condition for posts made by an user
     *
     * @param int $userId User ID of the posts author
     *
     * @return array
     */
    public function getUserPostListCondition(int $userId)
    {
        return ['Post.user_id' => $userId];
    }

    /**
     * Check whether the user can view the post
     *
     * @param int  $userId
     * @param int  $postId
     * @param bool $mustBelong Whether user must belong to the circle where post is shared to
     *
     * @return bool
     */
    public function checkUserAccessToPost(int $userId, int $postId, bool $mustBelong = false): bool
    {
        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init("CircleMember");

        /** @var Circle $Circle */
        $Circle = ClassRegistry::init('Circle');

        $circleOption = [
            'conditions' => [
                'PostShareCircle.post_id' => $postId,
            ],
            'fields'     => [
                'Circle.id',
                'Circle.public_flg',
                'Circle.team_all_flg'
            ],
            'table'      => 'circles',
            'alias'      => 'Circle',
            'joins'      => [
                [
                    'type'       => 'INNER',
                    'conditions' => [
                        'Circle.id = PostShareCircle.circle_id',
                    ],
                    'table'      => 'post_share_circles',
                    'alias'      => 'PostShareCircle',
                    'field'      => 'PostShareCircle.circle_id'
                ]
            ]
        ];

        /** @var CircleEntity $circles */
        $circles = $Circle->useType()->useEntity()->find('all', $circleOption);

        if (empty($circles)) {
            throw new GlException\GoalousNotFoundException(__("This post doesn't exist."));
        }

        $circleArray = [];

        foreach ($circles as $circle) {
            $circleArray[] = $circle['id'];
            //If circle is public or team_all, return true
            if (!$mustBelong && ($circle['public_flg'] || $circle['team_all_flg'])) {
                return true;
            }
        }

        $circleMemberOption = [
            'conditions' => [
                'CircleMember.circle_id' => $circleArray,
                'CircleMember.user_id'   => $userId,
                'CircleMember.del_flg'   => false
            ],
            'table'      => 'circle_members',
            'alias'      => 'CircleMember',
            'fields'     => 'CircleMember.circle_id'
        ];

        $circleList = (int)$CircleMember->find('count', $circleMemberOption) ?? 0;

        return $circleList > 0;
    }

    /**
     * Get list of attached files of a post
     *
     * @param int                                              $postId
     * @param Goalous\Enum\Model\AttachedFile\AttachedFileType $type Filtered file type
     *
     * @return AttachedFileEntity[]
     */
    public function getAttachedFiles(int $postId, AttachedFileType $type = null): array
    {
        /** @var AttachedFile $AttachedFile */
        $AttachedFile = ClassRegistry::init('AttachedFile');

        $conditions = [
            'conditions' => [],
            'table'      => 'attached_files',
            'alias'      => 'AttachedFile',
            'joins'      => [
                [
                    'type'       => 'INNER',
                    'table'      => 'post_files',
                    'alias'      => 'PostFile',
                    'conditions' => [
                        'PostFile.post_id' => $postId,
                        'PostFile.attached_file_id = AttachedFile.id'
                    ]
                ]
            ]
        ];

        if (!empty($type)) {
            $conditions['conditions']['file_type'] = $type->getValue();
        }

        return $AttachedFile->useType()->useEntity()->find('all', $conditions);
    }

    /**
     * Soft delete circle post and its related data
     *
     * @param int $postId
     *
     * @return bool
     * @throws Exception
     */
    public function softDelete(int $postId): bool
    {
        $condition = ["post_id" => $postId];
        $postCondition = ["Post.id" => $postId];

        /** @var PostDraft $PostDraft */
        $PostDraft = ClassRegistry::init('PostDraft');

        /** @var PostFile $PostFile */
        $PostFile = ClassRegistry::init('PostFile');

        /** @var PostLike $PostLike */
        $PostLike = ClassRegistry::init('PostLike');

        /** @var PostMention $PostMention */
        $PostMention = ClassRegistry::init('PostMention');

        /** @var PostRead $PostRead */
        $PostRead = ClassRegistry::init('PostRead');

        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        /** @var PostShareCircle $PostShareCircle */
        $PostShareCircle = ClassRegistry::init('PostShareCircle');

        /** @var PostShareUser $PostShareUser */
        $PostShareUser = ClassRegistry::init('PostShareUser');

        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        try {
            $this->TransactionManager->begin();
            $res = $PostDraft->softDeleteAll($condition, false) &&
                $PostFile->softDeleteAll($condition, false) &&
                $PostLike->softDeleteAll($condition, false) &&
                $PostMention->softDeleteAll($condition, false) &&
                $PostRead->softDeleteAll($condition, false) &&
                $PostResource->softDeleteAll($condition, false) &&
                $PostShareCircle->softDeleteAll($condition, false) &&
                $PostShareUser->softDeleteAll($condition, false) &&
                $Post->softDeleteAll($postCondition, false);
            if (!$res) {
                throw new RuntimeException("Error on deleting post $postId: failed post soft delete");
            }
            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error("Error on deleting post $postId: failed post soft delete", $e->getTrace());
            throw $e;
        }

        return true;
    }

    /**
     * Save all attached files
     *
     * @param int   $postId
     * @param int   $userId
     * @param int   $teamId
     * @param array $fileIDs
     *
     * @return bool
     * @throws Exception
     */
    private function saveFiles(int $postId, int $userId, int $teamId, array $fileIDs): bool
    {
        /** @var UploadService $UploadService */
        $UploadService = ClassRegistry::init('UploadService');
        /** @var AttachedFileService $AttachedFileService */
        $AttachedFileService = ClassRegistry::init('AttachedFileService');
        /** @var PostFileService $PostFileService */
        $PostFileService = ClassRegistry::init('PostFileService');

        $postFileIndex = 0;

        $addedFiles = [];

        try {
            //Save attached files
            foreach ($fileIDs as $id) {
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $UploadService->getBuffer($userId, $teamId, $id);

                /** @var AttachedFileEntity $attachedFile */
                $attachedFile = $AttachedFileService->add($userId, $teamId, $uploadedFile,
                    AttachedModelType::TYPE_MODEL_POST());

                $addedFiles[] = $attachedFile['id'];

                $PostFileService->add($postId, $attachedFile['id'], $teamId, $postFileIndex++);

                $UploadService->saveWithProcessing("AttachedFile", $attachedFile['id'], 'attached', $uploadedFile);
            }
        } catch (Exception $e) {
            //If any error happened, remove uploaded file
            foreach ($addedFiles as $id) {
                $UploadService->deleteAsset('AttachedFile', $id);
            }
            throw $e;
        }

        return true;
    }

    /**
     * Edit a post body
     *
     * @param string $newBody
     * @param int    $postId
     *
     * @return PostEntity Updated post
     * @throws Exception
     */
    public function editPost(string $newBody, int $postId): PostEntity
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init('Post');

        if (!$Post->exists($postId)) {
            throw new GlException\GoalousNotFoundException(__("This post doesn't exist."));
        }
        try {
            $this->TransactionManager->begin();

            $newData = [
                'body'     => '"' . $newBody . '"',
                'modified' => REQUEST_TIMESTAMP
            ];

            if (!$Post->updateAll($newData, ['Post.id' => $postId])) {
                throw new RuntimeException("Failed to update post");
            }

            //TODO GL-7259

            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            throw $e;
        }
        /** @var PostEntity $result */
        $result = $Post->useType()->useEntity()->find('first', ['conditions' => ['id' => $postId]]);

        return $result;
    }
}
