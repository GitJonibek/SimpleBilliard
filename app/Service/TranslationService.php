<?php

App::import('Service', 'AppService');
App::import('Service', 'ActionService');
App::import('Service', 'CommentService');
App::import('Service', 'PostService');
App::import('Service', 'TeamTranslationStatusService');
App::uses('ActionResult', 'Model');
App::uses('Comment', 'Model');
App::uses('Post', 'Model');
App::uses('Team', 'Model');
App::uses('TeamTranslationLanguage', 'Model');
App::uses('TeamTranslationStatus', 'Model');
App::uses('Translation', 'Model');
App::uses('TranslationLanguage', 'Model');
App::import('Lib/Translation', 'TranslationResult');
App::import('Lib/Translation', 'GoogleTranslatorClient');
App::uses('UrlUtil', 'Util');
App::uses('MentionComponent', 'Controller/Component');

use Goalous\Enum\Model\Translation\ContentType as TranslationContentType;
use Goalous\Enum\Model\Translation\Status as TranslationStatus;
use Goalous\Exception as GlException;

class TranslationService extends AppService
{
    const MAX_TRY_COUNT    = 3;
    const RETRY_SLEEP_SECS = 2;

    /**
     *  Get translation for a data
     *
     * @param TranslationContentType $contentType Content type.
     * @param int                    $contentId   Id of content type.
     *                                            ACTION_POST => posts.id
     *                                            CIRCLE_POST => posts.id
     *                                            CIRCLE_POST_COMMENT => comments.id
     *                                            ACTION_POST_COMMENT => comments.id
     * @param string                 $targetLanguage
     *
     * @return TranslationResult
     * @throws Exception
     */
    public function getTranslation(
        TranslationContentType $contentType,
        int $contentId,
        string $targetLanguage
    ): TranslationResult {
        /** @var TranslationLanguage $TranslationLanguage */
        $TranslationLanguage = ClassRegistry::init('TranslationLanguage');

        if (!$TranslationLanguage->isValidLanguage($targetLanguage)) {
            throw new InvalidArgumentException("Unknown translation language: " . $targetLanguage);
        }

        /** @var Translation $Translation */
        $Translation = ClassRegistry::init('Translation');

        if ($Translation->hasTranslation($contentType, $contentId, $targetLanguage)) {
            $tryCount = 0;

            do {
                $translation = $Translation->getTranslation($contentType, $contentId, $targetLanguage);

                $sourceModel = $this->getSourceModel($contentType, $contentId);

                if ($translation['status'] === TranslationStatus::DONE) {
                    return new TranslationResult($sourceModel['language'], $translation['body'], $targetLanguage);
                }

                sleep(self::RETRY_SLEEP_SECS);
                $tryCount++;
            } while ($tryCount < self::MAX_TRY_COUNT);

            $this->eraseTranslation($contentType, $contentId, $targetLanguage);
        }

        $this->createTranslation($contentType, $contentId, $targetLanguage);

        $sourceModel = $this->getSourceModel($contentType, $contentId);

        $translation = $Translation->getTranslation($contentType, $contentId, $targetLanguage);

        return new TranslationResult($sourceModel['language'], $translation['body'], $targetLanguage);
    }

    /**
     *  Get translation for a data for API v1
     *
     * @param TranslationContentType $contentType Content type.
     * @param int                    $contentId   Id of content type.
     *                                            ACTION_POST => posts.id
     *                                            CIRCLE_POST => posts.id
     *                                            CIRCLE_POST_COMMENT => comments.id
     *                                            ACTION_POST_COMMENT => comments.id
     * @param string                 $targetLanguage
     *
     * @return TranslationResult
     * @throws Exception
     */
    public function getTranslationForApiV1(
        TranslationContentType $contentType,
        int $contentId,
        string $targetLanguage
    ): TranslationResult {
        $result = $this->getTranslation($contentType, $contentId, $targetLanguage);

        switch ($contentType->getValue()) {
            case TranslationContentType::CIRCLE_POST_COMMENT:
            case TranslationContentType::ACTION_POST_COMMENT:
                $translation = $result->getTranslation();
                $translation = MentionComponent::replaceMentionTagForTranslationV1($translation);
                return new TranslationResult($result->getSourceLanguage(), $translation, $result->getTargetLanguage());
                break;
        };

        return $result;
    }

