<?php App::uses('GoalousTestCase', 'Test');
App::uses('AttachedFile', 'Model');

/**
 * AttachedFile Test Case
 *
 * @property AttachedFile $AttachedFile
 */
class AttachedFileTest extends GoalousTestCase
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
        'app.post_file',
        'app.action_result_file',
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

    function _setDefault()
    {
        $this->AttachedFile->current_team_id = 1;
        $this->AttachedFile->my_uid = 1;
        $this->AttachedFile->PostFile->current_team_id = 1;
        $this->AttachedFile->PostFile->my_uid = 1;
        $this->AttachedFile->CommentFile->current_team_id = 1;
        $this->AttachedFile->CommentFile->my_uid = 1;
        $this->AttachedFile->PostFile->Post->current_team_id = 1;
        $this->AttachedFile->PostFile->Post->my_uid = 1;
        $this->AttachedFile->PostFile->Post->PostShareCircle->current_team_id = 1;
        $this->AttachedFile->PostFile->Post->PostShareCircle->my_uid = 1;
        $this->AttachedFile->PostFile->Post->PostShareUser->current_team_id = 1;
        $this->AttachedFile->PostFile->Post->PostShareUser->my_uid = 1;
    }

    function testPreUpLoadFileSuccess()
    {
        $data = [
            'file' => [
                'name'     => 'test',
                'type'     => 'image/jpeg',
                'tmp_name' => IMAGES . 'no-image.jpg'
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
        $data = [
            'file' => [
                'name'     => 'test',
                'type'     => 'image/jpeg',
                'tmp_name' => IMAGES . 'no-image.jpg'
            ]
        ];
        $hashed_key = $this->AttachedFile->preUploadFile($data);
        $res = $this->AttachedFile->cancelUploadFile($hashed_key);
        $this->assertTrue($res);
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
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $res = $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);
        $this->assertTrue($res);
        $this->assertCount(2, $this->AttachedFile->find('all'));
        $this->assertCount(2, $this->AttachedFile->PostFile->find('all'));
    }

    function testSaveRelatedFilesFailSizeOver()
    {
        $this->_setDefault();
        $hashes = $this->_prepareTestFiles(10000000000000);
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $res = $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);
        $this->assertFalse($res);
    }

    function testSaveRelatedFilesActionImgSuccess()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $res = $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_ACTION_RESULT, $hashes);
        $this->assertTrue($res);
        $this->assertCount(2, $this->AttachedFile->find('all'));
        $this->assertCount(2, $this->AttachedFile->ActionResultFile->find('all'));
        $options = [
            'conditions' => [
                'removable_flg'         => false,
                'display_file_list_flg' => false
            ],
            'contain'    => [
                'ActionResultFile'
            ]
        ];
        $main_img = $this->AttachedFile->find('all', $options);
        $this->assertCount(1, $main_img);
        $this->assertEquals(0, $main_img[0]['ActionResultFile'][0]['index_num']);
    }

    function testSaveRelatedFilesFail()
    {
        $res = $this->AttachedFile->saveRelatedFiles(1, 1000, ['test']);
        $this->assertFalse($res);
    }

    function testUpdateRelatedFilesFail()
    {
        $res = $this->AttachedFile->updateRelatedFiles(1, 1000, [1, 2], ['test']);
        $this->assertFalse($res);
    }

    /**
     * ファイルアップデートのテスト
     * もともと２ファイルを持っており、１ファイル削除し、２ファイル追加したときに合計３ファイルになる事を確認
     */
    function testUpdateRelatedFilesSuccess()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $prepare_post_file_data = [
            'AttachedFile' => [
                'attached_file_name' => 'test.jpg',
                'user_id'            => 1,
                'team_id'            => 1,
                'file_type'          => AttachedFile::TYPE_FILE_IMG,
                'file_ext'           => 'jpg',
                'file_size'          => '1111',
                'model_type'         => AttachedFile::TYPE_MODEL_POST
            ],
            'PostFile'     => [
                [
                    'post_id'   => 1,
                    'index_num' => 0,
                    'team_id'   => 1,
                ]
            ]
        ];
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $prepare_post_file_data['PostFile']['index_num'] = 1;
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $res = $this->AttachedFile->updateRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, array_merge([1], $hashes),
                                                       [2]);
        $files = $this->AttachedFile->find('all');
        $post_files = $this->AttachedFile->PostFile->find('all');
        $this->assertTrue($res);
        $this->assertCount(3, $files);
        $this->assertCount(3, $post_files);
        $this->assertEquals(4, $post_files[1]['PostFile']['id']);
        $this->assertEquals(3, $post_files[1]['PostFile']['attached_file_id']);
    }

    function testUpdateRelatedFilesFailSizeOver()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles(100000000);
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $prepare_post_file_data = [
            'AttachedFile' => [
                'attached_file_name' => 'test.jpg',
                'user_id'            => 1,
                'team_id'            => 1,
                'file_type'          => AttachedFile::TYPE_FILE_IMG,
                'file_ext'           => 'jpg',
                'file_size'          => '1111',
                'model_type'         => AttachedFile::TYPE_MODEL_POST
            ],
            'PostFile'     => [
                [
                    'post_id'   => 1,
                    'index_num' => 0,
                    'team_id'   => 1,
                ]
            ]
        ];
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $prepare_post_file_data['PostFile']['index_num'] = 1;
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $res = $this->AttachedFile->updateRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, array_merge([1], $hashes),
                                                       [2]);
        $this->assertFalse($res);
    }

    /**
     * ファイルアップデートのテスト(アクション)
     * もともと２ファイルを持っており、１ファイル削除し、２ファイル追加したときに合計３ファイルになる事を確認
     */
    function testUpdateRelatedFilesActionSuccess()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $prepare_post_file_data = [
            'AttachedFile'     => [
                'attached_file_name' => 'test_abc.jpg',
                'user_id'            => 1,
                'team_id'            => 1,
                'file_type'          => AttachedFile::TYPE_FILE_IMG,
                'file_ext'           => 'jpg',
                'file_size'          => '1111',
                'model_type'         => AttachedFile::TYPE_MODEL_ACTION_RESULT
            ],
            'ActionResultFile' => [
                [
                    'action_result_id' => 1,
                    'index_num'        => 0,
                    'team_id'          => 1,
                ]
            ]
        ];
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $prepare_post_file_data['ActionResultFile'][0]['index_num'] = 1;
        $prepare_post_file_data['AttachedFile']['attached_file_name'] = 'test_zzz.jpg';
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $res = $this->AttachedFile->updateRelatedFiles(1, AttachedFile::TYPE_MODEL_ACTION_RESULT,
                                                       array_merge($hashes, [2]),
                                                       [1]);
        $files = $this->AttachedFile->find('all');
        $action_res_files = $this->AttachedFile->ActionResultFile->find('all', ['order' => ['index_num asc']]);
        $main_img = $this->AttachedFile->find('all', ['conditions' => ['display_file_list_flg' => false]]);
        $main_img_action_res_file = $this->AttachedFile->ActionResultFile->find('first',
                                                                                ['conditions' => ['attached_file_id' => $main_img[0]['AttachedFile']['id']]]);
        $this->assertTrue($res);
        $this->assertCount(3, $files);
        $this->assertCount(3, $action_res_files);
        $this->assertCount(1, $main_img);
        $this->assertFalse($main_img[0]['AttachedFile']['display_file_list_flg']);
        $this->assertFalse($main_img[0]['AttachedFile']['removable_flg']);
        $this->assertEquals(0, $main_img_action_res_file['ActionResultFile']['index_num']);
    }

    function testUpdateRelatedFilesActionNoChangesSuccess()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $prepare_post_file_data = [
            'AttachedFile'     => [
                'attached_file_name'    => 'test_abc.jpg',
                'user_id'               => 1,
                'team_id'               => 1,
                'file_type'             => AttachedFile::TYPE_FILE_IMG,
                'file_ext'              => 'jpg',
                'file_size'             => '1111',
                'model_type'            => AttachedFile::TYPE_MODEL_ACTION_RESULT,
                'removable_flg'         => false,
                'display_file_list_flg' => false,

            ],
            'ActionResultFile' => [
                [
                    'action_result_id' => 1,
                    'index_num'        => 0,
                    'team_id'          => 1,
                ]
            ]
        ];
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $prepare_post_file_data['ActionResultFile'][0]['index_num'] = 1;
        $prepare_post_file_data['AttachedFile']['attached_file_name'] = 'test_zzz.jpg';
        $this->AttachedFile->saveAll($prepare_post_file_data);
        $res = $this->AttachedFile->updateRelatedFiles(1, AttachedFile::TYPE_MODEL_ACTION_RESULT,
                                                       array_merge([1], $hashes), [2]);
        $files = $this->AttachedFile->find('all');
        $action_res_files = $this->AttachedFile->ActionResultFile->find('all', ['order' => ['index_num asc']]);
        $main_img = $this->AttachedFile->find('all', ['conditions' => ['display_file_list_flg' => false]]);
        $main_img_action_res_file = $this->AttachedFile->ActionResultFile->find('first',
                                                                                ['conditions' => ['attached_file_id' => $main_img[0]['AttachedFile']['id']]]);
        $this->assertTrue($res);
        $this->assertCount(3, $files);
        $this->assertCount(3, $action_res_files);
        $this->assertCount(1, $main_img);
        $this->assertFalse($main_img[0]['AttachedFile']['display_file_list_flg']);
        $this->assertFalse($main_img[0]['AttachedFile']['removable_flg']);
        $this->assertEquals(0, $main_img_action_res_file['ActionResultFile']['index_num']);
    }

    function testDeleteAllRelatedFilesSuccess()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);

        $res = $this->AttachedFile->deleteAllRelatedFiles(1, AttachedFile::TYPE_MODEL_POST);
        $this->assertTrue($res);
        $this->assertCount(0, $this->AttachedFile->find('all'));
        $this->assertCount(0, $this->AttachedFile->PostFile->find('all'));

    }

    function testDeleteAllRelatedFilesFail()
    {
        $res = $this->AttachedFile->deleteAllRelatedFiles(1, 1000);
        $this->assertFalse($res);
    }

    function testDeleteFile()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);
        $id = $this->AttachedFile->getLastInsertID();
        $this->AttachedFile->delete($id);
        $this->assertCount(1, $this->AttachedFile->find('all'));
        $this->assertCount(1, $this->AttachedFile->PostFile->find('all'));
    }

    function testGetFileTypeOptions()
    {
        $res = $this->AttachedFile->getFileTypeOptions();
        $this->assertNotEmpty($res);
    }

    function testGetFileTypeId()
    {
        $res = $this->AttachedFile->getFileTypeId('image');
        $this->assertEquals(AttachedFile::TYPE_FILE_IMG, $res);
        $res = $this->AttachedFile->getFileTypeId('not_found_item');
        $this->assertNull($res);
    }

    function testGetCountOfAttachedFilesFalse()
    {
        $res = $this->AttachedFile->getCountOfAttachedFiles(1, 10000);
        $this->assertFalse($res);
    }

    function testGetCountOfAttachedFilesNoFileType()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);
        $res = $this->AttachedFile->getCountOfAttachedFiles(1, AttachedFile::TYPE_MODEL_POST);
        $this->assertEquals(2, $res);
    }

    function testGetCountOfAttachedFilesWithFileType()
    {
        $this->_setDefault();
        $this->_resetTable();
        $hashes = $this->_prepareTestFiles();
        $upload_setting = $this->AttachedFile->actsAs['Upload'];
        $upload_setting['attached']['path'] = ":webroot/upload/test/:model/:id/:hash_:style.:extension";
        $this->AttachedFile->Behaviors->load('Upload', $upload_setting);
        $this->AttachedFile->saveRelatedFiles(1, AttachedFile::TYPE_MODEL_POST, $hashes);
        $res = $this->AttachedFile->getCountOfAttachedFiles(1, AttachedFile::TYPE_MODEL_POST,
                                                            AttachedFile::TYPE_FILE_IMG);
        $this->assertEquals(1, $res);
    }

    function testIsReadable()
    {
        $this->_setDefault();

        // 投稿への添付ファイル
        $res = $this->AttachedFile->isReadable(1);
        $this->assertTrue($res);

        // 投稿のコメントへの添付ファイル
        $res = $this->AttachedFile->isReadable(3);
        $this->assertTrue($res);

        // アクションのコメントへの添付ファイル
        $res = $this->AttachedFile->isReadable(4);
        $this->assertTrue($res);

        // 公開サークルへの添付ファイル
        $res = $this->AttachedFile->isReadable(5);
        $this->assertTrue($res);

        // 個人共有投稿の添付ファイル
        $res = $this->AttachedFile->isReadable(6);
        $this->assertTrue($res);

        // アクションへの添付ファイル
        $res = $this->AttachedFile->isReadable(2);
        $this->assertTrue($res);

        // 秘密サークルへの添付ファイル
        $res = $this->AttachedFile->isReadable(7);
        $this->assertFalse($res);

        // 存在しないファイルID
        $res = $this->AttachedFile->isReadable(99889988);
        $this->assertFalse($res);
    }

    function testGetFileUrl()
    {
        $this->_setDefault();
        // 正常
        $url = $this->AttachedFile->getFileUrl(1);
        $this->assertNotEmpty($url);

        // 存在しないファイルID
        $url = $this->AttachedFile->getFileUrl(99889988);
        $this->assertEmpty($url);
    }

    function _prepareTestFiles($file_size = 1000)
    {
        $destDir = TMP . 'attached_file';
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0777, true);
            @chmod($destDir, 0777);
        }
        $file_1_path = TMP . 'attached_file' . DS . 'attached_file_1.jpg';
        $file_2_path = TMP . 'attached_file' . DS . 'attached_file_2.php';
        copy(IMAGES . 'no-image.jpg', $file_1_path);
        copy(APP . WEBROOT_DIR . DS . 'test.php', $file_2_path);

        $data = [
            'file' => [
                'name'     => 'test.jpg',
                'type'     => 'image/jpeg',
                'tmp_name' => $file_1_path,
                'size'     => $file_size,
                'remote'   => true
            ]
        ];
        $hash_1 = $this->AttachedFile->preUploadFile($data);
        $data = [
            'file' => [
                'name'     => 'test.php',
                'type'     => 'test/php',
                'tmp_name' => $file_2_path,
                'size'     => 1000,
                'remote'   => true
            ]
        ];
        $hash_2 = $this->AttachedFile->preUploadFile($data);

        return [$hash_1, $hash_2];
    }

    function _resetTable()
    {
        $this->AttachedFile->query("DELETE FROM {$this->AttachedFile->useTable}");
//        $this->AttachedFile->query("ALTER TABLE {$this->AttachedFile->useTable} AUTO_INCREMENT = 1");
        $this->AttachedFile->query("delete from sqlite_sequence where name='{$this->AttachedFile->useTable}'");
        $this->AttachedFile->query("DELETE FROM {$this->AttachedFile->PostFile->useTable}");
//        $this->AttachedFile->query("ALTER TABLE {$this->AttachedFile->PostFile->useTable} AUTO_INCREMENT = 1");
        $this->AttachedFile->query("delete from sqlite_sequence where name='{$this->AttachedFile->PostFile->useTable}'");
        $this->AttachedFile->query("DELETE FROM {$this->AttachedFile->ActionResultFile->useTable}");
//        $this->AttachedFile->query("ALTER TABLE {$this->AttachedFile->ActionResultFile->useTable} AUTO_INCREMENT = 1");
        $this->AttachedFile->query("delete from sqlite_sequence where name='{$this->AttachedFile->ActionResultFile->useTable}'");
        $this->AttachedFile->query("DELETE FROM {$this->AttachedFile->CommentFile->useTable}");
//        $this->AttachedFile->query("ALTER TABLE {$this->AttachedFile->CommentFile->useTable} AUTO_INCREMENT = 1");
        $this->AttachedFile->query("delete from sqlite_sequence where name='{$this->AttachedFile->CommentFile->useTable}'");
    }
}
