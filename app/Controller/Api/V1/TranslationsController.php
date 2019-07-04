<?php
App::uses('ApiController', 'Controller/Api');
App::import('Service', 'TeamMemberService');
App::import('Service', 'TeamTranslationLanguageService');
App::import('Service', 'TranslationService');
App::uses('TeamMember', 'Model');
App::uses('TeamTranslationLanguage', 'Model');
App::uses('TeamTranslationStatus', 'Model');
App::import('Lib/Cache/Redis/NotificationFlag', 'NotificationFlagClient');
App::import('Lib/Cache/Redis/NotificationFlag', 'NotificationFlagKey');

use Goalous\Enum\Language as LangEnum;
use Goalous\Enum\Model\Translation\ContentType as TranslationContentType;
use Goalous\Enum\NotificationFlag\Name as NotificationFlagName;
use Goalous\Exception\GoalousNotFoundException;

class TranslationsController extends ApiController
{
    public function get_list()
    {
        $error = $this->validateQuery();

        if (!empty($error)) {
            return $this->_getResponseValidationFail($error);
        }

        $contentTypeValue = $this->request->query('type');
        $contentId = $this->request->query('id');
        $language = $this->request->query('lang');
        $teamId = $this->current_team_id;

        try {
            if (empty($language)) {
                $language = $this->selectDefaultTranslationLanguage();
            }

            $contentType = TranslationContentType::getEnumObj($contentTypeValue);

            /** @var TranslationService $TranslationService */
            $TranslationService = ClassRegistry::init('TranslationService');
            $translation = $TranslationService->getTranslation($contentType, $contentId, $language);

        } catch (GoalousNotFoundException $e) {
            return $this->_getResponseNotFound($e->getMessage());
        } catch (Exception $e) {
            return $this->_getResponseInternalServerError($e->getMessage());
        }

        $this->sendTranslationUsageNotification($teamId);

        /** @noinspection PhpUndefinedVariableInspection */
        return $this->_getResponseSuccess($translation->toArray());
    }

    private function validateQuery(): array
    {
        $userId = $this->Auth->user('id');
        $teamId = $this->current_team_id;

        if (empty($userId)) {
            return ["No user ID"];
        }

        if (empty($teamId)) {
            return ["No team ID"];
        }

        $errorMessage = [];

        $contentType = $this->request->query('type');
        $contentId = $this->request->query('id');
        $language = $this->request->query('lang');

        if (empty($contentType)) {
            $errorMessage[] = "Missing translation content type.";
        }

        if (empty($contentId)) {
            $errorMessage[] = "Missing translation content id.";
        }

        if (!empty($language)) {
            if (LangEnum::isValid($language)) {
                /** @var TeamTranslationLanguage $TeamTranslationLanguage */
                $TeamTranslationLanguage = ClassRegistry::init('TeamTranslationLanguage');
                if (!$TeamTranslationLanguage->supportTranslationLanguage($teamId, $language)) {
                    $errorMessage[] = "Language not supported in team";
                }
            } else {
                $errorMessage[] = "Invalid language code.";
            }
        }

        /** @var TranslationService $TranslationService */
        $TranslationService = ClassRegistry::init('TranslationService');

        if (!$TranslationService->checkUserAccess($userId, TranslationContentType::getEnumObj($contentType), $contentId)) {
            $errorMessage[] = "User has no access";
        }

        return $errorMessage;
    }


    public function sendTranslationUsageNotification(int $teamId)
    {
        /** @var TeamTranslationStatus $TeamTranslationStatus */
        $TeamTranslationStatus = ClassRegistry::init('TeamTranslationStatus');
        $teamTranslationStatus = $TeamTranslationStatus->getUsageStatus($teamId);

        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init('TeamMember');

        $notificationFlagClient = new NotificationFlagClient();

        $limitReachedKey = new NotificationFlagKey($teamId, NotificationFlagName::TYPE_TRANSLATION_LIMIT_REACHED());
        $limitClosingKey = new NotificationFlagKey($teamId, NotificationFlagName::TYPE_TRANSLATION_LIMIT_CLOSING());

        if (empty($notificationFlagClient->read($limitReachedKey)) && $teamTranslationStatus->isLimitReached()) {
            $this->notifyTranslateLimitReached($teamId, $TeamMember->findAdminList($teamId) ?? []);
            $notificationFlagClient->write($limitReachedKey);
        } else if (empty($notificationFlagClient->read($limitClosingKey)) && $teamTranslationStatus->isUsageWithinPercentageOfLimit(0.1)) {
            $this->notifyTranslateLimitClosing($teamId, $TeamMember->findAdminList($teamId) ?? []);
            $notificationFlagClient->write($limitClosingKey);
        }
    }

    private function notifyTranslateLimitReached(int $teamId, array $userIds)
    {
        $this->NotifyBiz->sendNotify(
            NotifySetting::TYPE_TRANSLATION_LIMIT_REACHED,
            null,
            null,
            $userIds,
            null,
            $teamId);
    }

    private function notifyTranslateLimitClosing(int $teamId, array $userIds)
    {
        $this->NotifyBiz->sendNotify(
            NotifySetting::TYPE_TRANSLATION_LIMIT_CLOSING,
            null,
            null,
            $userIds,
            null,
            $teamId);
    }

    private function selectDefaultTranslationLanguage(): string
    {
        $userId = $this->Auth->user('id');
        $teamId = $this->current_team_id;

        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init('TeamMember');
        /** @var TeamMemberService $TeamMemberService */
        $TeamMemberService = ClassRegistry::init('TeamMemberService');

        if (!empty($TeamMember->hasDefaultTranslationLanguage($teamId, $userId))) {
            return $TeamMemberService->getDefaultTranslationLanguageCode($teamId, $userId);
        }
        /** @var TeamTranslationLanguageService $TeamTranslationLanguageService */
        $TeamTranslationLanguageService = ClassRegistry::init('TeamTranslationLanguageService');

        $browserLanguages = CakeRequest::acceptLanguage();
        $defaultLanguage = $TeamTranslationLanguageService->selectFirstSupportedLanguage($teamId, $browserLanguages);

        if (empty($defaultLanguage)) {
            $defaultLanguage = $TeamTranslationLanguageService->getDefaultTranslationLanguageCode($teamId);
        }

        $TeamMemberService->setDefaultTranslationLanguage($teamId, $userId, $defaultLanguage);
        return $TeamMemberService->getDefaultTranslationLanguageCode($teamId, $userId);
    }
}