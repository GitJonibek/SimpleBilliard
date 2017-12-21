<?php
App::import('Service', 'AppService');
App::uses('VideoUploadRequestOnPost', 'Model/Video/Requests');
App::uses('VideoStorageClient', 'Model/Video');

App::uses('Video', 'Model');
App::uses('VideoStream', 'Model');

use Goalous\Model\Enum as Enum;

/**
 * Class VideoStreamService
 */
class VideoStreamService extends AppService
{
    public function updateFromTranscodeProgressData(array $videoStream, TranscodeProgressData $transcodeProgressData): array
    {
        $videoStreamId = $videoStream['id'];
        $progressState = $transcodeProgressData->getProgressState();

        /** @var VideoStream $VideoStream */
        $VideoStream = ClassRegistry::init('VideoStream');
        $currentVideoStreamStatus = new Enum\Video\VideoTranscodeStatus(intval($videoStream['status_transcode']));
        if ($progressState->equals(Enum\Video\VideoTranscodeProgress::PROGRESS())) {
            // if transcode is started
            if (!$currentVideoStreamStatus->equals(Enum\Video\VideoTranscodeStatus::QUEUED())) {
                throw new RuntimeException("video_streams.id({$videoStreamId}) is not queued");
            }
            $status = Enum\Video\VideoTranscodeStatus::TRANSCODING;
            $videoStream['status_transcode'] = $status;

            $transcodeInfo = $VideoStream->getTranscodeInfo($videoStream);
            $transcodeInfo->setTranscodeJobId($transcodeProgressData->getJobId());
            $videoStream['transcode_info'] = $transcodeInfo->toJson();
            $VideoStream->save($videoStream);
            GoalousLog::info('transcode status changed', [
                'video_streams.id' => $videoStreamId,
                'state' => $progressState->getValue(),
                'status_value_from' => $currentVideoStreamStatus->getValue(),
                'status_value_to' => $status,
            ]);
            return $videoStream;
        } else if ($progressState->equals(Enum\Video\VideoTranscodeProgress::ERROR())) {
            // if transcode is error
            $status = Enum\Video\VideoTranscodeStatus::ERROR;
            $videoStream['status_transcode'] = $status;

            $transcodeInfo = $VideoStream->getTranscodeInfo($videoStream);
            $transcodeInfo->setTranscodeJobId($transcodeProgressData->getJobId());
            if ($transcodeProgressData->isError()) {
                $transcodeInfo->addTranscodeError($transcodeProgressData->getError());
            }
            $videoStream['transcode_info'] = $transcodeInfo->toJson();

            $VideoStream->save($videoStream);
            GoalousLog::info('transcode status changed', [
                'video_streams.id' => $videoStreamId,
                'state' => $progressState->getValue(),
                'status_value_from' => $currentVideoStreamStatus->getValue(),
                'status_value_to' => $status,
            ]);
            return $videoStream;
        } else if ($progressState->equals(Enum\Video\VideoTranscodeProgress::COMPLETE())) {
            // if transcode is completed
            $status = Enum\Video\VideoTranscodeStatus::TRANSCODE_COMPLETE;

            // if we missed the "PROGRESSING" notification, our video_stream.status_transcode is QUEUED
            // but this has no problem when we receive "COMPLETE" notification
            if (!in_array($currentVideoStreamStatus->getValue(), [
                Enum\Video\VideoTranscodeStatus::TRANSCODING,
                Enum\Video\VideoTranscodeStatus::QUEUED,
            ])) {
                throw new RuntimeException("video_streams.id({$videoStreamId}) is not transcoding");
            }
            $videoStream['status_transcode']     = Enum\Video\VideoTranscodeStatus::TRANSCODE_COMPLETE;
            $videoStream['duration']             = $transcodeProgressData->getDuration();
            $videoStream['aspect_ratio']         = $transcodeProgressData->getAspectRatio();
            $videoStream['master_playlist_path'] = $transcodeProgressData->getPlaylistPath();

            $transcodeInfo = $VideoStream->getTranscodeInfo($videoStream);
            $transcodeInfo->setTranscodeJobId($transcodeProgressData->getJobId());
            $videoStream['transcode_info'] = $transcodeInfo->toJson();
            $VideoStream->save($videoStream);
            GoalousLog::info('transcode status changed ', [
                'video_streams.id' => $videoStreamId,
                'state' => $progressState->getValue(),
                'status_value_from' => $currentVideoStreamStatus->getValue(),
                'status_value_to' => $status,
            ]);
            return $videoStream;
        }
        throw new RuntimeException("video_streams.id({$videoStreamId}) is not transcoding");
    }

