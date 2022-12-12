<?php
	//#==================================================================
	//# Session Management 
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/include/RCS/ggsd-session-inc.php,v $
	//# $Id: ggsd-session-inc.php,v 1.2 2022/12/12 19:10:19 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================
	require_once('ggsd-msutils-inc.php');
    require_once('ggsd-config-inc.php');
    require_once('ggsd-auth-inc.php');

	//------------------------------------------------------------------
	// Function ggsd_session_start
	//------------------------------------------------------------------
	function ggsd_session_start($people_id) {
		$me = "ggsd-session-inc.php";
		global $GGSDCFG;
		$trace = $me;

		if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
		$trace .= '<BR>' . session_save_path();

		//
		// Verify valid people_id
		//
		if (! is_numeric($people_id) ) {
			die("Attempted to run ggsd_session_start with non-numeric people_id in $me");
		}

		//
		// Do Database lookups for everything to stuff into session
		//
		$dbh = ggsd_pdo_connect();

		//---------------------------------------------------
		// People Table Sources into SESSION
		//---------------------------------------------------
		//
		$PeopleGetList = array(
			'people_id',
			'first_name', 
			'full_name',
			'last_name', 
			'nickname', 
		);

		$sql = "SELECT ";
		$sql .= implode(',', $PeopleGetList);
		$sql .= " FROM people WHERE people_id = '$people_id'";
		$result = $dbh->query($sql);
		$num = $result->rowCount();
		$People = array();

        $People = $result->fetch(PDO::FETCH_ASSOC) ;

		foreach ($People as $var => $val) {
			if (! empty($val) ) {
				$_SESSION[$var] = $val;
			}
		}

		//
		// Populate 'Name'
		//
		if ( isset($People['nickname'])){
			$_SESSION['Name'] = $People['nickname'];
		}else{
			$_SESSION['Name'] = $People['first_name'];
		}


		//---------------------------------------------------
		// Security access table
		//---------------------------------------------------
		//
		$AccessList = array(
			'access_level',
			'access_role', 
			'access_class', 
		);

		$sql = "SELECT ";
		$sql .= implode(',', $AccessList);
		$sql .= " FROM access WHERE people_id = '$people_id'";
		$result = $dbh->query($sql);
		$num = $result->rowCount();
		$Access = array();
        $Access = $result->fetch(PDO::FETCH_ASSOC) ;

		foreach ($Access as $var => $val) {

			if (! empty($val) ) {
				$_SESSION[$var] = $val;

				if ($val >= $GGSDCFG['SPOOFLEVEL'] ){
					if ( $var == 'access_level' ){
						$_SESSION['proxy_id'] = $_SESSION['people_id'];
						$_SESSION['proxy_level'] = $val;
					}
				}
			}
		}


		//
		//---------------------------------------------------
		// Commit Session
		//---------------------------------------------------
		//
		session_write_close();

		return($trace);
	}


	//------------------------------------------------------------------
	// Function ggsd_session_end
	//------------------------------------------------------------------
	function ggsd_session_end() {
		session_start();
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!

		if (ini_get("session.use_cookies")) {
    		$params = session_get_cookie_params();
    		setcookie(session_name(), '', time() - 42000,
        		$params["path"], $params["domain"],
        		$params["secure"], $params["httponly"]
    		);
		}
		session_destroy();
	}
?>
