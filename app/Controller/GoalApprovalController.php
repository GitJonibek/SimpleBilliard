<?php
App::uses('AppController', 'Controller');
App::uses('Collaborator',  'Model');

/**
 * GoalApproval Controller
 *
 * @property GoalApproval $GoalApproval
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class GoalApprovalController extends AppController {
	/*
	 * コーチ判定フラグ
	 * true: コーチがいる false: コーチがいない
	 */
	private $coach_flag = FALSE;

	/*
	 * メンバー判定フラグ
	 * true: メンバーがいる false: メンバーがいない
	 */
	private $member_flag = FALSE;

	/*
	 * ログインしているユーザータイプ
	 */
	private $user_type = 0;

	/*
	 * ログインユーザーのuser_id
	 */
	private $user_id = NULL;

	/*
	 * ログインユーザーのteam_id
	 */
	private $team_id = NULL;

	/*
	 * オーバーライド
	 */
	public function beforeFilter() {

		parent::beforeFilter();

		$Session = new CakeSession();
		$this->user_id = $Session->read('Auth.User.id');
		$this->team_id = $Session->read('current_team_id');

		$this->setCoachFlag($this->user_id, $this->team_id);
		$this->setMemberFlag($this->user_id, $this->team_id);

		// コーチ認定機能が使えるユーザーはトップページ
		if ($this->user_type = $this->getUserType() === 0) {
		}

		// test code
		$this->user_type = 1;

		$this->layout = LAYOUT_ONE_COLUMN;
	}

	/*
	 * 処理待ちページ
	 */
	public function index() {

		$result_data = array();

		if ($this->user_type === 1) {
			$a = $this->Goal->getMyApprovalGoal(0);
			//var_dump($a[0]['MyCollabo']);

		} elseif ($this->user_type === 2) {
			$this->Goal->getMyGoals();
			// + メンバーのゴールを取得

		} elseif ($this->user_type === 3) {
			// + メンバーのゴールのみ取得
		}

		$this->set($result_data);
	}

	/*
	 * 処理済みページ
	 */
	public function done() {
	}

	/*
	 * 承認する
	 */
	public function doApproval() {
		return $this->index();
	}

	/*
	 * 承認しない
	 */
	public function dontApproval() {
		return $this->index();
	}

	/*
	 * 処理を取り消す
	 */
	public function cancle() {
		return $this->done();
	}

	/*
	 * ログインしているユーザーはコーチが存在するのか
	 */
	private function setCoachFlag ($user_id, $team_id) {
		$this->selectCoachUserIdFromTeamMembersTB($user_id, $team_id);
		$this->coach_flag = TRUE;
	}

	/*
	 * ログインしているユーザーのコーチIDを取得する
	 * TODO: Model/TeamMemberに定義するのが正しい
	 */
	private function selectCoachUserIdFromTeamMembersTB ($user_id, $team_id) {
		// 検索テーブル: team_members
		// 取得カラム: coach_user_id
		// 条件: user_id, team_id
	}

	/*
	 * ログインしているユーザーは管理するメンバー存在するのか
	 */
	private function setMemberFlag ($user_id, $team_id) {
		$this->selectUserIdFromTeamMembersTB($user_id, $team_id);
		$this->member_flag = TRUE;
	}

	/*
	 * ログインしているユーザーが管理するのメンバーIDを取得する
	 * TODO: Model/TeamMemberに定義するのが正しい
	 */
	private function selectUserIdFromTeamMembersTB ($user_id, $team_id) {
		// 検索テーブル: team_members
		// 取得カラム: user_id
		// 条件: coach_user_id = パラメータ1 team_id = パラメータ2
	}

	/*
	 * コーチ認定機能を使えるユーザーか判定
	 * 1: コーチがいる、メンバーいない
	 * 2: コーチいる、メンバーがいる
	 * 3: コーチがいない、メンバーがいる
	 */
	private function getUserType() {

		if ($this->coach_flag === TRUE && $this->member_flag === FALSE) {
			return 1;
		}

		if ($this->coach_flag === TRUE && $this->member_flag === TRUE) {
			return 2;
		}

		if ($this->coach_flag === FALSE && $this->member_flag === TRUE) {
			return 3;
		}

		return 0;
	}

}
