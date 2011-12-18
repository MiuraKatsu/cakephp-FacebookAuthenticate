<?php
class UsersController extends AppController
{
	var	$components = array('Auth');
 
	function beforeFilter()
	{

		$this->Auth->authenticate = array('Facebook');

		$this->Auth->allow('index', 'login','callback','logout');
		$this->Auth->loginRedirect = '/users/index/';
		$this->Auth->logoutRedirect = '/users/index/';
 
		parent::beforeFilter();
	}
 
	public function index()
	{
		$user = $this->Auth->user();
		if(isset($user['Member']['user_id'])) {
			$this->set('title_for_layout', $user['Member']['user_name'] . 'さんのマイページ');

		} else {
			$this->set('title_for_layout', 'ゲストさんのマイページ');
		}
	}
	
	public function login($service){
		$user = $this->Auth->user();

		if(isset($user['Member']['user_id'])) {
			$this->redirect($this->Auth->loginRedirect);

		}else{
			$this->Auth->login();
			$this->autoRender = false;
		}
	}

	public function callback($service){
		$this->autoRender = false;
		$user = $this->Auth->identify($this->request,$this->response);
	}
 
	public function logout()
	{
		$user = $this->Auth->user();
		if($user['Member']['user_id']) {
			$this->autoRender = false;
			$this->Auth->logout();
		}
		$this->redirect( $this->Auth->logoutRedirect );
	}
}
