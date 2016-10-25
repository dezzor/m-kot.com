<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php


class mobistarHelper
{
	var $url="http://mobistar.msgsend.com/mmp/cp3";
	var $curl_status="";
	var $curl_error=0;
	var $password="";
	var $permanentKey="";
	var $error_no=0;
	var $error_str="";
	var $phonenr="";
	var $email="";

	function mobistarHelper($phonenr,$email,$password="",$permanentKey="")
	{
		$this->phonenr=$phonenr;
		$this->email=$email;
		$this->password=$password;
		$this->permanentKey=$permanentKey;
	}

	function showError()
	{
		echo "Error number: <b>".$this->error_no."</b><br>Error description:<b>".$this->error_str."</b><br>";
	}

	function Authentication()
	{
		if(strlen($this->phonenr)>0 && strlen($this->password)>0)
						return "<authentication>\n<username>".$this->phonenr."</username>\n<password>".$this->password."</password>\n</authentication>\n";
		return "";
	}
	function startRegistration()
	{
		$xml="<startRegistration msisdn=\"".$this->phonenr."\" email=\"".$this->email."\"/>";
		$data = $this->send_xml($xml);
		if($this->curl_error!=0) return false;
		$result=simplexml_load_string($data);
		if($result['code'] == 100) return true;
		else
		{
			$this->error_no=$result['code'];
			$this->error_str=$result['description'];
			return false;
		}
	}

	function verifyRegistration($number,$code)
	{
		$xml="<verifyRegistration msisdn=\"$number\" pincode=\"$code\" />";
		$data = $this->send_xml($xml);
		if($this->curl_error != 0) return false;
		$result=simplexml_load_string($data);
		if($result['code'] == 100)
		{
			$this->password=(string)$result->password;
			$this->permanentKey=(string)$result->permanentKey;
			return true;
		}
		else
		{
			$this->error_no=$result['code'];
			$this->error_str=$result['description'];
			return false;
		}
	}

	function sendSMS($recipients,$text)
	{
		$xml=$this->Authentication();
		$xml.="<sendMessage>\n";
		$xml.="<message type=\"SMS\">\n<text>".$text."</text>\n";
		$xml.="<recipients>\n";

		foreach ($recipients as $recipient) $xml.="<recipient type=\"to\" addressType=\"msisdn\">".$recipient."</recipient>\n";

		$xml.="</recipients>\n</message>\n</sendMessage>";
		$data = $this->send_xml($xml);
		if($this->curl_error != 0) return false;
		$result = simplexml_load_string($data);
		if($result['code'] == 100)
		{
			$sms_id=(string)$result->message;
			return $sms_id;
		}
		else
		{
			$this->error_no=$result['code'];
			$this->error_str=$result['description'];
			return false;
		}
	}

	function checkStatus($sms_ids)
	{
		$xml=$this->Authentication();
		$xml.="<statusReport>\n";

		foreach ($sms_ids as $msg_id) $xml.="<message messageId=\"".$msg_id."\"/>\n";

		$xml.="</statusReport>";

		$data = $this->send_xml($xml);
		if($this->curl_error != 0) return false;
		$result=simplexml_load_string($data);

		if($result['code'] == 100)
		{
			foreach ($result->statusReport as $report)
			{
				echo "Message id: <b>".$report['messageId']."</b><br>";
				foreach ($report->recipient as $rcpt)
				{
					echo "To: <b>".$rcpt['msisdn']."</b> has status: <b>".$rcpt['status'].", ".$rcpt['statusId']."</b><br>";
				}
			}
			return true;
		}
		else
		{
			$this->error_no=$result['code'];
			$this->error_str=$result['description'];
			return false;
		}
	}

	function send_xml($code)
	{
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml.= "<cp version=\"3.0\" locale=\"en-US\" timezone=\"UTC+1\" clientVersion=\"1.0\" clientProduct=\"eapi\">\n";
		$xml.= $code."\n</cp>\n";

		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = curl_exec($ch);

		if($data===false)
		{ // Process curl error codes here
			$this->curl_status = curl_getinfo($ch);
			$this->curl_error = curl_errno($ch);
		}
		curl_close($ch);


		return $data;
	}
}
