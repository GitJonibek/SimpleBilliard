<?php
App::import('Lib/DataExtender', 'BaseExtender');
App::import('Service', 'ImageStorageService');
App::import('Lib/DataExtender/Extension', 'UserExtension');
App::import('Lib/DataExtender/Extension', 'CircleExtension');
App::import('Lib/DataExtender/Extension', 'PostLikeExtension');
App::import('Lib/DataExtender/Extension', 'PostSavedExtension');
App::import('Lib/DataExtender/Extension', 'PostReadExtension');
App::import('Service/Paging', 'CommentPagingService');
App::import('Service', 'PostService');
App::import('Service', 'VideoStreamService');

use Goalous\Enum as Enum;

class CirclePostExtender extends BaseExtender
{
    const EXTEND_ALL = "ext:circle_post:all";
    const EXTEND_USER = "ext:circle_post:user";
    const EXTEND_CIRCLE = "ext:circle_post:circle";
    const EXTEND_COMMENTS = "ext:circle_post:comments";
    const EXTEND_POST_SHARE_CIRCLE = "ext:circle_post:share_circle";
    const EXTEND_POST_SHARE_USER = "ext:circle_post:share_user";
    const EXTEND_RESOURCES = "ext:circle_post:resources";
    const EXTEND_LIKE = "ext:circle_post:like";
    const EXTEND_SAVED = "ext:circle_post:saved";
    const EXTEND_READ = "ext:circle_post:read";

    const DEFAULT_COMMENT_COUNT = 3;


    public function extend(array $data, int $userId, int $teamId, array $extensions = []): array
    {
        throw new RuntimeException("Please implement " . __METHOD__);
    }

    public function extendMulti(array $data, int $userId, int $teamId, array $extensions = []): array
    {
        if ($this->includeExt($extensions, self::EXTEND_USER)) {
            /** @var UserExtension $UserExtension */
            $UserExtension = ClassRegistry::init('UserExtension');
            $data = $UserExtension->extendMulti($data, "{n}.user_id");
        }
        if ($this->includeExt($extensions, self::EXTEND_CIRCLE)) {
            /** @var CircleExtension $CircleExtension */
            $CircleExtension = ClassRegistry::init('CircleExtension');
            $data = $CircleExtension->extendMulti($data, "{n}.id");
        }
        if ($this->includeExt($extensions, self::EXTEND_COMMENTS)) {
            /** @var CommentPagingService $CommentPagingService */
            $CommentPagingService = ClassRegistry::init('CommentPagingService');

            foreach ($data as &$result) {
                $commentPagingRequest = new PagingRequest();
                $commentPagingRequest->setResourceId(Hash::get($result, 'id'));
                $commentPagingRequest->setCurrentUserId($userId);
                $commentPagingRequest->setCurrentTeamId($teamId);

                $comments = $CommentPagingService->getDataWithPaging($commentPagingRequest, self::DEFAULT_COMMENT_COUNT,
                    CommentExtender::EXTEND_ALL);

                $result['comments'] = $comments;
            }
        }
        if ($this->includeExt($extensions, self::EXTEND_RESOURCES)) {

            foreach ($data as $index => $entry) {


                $data[$index]['resources'] = [

                ];

                /** @var PostService $PostService */
                $PostService = ClassRegistry::init('PostService');

                /** @var ImageStorageService $ImageStorageService */
                $ImageStorageService = ClassRegistry::init('ImageStorageService');
                /** @var VideoStreamService $VideoStreamService */
                $VideoStreamService = ClassRegistry::init('VideoStreamService');

                $Upload = new UploadHelper(new View());

                $resources = $PostService->getResourcesByPostId($entry['id']);
                foreach ($resources as $resource) {
                    /** @var PostResourceEntity $resource */
                    $postResource = $resource->offsetGet('PostResource');
                    $attachedFile = $resource->offsetGet('AttachedFile');

                    // Fetch data from attached_files
                    if (in_array($postResource['resource_type'], [
                        Enum\Model\Post\PostResourceType::IMAGE,
                        Enum\Model\Post\PostResourceType::FILE,
                        Enum\Model\Post\PostResourceType::FILE_VIDEO,
                    ])) {
                        $attachedFile['file_url'] = '';
                        $attachedFile['preview_url'] = '';
                        // download url is common.
                        // TODO: We should consider to preapare new API or using old processe;
                        //  $file['download_url'] = '/posts/attached_file_download/file_id:' . $file['id'];
                        $attachedFile['download_url'] = '';


                        GoalousLog::info('$attachedFile', $attachedFile);
                        if ($attachedFile['file_type'] == AttachedFile::TYPE_FILE_IMG) {
                            $attachedFile['file_url'] = $ImageStorageService->getImgUrlEachSize($attachedFile, 'AttachedFile',
                                'attached');
                            $attachedFile['resource_type'] = Enum\Model\Post\PostResourceType::IMAGE;
                            $data[$index]['resources'][] = $attachedFile;
                        } else {
                            $attachedFile['preview_url'] = $Upload->attachedFileUrl($attachedFile);
                            $attachedFile['resource_type'] = Enum\Model\Post\PostResourceType::FILE;
                            $data[$index]['resources'][] = $attachedFile;
                        }
                        continue;
                    };

                    // Fetch data from video stream
                    if ((int)$postResource['resource_type'] === Enum\Model\Post\PostResourceType::VIDEO_STREAM) {
                        $resourceVideoStream = $VideoStreamService->getVideoStreamForPlayer($postResource['resource_id']);
                        $data[$index]['resources'][] = $resourceVideoStream;

                    }
                }
            }
        }
        if ($this->includeExt($extensions, self::EXTEND_LIKE)) {
            /** @var PostLikeExtension $PostLikeExtension */
            $PostLikeExtension = ClassRegistry::init('PostLikeExtension');
            $PostLikeExtension->setUserId($userId);
            $data = $PostLikeExtension->extendMulti($data, "{n}.id", "post_id");
        }
        if ($this->includeExt($extensions, self::EXTEND_SAVED)) {
            /** @var PostSavedExtension $PostSavedExtension */
            $PostSavedExtension = ClassRegistry::init('PostSavedExtension');
            $PostSavedExtension->setUserId($userId);
            $data = $PostSavedExtension->extendMulti($data, "{n}.id", "post_id");
        }
        if ($this->includeExt($extensions, self::EXTEND_READ)) {
            /** @var PostSavedExtension $PostSavedExtension */
            $PostReadExtension = ClassRegistry::init('PostReadExtension');
            $PostReadExtension->setUserId($userId);
            $data = $PostReadExtension->extendMulti($data, "{n}.id", "post_id");
        }
        return $data;
    }
}
