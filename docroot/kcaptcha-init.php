<?php
	//==================================================================
	// Generate kcaptcha image and place id in session
	//------------------------------------------------------------------
	// $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/kcaptcha-init.php,v $
	// $Id: kcaptcha-init.php,v 1.1 2022/12/12 19:25:36 jbowes Exp $
	//------------------------------------------------------------------
	// SET EDITOR FOR 4 space TAB stops
	// :set autoindent tabstop=4 showmatch	 (vi)
	//==================================================================

	error_reporting (E_ALL);
	//------------------------------------------------------------------------
	// Session managment
	//------------------------------------------------------------------------
    if (session_status() == PHP_SESSION_NONE) {
       	session_start();
 	}

	include('kcaptcha.php');
	$captcha = new KCAPTCHA();

	$_SESSION['captcha_keystring'] = $captcha->getKeyString();
?>
