<?php
App::import('Lib/Upload/Uploader', 'Uploader');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/07/30
 * Time: 18:16
 */

use Goalous\Exception as GlException;

abstract class BaseUploader implements Uploader
{
    /** @var int */
    protected $teamId;

    /** @var int */
    protected $userId;

    public function __construct(int $userId, int $teamId)
    {
        if (empty($teamId) || empty($userId)) {
            throw new InvalidArgumentException();
        }

        $this->teamId = $teamId;
        $this->userId = $userId;
    }

    /**
     * Upload the file to a temp storage
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    abstract public function buffer(UploadedFile $file): string;

    /**
     * Move the file from temp storage to permanent one
     * Encapsulation of getBuffer() & save().
     *
     * @param string   $modelName
     * @param int      $modelId
     * @param string   $uuid
     * @param callable $preprocess Functions to be run on file before saving
     *
     * @return bool
     */
    public function move(string $modelName, int $modelId, string $uuid, callable $preprocess = null): bool
    {
        $file = $this->getBuffer($uuid);

        if (empty($file)) {
            throw new GlException\GoalousNotFoundException("Buffered file not found");
        }

        if (!empty($preprocess)) {
            $file = $preprocess($file);
        }

        if ($this->save($modelName, $modelId, $file)) {
            if (!$this->deleteBuffer($uuid)) {
                throw new RuntimeException("Couldn't delete buffer");
            }
            return true;
        }
        return false;
    }

    /**
     * Upload a file to specified storage
     *
     * @param string $bucket Storage destination
     * @param string $key    Full path of the file. Include file name & extension
     * @param string $body   File content
     * @param string $type   File type
     *
     * @return mixed
     */
    abstract protected function upload(string $bucket, string $key, string $body, string $type): bool;

    /**
     * Delete file from buffer
     *
     * @param string $uuid
     *
     * @return mixed
     */
    abstract public function deleteBuffer(string $uuid): bool;

    /**
     * Delete file from storage
     *
     * @param string $modelName
     * @param int    $modelId
     * @param string $fileName
     *
     * @return mixed
     */
    abstract public function delete(string $modelName, int $modelId, string $fileName = ""): bool;

    /**
     * Get buffered file
     *
     * @param string $uuid
     *
     * @return UploadedFile
     */
    abstract public function getBuffer(string $uuid): UploadedFile;

    /**
     * Save file to permanent storage
     *
     * @param string       $modelName
     * @param int          $modelId
     * @param UploadedFile $file
     * @param string       $suffix
     *
     * @return bool
     */
    abstract public function save(string $modelName, int $modelId, UploadedFile $file, string $suffix = ""): bool;

    /**
     * Package file into JSON format
     *
     * @param UploadedFile $file
     *
     * @return string JSON encoded array
     */
    protected final function package(UploadedFile $file): string
    {
        if (empty ($file->getFileName()) || empty ($file->getEncodedFile())) {
            throw new InvalidArgumentException();
        }

        $array['file_name'] = $file->getFileName();
        $array['file_data'] = $file->getEncodedFile();

        $json = json_encode($array);

        if (empty($json)) {
            throw new RuntimeException();
        }
        return $json;
    }

    /**
     * Unpackage JSON into UploadedFile
     *
     * @param string $jsonEncoded
     *
     * @return UploadedFile
     */
    protected final function unpackage(string $jsonEncoded): UploadedFile
    {
        if (empty($jsonEncoded)) {
            throw new InvalidArgumentException();
        }
        $array = json_decode($jsonEncoded);
        if (empty($array['file_data']) || empty ($array['file_name'])) {
            throw new RuntimeException();
        }
        return new UploadedFile($this->uncompress($array['file_data']), $array['file_name']);
    }

    /**
     * Create MD5 Hash out of filename
     *
     * @param string $fileName Filename. Must not contain file extension (e.g. .jpg)
     *
     * @return string
     */
    protected final function createHash(string $fileName): string
    {
        return md5(($fileName ?: "") . Configure::read('Security.salt'));
    }

    /**
     * Create buffer key
     *
     * @param string $uuid
     *
     * @return string
     */
    protected function createBufferKey(string $uuid): string
    {
        if (empty($uuid)) {
            throw new InvalidArgumentException();
        }
        return "/$this->teamId/$this->userId/" . $uuid . ".json";
    }

    /**
     * Create upload key
     *
     * @param string $modelName
     * @param int    $modelId
     * @param string $fileName
     * @param string $suffix
     * @param string $fileExt
     *
     * @return string
     */
    protected function createUploadKey(
        string $modelName,
        int $modelId,
        string $fileName,
        string $suffix,
        string $fileExt
    ): string {
        $key = '/' . Inflector::tableize($modelName) . "/" . $modelId . "/" . $this->createHash($fileName) . $suffix . "." . $fileExt;
        return $key;
    }

    /**
     * Create delete key. When file name is not given, will clear the folder instead
     *
     * @param string $modelName
     * @param int    $modelId
     * @param string $fileName
     * @param string $fileExt
     *
     * @return string
     */
    protected function createDeleteKey(
        string $modelName,
        int $modelId,
        string $fileName = "",
        string $fileExt = ""
    ): string {

        $key = '/' . Inflector::tableize($modelName) . "/" . $modelId;

        if (!empty($fileName)) {
            $key .= "/" . $fileName;
            if (!empty($fileExt)) {
                $key .= "." . $fileExt;
            }
        }

        return $key;
    }
}