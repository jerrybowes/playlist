<?php
	//#==================================================================
	//# GGSD Navigation Functionality Library
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/include/RCS/ggsd-nav-inc.php,v $
	//# $Id: ggsd-nav-inc.php,v 1.2 2022/12/12 19:10:06 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================
	//-------------------------------------------------------------------
	// Spew Navbar
	//-------------------------------------------------------------------
	function spew_navbar( array $FMT ) {
		require_once("ggsd-config-inc.php");
		global $GGSDCFG;

		$NavHandler = array (
			'ACCESS'		=>	"/access.php",
			'ACCESSLOG'		=>	"/accesslog.php",
			'ADDUSER'		=>	"/adduser.php",
			'FAQ'			=>	"/faq.php",
	 		'HELP'			=>	"/help.php",
	 		'FEEDBACK'		=>	"/feedback.php",
			'HOME'			=>	"/login.php",
			'LINK'			=>	"/link.php",
			'LOGIN'			=>	"/login.php",
			'LOGOUT'		=>	"/login.php?Action=Logout",
			'MYINFO'		=>	"/people.php?Action=Verify",
			'PEOPLE'		=>	"/people.php",
		);
		
		// Tag map to link displayed in navbar
		$NavName = array(
			'ACCESS'		=>	'Access',
			'ACCESSLOG'		=>	'Access Log',
			'ADDUSER'		=>	'Add User',
			'FAQ'			=>	'FAQ',
			'HELP'			=>	'Help',
			'FEEDBACK'		=>	'Feedback',
			'HOME'			=>	'Home',
			'LINK'			=>	'Links',
			'LOGIN'			=>	'Login',
			'LOGOUT'		=>	'Logout',
			'MYINFO'		=>	'My Profile Info',
			'PEOPLE'		=>	'People 411',
		);

		// Tag map to floating note on mouseover
		$NavMsg = array(
			'ACCESS'		=>	'Access',
			'ACCESSLOG'		=>	'Access log browser',
			'ADDUSER'		=>	'Add new user contact info to provide access to the website',
			'FAQ'			=>	'GGSD frequently asked questions',
			'HELP'			=>	'Help',
			'HOME'			=>	'Home',
			'LINK'			=>	'Dance links',
			'FEEDBACK'		=>	'Contact us, ask questions, or provide feedback',
			'LOGIN'			=>	'Login',
			'LOGOUT'		=>	'Logout',
			'MYINFO'		=>	'My contact information',
			'PEOPLE'		=>	'Member directory',
		);

		//
		// Display List: Everything in any of below groups MUST appear here to display
		//
		$REQUIRED_ACCESS = array (
			'ACCESS'			=>	'8',
			'ACCESSLOG'			=>	'8',
			'ADDUSER'			=>	'1',
			'FAQ'				=>	'0',
			'FEEDBACK'			=>	'0',
			'LOGOUT'			=>	'1',
			'MYINFO'			=>	'2',
			'PEOPLE'			=>	'2',
		);

		//--------------------
		// Determine my access
		//--------------------
		$my_access = '0';
		if ( isset ( $_SESSION['access_level'])) { 
			$my_access = $_SESSION['access_level'];
		}else{
			$REQUIRED_ACCESS['LOGIN'] = '0';
		}

		//-----------------------------------------
		// Navbar, Alphabetical sort by keys
		//-----------------------------------------

		$NAV = array();
	
		ksort($REQUIRED_ACCESS);

		foreach ($REQUIRED_ACCESS as $key => $val ) {
			if ( $my_access >= $val ) {
				if ( array_key_exists( $key , $NavHandler) ) {
					$NAV[$key] = "<A class=navbar HREF=$NavHandler[$key] TITLE=\"$NavMsg[$key]\">$NavName[$key]</A>";
				}
			}
		}
			
		$showme = implode('&nbsp;&nbsp;|&nbsp;&nbsp;', $NAV);

		//-----------------------------------------
		// Print it 
		//-----------------------------------------
		echo "$showme";

	}//Endfunction spew_navbar
	//--------------------------------------------------------------------
?>
