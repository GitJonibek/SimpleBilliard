<?php
App::import('Lib/Upload/Uploader', 'BaseUploader');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/07/30
 * Time: 18:16
 */
class UploaderFactory
{
    /**
     * Automatically generate uploader class based on environment setting
     *
     * @param int    $teamId
     * @param int    $userId
     * @param string $webroot
     *
     * @return Uploader
     */
    public static function generate(int $userId, int $teamId, string $webroot = ""): Uploader
    {
        if (empty($teamId) || empty($userId)) {
            throw new InvalidArgumentException();
        }
        if (PUBLIC_ENV) {
            return new S3Uploader($userId, $teamId);
        } else {
            return new LocalUploader($userId, $teamId, $webroot);
        }
    }

}