<?php
	//#==================================================================
	//# Access
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/access.php,v $
	//# $Id: access.php,v 1.2 2022/12/12 19:14:57 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	require_once("./include/ggsd-auth-inc.php");
	require_once("./include/ggsd-config-inc.php");
	require_once("./include/ggsd-looknfeel-inc.php");
	require_once("./include/ggsd-msutils-inc.php");
	require_once("./include/ggsd-session-inc.php");

	//--------------------------------------------------------------------------
	// If you are not authenticated (no people_id in $_SESSION), 
	// Construct return url and redirect to login for authentication
	//--------------------------------------------------------------------------
	//
	global $GGSDCFG;
	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

	if ( ! isset ( $_SESSION['people_id'] )) {
		if (array_key_exists('QUERY_STRING', $_SERVER)){
			$param = preg_replace('/&/', '|', $_SERVER['QUERY_STRING'] );
			$returl =  $_SERVER['PHP_SELF'] . '?' .  $param;
			header("Location: $GGSDCFG[BASEURL]/login.php?RetUrl=$returl");;
		}else{
			$returl =  $_SERVER['PHP_SELF'];
			header("Location: $GGSDCFG[BASEURL]/login.php?RetUrl=$returl");;
		}
		exit;
	}


	//------------------------------------------------------------------------
	// Formatting and navbar options for looknfeel-inc header/footer functions
	//------------------------------------------------------------------------
	//
	$FMT = array (
		'BANNER'		=>	"Login Access",
		'TITLE'			=>	"Login Access",
		'MODULENAME'	=>	"access.php",
		'NAV1'			=>	"INFO"	// Level 1 menu navigation group
	);

	//------------------------------------------------------------------------
	// Local configuration parameters
	//------------------------------------------------------------------------
	$ACCESSCFG = array (
		'VIEWLEVEL'		=>	'6',	// $GGSDCFG['ADMINLEVEL']
		'EDITLEVEL'		=>	'8',	// $GGSDCFG['EXECLEVEL']
		'ADMINLEVEL'	=>	'9',	// $GGSDCFG['SYSADMINLEVEL']
	);

	//------------------------------------------------------------------------
	// Database Fields
	//------------------------------------------------------------------------
	$ALLFIELD = array(
		'access_id',
		'people_id',
		'couple_id',
		'access_login',
		'access_class',
		'access_role',
		'access_level',
		'expiration_date',
		'last_updated',
	);

	$Redact = array(
		'access_password',
	);

	//
	//	Fields visible in query output list
	//
	$SHOW = array(
		'access_id',
		'people_id',
		'couple_id',
		'access_login',
		'access_class',
		'access_role',
		'access_level',
		'expiration_date',
		'last_updated',
	);

	//
	// Fields that can have query drill down links on display
	//
	$LINK = array(
		'access_class',
		'access_role',
		'access_level',
	);
	//
	// Fields that are from a Menu Picklist that can have new members
	//
	$EXTEND = array(
		'access_role',
		'access_class',
	);

	//
	// Required for New Entry
	//
	$RequiredField = array(
		'people_id'			=>	'choose person',
		'access_login'		=>	'enter login',
		'access_class'		=>	'choose class',
		'access_role'		=>	'choose role',
		'access_level'		=>	'choose level',
	);

	//
	// Global query choices
	//
	$InValidChoice = array(
		'All',
		'',
		' ',
		'None',
		'Choose'
	);
	//
	// Edit record fields with edit disabled
	//
	$NoEdit = array(
		'access_password',
		'last_updated'
	);

	$FieldType = array(
		'access_class'	=>	'Menu',
		'access_level'	=>	'Menu',
		'access_role'	=>	'Menu',
		'people_id'		=>	'MenuArray',
	);

	$BASE = "SELECT choice FROM menu WHERE table_name = 'access' AND ";

	$Menu = array(
		"access_class"		=> "$BASE field_name = 'access_class' order by choice",
		"access_role"		=> "$BASE field_name = 'access_role' order by choice",
		"access_level"		=> "$BASE field_name = 'access_level' order by choice",
		"people_id"			=> "SELECT people_id, full_name from people where people_id > 100 order by full_name",
	);

	//
	// Display exceptions from default tdcs centered display table cell
	//
	$JustifyCss = array(
		'access_class'		=>	'tds',
		'access_login'		=>	'tds',
		'access_role'		=>	'tds',
		'full_name'			=>	'tds',
		'people_id'			=>	'tds',
	);

	//------------------------------------------------------------------------
	// BEGIN Program
	//------------------------------------------------------------------------

	spew_header($FMT);
	if (array_key_exists('Action', $_REQUEST)) {


		spew_query_form();

		//----------------------------------------------------------------------
	  	// Insert New Entry
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Insert New Entry" ) {

			if ($_SESSION['access_level'] <= $ACCESSCFG['EDITLEVEL'] ) {
				die ("ERROR: You do not have sufficient privilege to update website access.");
			}

			$dbh = ggsd_pdo_connect();
			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('access','access',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabel);

			//
			// Define default values
			//
			$Default = array (
				'access_state'	 =>  'New'
			);

			//
			// Setup default values
			//
			foreach ($Default as $key => $val ) {
				if ( ! isset ( $_REQUEST[$key]) ) {
					$_REQUEST[$key] = $val;
				}
			}

			//
			// Eliminate all keys that have invalid answers
			// Overwrite open menu entries with NEW_.. entries
			//
			foreach ($fields as $f) {
				$altkey = "NEW_" . $f;

				if ( isset($_REQUEST[$altkey]) ) {
					if (!in_array($_REQUEST[$altkey], $InValidChoice)) {
						$_REQUEST[$f] = $_REQUEST[$altkey];
						unset ($_REQUEST[$altkey]);
					}
				}

				if (in_array($_REQUEST[$f], $InValidChoice)) {
					unset ($_REQUEST[$f]);
				}

			}

			//
			// Delete auto_increment primary keys
			//
			unset ($_REQUEST['access_id']);

			//
			// Required fields gauntlet
			//
			foreach ( $RequiredField as $key => $val) {
				if (! array_key_exists($key, $_REQUEST)) {
					$err .= '<LI>Please ' . $RequiredField[$key] . '.</LI>';
				}
			}

			if ( $err ) {

				echo "<CENTER>\n";
				echo "<H3>Incomplete Information</H3>\n";
				echo "<TABLE BORDER>\n";
				echo "<TR><TD><UL>$err</UL></TD></TABLE>\n";
				echo "</TABLE>\n";
				echo "</CENTER>\n";
				spew_footer($FMT);
				exit;
			}

			$sql = 'INSERT INTO access (';
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					$sql .= $f . ',';
				}
			}
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					$val = $dbh->quote($_REQUEST[$f]);
					$sql2 .=  $val . ',';
				}
			}
			// TODO SECURITY : prepare, etc for sql injection

			$finalsql = rtrim($sql, ",") .  ') VALUES (' .  rtrim($sql2, ",") . ")";
			$result = $dbh->query($finalsql);

            $_REQUEST['access_id'] = $dbh->lastInsertId() ;
            $_REQUEST['Action'] = 'View';

			echo "<CENTER>\n";
			echo "<H2>Record successfully added</H2>\n";
			echo "</CENTER>\n";
		}

		//----------------------------------------------------------------------
	  	// Update Existing Entry 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Update" ) {

			if ( array_key_exists('access_id', $_REQUEST)) {
				$access_id = $_REQUEST['access_id'];
				if (! is_numeric( $access_id ) ) {
					die ("ERROR: Attempt to update Access requires access_id to be integer. It is not.");
				}
			}else{
				die ("No Access Id Set") ;
			}

			if ($_SESSION['access_level'] <= $ACCESSCFG['EDITLEVEL'] ) {
				die ("ERROR: You do not have sufficient privilege to update website access.");
			}

			$dbh = ggsd_pdo_connect();

			//
			// Get Original Record
			//
			$Original = array();
			$sql = "SELECT * FROM access WHERE access_id = ";
			$sql .= $dbh->quote($access_id);
			$result = $dbh->query($sql);

			$Original = $result->fetch(PDO::FETCH_ASSOC);

			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('access','aaaaaaaaaa',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabel);



			//
			// Eliminate all keys that have invalid answers
			//
			foreach ($fields as $f) {
				if (in_array($_REQUEST[$f], $InValidChoice)) {
					unset ($_REQUEST[$f]);
				}
			}

			//
			// Update only the fields that have changed
			//
			$sql = 'UPDATE access SET ';
			$sqlentry = array ();
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					if ( $_REQUEST[$f] != $Original[$f] ) {
						$sqlentry[] = $f . " = " . $dbh->quote($_REQUEST[$f]);
					}
				}
			}

			if (count($sqlentry) > 0){
				$sql .= implode (', ', $sqlentry);
				$sql .= " WHERE access_id = '$access_id'";


				$result = $dbh->query($sql);
				echo "<H3>Update successful</H3>\n";
			}else{
				echo "<H3>No Changes Made</H3>\n";
			}

			$_REQUEST['access_id'] = $access_id;
			$_REQUEST['Action'] = "View";
		}

		//----------------------------------------------------------------------
	  	// List
		//----------------------------------------------------------------------
	  	if ( $_REQUEST['Action'] == "List" 
	  		|| $_REQUEST['Action'] == "List Unchanged" ) {

			$ALLFIELD = array(
				'access_id',
				'access_password',
				'people_id',
				'couple_id',
				'access_login',
				'access_class',
				'access_role',
				'access_level',
				'expiration_date',
				'last_updated',
			);

			$SHOW[] = 'access_password';


			$dbh = ggsd_pdo_connect();
			$fieldlabel = array();

			$fieldlabel = get_field_labels('access','access',$GGSDCFG['DBNAME']);
			$fieldlabel['access_password'] = 'PW';
			$fields = array_keys($fieldlabel);

			//----------------------------------------------------------
			// Capture previous selection criteria and append to links
			// To enable drill down subqueries
			//----------------------------------------------------------

			foreach(explode( '&', $_SERVER['QUERY_STRING'])  as $entry ) {
				list($key, $val) = explode( '=', $entry);	

				if ( ! empty( $val ) ) {
					if (! in_array($val, $InValidChoice)) {
						if (in_array($key, $ALLFIELD) ) {
							$parameters[$key] = $val;
						}
					}
				}
			}
			$parameters['Action'] = $_REQUEST['Action'];

			//----------------------------------------------------------
			// Uniquify for duplicate entries
			//----------------------------------------------------------
			$validentries = array();

			foreach ($parameters as $key => $val ) {
				$val = preg_replace('/\s+/', '+', $val);
				$validentries[] = $key . '=' . $val;
			}

			if (count($validentries)) {
				$drilldown = implode('&', $validentries);
			}

			//
			// Base sql query
			//
			$What = array(
				'a.*',
				'p.full_name',
			);

			$Where = array(
				'a.people_id = p.people_id',
			);

			$From = array(
				'access'	=>	'a',
				'people'	=>	'p',
			);

			//
			// Construct where clause into an array
			//
			foreach ($fields as $f) {
				if (array_key_exists($f, $_REQUEST)) { 
					$val = $_REQUEST[$f];

					if (in_array( $val, $InValidChoice ) ){
						unset($val) ;
                    }else{
					    $val = $dbh->quote( $_REQUEST[$f] );
						if ( preg_match('/%/', $val)) { 
							$Where[] = "a." . $f . " LIKE " . $val ;
						}else{
							$Where[] = "a." . $f . "=" . $val ;
						}
					}
				}
			}


	  		//if ( $_REQUEST['Action'] == "List Unchanged" ) {
				//$badsql = '(';
				//$badsql .= "a.access_password = " . $dbh->quote($GGSDCFG['GUESTPWENC']);
				//$badsql .=  ' OR ';
				//$badsql .= "a.access_password = " . $dbh->quote($GGSDCFG['MEMBERPWENC']);
				//$badsql .= ')';
				//$Where[] = $badsql;
			//}

			$sql = "SELECT DISTINCT " . implode(',', $What);

			$Fromsql = array();

			foreach ($From as $table => $abbr) {
				$Fromsql[] = $table . ' ' . $abbr;
			}
			$Fromsql = array_unique($Fromsql);

			$sql .= ' FROM ' . implode(', ', $Fromsql);

			if ( count($Where) ) {
				$sql .= ' WHERE ' . implode(' AND ', $Where);
			}

			//---------------------------------------------------
			// ORDER BY
			//---------------------------------------------------
			$OrderBy = array(
				'Name'			=>	'p.full_name',
				'Role'			=>	'a.access_role, p.full_name',
				'Login'			=>	'a.access_login',
				'Password'		=>	'a.access_password',
				'Class'			=>	'a.access_class, a.access_role, p.full_name',
				'Last Updated'	=>	'a.last_updated DESC',
				'Expiration'	=>	'a.expiration_date',
				'Couple ID'		=>	'a.couple_id',
				'Level'			=>	'a.access_level DESC, p.full_name',
			);

			$sortby = $_REQUEST['Sortmeby'];
			$sby = $OrderBy[$sortby];

			if (empty ($sby)){
				$sql .= ' ORDER BY p.full_name' ;
			}else{
				$sql .= ' ORDER BY ' . $sby;
			}

			$result = $dbh->query($sql);
			$rowcount = $result->rowCount();

			// Blurb
			echo "<P class=trace>\n";
			echo "Column entries that are links will &quot;drill down&quot; to refine your query.\n";
			echo "<BR>Query returned $rowcount " . ( $rowcount ==1 ? 'entry.' : 'entries.');
			echo "</P>\n";

			echo "<CENTER>\n";
			//---------------------------------------------------
			// Spew Icon Legend
			//---------------------------------------------------
			echo "<TABLE>\n";
			echo "<TH class=ths COLSPAN=2>Password Status</TH>\n";
			echo "<TR><TD class=tdsc><IMG SRC=/images/smallballs/redball.gif BORDER=0></TD>\n";
			echo "<TD class=tds>Default Password</TD>\n";
			echo "</TR>\n";
			echo "<TR><TD class=tdsc><IMG SRC=/images/smallballs/greenball.gif BORDER=0></TD>\n";
			echo "<TD class=tds>Customized Password</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";

			//---------------------------------------------------
			// Spew table
			//---------------------------------------------------
			echo "<TABLE BORDER>\n";

			if ($_SESSION['access_level'] >= $ACCESSCFG['EDITLEVEL'] ) {
				echo "<TH class=ths>Edit</TH>\n";		// SECURITY
			}

			echo "<TH class=ths>View</TH>\n";

			foreach ($ALLFIELD as $f) {
				if (array_key_exists($f, $NoShow)) {
					continue;
				}
				if (in_array($f, $SHOW)) {
					echo "<TH class=ths>$fieldlabel[$f]</TH>\n";
				}
			}

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";

				// Edit if authorized
				// SECURITY
				if ($_SESSION['access_level'] >= $ACCESSCFG['EDITLEVEL'] ) {	
					echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
					echo "<A HREF=$_SERVER[PHP_SELF]?access_id=$row[access_id]";
					echo "&Action=Edit>";
					echo "<IMG SRC=/images/smallballs/greenball.gif BORDER=0></A>";
					echo "</TD>\n";
				}
	
				// View for everyone
				echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
					echo "<A HREF=$_SERVER[PHP_SELF]?access_id=$row[access_id]";
					echo "&Action=View>";
					echo "<IMG SRC=/images/smallballs/yellowball.gif BORDER=0></A>";
				echo "</TD>\n";
	
				foreach ($ALLFIELD as $f) {
					$css = "tdcs";
					$display = stripslashes($row[$f]); 
					if (array_key_exists($f, $NoShow)) {
						continue;
					}

					//
					// Display Exceptions (lookup)
					//
					if ( $f  == 'people_id' ) {
						$pid = $row[$f];
						$display = "<A HREF=/people.php?people_id=$pid&Action=View>";
						$display .= $row[full_name];
						$display .= "</A>";
					}
					if ( $f  == 'access_password' ) {
						$display = "<IMG SRC=/images/smallballs/greenball.gif BORDER=0>";
						//if ( $row[$f] == $GGSDCFG['GUESTPWENC'] ){
							//$display =  "<IMG SRC=/images/smallballs/orangeball.gif BORDER=0>";
						//}
						if ($row[$f] == $GGSDCFG['MEMBERPWENC'] ){
							$display = "<IMG SRC=/images/smallballs/redball.gif BORDER=0>";
						}
					}

					if (in_array($f, $SHOW)) {

						if (array_key_exists($f, $JustifyCss)) {
							$css = $JustifyCss[$f];
						}

						echo "<TD VALIGN=TOP class=$css>";
						if (in_array($f, $LINK)) {
							echo "<A HREF=";
							echo "$_SERVER[PHP_SELF]";
							echo '?';
							$url = preg_replace('/\s+/', '+', $row[$f]);
							echo "$f=${url}"; 
							echo "&${drilldown}>";
							echo "$display</A>\n";
						}else{
							echo "$display\n";
						}
						echo "<BR></TD>\n";
					}
				}
			}
			echo "</TABLE>\n";
			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "List")) 

		//----------------------------------------------------------------------
	  	// New Entry Form
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "New" ) {
			$menulist = array();
			$table = 'access';

			$dbh = ggsd_pdo_connect();

			//
			// People Rosterselect 
			//
			$Who = array();
			$sql = "SELECT people_id, full_name from people where people_id > 100";
			$Who = get_menu_array($sql);

			// Blurb
			echo "<P class=trace>\n";
			echo "Enter entries. Details on meanings and choice details available via help links in left column.\n";
			echo "Asterisk (*) indicates field is required.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			$fieldlabel = get_field_labels('access','access',$GGSDCFG['DBNAME']);


			foreach ($ALLFIELD as $fieldname ) {
			    if ( $fieldname == 'access_id'){
                    continue;
                }
				$val = $fieldlabel[$fieldname];
				
				echo "<TR>\n";

                echo "<TD CLASS=tdls>";
				echo "<A HREF=/help.php?table_name=$table&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$val</A>";
				if ( isset($RequiredField[$fieldname]) ){
					echo '&nbsp;*&nbsp;';
				}
				echo "</TD>\n";

				echo "<TD class=tds>";

				if (array_key_exists($fieldname, $FieldType)) {
					if ( $FieldType[$fieldname] == "Menu" ) {
						$menusql = $Menu[$fieldname];
						$menulist = get_menu($menusql);
						sort($menulist);
						spew_select_menu($fieldname, $_REQUEST[$fieldname],'',$menulist);
						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_${fieldname}>";
						}
					}

					if ($FieldType[$fieldname] == "MenuArray" ) {
						$menusql = $Menu[$fieldname];
						$menulist = get_menu_array($menusql);
						asort($menulist);
						spew_select_hash_menu($fieldname, $_REQUEST[$fieldname],'',$menulist);
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA COLS=70 ROWS=20 NAME=$fieldname></TEXTAREA>\n";
					}

					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT SIZE=70 NAME=$fieldname>";
					}

					if ($FieldType[$fieldname] == "People" ) {
						spew_select_menu($fieldname, $_REQUEST[$fieldname],'Choose',$Who);
					}

				}else{
					echo "<INPUT TYPE=TEXT NAME=$fieldname>";
				}
				echo "</TD>\n";
			}
			echo "</TABLE>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Insert New Entry\">\n";
			echo "</FORM>\n";
			echo "</CENTER>\n";
		}//End if ($_REQUEST['Action'] == "New" ) 

		//----------------------------------------------------------------------
	  	// Edit 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Edit") {

			$table='access';

			if ( array_key_exists('access_id', $_REQUEST)) {
				if ( isset($_REQUEST['access_id'] ) ) {
					$access_id = stripslashes( $_REQUEST['access_id']);
				}else{
					die ("NO Access ID in edit function.") ;
				}
				if ( ! is_numeric($access_id) ) {
					die ("Access ID ($access_id) is not an integer.") ;
				}
			}else{
				die ("No Access Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// People Rosterselect 
			//
			$Who = array();
			$sql = "SELECT people_id, full_name from people where people_id >= 100;";
			$Who = get_menu_array($sql);

			$menulist = array();

			$sql = "SELECT * FROM access WHERE access_id = '$access_id'";
			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('access','access',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Change desired entries and click <B>Update</B> at bottom of form.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				if (array_key_exists($fieldname, $NoShow)) {
					continue;
				}
				echo "<TR><TD VALIGN=TOP class=tdls>";
				echo "<A HREF=/help.php?table_name=access&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$label";
				echo "</A>";
				echo "</TD>\n";
				echo "<TD VALIGN=TOP class=tds>";

				if (in_array($fieldname, $NoEdit)) {
					echo "$row[$fieldname]<BR>";
				}else{
					if (array_key_exists($fieldname, $FieldType)) {

						if ( $FieldType[$fieldname] == "Menu" ) {
							$menusql = $Menu[$fieldname];
							$menulist = get_menu($menusql);
							spew_select_menu($fieldname, $row[$fieldname],'',$menulist);
							if (in_array($fieldname, $EXTEND)) {
								echo "-OR- <INPUT TYPE=TEXT NAME=NEW_${fieldname}>";
							}
						}

						if ($FieldType[$fieldname] == "MenuArray" ) {
							$menusql = $Menu[$fieldname];
							$menulist = get_menu_array($menusql);
							spew_select_hash_menu($fieldname, $row[$fieldname],'',$menulist);
						}

						if ($FieldType[$fieldname] == "People" ) {
							spew_select_menu($fieldname, $_REQUEST[$fieldname],'',$Who);
						}

						if ( $FieldType[$fieldname] == "TextArea" ) {
							echo "<TEXTAREA NAME=$fieldname COLS=70 ROWS=20>$row[$fieldname]</TEXTAREA>\n";
						}

						if ( $FieldType[$fieldname] == "LongText" ) {
							echo "<INPUT TYPE=TEXT NAME=$fieldname SIZE=70 VALUE=\"$row[$fieldname]\">\n";
						}

					}else{	// No fieldtype
						echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$row[$fieldname]\"><BR>";
					}
						
				}//Endif NoEdit
				echo "</TD>\n";
			}//Endforeach fieldname
			echo "</TABLE>\n";
			echo "<INPUT TYPE=HIDDEN NAME=access_id VALUE=$_REQUEST[access_id]>\n";
			// SECURITY
			if ( $_SESSION['access_level'] >= $ACCESSCFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
			}
			echo "</FORM>\n";
			echo "</CENTER>\n";

			show_journal_history($access_id, 'access');

	  	}//if ($_REQUEST['Action'] == "Edit") 


		//----------------------------------------------------------------------
	  	// View
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "View"
	  		|| $_REQUEST['Action'] == "View Details" ) {

			$table = 'ggsd';

			if ( array_key_exists('access_id', $_REQUEST)) {
				if ( isset ( $_REQUEST['access_id'] ) ) {
					$access_id = $_REQUEST['access_id'];
				}else{
					die ("No Access ID in view function") ;
				}
				if ( ! is_numeric($access_id) ) {
					die ("Access ID ($access_id) is not an integer.") ;
				}
			}else{
				die ("No Access Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			$Who = array();
			$menulist = array();
			$row = array();

			$sql = "SELECT people_id, full_name from people where people_id > 100";
			$Who = get_menu_array($sql);

			$sql = "SELECT a.*, p.full_name  FROM access a, people p  WHERE access_id = '$access_id'";
			$sql .= " AND a.people_id = p.people_id ";
			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('access','access',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Field explanation available via link in left column field labels.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<H2>$row[full_name]</H2>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				if (array_key_exists($fieldname, $NoShow)) {
					continue;
				}
				$label = $fieldlabel[$fieldname];
				echo "<TR>\n";
				echo "<TD class=tdls>";
				echo "<A HREF=/help.php?table_name=access&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$label</A></TD>\n";
				echo "<TD class=tds>";

				$display = stripslashes($row[$fieldname]);

				//
				// People_id to Name
				//
				if ( $fieldname == "people_id" ) {
					$whoid = $row[$fieldname];
					$display = $Who[$whoid];
				}

				echo "$display<BR>";
				echo "</TD>\n";
			}//Endforeach fieldname

			echo "</TABLE>\n";

			echo "<INPUT TYPE=HIDDEN NAME=access_id VALUE=$access_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";
			if ( $_SESSION['access_level'] >= $ACCESSCFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Edit\">\n";
			}
			echo "</FORM>\n";

			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "View")

		//----------------------------------------------------------------------
		// END 'Action' Processing Options
		//----------------------------------------------------------------------

	}else{	// No Action Field
		spew_query_form();
	}

	spew_footer($FMT);

	//----------------------------------------------------------------
	// Function spew_query_form
	//----------------------------------------------------------------
	function spew_query_form() {
		global $GGSDCFG;
        $list = array();
		$dbh = ggsd_pdo_connect();

		echo "<P class=trace>";
		echo "Help and explanations available at ";
		echo "<A HREF=/help.php?table_name=access&field_name=Overview&Action=Help target=\"_blank\">Overview</A>.";
		echo "</P>\n";

		echo "<CENTER>\n";
		echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
		echo "<TABLE BORDER>\n";
		echo "<TH class=ths>Name</TH>\n";
		echo "<TH class=ths>Role</TH>\n";
		echo "<TH class=ths>Level</TH>\n";
		echo "<TH class=ths>Sort By</TH>\n";

		echo "<TR>\n";

		// Name
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT p.people_id, p.full_name from people p, access a";
		$sql .= " WHERE p.people_id = a.access_id ";
		$sql .= " ORDER BY p.full_name ";
		$list = get_menu_array($sql);
		$list['All'] = 'All';
		spew_select_hash_menu('people_id','','All',$list);
		echo "</TD>\n";

		// Role
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct access_role from access order by access_role";
		$list = get_menu($sql);
		spew_select_menu('access_role','All','All',$list);
		echo "</TD>\n";

		// Level
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct access_level from access order by access_level";
		$list = get_menu($sql);
		$list['All'] = 'All';
		spew_select_menu('access_level','All','All',$list);
		echo "</TD>\n";

		// Sort By
		echo "<TD class=tds>\n";
		$sortby = array (
			'Name',
			'Role',
			'Login',
			'Password',
			'Class',
			'Expiration',
			'Couple ID',
			'Last Updated',
			'Level',
			);
		sort($sortby);
		spew_select_menu('Sortmeby','','Last Updated',$sortby);
		echo "</TD>\n";

		// End Table

		echo "</TR>\n";
		echo "</TABLE>\n";

		// End Form
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"List Unchanged\">\n";

		// SECURITY
		if ($_SESSION['access_level'] >= $ACCESSCFG['EDITLEVEL'] ) {
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"New\">\n";
		}
		echo "</FORM>\n";

		echo "</CENTER>\n";
	}


	//----------------------------------------------------------------
	// END FUNCTIONS
	//----------------------------------------------------------------
		
?>