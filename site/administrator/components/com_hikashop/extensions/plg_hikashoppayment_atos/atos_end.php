<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

	$usefullVars=array(
		'address' =>  $this->vars['address'],	'address2' =>  $this->vars['address2'], 'lastname' =>  $this->vars['lastname'],
		'country' =>  $this->vars['country'],	'postal_code' =>  $this->vars['postal_code'],	'city' =>  $this->vars['city'],
		'state' =>  $this->vars['state'],	'phone_number' =>  $this->vars['phone_number'],	'title' =>  $this->vars['title'],
		'firstname' =>  $this->vars['firstname'], 'caddie' => $this->vars['caddie']
	);

	$xCaddie = base64_encode(serialize($usefullVars));
	$parm="merchant_id=". $this->vars["merchant_id"];
	$parm.=" merchant_country=".$this->vars["merchant_country"];
	$amount=$this->vars["amount"];
	$parm.=" amount=".$amount;
	$parm.=" currency_code=".$this->vars["currency_code"];

	$parm.=" pathfile=".$this->vars["upload_folder"]."pathfile";


	$parm.=" normal_return_url=".$this->vars["return_url"];
	$parm.=" cancel_return_url=".$this->vars["cancel_return_url"];
	$parm.=" automatic_response_url=".$this->vars["automatic_response_url"];
	$parm.=" language=".$this->vars["language"];
	$parm.=" payment_means=".$this->vars["payment_means"];
	$parm.=" header_flag=yes";
	$parm.=" capture_day=".$this->vars["delay"];
	$parm.=" capture_mode=".$this->vars["capture_mode"];
	$parm.=" block_align=center";
	$parm.=" block_order=1,2,3,4,5,6,7,8";
	$parm.=" caddie=".$xCaddie;
	$parm.=" customer_id=".$this->vars["user_id"];
	$parm.=" customer_email=".$this->vars["customer_email"];
	if(strpos($vars["customer_ip"],':') === false) $parm.=" customer_ip_address=".$this->vars["customer_ip"];
	$parm.=" order_id=".$this->vars["caddie"];
	if(!empty($this->vars["data"])) $parm.=" data=".$this->vars["data"];

	$os=substr(PHP_OS, 0, 3);
	$os=strtolower($os);
	if($os=='win')
		$path_bin = $this->vars["bin_folder"]."request.exe";
	else
		$path_bin = $this->vars["bin_folder"]."request";

	$result=exec($path_bin.' '.$parm);
	$tableau = explode ("!", $result);

$code = $tableau[1];
$error = $tableau[2];
$message = $tableau[3];

if(( $code == "" ) && ( $error == "" ) ) {
	echo "<<br/><center>erreur appel request</center><br/>" .
		"executable request non trouve ".$path_bin;
} else if($code != 0) {
	echo "<center><b><h2>Erreur appel API de paiement.</h2></center></b>" .
		"<br /><br /><br />" .
		" message erreur : ".$error." <br />";
} else {
	echo "<br />" . $error . "<br />" . $message . "<br />";
}
echo ("</body></html>");
