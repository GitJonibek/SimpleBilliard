<?php

App::import('Service', 'AppService');
App::uses('PostResource', 'Model');
App::uses('PostFile', 'Model');

use Goalous\Enum as Enum;

/**
 * Class PostResourceService
 */
class PostResourceService extends AppService
{
    public function updatePostResourceIndex(int $postId, int $resourceId, int $resourceOrder)
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        $PostResource->updateAll([
            'resource_order' => $resourceOrder,
        ],[
            'post_id' => $postId,
            'resource_id' => $resourceId,
        ]);
    }

    public function deleteAllPostResourceByPostId(int $postId)
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        $PostResource->updateAll([
            'del_flg' => true,
            'deleted' => GoalousDateTime::now()->timestamp
        ],[
            'post_id' => $postId,
        ]);
    }

    public function deletePostResource(int $postId, int $resourceId)
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        $PostResource->updateAll([
            'del_flg' => true,
            'deleted' => GoalousDateTime::now()->timestamp
        ],[
            'post_id' => $postId,
            'resource_id' => $resourceId,
        ]);
    }

    public function isPostResourceExists(int $postId, int $resourceId, int $resourceType): bool
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        $r = $PostResource->find('first', [
            'conditions' => [
                'PostResource.post_id' => $postId,
                'PostResource.resource_type' => $resourceType,
                'PostResource.resource_id' => $resourceId,
                'PostResource.del_flg'   => [0, 1],
            ],
            'fields'     => [
                'PostResource.id'
            ]
        ]);
        return !empty($r);
    }

    public function addResourcePost(int $postId, Enum\Model\Post\PostResourceType $postResourceType, int $resourceId, int $resourceOrder)
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init("PostResource");

        $PostResource->create();
        $result = $PostResource->save([
            'post_id'        => $postId,
            'post_draft_id'  => null,
            'resource_type'  => $postResourceType->getValue(),
            'resource_id'    => $resourceId,
            'resource_order' => $resourceOrder,
        ], [
            'atomic' => false
        ]);
        return reset($result);
    }

    public function addResourceDraft(int $postDraftId, Enum\Model\Post\PostResourceType $postResourceType, int $resourceId, int $resourceOrder)
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init("PostResource");

        $PostResource->create();
        $result = $PostResource->save([
            'post_id' => null,
            'post_draft_id' => $postDraftId,
            'resource_type' => $postResourceType->getValue(),
            'resource_id'   => $resourceId,
            'resource_order' => $resourceOrder,
        ], [
            'atomic' => false
        ]);
        return reset($result);
    }

    public function getPostResourceTypeFromAttachedFileType(int $attachedFileType): Enum\Model\Post\PostResourceType
    {
        switch ($attachedFileType) {
            case Enum\Model\AttachedFile\AttachedFileType::TYPE_FILE_IMG:
                return Enum\Model\Post\PostResourceType::IMAGE();
            case Enum\Model\AttachedFile\AttachedFileType::TYPE_FILE_VIDEO:
                return Enum\Model\Post\PostResourceType::FILE_VIDEO();
            case Enum\Model\AttachedFile\AttachedFileType::TYPE_FILE_DOC:
                return Enum\Model\Post\PostResourceType::FILE();
        }
        throw new RuntimeException('Unknown AttachedFileType: ' . $attachedFileType);
    }

    public function updatePostIdByPostDraftId(int $postId, $postDraftId): bool
    {
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init('PostResource');

        $ret = $PostResource->updateAll([
            'PostResource.post_id' => $postId,
        ], [
            'PostResource.post_draft_id' => $postDraftId,
        ]);

        return !empty($ret);
    }

    public function copyResourceToPostFiles(int $postId)
    {
        /** @var Post $Post */
        $Post = ClassRegistry::init("Post");
        /** @var PostResource $PostResource */
        $PostResource = ClassRegistry::init("PostResource");
        /** @var PostFileService $PostFileService */
        $PostFileService = ClassRegistry::init("PostFileService");

        $post = $Post->getById($postId);

        $postResources = $PostResource->find('all', [
            'conditions' => [
                'post_id' => $postId
            ]
        ]);
        $postResources = Hash::extract($postResources, '{n}.PostResource');
        foreach ($postResources as $postResource) {
            $isAttachedFileResource = in_array($postResource['resource_type'], [
                Enum\Model\Post\PostResourceType::IMAGE,
                Enum\Model\Post\PostResourceType::FILE,
                Enum\Model\Post\PostResourceType::FILE_VIDEO
            ]);
            if ($isAttachedFileResource) {
                $PostFileService->add(
                    $postId,
                    $postResource['resource_id'],
                    $post['team_id'],
                    $postResource['resource_order']);
            }
        }
    }
}