    /**
     *  Get translation for a data for API v2.
     *
     * @param TranslationContentType $contentType Content type.
     * @param int                    $contentId   Id of content type.
     *                                            ACTION_POST => posts.id
     *                                            CIRCLE_POST => posts.id
     *                                            CIRCLE_POST_COMMENT => comments.id
     *                                            ACTION_POST_COMMENT => comments.id
     * @param string                 $targetLanguage
     *
     * @return TranslationResult
     * @throws Exception
     */
    public function getTranslationForApiV2(
        TranslationContentType $contentType,
        int $contentId,
        string $targetLanguage
    ): TranslationResult {
        $result = $this->getTranslation($contentType, $contentId, $targetLanguage);

        switch ($contentType->getValue()) {
            case TranslationContentType::ACTION_POST:
                $translation = self::removeUrlEncapsulation($result->getTranslation());
                return new TranslationResult($result->getSourceLanguage(), $translation, $result->getTargetLanguage());
            case TranslationContentType::CIRCLE_POST:
            case TranslationContentType::CIRCLE_POST_COMMENT:
            case TranslationContentType::ACTION_POST_COMMENT:
                $translation = $result->getTranslation();
                $translation = MentionComponent::replaceMentionTagForTranslationV2($translation);
                $translation = self::removeUrlEncapsulation($translation);
                return new TranslationResult($result->getSourceLanguage(), $translation, $result->getTargetLanguage());
        };

        return $result;
    }

    /**
     * Delete existing entry
     *
     * @param TranslationContentType $contentType
     * @param int                    $contentId Id of content type.
     *                                          ACTION_POST => posts.id
     *                                          CIRCLE_POST => posts.id
     *                                          CIRCLE_POST_COMMENT => comments.id
     *                                          ACTION_POST_COMMENT => comments.id
     * @param string                 $targetLanguage
     *
     * @throws Exception
     */
    public function eraseTranslation(TranslationContentType $contentType, int $contentId, string $targetLanguage)
    {
        /** @var Translation $Translation */
        $Translation = ClassRegistry::init('Translation');

        try {
            $this->TransactionManager->begin();
            $Translation->eraseTranslation($contentType, $contentId, $targetLanguage);
            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error(
                'Failed to erase translation.',
                [
                    'message'      => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                    'content_type' => $contentType->getValue(),
                    'content_id'   => $contentId,
                    'language'     => $targetLanguage
                ]
            );
            throw $e;
        }
    }

    /**
     * Create translation for a data
     *
     * @param TranslationContentType $contentType
     * @param int                    $contentId Id of content type.
     *                                          ACTION_POST => posts.id
     *                                          CIRCLE_POST => posts.id
     *                                          CIRCLE_POST_COMMENT => comments.id
     *                                          ACTION_POST_COMMENT => comments.id
     * @param string                 $targetLanguage
     *
     * @throws Exception
     */
    public function createTranslation(TranslationContentType $contentType, int $contentId, string $targetLanguage)
    {
        /** @var TranslationLanguage $TranslationLanguage */
        $TranslationLanguage = ClassRegistry::init('TranslationLanguage');

        if (!$TranslationLanguage->isValidLanguage($targetLanguage)) {
            throw new InvalidArgumentException("Unknown translation language: " . $targetLanguage);
        }

        /** @var Translation $Translation */
        $Translation = ClassRegistry::init('Translation');

        try {
            $this->TransactionManager->begin();
            $Translation->createEntry($contentType, $contentId, $targetLanguage);
            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error(
                'Failed to create translation entry.',
                [
                    'message'      => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                    'content_type' => $contentType->getValue(),
                    'content_id'   => $contentId,
                    'language'     => $targetLanguage
                ]
            );
            throw $e;
        }

        $sourceModel = $this->getSourceModel($contentType, $contentId);

        $sourceBody = Hash::get($sourceModel, 'body');

        //If body is empty
        if (empty($sourceModel['body'])) {
            $Translation->updateTranslationBody($contentType, $contentId, $targetLanguage, "");
            return;
        }

        $teamId = $sourceModel['team_id'];

        /** @var TeamTranslationStatusService $TeamTranslationStatusService */
        $TeamTranslationStatusService = ClassRegistry::init('TeamTranslationStatusService');

        $TranslatorClient = $this->getTranslatorClient();

        try {
            $this->TransactionManager->begin();
            $translatedResult = $TranslatorClient->translate($sourceBody, $targetLanguage);
            $this->updateSourceBodyLanguage($contentType, $contentId, $translatedResult->getSourceLanguage());

            $Translation->updateTranslationBody(
                $contentType,
                $contentId,
                $targetLanguage,
                $translatedResult->getTranslation()
            );

            $TeamTranslationStatusService->incrementUsageCount(
                $teamId,
                $contentType,
                StringUtil::mbStrLength($sourceBody)
            );
            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            GoalousLog::error(
                'Failed to insert translation.',
                [
                    'message'      => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                    'content_type' => $contentType->getValue(),
                    'content_id'   => $contentId,
                    'language'     => $targetLanguage
                ]
            );
            throw $e;
        }
    }

