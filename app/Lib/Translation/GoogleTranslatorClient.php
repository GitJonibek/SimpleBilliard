<?php
App::import('Lib/Translation', 'BaseTranslatorClient');
App::import('Lib/Translation', 'TranslationResult');

use Google\Cloud\Translate\TranslateClient;
use Goalous\Enum\Language as LangEnum;

class GoogleTranslatorClient extends BaseTranslatorClient
{
    /**
     * Recommended maximum of 2k code points
     * https://cloud.google.com/translate/quotas
     */
    const MAX_SEGMENT_CHAR_LENGTH = 2000;
    const MAX_BATCH_ARRAY_SIZE = 128;

    protected function requestTranslation(array $segmentedString, string $targetLanguage): array
    {
        if (!LangEnum::isValid($targetLanguage)) {
            throw new InvalidArgumentException("Invalid language code: $targetLanguage");
        }

        if (count($segmentedString) > static::MAX_BATCH_ARRAY_SIZE) {
            throw new InvalidArgumentException("Batch size is too big.");
        }

        $translate = new TranslateClient([
            'key' => GCP_API_KEY
        ]);

        $translationResults = $translate->translateBatch($segmentedString, [
            'target' => $targetLanguage
        ]);

        $translationResultArray = [];

        foreach ($translationResults as $translationResult) {
            $translationResultArray[] = new TranslationResult($translationResult['source'], $translationResult['text'], $targetLanguage);
        }

        return $translationResultArray;
    }

}