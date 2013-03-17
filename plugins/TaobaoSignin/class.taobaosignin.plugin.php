<?php if (!defined('APPLICATION')) exit();

$PluginInfo['TaobaoSignin'] = array(
	'Name' => '淘宝账户登录',
	'Description' => '使用淘宝账户登录',
	'Version' => '0.1',
	'RequiredApplications' => array('Vanilla' => '2.0.12a'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => FALSE,
	'SettingsUrl' => '/dashboard/settings/taobao',
	'SettingsPermission' => 'Garden.Settings.Manage',
	'MobileFriendly' => FALSE,
	'HasLocale' => FALSE,
	'RegisterPermissions' => FALSE,
	'Author' => "T.G.",
	'AuthorEmail' => 'farmer1992@gmail.com',
);

class TaobaoSignInPlugin extends Gdn_Plugin {

	private static $BUTTON_IMG_URL = 'http://img03.taobaocdn.com/tps/i3/T1KKOzXX0VXXcyhH33-100-25.png';
	private static $BUTTON_IMG_TITLE = '使用淘宝账户登录';

	public function AuthenticationController_Render_Before($Sender, $Args) {
		if (isset($Sender->ChooserList)) {
			$Sender->ChooserList['taobaosignin'] = 'Taobao';
		}
		if (is_array($Sender->Data('AuthenticationConfigureList'))) {
			$List = $Sender->Data('AuthenticationConfigureList');
			$List['taobaosignin'] = '/dashboard/settings/taobao';
			$Sender->SetData('AuthenticationConfigureList', $List);
		}
	}

	public function EntryController_SignIn_Handler($Sender, $Args) {
		if (isset($Sender->Data['Methods'])) {
			if (!$this->IsConfigured())
				return;

			// Add the twitter method to the controller.
			$Method = array(
				'Name' => 'Taobao',
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
		if (GetValue(0, $Args) != 'taobaosignin'){
			return;
		}

		$parameters = GetValue('top_parameters', $_GET);
		$sign = GetValue('top_sign', $_GET);
		$secret = C('Plugins.TaobaoSignin.AppSecret');

		if(!$parameters || !$sign || base64_encode(md5($parameters . $secret, true)) != $sign){
			$Sender->Form->AddError('Auth Failed');
			return;
		}

		$parameters = base64_decode($parameters);
		$parameters = parse_str($parameters, $param);

		$Form = $Sender->Form; //new Gdn_Form();
		$ID = GetValue('user_id', $param);
		$Form->SetFormValue('UniqueID', $ID);
		$Form->SetFormValue('Provider', 'Taobao');
		$Form->SetFormValue('ProviderName', 'TaobaoSignIn');
		$Form->SetFormValue('Name', GetValue('nick', $param));
		//$Form->SetFormValue('FullName', GetValue('name', $Profile));
		$Form->SetFormValue('Email', GetValue('nick', $param).'@via.taobao');
		//$Form->SetFormValue('Photo', GetValue('profile_image_url', $Profile));
		$Sender->SetData('Verified', TRUE);
	}

	public function SettingsController_Taobao_Create($Sender, $Args) {
		$Sender->Permission('Garden.Settings.Manage');
		if ($Sender->Form->IsPostBack()) {
			$Settings = array(
				'Plugins.TaobaoSignin.AppKey' => $Sender->Form->GetFormValue('AppKey'),
				'Plugins.TaobaoSignin.AppSecret' => $Sender->Form->GetFormValue('Secret')
			);

			SaveToConfig($Settings);
			$Sender->InformMessage(T("Your settings have been saved."));
		} else {
			$Sender->Form->SetFormValue('AppKey', C('Plugins.TaobaoSignin.AppKey'));
			$Sender->Form->SetFormValue('Secret', C('Plugins.TaobaoSignin.AppSecret'));
		}

		$Sender->AddSideMenu();
		$Sender->SetData('Title', T('淘宝登录设置'));
		$Sender->Render('Settings', '', 'plugins/TaobaoSignin');
	}

	// {{{ Draw button
	private function _GetButton() {      
		$ImgSrc = self::$BUTTON_IMG_URL;
		$ImgAlt = T(self::$BUTTON_IMG_TITLE);
		$SigninHref = $this->_AuthorizeHref();
		return "<a href=\"$SigninHref\" title=\"$ImgAlt\"><img src=\"$ImgSrc\" alt=\"$ImgAlt\" /></a>";
	}

	private function _AuthorizeHref($popup){
		$redirect_uri = Url('/entry/connect/taobaosignin', TRUE);
		$client_id = C('Plugins.TaobaoSignin.AppKey');

		return $this->build_url(
			'https://oauth.taobao.com/authorize',
			array(
				'client_id' => $client_id,
				'response_type' => 'user',
				'redirect_uri' => $redirect_uri,
				// popup here
			)
		);
	}
	// }}}

	private function build_url($url, $arr){
		return $url . '?' . http_build_query($arr);
	}

	public function IsConfigured() {
		$Result = C('Plugins.TaobaoSignin.AppKey') && C('Plugins.TaobaoSignin.AppSecret');
		return $Result;
	}
}