    /**
     * Create default translation of a content
     *
     * @param int                    $teamId
     * @param TranslationContentType $contentType
     * @param int                    $contentId Id of content type.
     *                                          ACTION_POST => posts.id
     *                                          CIRCLE_POST => posts.id
     *                                          CIRCLE_POST_COMMENT => comments.id
     *                                          ACTION_POST_COMMENT => comments.id
     *
     * @throws Exception
     */
    public function createDefaultTranslation(int $teamId, TranslationContentType $contentType, int $contentId)
    {
        /** @var TeamTranslationLanguageService $TeamTranslationLanguageService */
        $TeamTranslationLanguageService = ClassRegistry::init('TeamTranslationLanguageService');
        /** @var TranslationService $TranslationService */
        $TranslationService = ClassRegistry::init('TranslationService');

        $defaultLanguage = $TeamTranslationLanguageService->getDefaultTranslationLanguageCode($teamId);

        try {
            $TranslationService->createTranslation($contentType, $contentId, $defaultLanguage);
        } catch (Exception $e) {
            GoalousLog::error(
                'Failed create default translation on new content',
                [
                    'message'      => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                    'content_type' => $contentType->getKey(),
                    'content_id'   => $contentId,
                ]
            );
        }
    }

    /**
     * Check user access right to the corresponding content
     *
     * @param int                    $userId
     * @param TranslationContentType $contentType
     * @param int                    $contentId Id of content type.
     *                                          ACTION_POST => posts.id
     *                                          CIRCLE_POST => posts.id
     *                                          CIRCLE_POST_COMMENT => comments.id
     *                                          ACTION_POST_COMMENT => comments.id
     *
     * @return bool
     */
    public function checkUserAccess(int $userId, TranslationContentType $contentType, int $contentId): bool
    {
        switch ($contentType->getValue()) {
            case TranslationContentType::ACTION_POST:
                /** @var ActionService $ActionService */
                $ActionService = ClassRegistry::init('ActionService');
                return $ActionService->checkUserAccess($userId, $contentId);
            case TranslationContentType::CIRCLE_POST:
                /** @var PostService $PostService */
                $PostService = ClassRegistry::init('PostService');
                return $PostService->checkUserAccessToCirclePost($userId, $contentId);
            case TranslationContentType::CIRCLE_POST_COMMENT:
            case TranslationContentType::ACTION_POST_COMMENT:
                /** @var CommentService $CommentService */
                $CommentService = ClassRegistry::init('CommentService');
                return $CommentService->checkUserAccessToComment($userId, $contentId);
            case TranslationContentType::MESSAGE:
                /** @var MessageService $MessageService */
                $MessageService = ClassRegistry::init('MessageService');
                return $MessageService->checkUserAccessToMessage($userId, $contentId);
            default:
                throw new UnexpectedValueException("Unknown translation model type.");
        }
    }

    /**
     * Check whether team can do translation.
     * Only trial or paid team with translation languages & remaining usage can translate.
     *
     * @param int  $teamId
     * @param bool $checkUsage Check team's translation usage too
     *
     * @return bool
     */
    public function canTranslate(int $teamId, bool $checkUsage = true): bool
    {
        /** @var Team $Team */
        $Team = ClassRegistry::init('Team');
        /** @var TeamTranslationLanguage $TeamTranslationLanguage */
        $TeamTranslationLanguage = ClassRegistry::init('TeamTranslationLanguage');
        /** @var TeamTranslationStatus $TeamTranslationStatus */
        $TeamTranslationStatus = ClassRegistry::init('TeamTranslationStatus');

        try {
            // Only free trial or paid team can translate
            $teamTypeFlg = $Team->isFreeTrial($teamId) || $Team->isPaidPlan($teamId);

            if (!$teamTypeFlg) {
                return false;
            }
            // Team must have translation language selected & remaining usage count to translate
            $translationFlg = $TeamTranslationLanguage->hasLanguage($teamId) &&
                $TeamTranslationStatus->hasEntry($teamId);

            if ($checkUsage) {
                $translationFlg = $translationFlg && !$TeamTranslationStatus->getUsageStatus($teamId)->isLimitReached();
            }

            return $translationFlg;
        } catch (Exception $e) {
            GoalousLog::error(
                "Error in checking translation availability of a team.",
                [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                    'team.id' => $teamId
                ]
            );
            return false;
        }
    }

