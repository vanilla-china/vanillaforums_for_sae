<?php if (!defined('APPLICATION')) exit();

$PluginInfo['WeiboSignin'] = array(
	'Name' => '新浪微博登录',
	'Description' => '使用新浪微博登录',
	'Version' => '0.1',
	'RequiredApplications' => array('Vanilla' => '2.0.12a'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => FALSE,
	'SettingsUrl' => '/dashboard/settings/weibo',
	'SettingsPermission' => 'Garden.Settings.Manage',
	'MobileFriendly' => FALSE,
	'HasLocale' => FALSE,
	'RegisterPermissions' => FALSE,
	'Author' => "T.G.",
	'AuthorEmail' => 'farmer1992@gmail.com',
);

class WeiboSignInPlugin extends Gdn_Plugin {

	private static $BUTTON_IMG_URL = 'http://www.sinaimg.cn/blog/developer/wiki/240.png';
	private static $BUTTON_IMG_TITLE = '使用新浪微博登录';

	public function AuthenticationController_Render_Before($Sender, $Args) {
		if (isset($Sender->ChooserList)) {
			$Sender->ChooserList['weibosignin'] = 'Weibo';
		}
		if (is_array($Sender->Data('AuthenticationConfigureList'))) {
			$List = $Sender->Data('AuthenticationConfigureList');
			$List['weibosignin'] = '/dashboard/settings/weibo';
			$Sender->SetData('AuthenticationConfigureList', $List);
		}
	}

	public function EntryController_SignIn_Handler($Sender, $Args) {
		if (isset($Sender->Data['Methods'])) {
			if (!$this->IsConfigured())
				return;

			// Add the twitter method to the controller.
			$Method = array(
				'Name' => 'Weibo',
				'SignInHtml' => $this->_GetButton(),
			);
			$Sender->Data['Methods'][] = $Method;
		}
	}


	public function Base_BeforeSignInButton_Handler($Sender, $Args) {
		if (!$this->IsConfigured())
			return;
			
		echo "\n".$this->_GetButton();
	}

	public function Base_ConnectData_Handler($Sender, $Args) {
		if (GetValue(0, $Args) != 'weibosignin'){
			return;
		}

		$client_id = C('Plugins.WeiboSignin.AppKey');
		$client_secret = C('Plugins.WeiboSignin.AppSecret');
		$redirect_uri = Url('/entry/connect/weibosignin', TRUE);

		$code = GetValue('code', $_GET);


		$Response = $this->PostAndJsondecode("https://api.weibo.com/oauth2/access_token" ,array(
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $redirect_uri, 
		));

		$access_token = GetValue('access_token', $Response);
		$uid = GetValue('uid', $Response);

		$Response = $this->GetAndJsondecode('https://api.weibo.com/2/users/show.json', array(
			'uid' => $uid,
			'access_token' => $access_token
		));

		if($uid <= 0){
			$Sender->Form->AddError('Auth Failed');
			return;
		}

		$Form = $Sender->Form; //new Gdn_Form();
		$ID = $uid;
		$Form->SetFormValue('UniqueID', $ID);
		$Form->SetFormValue('Provider', 'Weibo');
		$Form->SetFormValue('ProviderName', 'WeiboSignIn');
		$Form->SetFormValue('Name', GetValue('screen_name', $Response));
		//$Form->SetFormValue('FullName', GetValue('name', $Profile));
		$Form->SetFormValue('Email', GetValue('screen_name', $Response).'@via.weibo');
		//$Form->SetFormValue('Photo', GetValue('profile_image_url', $Profile));
		$Sender->SetData('Verified', TRUE);
	}

	public function SettingsController_Weibo_Create($Sender, $Args) {
		$Sender->Permission('Garden.Settings.Manage');
		if ($Sender->Form->IsPostBack()) {
			$Settings = array(
				'Plugins.WeiboSignin.AppKey' => $Sender->Form->GetFormValue('AppKey'),
				'Plugins.WeiboSignin.AppSecret' => $Sender->Form->GetFormValue('Secret')
			);

			SaveToConfig($Settings);
			$Sender->InformMessage(T("Your settings have been saved."));
		} else {
			$Sender->Form->SetFormValue('AppKey', C('Plugins.WeiboSignin.AppKey'));
			$Sender->Form->SetFormValue('Secret', C('Plugins.WeiboSignin.AppSecret'));
		}

		$Sender->AddSideMenu();
		$Sender->SetData('Title', T('新浪微博登录设置'));
		$Sender->Render('Settings', '', 'plugins/WeiboSignin');
	}

	// {{{ Draw button
	private function _GetButton() {      
		$ImgSrc = self::$BUTTON_IMG_URL;
		$ImgAlt = T(self::$BUTTON_IMG_TITLE);
		$SigninHref = $this->_AuthorizeHref();
		return "<a href=\"$SigninHref\" title=\"$ImgAlt\"><img src=\"$ImgSrc\" alt=\"$ImgAlt\" /></a>";
	}

	private function _AuthorizeHref($popup){
		$redirect_uri = Url('/entry/connect/weibosignin', TRUE);
		$client_id = C('Plugins.WeiboSignin.AppKey');

		return $this->build_url(
			'https://api.weibo.com/oauth2/authorize',
			array(
				'client_id' => $client_id,
				'response_type' => 'code',
				'redirect_uri' => $redirect_uri,
				// popup here
			)
		);
	}
	// }}}


	// {{{ http call utils ... 
	private function GetAndJsondecode($url, $arr){
		return @json_decode($this->get($url, $arr), true);
	}

	private function get($url, $arr){
		return file_get_contents(build_url($url, $arr));
	}

	private function build_url($url, $arr){
		return $url . '?' . http_build_query($arr);
	}

	private function PostAndJsondecode($url, $arr){
		return @json_decode($this->post($url, $arr), true);
	}

	private function post($url, $arr){
		$C = curl_init();
		curl_setopt($C, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($C, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($C, CURLOPT_URL, $url);
		curl_setopt($C, CURLOPT_POST, TRUE);
		curl_setopt($C, CURLOPT_POSTFIELDS, http_build_query($arr));
		$Response = curl_exec($C);
		curl_close($C);

		return $Response;
	}
	// }}}

	public function IsConfigured() {
		$Result = C('Plugins.WeiboSignin.AppKey') && C('Plugins.WeiboSignin.AppSecret');
		return $Result;
	}
}
