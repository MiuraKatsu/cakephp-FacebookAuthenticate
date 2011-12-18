<?php
App::uses('Router', 'Routing');
App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::import('Vendor','facebook', array('file' => 'facebook/src/facebook.php'));
class FacebookAuthenticate extends BaseAuthenticate {

	public $myConsumerKey = 'YOUR APPID';
	public $myConsumerSec = 'YOUR APP SECRET';

	var $service = 'facebook';

	public function authenticate(CakeRequest $request, CakeResponse $response) {
		$user = $this->_Collection->Auth->user();

		$service = strtolower($request->params['pass'][0]);
		if($service !== $this->service){
			return false;
		}

		try{
        	$facebook = new Facebook(array('appId'=>$this->myConsumerKey,'secret'=> $this->myConsumerSec,'cookie'=>true));
			$action = $request->params['action'];

			if($action === 'login' ){
				$url = $facebook->getLoginUrl(array('redirect_uri'=>Router::url('callback/facebook/',true),'scope'=>'email,publish_stream'));

				if ($url !== null) {
					$response->header('Location', $url);
					$response->send();
				}
			}elseif($action === 'callback' ){
				preg_match('/state=(.*)/',$_REQUEST['url'],$state);
				$_REQUEST['state'] = $state[1];
		
				$accessToken = $facebook->getAccessToken();

				if($accessToken != '') {
					$this->_fetch($facebook,$accessToken,$response);
				}else{
					//you should throw exception
				}
			}

		}catch(OAuthException $E){
			//you can catch OAuth exception
		}
	}


	protected function _fetch($facebook,$access_oauth_token,CakeResponse $response){

		try{
				// get user infomation from Facebook
				$user_id = $facebook->getUser();
				$me = $facebook->api('/me');

				$user = $this->_Collection->Auth->user();
				$user['Member']["user_id"] = $me['id'];
				$user['Member']["user_name"] = $me['name'];
				$user['Member']["access_oauth_token"] = $access_oauth_token;
 
				if ($this->_Collection->Auth->login($user)) {
					$loginRedirect = $this->_Collection->Auth->loginRedirect;
					$response->header('Location', $loginRedirect);
					$response->send();
				}
		}catch(OAuthException $E){
			//you can catch OAuth exception
		}
	}

}