    /**
     * Get source body of data to be translated
     *
     * @param TranslationContentType $contentType
     * @param int                    $contentId
     *
     * @return array
     *                 ['body' => "", 'language' => "", 'team_id' => 0]
     */
    private function getSourceModel(TranslationContentType $contentType, int $contentId): array
    {
        $originalModel = [];

        switch ($contentType->getValue()) {
            case TranslationContentType::ACTION_POST:
                /** @var Post $Post */
                $Post = ClassRegistry::init('Post');
                $post = $Post->getById($contentId);
                /** @var ActionResult $ActionResult */
                $ActionResult = ClassRegistry::init('ActionResult');
                $actionResult = $ActionResult->getById($post['action_result_id']);
                if (empty($actionResult)) {
                    break;
                }
                $actionBody = self::encapsulateUrlForTranslation($actionResult['name']);
                $originalModel = [
                    'body'     => $actionBody,
                    'language' => $post['language'] ?: "",
                    'team_id'  => $actionResult['team_id']
                ];
                break;
            case TranslationContentType::CIRCLE_POST:
                /** @var Post $Post */
                $Post = ClassRegistry::init('Post');
                $post = $Post->getById($contentId);
                if (empty($post)) {
                    break;
                }
                $postBody = MentionComponent::replaceMentionForTranslation($post['body']);
                $postBody = self::encapsulateUrlForTranslation($postBody);
                $originalModel = [
                    'body'     => $postBody,
                    'language' => $post['language'] ?: "",
                    'team_id'  => $post['team_id']
                ];
                break;
            case TranslationContentType::CIRCLE_POST_COMMENT:
            case TranslationContentType::ACTION_POST_COMMENT:
                /** @var Comment $Comment */
                $Comment = ClassRegistry::init('Comment');
                $comment = $Comment->getById($contentId);
                if (empty($comment)) {
                    break;
                }
                $commentBody = MentionComponent::replaceMentionForTranslation($comment['body']);
                $commentBody = self::encapsulateUrlForTranslation($commentBody);
                $originalModel = [
                    'body'     => $commentBody,
                    'language' => $comment['language'] ?: "",
                    'team_id'  => $comment['team_id']
                ];
                break;
            case TranslationContentType::MESSAGE:
                /** @var Message $Message */
                $Message = ClassRegistry::init('Message');
                $message = $Message->getById($contentId);
                if (empty($message)) {
                    break;
                }
                $originalModel = [
                    'body'     => $message['body'],
                    'language' => $message['language'] ?: "",
                    'team_id'  => $message['team_id']
                ];
                break;
            default:
                throw new UnexpectedValueException("Unknown translation model type.");
        }

        if (empty($originalModel)) {
            throw new GlException\GoalousNotFoundException('Original body for translation is not found');
        }

        return $originalModel;
    }

    /**
     * Update the detected language
     *
     * @param TranslationContentType $contentType
     * @param int                    $contentId
     * @param string                 $sourceLanguage
     *
     * @throws Exception
     */
    private function updateSourceBodyLanguage(
        TranslationContentType $contentType,
        int $contentId,
        string $sourceLanguage
    ) {
        switch ($contentType->getValue()) {
            case TranslationContentType::ACTION_POST:
            case TranslationContentType::CIRCLE_POST:
                /** @var Post $Post */
                $Post = ClassRegistry::init('Post');
                $Post->updateLanguage($contentId, $sourceLanguage);
                break;
            case TranslationContentType::CIRCLE_POST_COMMENT:
            case TranslationContentType::ACTION_POST_COMMENT:
                /** @var Comment $Comment */
                $Comment = ClassRegistry::init('Comment');
                $Comment->updateLanguage($contentId, $sourceLanguage);
                break;
            case TranslationContentType::MESSAGE:
                /** @var Message $Message */
                $Message = ClassRegistry::init('Message');
                $Message->updateLanguage($contentId, $sourceLanguage);
                break;
            default:
                throw new UnexpectedValueException("Unknown translation model type.");
                break;
        }
    }

    /**
     * Get singleton of GoogleTranslatorClient
     *
     * @return GoogleTranslatorClient
     */
    private function getTranslatorClient(): GoogleTranslatorClient
    {
        $registeredClient = ClassRegistry::getObject(GoogleTranslatorClient::class);
        if ($registeredClient instanceof GoogleTranslatorClient) {
            return $registeredClient;
        }
        return new GoogleTranslatorClient();
    }

    /**
     * Encapsulate URLs before translation
     *
     * @param string $baseString
     *
     * @return string
     */
    private static function encapsulateUrlForTranslation(string $baseString): string
    {
        $urlPrefix = "<span class=\"glsurl\" translate=\"no\">";
        $urlSuffix = "</span>";

        return UrlUtil::encapsulateUrl($baseString, ["http", "https"], $urlPrefix, $urlSuffix);
    }

    /**
     * Remove URL encapsulation after translated
     *
     * @param string $baseString
     *
     * @return string
     */
    private static function removeUrlEncapsulation(string $baseString): string
    {
        $result = preg_replace(
            '/<span class="glsurl" translate="no">(((?!(\<|\>)).)*)<\/span>/ium',
            '$1 ',
            $baseString
        );

        $result = rtrim($result);

        return $result;
    }

}
