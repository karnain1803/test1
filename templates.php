<?php
class templates
{
	public function __construct()
	{
	}

	public function GetTemplate($template_name, $replace_list = array())
	{
		global $$template_name;
		if (!isset($$template_name)) return false;
		$data = $$template_name;
		foreach ($replace_list as $i => $val)
		{
			$data = str_replace($i, $val, $data);
		}
		return $data;
	}

	public function ActTemplate($act_link)
	{
		$replace_list = array(
			'{ACT_LINK}' => $act_link
		);
		$data = $this->GetTemplate('act_template', $replace_list);
		return $data;
	}

	public function RegisterMail($login, $passw, $question, $answer, $name, $act_link, $ref_link)
	{
		$act_template = $this->ActTemplate($act_link);
		$replace_list = array(
			'{LOGIN}' => $login,
			'{PASSW}' => $passw,
			'{QUESTION}' => $question,
			'{ANSWER}' => $answer,
			'{NAME}' => $name,
			'{ACT_TEMPLATE}' => $act_template,
			'{REF_LINK}' => $ref_link
		);
		$data = $this->GetTemplate('reg_template', $replace_list);
		return $data;
	}

	public function AccountsDataTemplate($accounts)
	{
		$data = '';
		foreach ($accounts as $i => $val)
		{
			$replace_list = array(
				'{LOGIN}' => $val['login'],
				'{QUESTION}' => $val['question'],
				'{ANSWER}' => $val['answer'],
				'{RESET_PASSW_LINK}' => $val['reset_passw_link'],
				'{RESET_IP_LINK}' => $val['reset_ip_link']
			);
			$data .= $this->GetTemplate('acc_data_template', $replace_list);
		}
		return $data;
	}

	public function RememberMail($ip, $servname, $accounts)
	{
		$accounts_data = $this->AccountsDataTemplate($accounts);
		$replace_list = array(
			'{IP}' => $ip,
			'{SERVNAME}' => $servname,
			'{ACCOUNTS_DATA}' => $accounts_data
		);
		$data = $this->GetTemplate('rem_template', $replace_list);
		return $data;
	}

	public function ResetPasswMail($ip, $login, $passw, $question, $answer, $servname)
	{
		$replace_list = array(
			'{IP}' => $ip,
			'{LOGIN}' => $login,
			'{PASSW}' => $passw,
			'{QUESTION}' => $question,
			'{ANSWER}' => $answer,
			'{SERVNAME}' => $servname
		);
		$data = $this->GetTemplate('reset_template', $replace_list);
		return $data;
	}

	public function ChangePasswMail($ip, $login, $passw, $servname)
	{
		$replace_list = array(
			'{IP}' => $ip,
			'{LOGIN}' => $login,
			'{PASSW}' => $passw,
			'{SERVNAME}' => $servname
		);
		$data = $this->GetTemplate('change_passw_template', $replace_list);
		return $data;
	}

	public function TestMail()
	{
		$replace_list = array();
		$data = $this->GetTemplate('test_mail_template', $replace_list);
		return $data;
	}

	public function ResetIPMail($ip, $login, $servname)
	{
		$replace_list = array(
			'{IP}' => $ip,
			'{LOGIN}' => $login,
			'{SERVNAME}' => $servname
		);
		$data = $this->GetTemplate('rem_ip_process_template', $replace_list);
		return $data;
	}
}