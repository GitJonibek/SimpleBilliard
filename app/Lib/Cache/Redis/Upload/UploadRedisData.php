<?php
App::import('Lib/Upload', 'UploadedFile');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/07/24
 * Time: 14:25
 */
class UploadRedisData
{
    /**
     * Binary encoded file
     *
     * @var string
     */
    private $rawFile;

    /**
     * @var int|null
     */
    private $timeToLive;

    public function __construct(UploadedFile $file)
    {
        if (empty($file) || $file->isEmpty()) {
            throw new InvalidArgumentException();
        }

        $this->rawFile = $file->getFile();
    }

    public function withFile(string $file):self
    {
        $this->rawFile = $file;
        return $this;
    }

    public function getFile(): string
    {
        return $this->rawFile;
    }

    /**
     * @return int|null
     */
    public function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * @param int $timeToLive
     *
     * @return $this
     */
    public function withTimeToLive(int $timeToLive):self
    {
        $this->timeToLive = $timeToLive;
        return $this;
    }
}