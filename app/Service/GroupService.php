<?php
App::import('Service', 'AppService');
App::uses('MemberGroup', 'Model');
App::uses('Group', 'Model');
App::uses('TeamMember', 'Model');
App::import('Model/Entity', 'GroupEntity');

/**
 * This Class is GroupService
 * Created by PhpStorm.
 * User: bigplants
 * Date: 11/28/16
 * Time: 1:11 PM
 */
class GroupService extends AppService
{
    /**
     * ログインユーザがグループメンバーかどうか？
     *
     * @param $groupId
     *
     * @return bool
     */
    function isGroupMember($groupId): bool
    {
        /** @var MemberGroup $MemberGroup */
        $MemberGroup = ClassRegistry::init("MemberGroup");
        $myGroupList = $MemberGroup->getMyGroupList();
        if (empty($myGroupList)) {
            return false;
        }

        if (!array_key_exists($groupId, $myGroupList)) {
            return false;
        }
        return true;
    }

    /**
     * 全てのグループをメンバー数付きで返す
     *
     * @return array
     */
    function findAllGroupsWithMemberCount()
    {
        /** @var Group $Group */
        $Group = ClassRegistry::init("Group");
        $allGroups = $Group->findAllGroupWithMemberIds();
        foreach ($allGroups as &$group) {
            $group['Group']['member_count'] = count($group['MemberGroup']);
        }
        $ret = Hash::combine($allGroups, '{n}.Group.id', '{n}.Group');
        return $ret;
    }

    function createGroup(array $data): array
    {
        /** @var Group $Group */
        $Group = ClassRegistry::init("Group");

        $groupData = [
            'team_id'       => $data['team_id'],
            'name'          => $data['name'],
            'active_flg'    => true,
            'del_flg'       => false,
            'created'     => REQUEST_TIMESTAMP
        ];

        $Group->create();
        $entity = $Group->useType()->useEntity()->save($groupData, false);
        return $entity->toArray();
    }

    function editGroup(string $groupId, array $data): array
    {
        /** @var Group $Group */
        $Group = ClassRegistry::init("Group");
        $group = $Group->getById($groupId);
        $groupData = array_merge($group, $data);
        $entity = $Group->useType()->useEntity()->save($groupData, false);
        return $entity->toArray();
    }

    function parseMembers(string $groupId, string $teamId, string $tmp_file_path): array
    {
        $results = [
            'invalidIds' => [],
            'validUserIds' => [],
            'existingUserIds' => []
        ];

        $this->parseCsv(
            $tmp_file_path,
            function ($rows) use ($groupId, $teamId, &$results) {
                // take first column of each row
                $emails = array_map('array_shift', $rows);
                $queried = $this->queryPossibleMembers($groupId, $teamId, $emails);
                $results = array_merge_recursive($results, $queried);
            }
        );

        return [
            'existing' => count(array_unique($results['existingUserIds'])),
            'valid' => count(array_unique($results['validUserIds'])),
            'invalid' => count(array_unique($results['invalidIds'])),
            'validUserIds' => $results['validUserIds']
        ];
    }

    function addMembers(string $groupId, string $teamId, array $userIds): int
    {
        /** @var MemberGroup $MemberGroup */
        $MemberGroup = ClassRegistry::init('MemberGroup');
        $data = [];

        foreach ($userIds as $userId) {
            $userData = [
                'team_id'  => $teamId,
                'user_id'  => $userId,
                'group_id' => $groupId,
                'created'  => REQUEST_TIMESTAMP
            ];

            array_push($data, $userData);
        }

        $MemberGroup->saveMany($data);

        return count($data);
    }

    private function parseCsv($tmpFilePath, $callback): void
    {
        ini_set('auto_detect_line_endings', TRUE);
        $chunk_size = 500;
        $count = 0;
        $rows = [];

        $pre_data = file_get_contents($tmpFilePath);
        $bomPresent = substr($pre_data, 0, 2) == (chr(0xFF) . chr(0xFE));

        if ($bomPresent) {
            $pre_data = hex2bin(preg_replace("/^fffe/", "", bin2hex($pre_data)));
            file_put_contents($tmpFilePath, mb_convert_encoding($pre_data, "UTF-8", "UTF-16LE"));
        }

        if (($handle = fopen($tmpFilePath, "r")) === FALSE) {
            return;
        }

        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // ignore header row
        fgetcsv($handle, 2000, ",");

        // aggregrate rows till it reaches chunk_size, trigger callback and reset
        // max 2000 char line based on CsvComponent
        while (($row_data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            array_push($rows, $row_data);
            $count += 1;

            if ($count == $chunk_size) {
                $callback($rows);
                $rows = [];
                $count = 0;
            }
        }

        // trigger callback on any remaining rows that did not reach chunk size
        if (count($rows) > 0) {
            $callback($rows);
        }
    }

    private function queryPossibleMembers(string $groupId, string $teamId, array $ids): array
    {
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");

        $results = $TeamMember->findVerifiedTeamMembersByTeamAndGroup(
            (int) $groupId,
            (int) $teamId,
            $ids
        );

        $retrievedIds = Hash::extract($results, '{n}.TeamMember.member_no');
        $invalidIds = array_diff(array_unique($ids), $retrievedIds);

        return array_reduce(
            $results,
            function ($acc, $data) {

                if ($data['MemberGroup']['group_id'] === null) {
                    array_push($acc['validUserIds'], $data['TeamMember']['user_id']);
                } else {
                    array_push($acc['existingUserIds'], $data['TeamMember']['user_id']);
                }

                return $acc;
            },
            [
                'invalidIds' => $invalidIds,
                'validUserIds' => [],
                'existingUserIds' => []
            ]
        );
    }
}
