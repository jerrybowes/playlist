<?php
	//#==================================================================
	//#	GGSD Configuration File
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/include/RCS/ggsd-config-inc.php,v $
	//# $Id: ggsd-config-inc.php,v 1.2 2022/12/12 19:09:25 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================
	$GGSDCFG = array (
		'ADMINEMAIL'					=>	'jerbowes@yahoo.com',
		'AUTHOR'						=> 	'Jerry Bowes',
		'AUTHOREMAIL'					=>	'jerbowes@yahoo.com',
		'BASEURL'						=>	'https://ggsd.info',
		'COMPANYNAME'					=>	'GGSD Music Management',
		//---------------------------------------------------------------------
		// Cookies, Auth
		//---------------------------------------------------------------------
		'COOKIEEXP'						=>	'+8h',		
		'COOKIEEXPSECS'					=>	'9600',	
		'CSS'							=>	'ggsdinfo',					

		//---------------------------------------------------------------------
		// Database
		//---------------------------------------------------------------------
		'DBUSER'						=>	'*redacted*',
		'DBNAME'						=>	'*redacted*',
		'DBPASSWD'						=>	'*redacted*',
		'DBHOST'						=>	'localhost',
		'DBPORT'						=>	'3306',

		//---------------------------------------------------------------------
		// Misc
		//---------------------------------------------------------------------
		'DOMAIN'						=>	'ggsd.info',
		'LOCALTIMEZONE'					=>	'PST-8',
		'MAILER'						=>  '/usr/sbin/sendmail -t',
		'MAILFROM'						=>  'GGSD Music Manager',
		'MAILFROMEMAIL'					=>  'info@ggsd.info',
		'SITENAME'						=>	'GGSD Music Management',
		'TIMEZONE'						=>	'America/Los_Angeles',
		'WEBMASTER'						=>	'jerbowes@yahoo.com',
		);


	//------------------------------------------------------------------
	// All system recognized preferences and their default (US) values
	//------------------------------------------------------------------
	$VALIDPREFERENCE = array (
		'encoding'						=>	'utf8',
		'language'						=>	'US_English',
		'datefmt'						=>	'YYYY-MM-DD'
		);

?>
