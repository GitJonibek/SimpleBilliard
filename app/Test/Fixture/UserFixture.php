<?php

/**
 * UserFixture
 * /** @noinspection PhpUndefinedClassInspection
 */
class UserFixture extends CakeTestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = array(
        'id'                => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary', 'comment' => 'ユーザID'),
        'password'          => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'パスワード(暗号化)', 'charset' => 'utf8'),
        'password_token'    => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'パスワードトークン(パスワード失念時の認証用)', 'charset' => 'utf8'),
        'password_modified' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'パスワード最終更新日'),
        'no_pass_flg'       => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'パスワード未使用フラグ(ソーシャルログインのみ利用時)'),
        'photo_file_name'   => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'プロフィール画像', 'charset' => 'utf8'),
        'primary_email_id'  => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'プライマリメールアドレスID(hasOneでEmailモデルに関連)'),
        'active_flg'        => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => 'アクティブフラグ(ユーザ認証済みの場合On)'),
        'last_login'        => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '最終ログイン日時'),
        'admin_flg'         => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '管理者フラグ(管理画面が開ける人)'),
        'default_team_id'   => array('type' => 'biginteger', 'null' => true, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'デフォルトチーム(belongsToでTeamモデルに関連)'),
        'timezone'          => array('type' => 'float', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'タイムゾーン(UTCを起点とした時差)'),
        'auto_timezone_flg' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '自動タイムゾーンフラグ(Onの場合はOSからタイムゾーンを取得する)'),
        'language'          => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '言語(日本語ならjpn)', 'charset' => 'utf8'),
        'auto_language_flg' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '自動言語設定フラグ(Onの場合はブラウザから言語を取得する)'),
        'romanize_flg'      => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ローマ字表記フラグ(Onの場合は自分の名前がアプリ内で英語表記になる)。local_first_name,local_last_nameが入力されていても、first_name,last_nameがつかわれる。'),
        'update_email_flg'  => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '更新情報メールフラグ(Onの場合はアプリから更新情報がメールで届く)'),
        'del_flg'           => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '削除フラグ'),
        'deleted'           => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => 'ユーザが退会した日付時刻'),
        'gender_type'       => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 3, 'unsigned' => true, 'comment' => '性別(1:男,2:女)'),
        'birth_day'         => array('type' => 'date', 'null' => true, 'default' => null, 'comment' => '誕生日'),
        'hide_year_flg'     => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '誕生日の年を隠すフラグ'),
        'hometown'          => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '出身地', 'charset' => 'utf8'),
        'comment'           => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメント', 'charset' => 'utf8'),
        'first_name'        => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '英名', 'charset' => 'utf8'),
        'last_name'         => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '英姓', 'charset' => 'utf8'),
        'middle_name'       => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '英ミドルネーム', 'charset' => 'utf8'),
        'created'           => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => 'ユーザーデータを登録した日付時刻'),
        'modified'          => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => 'ユーザーデータを最後に更新した日付時刻'),
        'indexes'           => array(
            'PRIMARY'          => array('column' => 'id', 'unique' => 1),
            'primary_email_id' => array('column' => 'primary_email_id', 'unique' => 0),
            'default_team_id'  => array('column' => 'default_team_id', 'unique' => 0),
            'password_token'   => array('column' => 'password_token', 'unique' => 0),
            'del_flg'          => array('column' => 'del_flg', 'unique' => 0),
            'active_flg'       => array('column' => 'active_flg', 'unique' => 0)
        ),
        'tableParameters'   => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    /**
     * Records
     *
     * @var array
     */
    public $records = array(
        array(
            'id'               => '1',
            'first_name'        => 'Lorem ipsum dolor sit amet',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 1,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => '',
            'primary_email_id' => '1',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => null,
            'timezone'          => 1,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '2',
            'first_name'        => 'メール認証テスト',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => '',
            'password_token'    => '',
            'password_modified' => '',
            'no_pass_flg'       => 1,
            'photo_file_name'  => '',
            'primary_email_id' => '',
            'active_flg'        => 0,
            'last_login'       => null,
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 2,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '2014-05-22 02:28:04',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '3',
            'first_name'        => 'Lorem ipsum dolor sit amet',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 3,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 3,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '2014-05-22 02:28:04',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '4',
            'first_name'       => '過去に一度もログインしていないユーザ',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 4,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'       => '',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 4,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '2014-05-22 02:28:04',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '5',
            'first_name'       => 'English user',
            'last_name'        => 'Last name',
            'middle_name'       => '',
            'gender_type'       => 1,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 5,
            'auto_timezone_flg' => 1,
            'language'          => 'eng',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '6',
            'first_name'        => 'first',
            'last_name'         => 'last',
            'middle_name'       => '',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 6,
            'auto_timezone_flg' => 1,
            'language'          => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 0,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '7',
            'first_name'        => 'first',
            'last_name'         => 'last',
            'middle_name'       => '',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 6,
            'auto_timezone_flg' => 1,
            'language'          => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 0,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '8',
            'first_name'        => 'first',
            'last_name'         => 'last',
            'middle_name'       => '',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 6,
            'auto_timezone_flg' => 1,
            'language'          => 'jpn',
            'auto_language_flg' => 0,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '9',
            'first_name'        => 'Lorem ipsum dolor sit amet',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 9,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => 'Lorem ipsum dolor sit amet',
            'password_token'    => 'Lorem ipsum dolor sit amet',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 9,
            'auto_timezone_flg' => 1,
            'language'         => null,
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'          => 0,
            'deleted'          => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '10',
            'first_name'       => 'login test',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'      => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'         => '4d7b8198a8560d4002201aa1ef75a6f537c45f5c',
            'password_token'   => '',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '9',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 10,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'          => 0,
            'deleted'          => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '11',
            'first_name'        => 'verify email success',
            'last_name'         => 'Lorem ipsum dolor sit amet',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => '4d7b8198a8560d4002201aa1ef75a6f537c45f5c',
            'password_token'    => '',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'Lorem ipsum dolor sit amet',
            'primary_email_id' => '4',
            'active_flg'        => 0,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 10,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
        array(
            'id'               => '12',
            'first_name'       => 'testFirst',
            'last_name'        => 'testLast',
            'middle_name'       => 'Lorem ipsum dolor sit amet',
            'gender_type'       => 2,
            'birth_day'         => '2014-05-22',
            'hide_year_flg'     => 1,
            'hometown'          => 'Lorem ipsum dolor sit amet',
            'comment'           => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'password'          => '4d7b8198a8560d4002201aa1ef75a6f537c45f5c',
            'password_token'    => '',
            'password_modified' => '2014-05-22 02:28:04',
            'no_pass_flg'       => 1,
            'photo_file_name'  => 'aaa.jpg',
            'primary_email_id' => '4',
            'active_flg'        => 1,
            'last_login'        => '2014-05-22 02:28:04',
            'admin_flg'         => 1,
            'default_team_id'  => 1,
            'timezone'          => 10,
            'auto_timezone_flg' => 1,
            'language'         => 'jpn',
            'auto_language_flg' => 1,
            'romanize_flg'      => 1,
            'update_email_flg'  => 1,
            'del_flg'           => 0,
            'deleted'           => '',
            'created'           => '2014-05-22 02:28:04',
            'modified'          => '2014-05-22 02:28:04'
        ),
    );
}