    public function findVideoStreamIfExists(int $userId, int $teamId, string $hash): array
    {
        /** @var Video $Video */
        $Video = ClassRegistry::init("Video");
        /** @var VideoStream $VideoStream */
        $VideoStream = ClassRegistry::init("VideoStream");

        $video = $Video->getByUserIdAndTeamIdAndHash($userId, $teamId, $hash);
        if(empty($video)) {
            return [];
        }
        $videoId = $video['id'];
        $videoStream = $VideoStream->getFirstByVideoId($videoId);
        if (empty($videoStream)) {
            return [];
        }
        return $videoStream;
    }

    public function uploadNewVideoStream(array $uploadFile, array $user, int $teamId): array
    {
        /** @var Video $Video */
        $Video = ClassRegistry::init("Video");
        /** @var VideoStream $VideoStream */
        $VideoStream = ClassRegistry::init("VideoStream");

        $userId = $user['id'];

        $filePath = $uploadFile['tmp_name'];
        $fileName = $uploadFile['name'];

        $hash = hash_file('sha256', $filePath);// TODO: move to some function/class

        $videoStreamIfExists = $this->findVideoStreamIfExists($userId, $teamId, $hash);
        if (!empty($videoStreamIfExists)) {
            GoalousLog::info('uploaded same hash video exists', [
                'user_id' => $userId,
                'team_id' => $teamId,
                'hash'    => $hash,
                'video_streams.id' => $videoStreamIfExists['id'],
            ]);
            return $videoStreamIfExists;
        }
        GoalousLog::info('uploaded same hash video NOT exists', [
            'user_id' => $userId,
            'team_id' => $teamId,
            'hash'    => $hash,
        ]);

        // create video, video_stream
        // need to be create for Storage Meta data to save ids
        $Video->create([
            'user_id'       => $userId,
            'team_id'       => $teamId,
            'duration'      => null,// currently cant estimate (need ffprove or something)
            'width'         => null,// (same as above)
            'height'        => null,// (same as above)
            'hash'          => $hash,
            'file_size'     => filesize($filePath),
            'file_name'     => $fileName,
            'resource_path' => null,
        ]);
        $video = $Video->save();
        $video = reset($video);
        $VideoStream->create([
            'video_id'         => $video['id'],
            'duration'         => null,
            'aspect_ratio'     => null,
            'storage_path'     => null,
            'status_transcode' => Enum\Video\VideoTranscodeStatus::UPLOADING,
            'output_version'   => Enum\Video\TranscodeOutputVersion::V1,
            'transcode_info'   => TranscodeInfo::createNew()->toJson(),
        ]);
        $videoStream = $VideoStream->save();
        $videoStream = reset($videoStream);

        $request = new VideoUploadRequestOnPost(new SplFileInfo($filePath), $user, $teamId, $video, $videoStream);
        $result = VideoStorageClient::upload($request);

        if (!$result->isSucceed()) {
            GoalousLog::info('video uploading to storage failed', [
                'code'    => $result->getErrorCode(),
                'message' => $result->getErrorMessage(),
            ]);
            $videoStream['status_transcode'] = Enum\Video\VideoTranscodeStatus::ERROR;
            $VideoStream->save($videoStream);
            throw new RuntimeException("failed upload video:" . $result->getErrorMessage());
        }
        // Succeeded upload
        $video['resource_path'] = $result->getResourcePath();
        $Video->save($video);

        // Upload complete and queued is same timing on AWS Elastic Transcoder (using S3 event + Lambda)
        $videoStream['status_transcode'] = Enum\Video\VideoTranscodeStatus::UPLOAD_COMPLETE;

        // TODO: make transcode job on AWS ETS

        $videoStream['status_transcode'] = Enum\Video\VideoTranscodeStatus::QUEUED;
        $VideoStream->save($videoStream);

        GoalousLog::info('video uploading to storage succeeded', [
            'teams.id'         => $teamId,
            'videos.id'        => $video['id'],
            'video_streams.id' => $videoStream['id'],
        ]);

        return $videoStream;
    }
}
