<?php
App::uses('GlRedis', 'Model');
App::uses('TransactionManager', 'Model');

/**
 * Created by PhpStorm.
 * User: yoshidam2
 * Date: 2016/09/21
 * Time: 17:57
 */

/**
 * Class AppService
 */
class AppService extends CakeObject
{
    /** @var TransactionManager $TransactionManager */
    protected $TransactionManager = null;

    /* variable cache not to avoid to get same data wastefully */
    protected static $cacheList = [];

    function __construct()
    {
        $this->TransactionManager = ClassRegistry::init("TransactionManager");
    }

    /**
     *
     *
     * @param int $id
     * @param string $modelName
     * @return entity|array
     */
    protected function _getWithCache(int $id, string $modelName) {
        // In case already got data from db and cached, but data is empty
        if (array_key_exists($id, self::$cacheList) && empty(self::$cacheList[$id])) {
            return [];
        }

        // In case already got data from db and cached, data is not empty
        if (!empty(self::$cacheList[$id])) {
            $data = self::$cacheList[$id];
            return $data;
        }

        $model = ClassRegistry::init($modelName);

        // Get data from db and cache
        $data = $model->useType()->findById($id);
        $data = Hash::get($data, $modelName) ?? [];
        self::$cacheList[$id] = $data;
        if (empty($data)) {
            return [];
        }
        return $data;
    }


    /**
     * バリデーションメッセージの展開
     * key:valueの形にして1フィールド1メッセージにする
     *
     * @param $validationErrors
     *
     * @return array
     */
    function validationExtract($validationErrors)
    {
        $res = [];
        if (empty($validationErrors)) {
            return $res;
        }
        if ($validationErrors === true) {
            return $res;
        }
        foreach ($validationErrors as $k => $v) {
            if (is_array($v)) {
                $res[$k] = $v[0];
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }

    /**
     * Validate only specified fields and model
     *
     * @param array  $data
     * @param array  $fields
     * @param string $dataParentKey
     * @param string $modelKey
     * @param Model  $model
     *
     * @return array
     */
    protected function validateSingleModelFields(
        array $data,
        array $fields,
        string $dataParentKey,
        string $modelKey,
        Model $model
    ): array {
        $validationFields = Hash::get($fields, $modelKey) ?? [];
        $validationBackup = $model->validate;
        // Set each field rule
        $validationRules = [];
        foreach ($validationFields as $field) {
            $validationRules[$field] = Hash::get($validationBackup, $field);
        }
        $model->validate = $validationRules;

        $checkData = Hash::get($data, $dataParentKey) ?? [];
        $model->set($checkData);
        $res = $model->validates();
        $model->validate = $validationBackup;
        if (!$res) {
            $validationErrors = $this->validationExtract($model->validationErrors);
            return [$dataParentKey => $validationErrors];
        }
        return [];

    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient(): \GuzzleHttp\Client
    {
        // use ClassRegistry::getObject() for test cases
        // usually returning false on default case
        $registeredClient = ClassRegistry::getObject(\GuzzleHttp\Client::class);
        if ($registeredClient instanceof \GuzzleHttp\Client) {
            return $registeredClient;
        }
        return new \GuzzleHttp\Client();
    }
}
