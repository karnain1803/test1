<?php
require_once('reCAPTCHA.php');
class captcha extends reCAPTCHA
{

	public function __construct()
	{
		global $kaptcha_type, $recaptcha2_site_key, $recaptcha2_secret_key;
		if ($kaptcha_type == 'ReCaptcha v2')
		{
			$this->setSiteKey($recaptcha2_site_key);
			$this->setSecretKey($recaptcha2_secret_key);
		}
	}

	public function GetKaptchaScript()
	{
		global $kaptcha_type;
		switch ($kaptcha_type)
		{
			case 'ReCaptcha v2':				
				return $this->GetScript();
			break;

			case 'Internal':
			default:
				return '';
		}		
	}

	public function ShowCaptcha()
	{
		global $kaptcha_type;
		switch ($kaptcha_type)
		{
			case 'ReCaptcha v2':
				return $this->getHtml();
			break;

			case 'Internal':
			default:
				return '<input type="text" value="" name="kapcha" id="kapcha" class="inputc" maxlength="4"> <img src="captcha/?'.session_name().'='.session_id().'&tt='.doubleval(microtime()).'" id="image" onClick="iload()" border="0"><p class="help-block">Введите код, который Вы видите на картинке</p>';
		}
	}

	public function Validate()
	{
		global $kaptcha_type;
		switch ($kaptcha_type)
		{
			case 'ReCaptcha v2':
				$postkapcha = (isset($_POST['g-recaptcha-response']))?$_POST['g-recaptcha-response']:'';
				return ($postkapcha && $this->isValid($postkapcha));
			break;

			case 'Internal':
			default:
				$postkapcha = (isset($_POST['kapcha']))?$_POST['kapcha']:'';
				$res = ($postkapcha && isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] == $postkapcha);
				unset($_SESSION['captcha_keystring']);
				return $res;
		}
	}

}