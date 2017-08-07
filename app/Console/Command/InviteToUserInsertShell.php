<?php
App::uses('AppUtil', 'Util');

class InviteToUserInsertShell extends AppShell
{
    var $uses = array(
        'Invite',
        'User',
        'Email',
        'TeamMember'
    );

    public function startup()
    {
        parent::startup();
    }

    /**
     * @return ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $options = [
            'currentTimestamp' => [
                'short'    => 'c',
                'help'     => '[ It is used for only test cases ]',
                'required' => false,
            ],
        ];
        $parser->addOptions($options);
        return $parser;
    }

    /**
     * main process
     * - Get unverified & before expired invites
     * - (If email not exists, save email record & save user record)
     * - Save Team Member record
     *
     * @return void
     */
    public function main()
    {
        $currentTimestamp = $this->params['currentTimestamp'] ?? time();
        $targetInvites = $this->Invite->findUnverifiedBeforeExpired($currentTimestamp);
        if (count($targetInvites) === 0) {
            $this->out('There is no invites data to create new user.');
            return;
        }

        try {
            $this->User->begin();

            // register new user
            // "to_user_id=/^$/" means to_user_id == null
            // if to_user_id isn't null, user already exists in other team.
            $newUserInvites = Hash::combine($targetInvites, '{n}[to_user_id=/^$/].id', '{n}[to_user_id=/^$/]');
            $insertEmails = [];
            foreach ($newUserInvites as $invite) {
                // Save user
                $this->User->create();

                // The reason data is empty, at this time there is no user save data.
                // This data will be registered by user signup.
                if (!$this->User->save([], false)) {
                    throw new Exception(sprintf("Failed to insert users. data:%s",
                            AppUtil::varExportOneLine(compact('invite')))
                    );
                }
                $newUserId = $this->User->getLastInsertID();

                // Update invite
                $this->Invite->id = $invite['id'];
                if (!$this->Invite->saveField('to_user_id', $newUserId)) {
                    throw new Exception(sprintf("Failed to update invite. data:%s",
                            AppUtil::varExportOneLine(compact('invite', 'newUserId')))
                    );
                }

                // Save email
                $insertEmail = [
                    'user_id' => $newUserId,
                    'email'   => $invite['email']
                ];
                if (!$this->Email->save($insertEmail)) {
                    throw new Exception(sprintf("Failed to insert emails. data:%s",
                            AppUtil::varExportOneLine(compact('insertEmails', 'newUserInvites')))
                    );
                }
                $newEmailId = $this->Email->getLastInsertID();

                // Update user primary_email_id
                $this->User->id = $newUserId;
                if (!$this->User->saveField('primary_email_id', $newEmailId)) {
                    throw new Exception(sprintf("Failed to update user primary email. data:%s",
                            AppUtil::varExportOneLine(compact('newUserId', 'newEmailId')))
                    );
                }
            }

            /* Insert team_members table */
            $insertTeamMembers = [];
            // invites after updating to_user_id
            $targetInvitesWithToUserId = $this->Invite->findUnverifiedBeforeExpired($currentTimestamp);
            foreach ($targetInvitesWithToUserId as $invite) {
                $insertTeamMembers[] = [
                    'user_id' => $invite['to_user_id'],
                    'team_id' => $invite['team_id'],
                    'status'  => TeamMember::USER_STATUS_INVITED
                ];
            }
            if (!$this->TeamMember->bulkInsert($insertTeamMembers)) {
                throw new Exception(sprintf("Failed to insert team members. data:%s",
                        AppUtil::varExportOneLine(compact('insertTeamMembers', 'targetInvitesWithToUserId')))
                );
            }

        } catch (Exception $e) {
            $this->User->rollback();
            CakeLog::error($e->getMessage());
            CakeLog::error($e->getTraceAsString());
            exit(1);
        }
        $this->User->commit();
    }
}
