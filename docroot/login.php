<?php
	//#==================================================================
	//# GGSD Login
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/login.php,v $
	//# $Id: login.php,v 1.2 2022/12/12 19:16:16 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	//require_once("./include/ggsd-activitylog-inc.php");
	require_once("./include/ggsd-auth-inc.php");
	require_once("./include/ggsd-config-inc.php");
	require_once("./include/ggsd-looknfeel-inc.php");
	require_once("./include/ggsd-msutils-inc.php");
	require_once("./include/ggsd-session-inc.php");
	require_once("./kcaptcha.php");
	require_once("./include/kcaptcha_config-inc.php");

	global $VALIDPREFERENCE;
	global $GGSDCFG;

	//
	// Formatting and navbar options for looknfeel-inc header and footer functions
	//
	$MyFunction = 'Login';
	$FMT = array (
		'BANNER'		=>	"GGSD Info Login",
		'BANNER2'		=>	"Login and password management",
		'TITLE'			=>	"GGSD Info Login",
		'MODULENAME'	=>	'login.php',
		'NAV1'			=>	'INFO'
	);

	$FMT['BANNER'] = $GGSDCFG['SITENAME'] . ' ' . $MyFunction;
	$FMT['TITLE'] = $GGSDCFG['SITENAME'] . ' ' . $MyFunction;

	//
	// Config parameters for this module
	//
	$LOGINCFG = array (
		'EDITLEVEL'				=>	'5',	// You have to be this important to modify info
		'ADMINLEVEL'			=>	'5',	// Spoof authorization
		'CRITERIACOUNT'			=>	'2',	// Minimum number of identifiers are required for Help/reset my password form
		'TMPPWEXPIREDAYS'			=>	'3',	// Duration in days of temporary password validity (today + N days)
		'UPDATEEXP'				=>	'2099-09-09',	// When we have to create a new access password
		'DEFAULTEXPIRATION'		=>	'2032-12-12'	// Unexpiring password (in this lifetime) expiration date
	);
	//
    // Import following from VALIDPREFS for defaults
	//
    $SESSIONPREFS = array (
        'TIMEZONE'
    );

	//
	// Config parameters for email acknowledgement
	//
	$LOGINEMAIL = array (
		'subject'		=>	"GGSD Info Login Assistance", 
		'fromemail'		=>	"info@ggsd.info",
		'toemail'		=>	"jerbowes@gmail.com",	// Safety, overwritten in send_email_ack
		'fromname'		=>	"GGSD Website" 
	);

	//-----------------------------------------------------------------------------
	// Begin
	//-----------------------------------------------------------------------------
	$people_id=0;

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

	//
	// No nav until logged in
	//
	if ( isset($_SESSION) ){

		if ( isset($_SESSION['people_id']) ) {
			$people_id = $_SESSION['people_id'];
			$FMT['BANNER2'] = 'Welcome';
		}

        //
        // Setup Defaults
        //
        foreach ($VALIDPREFERENCE as $key => $val) {
            if (in_array($key, $SESSIONPREFS)) {
                $_SESSION[$key] = $val;
            }
        }
	}


	//
	// For applications sending unauthenticated users here, extract
	// the return url for redirection return after login is complete
	//

	if (isset($_SERVER['QUERY_STRING'] )) {
		if ( array_key_exists( 'RetUrl' , $_REQUEST )) {
			$rawurl = $_REQUEST['RetUrl'];
			$ReturnUrl = preg_replace('/\|/', '&', $rawurl);
		}
	}

	if ( array_key_exists( 'Action' , $_REQUEST )) {


		//-----------------------------------------------------------------------------
		// Log Out
		//-----------------------------------------------------------------------------
		//
	  	if ( $_REQUEST['Action'] == "Logout" ) {
			ggsd_session_end();
			$FMT['NONAV'] = "True";

			spew_header($FMT);


			echo "<CENTER>\n";
			echo "<H3>You have been successfully logged out.</H3>\n";
			echo "</CENTER>\n";
			spew_login_form();
			spew_footer($FMT);
			exit;
		}

		//-----------------------------------------------------------------------------
		// With known old plaintext password or encrypted passwd from email link
		// Provide a form to collect new password to manually change it
		//-----------------------------------------------------------------------------
		//
	  	if ( $_REQUEST['Action'] == "Change My Password" 
            || $_REQUEST['Action'] == "Change" ) {

			$dbh = ggsd_pdo_connect();

			$FMT[BANNER3] = "Change My Password";

			spew_header($FMT);


            if (! empty ($_SESSION['people_id']) ) {
                $people_id = $_SESSION['people_id'];
                $sql = "SELECT a.access_login, a.people_id from access a where a.people_id = ";
				$sql .= $dbh->quote($people_id);

				$result = $dbh->query($sql);

                $Me = array();
				$Me = $result->fetch(PDO::FETCH_ASSOC);

            }

			if ( $_REQUEST['Action'] == "Change" ) {

                $people_id = $_REQUEST['people_id'];

                $sql = "SELECT * from access where people_id = ";
				$sql .= $dbh->quote($people_id);

				$changeresult = $dbh->query($sql);

                $ChangeMe = array();
				$ChangeMe = $changeresult->fetch(PDO::FETCH_ASSOC);

			}

			echo "<CENTER>\n";

			if ( empty( $_REQUEST['encpw'])) {
				// Selection via GUI
				echo "<P>If you have forgotten your password, click on ";
				echo "the <B>Forgot My Password</B> button at the bottom of the page.</P>\n";
			}else{
				// Coming from a reset request email provided link
				$today = date('Y-m-d');
				if (  $ChangeMe['expiration_date'] <= $today  ){
					echo "<P>Your password password reset link has expired.\n";
					echo "<BR>Enter email at <A HREF=$_SERVER[PHP_SELF]?Action=Forgot+My+Password>Forgot My Password</A> to receive new reset link.";
					spew_footer($FMT);
					exit;
				}else{
					echo "<P>Your password has been previously reset. Enter a new password.</P>\n";
				}
			}


			$oldpass = $_REQUEST['oldpass'];
			$login = $_REQUEST['login'];

			echo "<FORM ACCEPT-CHARSET=\"UTF-8\" ACTION=$_SERVER[PHP_SELF]  METHOD=POST>\n";
			echo "<TABLE id=change_password_form CELLPADDING=4 BORDER>\n";

			//
			// If email link response, skip asking for username/login
			//
			if ( empty ($_REQUEST['login'] ) ) {
				echo "<TR>\n";
				echo "<TD class=tdl><B>Login:</B></TD>\n";
				echo "<TD><input type=text name=login value=\"$Me[access_login]\"></TD>\n";
			}

			//
			// If email link reset, don't need password
			//
			if ( empty ($_REQUEST['encpw'] ) ) {
				echo "<TR>\n";
				echo "<TD class=tdl><B>Old Password:</B></TD>\n";
				echo "<TD><input type=password name=oldpass></TD>\n";
			}

			echo "<TR>\n";
			echo "<TD class=tdl><B>New Password:</B></TD>\n";
			echo "<TD><input type=password name=pass></TD>\n";

			echo "<TR>\n";
			echo "<TD class=tdl><B>Confirm New Password:</B></TD>\n";
			echo "<TD><input type=password name=pass2></TD>\n";

			echo "</TABLE>\n";

			if ( ! empty( $_SESSION['people_id'])) {
				$people_id = $_SESSION['people_id'];
				echo "<input type=HIDDEN name=people_id value=\"$people_id\">\n";
			}

			if (!  empty ($_REQUEST['login'] ) ) {
				echo "<input type=HIDDEN name=login value=\"$login\">\n";
            }

			if ( ! empty( $_REQUEST['people_id'])) {
				$people_id = $_REQUEST['people_id'];
				echo "<input type=HIDDEN name=people_id value=\"$people_id\">\n";
			}

			if ( ! empty( $_REQUEST['encpw'])) {
				$encpw = $_REQUEST['encpw'];
				echo "<input type=HIDDEN name=encpw value=\"$encpw\">\n";
			}

			//
			// Captcha
			//
			echo "<p>Enter the numbers shown in the <BR>following 'captcha' image into the box below.</p>\n";
			echo "<p><img src='kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
			echo "<p><input type=text name=keystring></p>\n";

			echo "<input type=HIDDEN name=RetUrl value=\"$ReturnUrl\">\n";

			echo "<P>\n";
			echo "<input type=SUBMIT name=Action value=\"Update My Password\">\n";
			echo "</P>\n";

			if ( empty( $_REQUEST['encpw'])) {
				echo "<input type=SUBMIT name=Action value=\"Forgot My Password\">\n";
			}

			echo "</FORM>\n";
			echo "</CENTER>\n";
			spew_footer($FMT);
			exit;
		}

		//-----------------------------------------------------------------------------
		// Update My Password 
		// All of this goes on prior to spew_header so that if this is an authentication
		// referral, we can just redirect to the requester after authentication
		//-----------------------------------------------------------------------------
		//
	  	if ( $_REQUEST['Action'] == "Update My Password" 
            || $_REQUEST['Action'] == "Update" ) {

			$FMT['BANNER3'] = "Update My Password";

			$dbh = ggsd_pdo_connect();


			//
			// Captcha validation
			//
			if (! isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != $_REQUEST['keystring']){
				spew_header($FMT);
				echo "<CENTER>\n";
				echo "<H3>\n";
				echo "The numbers you entered under the captcha image do not match those in the image.\n";
				echo "<BR>Please click on the back button of your browser and try again.\n";
				echo "</H3>\n";
				echo "</CENTER>\n";
				spew_footer($FMT);
				exit;
			}

			$login = $_REQUEST['login'];
			$people_id = $_REQUEST['people_id'];
			$encpw = $_REQUEST['encpw'];
			$pass = $_REQUEST['pass'];
			$pass2 = $_REQUEST['pass2'];
			$oldpass = $_REQUEST['oldpass'];


			//
			// Error Checking
			//

			if (empty( $login )) {
				if ( empty( $people_id ) ) {
					$err .= "<LI>No login or people_id provided.</LI>\n";
				}
			}

			if (empty( $pass) ) {
				$err .= "<LI>No password provided.</LI>\n";
			}else{
				if ( $pass !== $pass2 ) {
					$err .= "<LI>Passwords do not match.</LI>\n";
				}
			}

			//
			// Error Notification
			//
			if ( isset ($err)  ) {
				spew_header($FMT);	
				bail($err);
				spew_footer($FMT);
				exit;
			}

			//
			// Original encrypted password
			//
			if ( empty($encpw) ) {
				if (empty($oldpass)) {
					$err .= "<LI>No original password provided</LI>\n";
				}else{
					$encoldpass = md5($oldpass);
				}
			}else{
				$encoldpass = $encpw;
			}

			//
			// Encrypt new password
			//
			$encdbpass = md5($pass);
	
			//------------------------------------------------------------------
			// Q1: First Query 
			// See if there exists a login entry in access
			// With this encrypted password
			//------------------------------------------------------------------
			//
			$where = array();

			$where[] = "access_password = " . $dbh->quote($encoldpass);

			//
			// See if there is a login form field entry
			//
			if (isset($_REQUEST['login'])) {
				if (! empty( $_REQUEST['login'])) {
					$where[] =  'access_login = ' .  $dbh->quote($login);
				}
			}

			//
			// See if there is a people_id form field entry 
			//
			if ( isset($_REQUEST['people_id']) ) {
				if (! empty( $_REQUEST['people_id'] ) ) {
					$where[] = "people_id = " . $dbh->quote($_REQUEST['people_id']);
				}
			}

			$sql = "SELECT * FROM  access  WHERE ";
			$sql .= implode(' AND ', $where);


			$result = $dbh->query($sql);
			$num = $result->rowCount();


			//
			//-----------------------------------------------------------------------------
			// Q1 Error checking: number of matching records found in access
			//-----------------------------------------------------------------------------
			//
			$Access = array();
			if ($num == 1) {
				$Access = $result->fetch(PDO::FETCH_ASSOC);


				$people_id = $Access['people_id'];
				$login = $Access['access_login'];

				if ( empty($people_id) ) {
					die("Error: Unexpected problem. Password failure, no people_id specified in Q1 Access check");
				}

				//
				// Update access
				//
				$exp = $LOGINCFG['DEFAULTEXPIRATION'];

				$updatesql = "UPDATE access SET access_password = ";
				$updatesql .= $dbh->quote(md5($pass)) . ',';
				$updatesql .= " expiration_date = ";
				$updatesql .= $dbh->quote($exp) ;
				$updatesql .= " WHERE people_id = ";
				$updatesql .= $dbh->quote($people_id) ;

				$updateresult = $dbh->query($updatesql);

			}else{
				//-------------------------------------------------------------
				// No matching records found in access_password
				//-------------------------------------------------------------
				$err .= "<LI>Login and/or old password incorrect</LI>";
			}

			if ( isset ($err) ) {
				spew_header($FMT);
				bail($err);
			}
	
			ggsd_session_start($Access['people_id']);

			spew_header($FMT);
			echo "<CENTER>\n";
			echo "<H2>Password updated.</H2>\n";
			echo "</CENTER>\n";
				now_what($people_id);
			spew_footer($FMT);
	  	}//Endif Action = 'Update My Password'


		//-----------------------------------------------------------------------------
		// Collect all debug info in $trace string as we are pre-header for
		// session processing. 
		//-----------------------------------------------------------------------------
	  	if ( $_REQUEST['Action'] == "Login" 
			|| $_REQUEST['Action'] == "Authenticate" ) {


			// Setup Accesslog Defaults
			$Alog = array();
			// accesslog_id 
			// people_id 
			// access_timestamp     
			// access_action 
			// access_login 
			// context 
			// access_result 
			// access_status 

			$Alog['url'] = $_SERVER['REQUEST_URI'];
			$Alog['access_action'] = $_REQUEST['Action'];
			$Alog['access_login'] = $_REQUEST['login'];
            $Alog['context'] = $_REQUEST['Returl'];

			//
			// Captcha validation
			//
			if( count($_REQUEST)>1 ){
				if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $_REQUEST['keystring']){
				}else{
					$FMT['BANNER3'] = "Captcha verification failure";
					spew_header($FMT);
					echo "<CENTER>\n";
					echo "<H3>Alas, the numbers you entered do not match those of the captcha image.\n";
					echo "<BR>I am afraid I am unable to confirm you are human. \n";
					echo "<BR>Please click on the back button of\n";
					echo "your browser and try again.</H3>\n";
					echo "</CENTER>\n";
					spew_footer($FMT);

					if (isset($_SESSION['captcha_keystring'] )){
						$Alog['access_status'] = 'Captcha match fail';
						$Alog['access_result'] = 'Fail';
					}else{
						$Alog['access_result'] = "Incomplete";
						$Alog['access_status'] = 'Captcha string missing';
					}

					AccessLog($Alog);
					exit;
				}
			}

			global $ReturnUrl;
			global $GGSDCFG;
			$people = array();		// Lookup info for retreived people info
			$where = array();		// Assembly of all sql where clauses

			$dbh = ggsd_pdo_connect();

			$trace = "<P>";

			$login = $_REQUEST['login'];
			$pass = $_REQUEST['pass'];

			//-----------------------------------------------------------------------------
			// Error Checking
			//-----------------------------------------------------------------------------

			if (empty($login) ) {
				$err .= "<LI>No login provided.</LI>\n";
				$Alog['access_status'] = "No login provided";
				$Alog['access_result'] = "Incomplete";
			}

			if (empty($pass) ) {
				$err .= "<LI>No password provided.</LI>\n";
				$Alog['access_status'] = "No password provided";
				$Alog['access_result'] = "Incomplete";
			}

			//
			// Error Notification and logging
			//
			if ( isset($err)  ) {
				spew_header($FMT);
				AccessLog($Alog);
				echo "$trace\n";
				bail($err);
				spew_footer($FMT);
				exit;
			}

			//
			// Encrypt password
			//
			$encdbpass = md5($pass);

			//
			// See if there is a login entry in access
			//

			$initialsql = "SELECT * FROM access a WHERE ";

			$wlogin = $dbh->quote($login);
            $safepass = $dbh->quote($encdbpass);

			$where[] = "a.access_login = " . $wlogin;
			$where[] = "a.access_password = " . $safepass;

			$initialsql .= implode(' AND ', $where);


			$initialresult = $dbh->query($initialsql);
			$num = $initialresult->rowCount();

	
			//
			//-----------------------------------------------------------------------------
			// Error checking on number of records found for query to access
			//-----------------------------------------------------------------------------
			//
			if ( $num < 1) {
				//-------------------------------------------------------------------------
				// If no valid matching entries found in access table, EITHER:
				// - There is no access_password entry that matches that people entry
				// - There is no people entry for that username
				// - Login is bogus
				// - Password is bogus
				//-------------------------------------------------------------------------
				// Verify there is no record in access with that Login
				//-------------------------------------------------------------------------
				//

				$sqlverify = "SELECT * FROM access WHERE access_login = $wlogin";

				$resultverify = $dbh->query($sqlverify);
				$numverify = $resultverify->rowCount();


				if ($numverify < 1 ) {
					// Not in access_password
					$err .= "<LI>No record of an account for  [$login].</LI>";
					$Alog['access_status'] = "No login record";
					$Alog['access_result'] = "Fail";
				}else{
					// Already in access_password, so authentication failure 
					$err .= "<LI>No valid login records matching login of [$login] and that password were found</LI>";
					$Alog['access_status'] = "Authentication failure";
					$Alog['access_result'] = "Fail";
				}

			}elseif ( $num == 1) {

				//
				// Check for Expired Password (Gregorian prevents end of unix epoch failure in 2038)
				//

				$people = array();
				$people = $initialresult->fetch(PDO::FETCH_ASSOC);

				$today = date('Y-m-d');	// YYYY-MM-DD

				list($yr,$mo,$day) = explode('-', $today);

				$now  = gregoriantojd($mo,$day,$yr);
				list($yr,$mo,$day) = explode('-', $people['expiration_date']);
				$expiration = gregoriantojd($mo,$day,$yr);

				if ( $now > $expiration ) {
					$err .= "<LI>Your password expired on $people[expiration_date]. You will need to reset it using the <A HREF=/login.php?Action=Forgot+My+Password>Forgot My Password</A> form.  </LI>";
					$Alog['access_status'] = "Password expired for $login";
					$Alog['access_result'] = "Fail";
				}else{
					$people_id = $people['people_id'];
					$access_login = $people['access_login'];
					$Alog['access_status'] = "One access password entry, not expired";
					$Alog['access_result'] = "OK";
				}

			}else{ // Implicit num > 1 e.g. 
				$err .= "<LI>Multiple records matching login of [$login] and that password were found. Contact administrator.</LI>";
				$Alog['access_status'] = "Multiple access records for $login";
				$Alog['access_result'] = "Critical";
			}

			if ( isset ( $err) ) {
				spew_header($FMT);

				AccessLog($Alog);	

				echo "$trace\n";
				bail($err);
			}else{
				$Alog['access_status'] = "Success";
				$Alog['access_result'] = "OK";
				//
				// Retrieve People Info to setup session
				//
				$Alog['people_id'] = $people_id = $people['people_id'];
				$Alog['access_login'] = $access_login = $people['access_login'];


				AccessLog($Alog);

				//
				// ggsd_session_start loads all the data from database into session
				//
				$mysessiondata = array();
				$mysessiondata = ggsd_session_start($people_id);
				

				//
				// Standalone login or enroute to authenticate some other application
				//
				if ( empty($ReturnUrl) ) {
                    unset($FMT['NONAV']);           // ASSUME authentication successful
					$FMT['BANNER2'] = 'Welcome';

					spew_header($FMT);






					now_what($people_id);

					// Show my Information

					spew_footer($FMT);
					exit;
				}else{
					if ( preg_match('/http/', $ReturnUrl) ) {
						header("Location: ${ReturnUrl}");
					}else{
						header("Location: $GGSDCFG[BASEURL]${ReturnUrl}");
					}
                    spew_footer($FMT);
				}
				exit;
			}
		}// Endif ( $_REQUEST['Action'] == "Login" ) 

		//-----------------------------------------------------------------------------
		// We have already logged in, now we don the identity and set cookies
		// to spoof someone else
		//-----------------------------------------------------------------------------
	  	if ( $_REQUEST['Action'] == "Login As" ) {

			$Alog = array();
			$Alog['people_id'] = $_SESSION['people_id'];
			$Alog['access_action'] = $_REQUEST['Action'];

			// Gauntlet
			if (! isset($_SESSION['access_level']) || ( $_SESSION['access_level'] < $LOGINCFG['ADMINLEVEL'] ) ){
				$Alog['access_result'] = 'Proxy user fail';

				if (! isset($_SESSION['access_level']) ){
					$Alog['access_status'] = 'No session access_level';
				}else{
					$Alog['access_status'] = 'Insufficent privilege to proxy user';
				}

				echo "<H2>";
				echo "Either you are not logged in or do not";
				echo "<BR>have sufficient privilege to login on behalf of someone else.";
				echo "</H2>\n";

				AccessLog($Alog);

				spew_footer($FMT);
				exit;
			}

			//
			// Captcha validation
			//
			if( count($_REQUEST)>1 ){
				if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $_REQUEST['keystring']){
					$Alog['context'] .= '<BR>' . 'Login: Captcha validation successsful';

				}else{
					$Alog['context'] .= '<BR>' . 'Login: Captcha validation failure';

					if( isset($_SESSION['captcha_keystring']) ) {
						$Alog['access_result'] = 'Fail';
						$Alog['access_status'] = 'Captcha match fail';
					}else{
						$Alog['access_result'] = 'Incomplete';
						$Alog['access_status'] = 'Captcha missing';
					}

					$FMT['BANNER3'] = "Captcha verification failure";
					spew_header($FMT);
					echo "<CENTER>\n";
					echo "<H3>Alas, the numbers you entered do not match those of the captcha image.\n";
					echo "<BR>I am afraid I am unable to confirm you are human. \n";
					echo "<BR>Please click on the back button of\n";
					echo "your browser and try again.</H3>\n";
					echo "</CENTER>\n";

					AccessLog($Alog);

					spew_footer($FMT);
					exit;
				}
			}

			global $ReturnUrl;
			global $GGSDCFG;
			$people = array();		// Lookup info for retreived people info
			$where = array();		// Assembly of all sql where clauses

			$dbh = ggsd_pdo_connect();

			$trace = "<P>";


			$login = $_REQUEST['login'];
			$loginas = $_REQUEST['loginas'];
			$pass = $_REQUEST['pass'];

			//-----------------------------------------------------------------------------
			// Error Checking
			//-----------------------------------------------------------------------------
			$Alog['context'] .= '<BR>' . 'Login: Error checking';

			if (empty($login) ) {
				$err .= "<LI>No login provided.</LI>\n";
				$Alog['access_result'] = 'Incomplete';
				$Alog['access_status'] = 'No login provided';
			}

			if (empty($loginas) ) {
				$err .= "<LI>No login as provided.</LI>\n";
				$Alog['access_result'] = 'Incomplete';
				$Alog['access_status'] = 'No login as provided';
			}

			if (empty($pass) ) {
				$err .= "<LI>No password provided.</LI>\n";
				$Alog['access_result'] = 'Incomplete';
				$Alog['access_status'] = 'No password provided';
			}

			// Error Notification
			$Alog['context'] .= '<BR>' . 'Login: Error notification';
			if ( isset($err)  ) {
				spew_header($FMT);

				AccessLog($Alog);

				echo "$trace\n";
				bail($err);
				spew_footer($FMT);
				exit;
			}

			//
			// Encrypt password
			//
			$encdbpass = md5($pass);

			//
			// See if there is a login entry in access
			//

			$initialsql = "SELECT * FROM access a WHERE ";

			$wlogin = $dbh->quote($login);
			$wloginas = $dbh->quote($loginas);
            $safepass = $dbh->quote($encdbpass);

			//$where[] = "a.access_login = " . $wlogin;
			$where[] = "a.access_login = " . $wloginas;
			//$where[] = "a.access_password = " . $safepass;

			$initialsql .= implode(' AND ', $where);


			$initialresult = $dbh->query($initialsql);
			$num = $initialresult->rowCount();

	
			//
			//-----------------------------------------------------------------------------
			// Error checking on number of records found for query to access
			//-----------------------------------------------------------------------------
			//
			if ( $num < 1) {
				//-------------------------------------------------------------------------
				// If no valid matching entries found in access table, EITHER:
				// - There is no access_password entry that matches that people entry
				// - There is no people entry for that username
				// - Login is bogus
				// - Password is bogus
				//-------------------------------------------------------------------------
				// Verify there is no record in access with that Login
				//-------------------------------------------------------------------------
				//

				$sqlverify = "SELECT * FROM access WHERE access_login = $wloginas";

				$resultverify = $dbh->query($sqlverify);
				$numverify = $resultverify->rowCount();


				//-------------------------------------------------------------------------
				//-------------------------------------------------------------------------
				//
				if ($numverify < 1 ) {
					// Not in access_password
					$err .= "<LI>No account record for $login</LI>";
				}else{
					// Already in access_password, so failure due to user mis-authentication
					$err .= "<LI>No valid login records matching login of [$login] and that password were found</LI>";
				}
			}elseif ( $num == 1) {
				//
				// Check for Expired Password (Gregorian prevents end of unix epoch failure in 2038)
				//

				$people = array();
				$people = $initialresult->fetch(PDO::FETCH_ASSOC);

				$people_id = $people['people_id'];
				$access_login = $people['access_login'];

			}else{ // Implicit num > 1 e.g. 
				$err .= "<LI>Multiple records matching login of [$loginas]. Contact administrator.</LI>";
			}

			if ( isset ( $err) ) {
				spew_header($FMT);
				echo "$trace\n";
				bail($err);
			}else{
				//
				// Retrieve People Info to setup session
				//
				$people_id = $people['people_id'];
				$access_login = $people['access_login'];

				//
				// ggsd_session_start loads all the data from database into session
				//
				$mysessiondata = array();
				$mysessiondata = ggsd_session_start($people_id);

				//
				// Standalone login or enroute to authenticate some other application
				//
				if ( empty($ReturnUrl) ) {
                    unset($FMT['NONAV']);           // ASSUME authentication successful

					spew_header($FMT);






					now_what($people_id);

					// Show my Information

					spew_footer($FMT);
					exit;
				}else{
					if ( preg_match('/http/', $ReturnUrl) ) {
						header("Location: ${ReturnUrl}");
					}else{
						header("Location: $GGSDCFG[BASEURL]${ReturnUrl}");
					}
                    spew_footer($FMT);
				}
				exit;
			}
		}// Endif ( $_REQUEST['Action'] == "Login As" ) 

		//-----------------------------------------------------------------------------
		// Forgot My Password Help Explanation and Form
		//-----------------------------------------------------------------------------
		//
	  	if ( $_REQUEST['Action'] == "Forgot My Password" ) {
			$FMT['BANNER3'] = "Forgot My Password";
			spew_header($FMT);


			echo "<CENTER>\n";
			echo "<FORM ACCEPT-CHARSET=\"UTF-8\" ACTION=\"$_SERVER[PHP_SELF]\"  METHOD=POST>\n";

			echo "<P>\n";
            echo "<BR>If you have forgotten your login or password, enter your email";
			echo "<BR>into the box below and click on the <B>Reset My Password</B> button.\n";
            echo "<BR>Your login and a web link will be emailed to you.\n";
			echo "<BR>Click on that web link to reset your password.\n";
            echo "</P>\n";

			echo "<TABLE id=login_iforgot_form BORDER CELLPADDING=5>\n";
			echo "<TH>Enter Your Full Email</TH>\n";
			echo "<TR><TD><input type=text name=email_1></TD>\n";
			echo "</TABLE>\n";

			//
			// Captcha Image validation
			//
			//echo "<p class=trace>Enter the numbers shown in the <BR>following 'captcha' image into the box below.</p>\n";
			//echo "<p><img src='kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
			//echo "<p><input type=text name=keystring></p>\n";

			echo "<P>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Reset My Password\">\n";
			echo "<input type=HIDDEN name=RetUrl value=\"$ReturnUrl\">\n";
			echo "</P>\n";
			echo "</FORM>\n";

			echo "<CENTER>\n";
			spew_footer($FMT);
			exit;
	  	}

		//-----------------------------------------------------------------------------
		// Take identifying input from Help form, identify the exactly one person that
		// wants their password reset, change the password in access_password table with
		// and expiration date defined by LOGINCFG[TMPPWEXPIREDAYS] and send an email
		// with the new PW and a link to reset their password which goes to the
		// section of Action='Change'
		//-----------------------------------------------------------------------------

	  	if ( $_REQUEST['Action'] == "Reset My Password" 
			|| $_REQUEST['Action'] == "Reset" ) {

			$FMT['BANNER3'] = "Resetting my password";

			spew_header($FMT);


			//
			// Captcha validation
			//
			//if(! isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != $_REQUEST['keystring']){
				//echo "<CENTER>\n";
				//echo "<H3>\n";
				//echo "The numbers you entered under the captcha image do not match those in the image.\n";
				//echo "<BR>Please click on the back button of your browser and try again.\n";
				//echo "</H3>\n";
				//echo "</CENTER>\n";
				//spew_footer($FMT);
				//exit;
			//}


			// Email Existance
			if (! isset($_REQUEST['email_1']) || empty($_REQUEST['email_1']) ){
				$err = "<LI>No email address provied in Forgot My Password form.</LI>";
				bail($err);
			}

			// Email Format
			if (! preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $_REQUEST['email_1'])){
				$err = "<LI>Invalid email format.</LI>";
				bail($err);
			}


			$dbh = ggsd_pdo_connect();

			$criteria = array ( 
				'access_login'	=>	'a.access_login',
				'last_name'		=>	'p.last_name',
				'people_id'		=>	'p.people_id',
				'first_name'	=>	'p.first_name',
				'email_1'		=>	'p.email_1',
				'encpw'			=>	'a.access_password',
				'email_2'		=>	'p.email_2'
			);


			$where = array(
				'a.people_id = p.people_id'
			);

			$err = '';

			//------------------------------------------------------
			// Query people info assuming he is in access_password
			//------------------------------------------------------
			$sql = "SELECT p.people_id,  p.first_name, p.last_name, p.email_1, p.email_2, ";
			$sql .= " a.access_password, a.access_login ";
			$sql .= " FROM people p, access a WHERE ";

			foreach ($criteria as $form => $dbfield ) {
				if (!empty( $_REQUEST[$form] ) ) {

					if ( $form == 'access_login' ||  $form == 'email_1'){
						$_REQUEST[$form] = strtolower( $_REQUEST[$form] );
					}

					$where[] = "$dbfield = " . $dbh->quote($_REQUEST[$form]);
				}
			}

			
			$criteriacnt = count($where);

			$sql .= implode (' AND ', $where);

			$result = $dbh->query($sql);
			$num = $result->rowCount();



			//
			// No matches with ACCess Password
			//
			if ($num < 1){
			    $where = array();
			    $err = '';

			    //------------------------------------------------------
			    // Query people info assuming he is NOT in access_password
			    //------------------------------------------------------
			    $sql = "SELECT p.people_id,  p.first_name, p.last_name, p.email_1, p.email_2 ";
			    $sql .= " FROM people p WHERE ";

				$criteria['people_id']	=	'p.people_id';
     
		        foreach ($criteria as $form => $dbfield ) {
			        if (!empty( $_REQUEST[$form] ) ) {
				        $sqlw = "$dbfield = " ; 
				        $sqlw .= $dbh->quote($_REQUEST[$form]);
				        $sqlw .= "";
				        $where[] =  $sqlw;
			        }
		        }
    

			    if ( $criteriacnt >= $LOGINCFG['CRITERIACOUNT'] ) {
				    $sql .= implode (' AND ', $where);

					$result = $dbh->query($sql);
				    $num = $result->rowCount();
			    }else{
				    echo "<P class=trace>Need to specifiy at least two search criteria</P>\n";
				    $err .= "<LI>Insufficient search criteria specified, please try again.</LI>";
				    $num = "77777";	// Random bogus number above 2
			    }

			};
			

			if ($num < 1){
				$err .= "<LI>No records matching your account information were found</LI>";
				if (!empty( $_REQUEST[access_login] )) {
					$err .= "<LI>No login records matching login of";
					$err .= " $_REQUEST[access_login] and password were found</LI>";
				}
				if (!empty( $_REQUEST[email_1] )) {
					$err .= "<LI>No login records matching primary email address";
					$err .= " of $_REQUEST[email_1] were found</LI>";
				}
				if ( !empty ($_REQUEST[email_2]) ) {
					$err .= "<LI>No login records matching alternate email address";
					$err .= " of $_REQUEST[email_2] were found</LI>";
				}
			}

			//
			// Multiple matches no joy
			//
			if ($num > 1){
				$err .= "<LI>Multiple login records were found. Please try again with more information.";
			}

			//
			// Gauntlet passed, assume 1 match
			//
			if ( empty($err) ) {
				//----------------------------
				// Assuming num == 1
				// Create new random password
				//----------------------------
				//
				list($newpw,$phonetic) = generate_password();
				$newencpw = md5($newpw);

				$people = array();

				$people = $result->fetch(PDO::FETCH_ASSOC);


				//
				// Determine expiration date for this temporary password
				//
				$today = date('Y-m-d');;	// YYYY-MM-DD
				$expsql = "SELECT allcal_id from allcal where calendar_date = '$today'";
				$today_id = get_value($expsql);

				$idx = ($today_id + $LOGINCFG[TMPPWEXPIREDAYS] );
				$expsql = "SELECT calendar_date from allcal where allcal_id = '$idx'";
				$expdate = get_value($expsql);

				//
				// Update access with temporary password
				//
				$updatesql = "UPDATE access set ";
				$updatesql .= " access_password = ";
				$updatesql .= $dbh->quote($newencpw) . ',';
				$updatesql .= " expiration_date = ";
				$updatesql .= $dbh->quote($expdate) ;
				$updatesql .= " WHERE people_id = $people[people_id]";

				$result = $dbh->query($updatesql);

				//
				// Add to $people array to give to email_ack
				//
				$people[expiration_date] = $expdate;
				$people[password] = $newpw;
				$people[encrypted_password] = $newencpw;
				$people[login] = $people[access_login];
				$people[phonetic] = $phonetic;

				//
				// Send Email
				//
				send_email_ack($people);
			    spew_login_form();
				spew_footer($FMT);
				exit;
			}else{
				$err . "<LI> . $trace . </LI>";
				bail($err);
			}
		}

		//-----------------------------------------------------------------------------
		// Endif ( $_REQUEST['Action'] == "Reset My Password" ) 
		//-----------------------------------------------------------------------------

	}else{
		//-----------------------------------------------------------------------------
		// No Action Parameter
		//-----------------------------------------------------------------------------

		if (array_key_exists( 'people_id', $_SESSION)) {
			if (  empty ( $ReturnUrl ) ) {
				spew_header($FMT);



                $mypeople_id = $_SESSION['people_id'];
				now_what($mypeople_id);

			}else{
				header("Location: $GGSDCFG[BASEURL]${ReturnUrl}");
				exit;
			}
		}else{
			spew_header($FMT);
			spew_login_form();
		}
		spew_footer($FMT);
		exit;
	}

	//=========================================================================
	// Begin Functions
	//=========================================================================

	//----------------------------------------------------------------
	// Display basic login subform
	//----------------------------------------------------------------
	function spew_login_form() {
		global $FMT;
		global $ReturnUrl;
		global $url;
		global $rawurl;




		echo "<P class=trace>\n";
		echo "For general login help, see";
		echo " <A HREF=/help.php?application=Login&context=Overview&Action=Help target=\"_blank\">login overview</A>.\n";
		echo "For an intro to the website, see";
		echo " <A HREF=/help.php?application=Website&context=Overview&Action=Help target=\"_blank\">website overview</A>.\n";
		echo "</P>\n";

		echo "<CENTER>\n";
		echo "<TABLE>\n";
		echo "<TR><TD>";
		echo "<OL>\n";
		echo "<LI class=small>";
        echo "In the first box enter your login";
		//TODO: Check access log for failures, if recent, reveal hint
		//echo " (normally first and last name all in lower case joined with a period, no spaces... not your email address) "; // DEV_ONLY
		echo " ( no spaces, all lower case, not your email address ) "; // DEV_ONLY
		echo " and password.\n";
		echo "</LI>";
		echo "<LI class=small>";
		echo "Enter the numbers shown in the colored image ";
		echo " into the box below that image.\n";
		echo "</LI>";
		echo "<LI class=small>";
		echo "Click on the <B>Login</B> or <B>Authenticate</B> button.\n";
		echo "</LI>";
		echo "</TD></TR></TABLE>\n";
		echo "<P class=trace>\n";
		echo "Click on <A HREF=$_SERVER[PHP_SELF]?Action=Forgot+My+Password>Forgot My Password</A> button ";
		echo "if you need to (re)set your password.";
		echo "</P>\n";

		if ( isset ($_SESSION['people_id']) ) {
			echo "<TABLE CELLPADDING=4>\n";
			echo "<TR><TD VALIGN=TOP ALIGN=CENTER>\n";
				spew_login_subform();
			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";
			echo "</CENTER>\n";
		}else{
			echo "<TABLE CELLPADDING=4>\n";
			echo "<TR><TD VALIGN=TOP ALIGN=CENTER>\n";
				spew_login_subform();
			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";
			echo "</CENTER>\n";
		}
	}


	//----------------------------------------------------------------
	// Display login subform
	//----------------------------------------------------------------
	function spew_login_subform() {
		global $ReturnUrl;
		echo "<form action=$_SERVER[PHP_SELF]  method=post>\n";
		echo "<TABLE CELLPADDING=4 BORDER>\n";
		echo "<TR><TD class=tdl><B>Login:</B></TD><TD><input type=text name=login></TD></TR>\n";
		echo "<TR><TD class=tdl><B>Password:</B></TD><TD><input type=password name=pass></TD></TR>\n";
		echo "</TABLE>\n";
		//
		// Captcha
		//
		//echo "<p class=trace>";
		echo "<p><img src='/kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
		echo "<p><input type=text name=keystring></p>\n";

		if ( empty ($ReturnUrl) ) {
			echo "<input type=SUBMIT name=Action value=\"Login\">\n";
		}else{
			echo "<input type=SUBMIT name=Action value=\"Authenticate\">\n";
			echo "<input type=HIDDEN name=RetUrl value=\"$ReturnUrl\">\n";
		}

		echo "<HR>\n";

        echo "<P class=trace>";
        //echo "<P>";
		echo "More information, see";
        echo " <A HREF=/help.php?application=Login&context=Password+Recovery&Action=Help target=\"_blank\">";
        echo " help</A>.</P>\n";

		echo "<input type=SUBMIT name=Action value=\"Forgot My Password\">\n";
		echo "<input type=SUBMIT name=Action value=\"Change My Password\">\n";
		echo "</form>\n";
	}


	//----------------------------------------------------------------
	// Display loginas subform
	//----------------------------------------------------------------
	function spew_loginas_subform() {
		global $ReturnUrl;
		echo "<form action=$_SERVER[PHP_SELF]  method=post>\n";
		echo "<TABLE CELLPADDING=4 BORDER>\n";
		echo "<TR><TD class=tdl>Login:</TD><TD class=tds><input type=text name=loginas></TD></TR>\n";
		echo "<TR><TD class=tdl>Password:</TD><TD class=tds><input type=password name=pass></TD></TR>\n";
		echo "</TABLE>\n";
		//
		// Captcha
		//
		//echo "<p class=trace>";
		echo "<p><img src='/kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
		echo "<p><input type=text name=keystring></p>\n";

		if ( empty ($ReturnUrl) ) {
			echo "<input type=SUBMIT name=Action value=\"Login As\">\n";
		}else{
			echo "<input type=SUBMIT name=Action value=\"Authenticate\">\n";
			echo "<input type=HIDDEN name=RetUrl value=\"$ReturnUrl\">\n";
		}

		echo "<HR>\n";

        echo "<P class=trace>";
		echo "More information, see";
        echo " <A HREF=/help.php?application=Login&context=Password+Recovery&Action=Help target=\"_blank\">";
        echo " help</A>.</P>\n";

		echo "<input type=SUBMIT name=Action value=\"Forgot My Password\">\n";
		echo "<input type=SUBMIT name=Action value=\"Change My Password\">\n";
		echo "</form>\n";
	}


	//----------------------------------------------------------------
	// Function bail: Gracefull exit
	//----------------------------------------------------------------
	function bail ( $err ) {
			global $FMT;
			echo "<CENTER>\n";
			echo "<TABLE id=error_form_incomplete BORDER=3 WIDTH=90% CELLSPACING=5 CELLPADDING=5>\n";
			echo "<TH>Well... This is not where we want to be, is it?</TH>\n";
			echo "<TR><TD class=tds>\n";
			echo "Why are we here? (That's a website help question, not the existential one...)\n";
			echo "<UL>\n";
			echo "$err\n";
			echo "</UL>\n";
			echo "Lets work to get you where you want to be. Please click on the appropriate link\n";
			echo "<UL>\n";
			echo "<LI>Try to <A HREF=/login.php>login</A> again. </LI>\n";
			echo "<LI>If you do not remember your password, return to the login page and click on the\n";
			echo "<A HREF=/login.php?Action=Forgot+My+Password>Forgot My Password</A> button.</LI>\n";
			echo "<LI>If you need help, you can fill out the ";
			echo "<A HREF=/feedback.php>feedback form</A> or send email by clicking the webmaster link";
			echo " at the bottom of the page.</LI>\n";
			echo "</UL>\n";
			echo "</TD>\n";
			echo "</TABLE>\n";
			echo "</CENTER>\n";
			spew_footer($FMT);
			exit;
	}
	//----------------------------------------------------------------
	// Function now_what: Provide list of Links/options
	//----------------------------------------------------------------
	function now_what($people_id) {
		global $GGSDCFG;
		global $FMT;
		$dbh = ggsd_pdo_connect();

		require_once("./include/ggsd-nav-inc.php");
		require_once("./include/ggsd-looknfeel-inc.php");


		echo "<CENTER>\n";
		echo "<H2>Welcome to GGSD Info,";
		echo " $_SESSION[full_name]";
		echo "</H2>\n";
		echo "<P>You can always get back here by clicking the logo image in the upper left corner.</P>\n";


		echo "<TABLE id=login_link_roster BORDER  CELLPADDING=7>\n";

		//----------------------------
		// Columns
		//----------------------------
		echo "<TH>What Would You Like To Do?</TH>\n";

		echo "<TR>\n";
		//----------------------------
		// Links
		//----------------------------
		echo "<TD>\n";

		echo "Photos, Website Admin, and Non-Dancing Stuff\n";
        echo "<OL>\n";

		if ( $_SESSION['access_level'] > 1 ){
			echo "<LI><A HREF=/login.php?Action=Change+My+Password>Change my passord.</A></LI>\n";
			echo "<LI><A HREF=/people.php?Action=Verify>Review my contact information.</A></LI>\n";
		}

		if ( $_SESSION['access_level'] > 0 ){
			echo "<LI><A HREF=/adduser.php>Add a new user to the website.</A></LI>\n";	
		}



        echo "</OL>\n";
		echo "</TD>\n";

		echo "</TR>\n";
		echo "</TABLE>\n";
		echo "</CENTER>\n";
	}

	//----------------------------------------------------------------
	// Function send_email_ack
	//----------------------------------------------------------------
	function send_email_ack ( array $data ) {

		//return; 	// DEBUG DEMO

		global $GGSDCFG;
		global $LOGINEMAIL;


		if ( $data['email_1'] ) {
			$LOGINEMAIL['toemail'] = $data['email_1'];
			echo "<CENTER>\n";
			echo "<H2>The info has been emailed to $data[email_1]</H2>\n";
			echo "</CENTER>\n";
		}else{
			die("No primary email provided for email acknowledgement");
		}


		$fd = popen($GGSDCFG['MAILER'],"w"); 
		fputs($fd, "From: $LOGINEMAIL[fromname] <$LOGINEMAIL[fromemail]>\n"); 
		fputs($fd, "To: $LOGINEMAIL[toemail]\n"); 

		if ( $data['email_2'] ) {
			fputs($fd, "Cc: $data[email_2]\n"); 
			echo "<CENTER>\n";
			echo "<H2>A copy has also been sent to your alternate email $data[email_2]</H2>\n";
			echo "</CENTER>\n";
		}

		fputs($fd, "Subject: $LOGINEMAIL[subject]\n\n"); 
		fputs($fd, "Your login password for $GGSDCFG[BASEURL] has been reset.\n");
		fputs($fd, "Below is your login (no spaces, all lower case).\n\n"); 
		fputs($fd, "Login : $data[access_login]\n\n");
		fputs($fd, "Password Change Link Expiration Date: $data[expiration_date]\n\n");
		fputs($fd, "Click on the following link to select a new password before the above expiration date.\n\n");
		fputs($fd, "$GGSDCFG[BASEURL]/login.php?people_id=$data[people_id]&encpw=$data[encrypted_password]&login=$data[login]&Action=Change\n\n");
		fputs($fd, "Do not reply to this email\n");

		pclose($fd); 
	}//End function send_email_ack

	//=========================================================================
	// END Functions
	//=========================================================================
?>

