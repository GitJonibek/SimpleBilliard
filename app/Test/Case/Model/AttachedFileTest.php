<?php
App::uses('AttachedFile', 'Model');

/**
 * AttachedFile Test Case
 *
 * @property AttachedFile $AttachedFile
 */
class AttachedFileTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.attached_file',
        'app.user',
        'app.team',
        'app.badge',
        'app.circle',
        'app.circle_member',
        'app.post_share_circle',
        'app.post',
        'app.goal',
        'app.purpose',
        'app.goal_category',
        'app.key_result',
        'app.action_result',
        'app.collaborator',
        'app.approval_history',
        'app.follower',
        'app.evaluation',
        'app.evaluate_term',
        'app.evaluator',
        'app.evaluate_score',
        'app.comment',
        'app.comment_like',
        'app.comment_read',
        'app.post_share_user',
        'app.post_like',
        'app.post_read',
        'app.comment_mention',
        'app.given_badge',
        'app.post_mention',
        'app.group',
        'app.member_group',
        'app.group_vision',
        'app.invite',
        'app.job_category',
        'app.team_member',
        'app.member_type',
        'app.thread',
        'app.message',
        'app.evaluation_setting',
        'app.team_vision',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name',
        'app.comment_file',
        'app.post_file'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->AttachedFile = ClassRegistry::init('AttachedFile');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AttachedFile);

        parent::tearDown();
    }

    function testPreUpLoadFileSuccess()
    {
        $data = [
            'AttachedFile' => [
                'file' => [
                    'name'     => 'test',
                    'type'     => 'image/jpeg',
                    'tmp_name' => IMAGES . 'no-image.jpg'
                ]
            ]
        ];
        $res = $this->AttachedFile->preUploadFile($data);
        $this->assertNotEmpty($res);
    }

    function testPreUpLoadFileFail()
    {
        $res = $this->AttachedFile->preUploadFile([]);
        $this->assertFalse($res);
    }

    function testCancelUploadFileSuccess()
    {
        $res = $this->AttachedFile->cancelUploadFile('test');
        $this->assertNotEmpty($res);
    }

    function testCancelUploadFileFail()
    {
        $res = $this->AttachedFile->cancelUploadFile(null);
        $this->assertFalse($res);
    }

    function testIsUnavailableModelTypeFalse()
    {
        $res = $this->AttachedFile->isUnavailableModelType(AttachedFile::TYPE_MODEL_POST);
        $this->assertFalse($res);
    }

    function testIsUnavailableModelTypeTrue()
    {
        $res = $this->AttachedFile->isUnavailableModelType(1000);
        $this->assertTrue($res);
    }

    function testSaveRelatedFilesSuccess()
    {
        $res = $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, ['test']);
        $this->assertTrue($res);
    }

    function testSaveRelatedFilesFail()
    {
        $res = $this->AttachedFile->saveRelatedFiles(1, 1000, ['test']);
        $this->assertFalse($res);
    }

    function testDeleteAllRelatedFilesSuccess()
    {
        $res = $this->AttachedFile->deleteAllRelatedFiles(1, AttachedFile::TYPE_MODEL_POST);
        $this->assertTrue($res);
    }

    function testDeleteAllRelatedFilesFail()
    {
        $res = $this->AttachedFile->deleteAllRelatedFiles(1, 1000);
        $this->assertFalse($res);
    }

    function testDeleteRelatedFileSuccess()
    {
        $res = $this->AttachedFile->deleteRelatedFile(1, AttachedFile::TYPE_MODEL_POST);
        $this->assertTrue($res);
    }

    function testDeleteRelatedFileFail()
    {
        $res = $this->AttachedFile->deleteRelatedFile(1, 1000);
        $this->assertFalse($res);
    }
}
