<?php
App::import('Lib/DataExtender', 'BaseExtender');
App::import('Service', 'ImageStorageService');
App::uses('TeamMember', 'Model');
App::uses('LangUtil', 'Util');
use Goalous\Enum as Enum;

class MeExtender extends BaseExtender
{
    const EXTEND_ALL = "ext:user:all";
    const EXTEND_CURRENT_TEAM_MEMBER_OWN = "ext:user:is_current_team_admin";
    const EXTEND_JOINED_ACTIVE_TEAMS = "ext:user:joined_active_teams";
    const EXTEND_NOTIFICATION_SETTING = "ext:user:notification_setting";

    public function extend(array $data, int $userId, int $teamId, array $extensions = []): array
    {
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init('TeamMember');
        /** @var NotifySetting $NotifySetting */
        $NotifySetting = ClassRegistry::init('NotifySetting');

        /** @var ImageStorageService $ImageStorageService */
        $ImageStorageService = ClassRegistry::init('ImageStorageService');
        $data['profile_img_url'] = $ImageStorageService->getImgUrlEachSize($data, 'User');
        $data['cover_img_url'] = $ImageStorageService->getImgUrlEachSize($data, 'User', 'cover_photo');

        $data['current_team_id'] = $teamId;
        $data['language'] = LangUtil::convertISOFrom3to2($data['language']);

        if ($this->includeExt($extensions, self::EXTEND_CURRENT_TEAM_MEMBER_OWN)) {
            $data['current_team_member_own'] = $TeamMember->getUnique($userId, $teamId);
        }
        if ($this->includeExt($extensions, self::EXTEND_JOINED_ACTIVE_TEAMS)) {
            $activeTeams = $TeamMember->getActiveTeamList($userId);
            $data['my_active_teams'] = [];
            foreach ($activeTeams as $teamId => $name) {
                $data['my_active_teams'][] = [
                    'id' => $teamId,
                    'name' => $name
                ];
            }
        }
        if ($this->includeExt($extensions, self::EXTEND_NOTIFICATION_SETTING)) {
            $NotifySetting->current_team_id = $teamId;
            $data['notify_setting'] = $NotifySetting->getMySettings($userId);
        }

        return $data;
    }

    public function extendMulti(array $data, int $userId, int $teamId, array $extensions = []): array
    {
        throw new RuntimeException("Please implement " . __METHOD__);
    }
}
