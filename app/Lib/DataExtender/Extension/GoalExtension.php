<?php
App::uses("Goal", "Model");
App::uses("Group", "Model");
App::import('Lib/DataExtender/Extension', 'DataExtension');
App::import('Service', 'ImageStorageService');

class GoalExtension extends DataExtension
{
    protected function fetchData(array $keys): array
    {
        /** @var Goal $Goal */
        $Goal = ClassRegistry::init("Goal");

        //Remove null values
        $uniqueKeys = $this->filterKeys($keys);

        $conditions = [
            'conditions' => [
                'id' => $uniqueKeys,
            ],
            'contain' => ['GoalGroup' => ['Group']]
        ];

        $fetchedData = $Goal->useType()->find('all', $conditions);

        if (count($fetchedData) != count($uniqueKeys)) {
            GoalousLog::error("Missing data for data extension. Goal ID: " . implode(',',
                    array_diff($uniqueKeys, Hash::extract($fetchedData, '{n}.Goal.id'))));
        }

        // Set profile image url each data
        /** @var ImageStorageService $ImageStorageService */
        $ImageStorageService = ClassRegistry::init('ImageStorageService');
        foreach ($fetchedData as $i => $v) {
            $fetchedData[$i]['Goal']['photo_img_url'] = $ImageStorageService->getImgUrlEachSize($fetchedData[$i], 'Goal');
            $fetchedData[$i]['Goal']['groups'] = [];
            $goalGroups = $fetchedData[$i]['GoalGroup'];

            if (!empty($goalGroups)) {
                $fetchedData[$i]['Goal']['groups'] = Hash::extract($goalGroups, '{n}.Group');
            }

            unset($fetchedData[$i]['GoalGroup']);
        }

        return $fetchedData;
    }
}
