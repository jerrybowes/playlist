<?php
	//#==================================================================
	//# GGSD Feedback
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/feedback.php,v $
	//# $Id: feedback.php,v 1.2 2022/12/12 19:15:14 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	require_once("./include/ggsd-auth-inc.php");
	require_once("./include/ggsd-config-inc.php");
	require_once("./include/ggsd-looknfeel-inc.php");
	require_once("./include/ggsd-msutils-inc.php");
	require_once("./include/ggsd-session-inc.php");
	require_once("./kcaptcha.php");
	require_once("./include/kcaptcha_config-inc.php");

	//--------------------------------------------------------------------------

	global $GGSDCFG;
    if (session_status() == PHP_SESSION_NONE) {
       	session_start();
    }

	//------------------------------------------------------------------------
	// Formatting and navbar options for looknfeel-inc header/footer functions
	//------------------------------------------------------------------------
	//
	$FMT = array (
		'BANNER'		=>	"GGSD Info Feedback and Suggestions",
		'BANNER2'		=>	"Help us help you",
		'TITLE'			=>	"GGSD Info Feedback and Suggestions",
		'MODULENAME'	=>	"feedback.php",
		'NAV1'			=>	"INFO"	// Level 1 menu navigation group
	);

	//------------------------------------------------------------------------
	// Local configuration parameters
	//------------------------------------------------------------------------
	$FDBKCFG = array (
        'APPTAG'        =>  'FEEDBACK',
		'EDITLEVEL'		=>	'4',
		'ADMINLEVEL'	=>	'6'
	);
	global $FDBKCFG;

	//------------------------------------------------------------------------
	// Database Fields
	//------------------------------------------------------------------------
	$ALLFIELD = array(
        'feedback_id',
        'cc_list',
        'contact_info',
		'assignee_id',
        'feedback_category',
        'feedback_detail',
        'feedback_state',
        'feedback_status',
        'feedback_resolution',
        'feedback_type',
        'feedback_summary',
        'date_created',
        'last_modified',
        'requester_id',
        'requester_email',
        'requester_type',
        'resolution_type',
	);

	$NEWFIELD = array(
        'feedback_category',
        'feedback_summary',
        'feedback_type',
        'requester_email',
        'requester_type',
        //'cc_list',
        'contact_info',
        'feedback_detail',
	);

	//
	//	Fields visible in query output list
	//
	$SHOW = array(
        'feedback_category',
        'feedback_state',
        'feedback_status',
        'feedback_summary',
        'last_modified',
        'requester_type',
        'feedback_type',
	);

	//
	// Fields that can have query drill down links on display
	//
	$LINK = array(
        'feedback_category',
        'feedback_type',
		'assignee_id',
        'feedback_state',
        'requester_type',
	);
	//
	// Fields that are from a Menu Picklist that can have new members
	//
	$EXTEND = array(
        'feedback_category',
	);

	//
	// Required for New Entry
	//
	$RequiredField = array(
        'feedback_category' =>	'select category from list or enter new feedback category',
        'feedback_detail'   =>	'enter details of your request, suggestion, or feedback',
        'feedback_summary'  =>	'enter brief summary of your request, suggestion, or feedback (will be subject line of response emails)',
        'requester_type'    =>	'select the option that best describes you from list',
        'requester_email'	=>	'enter your email so we can provide updates or answers regarding your comment or feedback',
        'feedback_type'     =>	'select feedback type from list',
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
		'feedback_id',
		'last_updated'
	);

	$FieldType = array(
        'cc_list'				=>	'LongText',
        'contact_info'          =>	'LongText',
        'requester_email'		=>	'LongText',
		'assignee_id'			=>	'MenuArray',
        'feedback_category'     =>	'Menu',
        'feedback_detail'       =>  'TextArea',
        'feedback_state'        =>	'Menu',
        'feedback_status'       =>	'LongText',
        'feedback_resolution'   =>  'TextArea',
        'feedback_summary'      =>  'LongText',
        'feedback_type'         =>	'Menu',
        'requester_id'          =>	'Menu',
        'requester_type'        =>	'Menu',
        'feedback_type'         =>	'Menu',
	);

	$BASE = "SELECT choice FROM menu WHERE table_name = 'feedback' AND ";

	$Menu = array(
		"feedback_category"	=> "$BASE field_name = 'feedback_category' ORDER BY choice",
		"feedback_state"	=> "$BASE field_name = 'feedback_state' ORDER BY choice",
		"feedback_type"		=> "$BASE field_name = 'feedback_type' ORDER BY choice",
		"requester_type"	=> "$BASE field_name = 'requester_type' ORDER BY choice",
		"assignee_id"		=> "SELECT people_id, full_name from people ORDER BY choice",
	);

	//
	// Display exceptions from default tdcs centered display table cell
	//
	$JustifyCss = array(
		'feedback_summary'		=>	'tds',
	);

	//------------------------------------------------------------------------
	// BEGIN Program
	//------------------------------------------------------------------------

	spew_header($FMT);
	if (! array_key_exists('Action', $_REQUEST)) {
        $_REQUEST['Action'] = 'New';
    }

	if (array_key_exists('Action', $_REQUEST)) {



		//----------------------------------------------------------------------
	  	// Query
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Query" ) {
		    spew_query_form();
        }

		//----------------------------------------------------------------------
	  	// Submit Feedback
		//----------------------------------------------------------------------

	  	if ($_REQUEST['Action'] == "Submit Feedback" ) {
			$dbh = ggsd_pdo_connect();

			$pcnt = count($_POST);
			$rcnt = count($_REQUEST);


			// CAPTCHA
			if( count($_REQUEST) > 1 ){
				if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $_REQUEST['keystring']){
					echo "<P class=trace>Excellent, you are a human!</P>";
				}else{
					echo "<P class=trace>The numbers from the captcha form entry you filled in do not match those of the image.";
					echo "<BR>Please press the back button on your browser and try again.\n";
					echo "</P>";
					spew_footer($FMT);
					exit;
				}
			}else{
					echo "<P class=trace>No Data </P>";
					spew_footer($FMT);
					exit;
			}


			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabel);

			//
			// Define default values
			//
			$Default = array (
				'feedback_state'	 =>  'New',
				'feedback_priority'	 =>  'P3: Routine',
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
			unset ($_REQUEST['feedback_id']);

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
				echo "<TABLE BORDER WIDTH=80% CELLPADDING=4>\n";
				echo "<TR><TD><UL>$err</UL></TD></TABLE>\n";
				echo "</TABLE>\n";
				echo "<P>Please click on the back button of your browser to add missing information.\n";
				echo "<BR>(You will also have to update the captcha number to match the image.)\n";
				echo "</P>\n";
				echo "</CENTER>\n";
				spew_footer($FMT);
				exit;
			}

			$Data = array();

			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST) && !empty($_REQUEST[$f]) ) {
					$Data[$f] = $dbh->quote($_REQUEST[$f]);
				}
			}

			//
			// Escape the quotes for date_created, now is a function, not a text literal
			//
			$Data['date_created'] = "now()";

			$sql = "INSERT INTO feedback (";
			$sql .= implode(',', array_keys($Data));
			$sql .= ") VALUES (";
			$sql .= implode(',', array_values($Data));
			$sql .= ")";


			$dbh->query($sql);

			$feedback_id = $dbh->lastInsertId() ;
			$_REQUEST['feedback_id'] = $feedback_id;

            $_REQUEST['Action'] = 'View';

			echo "<CENTER>\n";
			echo "<H2>Thank you for your feedback!</H2>\n";
			echo "</CENTER>\n";

			send_email_ack($_REQUEST);

			now_what();

			spew_footer($FMT);
			exit;
		}

		//----------------------------------------------------------------------
	  	// Update Existing Entry 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Update" ) {

			if ( array_key_exists('feedback_id', $_REQUEST)) {
				$feedback_id = $_REQUEST['feedback_id'];
				if (! is_numeric( $feedback_id ) ) {
					die ("ERROR: Attempt to update Feedback requires feedback_id to be integer. It is not.");
				}
			}else{
				die ("No Feedback Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// Get Original Record
			//
			$Original = array();

			$sql = 'SELECT * FROM feedback WHERE feedback_id = ';
			$sql .= $dbh->quote($feedback_id);
			$result = $dbh->query($sql);

			$Original = $result->fetch(PDO::FETCH_ASSOC);

			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('feedback','aaaaaaaaaa',$GGSDCFG['DBNAME']);


			$fields = array_keys($fieldlabel);

			//
			// Eliminate all keys that have invalid answers
			// Overwrite entries with NEW_... entries for Open Menus
			//
			foreach ($fields as $f) {
				$altkey = "NEW_" . $f;
				if (isset($_REQUEST[$altkey])) {
					if (!in_array($_REQUEST[$altkey], $InValidChoice)) {
						$_REQUEST[$f] =  $_REQUEST[$altkey];
						unset ($_REQUEST[$altkey]);
					}
				}
				if (in_array($_REQUEST[$f], $InValidChoice)) {
					unset ($_REQUEST[$f]);
				}
			}

			//
			// Update only the fields that have changed
			//
			$DataKey = array();
			$UpdateData = array();

			foreach ($fields as $f) {
				if (in_array($f, $NoEdit)) {
                    continue;
				}
				if ( array_key_exists($f, $_REQUEST)) {
					$val = $_REQUEST[$f];


					if ( $_REQUEST[$f] != $Original[$f] ) {
						$UpdateData[] = "$f = " . $dbh->quote($_REQUEST[$f]);
					}
				}
			}


			if (count($UpdateData) > 0){

	
				$sql = 'UPDATE feedback SET ';
				$sql .= implode(', ', $UpdateData);
				$sql .= " WHERE feedback_id = ";
				$sql .= $dbh->quote($feedback_id);
	
				$result = $dbh->query($sql);
			}else{
				echo "<H3>No Changes Made</H3>\n";
			}

			$_REQUEST['feedback_id'] = $feedback_id;
			$_REQUEST['Action'] = "View";
		}

		//----------------------------------------------------------------------
	  	// List
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "List" ) {


			$dbh = ggsd_pdo_connect();
			$fieldlabel = array();

			$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);
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
				'z.*'
			);

			$Where = array(
			);

			$From = array(
				'feedback'	=>	'z'
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
						if ( preg_match('/%/', $val)) { 
							$_REQUEST[$f] .= '%';
							$Where[] = "z." . $f . ' LIKE ' . $dbh->quote($_REQUEST[$f]);
						}else{
							$Where[] = "z." . $f . "=" . $dbh->quote($_REQUEST[$f]);
						}
					}
				}
			}

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
				'Name'		=>	'z.feedback_name',
				'Color'		=>	'z.feedback_color, z.feedback_name',
				'Type'		=>	'z.feedback_type, z.feedback_name'
			);

			$sortby = $_REQUEST['Sortmeby'];
			$sby = $OrderBy[$sortby];

			if (empty ($sby)){
				$sql .= ' ORDER BY z.feedback_name';
			}else{
				$sql .= ' ORDER BY ' . $sby;
			}


			$result = $dbh->query($sql);
			

			// Blurb
			echo "<P class=trace>\n";
			echo "Column entries that are links will &quot;drill down&quot; to refine your query.\n";
			echo "</P>\n";

			//---------------------------------------------------
			// Spew table
			//---------------------------------------------------
			echo "<CENTER>\n";
			echo "<TABLE BORDER>\n";

            if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
				echo "<TH class=ths>Edit</TH>\n";		// SECURITY
			}

			echo "<TH class=ths>View</TH>\n";

			foreach ($ALLFIELD as $f) {
				if (in_array($f, $SHOW)) {
					echo "<TH class=ths>$fieldlabel[$f]</TH>\n";
				}
			}
			$row = array();

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";

				// Edit if authorized
				// SECURITY
                if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
					echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
					echo "<A HREF=$_SERVER[PHP_SELF]?feedback_id=$row[feedback_id]";
					echo "&Action=Edit>";
					echo "<IMG SRC=/images/smallballs/greenball.gif BORDER=0></A>";
					echo "</TD>\n";
				}
	
				// View for everyone
				echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
					echo "<A HREF=$_SERVER[PHP_SELF]?feedback_id=$row[feedback_id]";
					echo "&Action=View>";
					echo "<IMG SRC=/images/smallballs/yellowball.gif BORDER=0></A>";
				echo "</TD>\n";
	
				foreach ($ALLFIELD as $f) {
					$css = "tdcs";
					$display = stripslashes($row[$f]); 

					//
					// Display Exceptions (lookup)
					//
					//if ( $f  == 'feedback_id' ) {
						//$display = $row[feedback_name];
					//}

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
			$table = 'feedback';

			$dbh = ggsd_pdo_connect();

			$Default = array ();
			$Dude = array();
			$today = date('Y-m-d');

			if ( isset($_SESSION['people_id']) ){
				$sql = "SELECT * from people where people_id = ";
				$sql .= $dbh->quote($_SESSION['people_id']);
				$result = $dbh->query($sql);
				$Dude =  $result->fetch(PDO::FETCH_ASSOC);

        		$Default['contact_info']	=	$Dude['full_name'];
        		$Default['requester_id']	=	$Dude['people_id'];
        		$Default['requester_email']	=	$Dude['email_1'];
			}

			$Default['assignee_id']		=	'191';
        	$Default['feedback_state']	=	'New';
        	$Default['feedback_status']	=	'New';
        	$Default['date_created']	=	$today;

			$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);
			$fieldlabel['contact_info'] = "Name, Phone";

			echo "<CENTER>\n";
			
			// Blurb
			echo "<TABLE CELLPADDING=9>\n";
			echo "<TR><TD class=tdsc>\n";
            echo "Thanks for your willingness to improve GGSD Info with you suggestions and feedback!\n";
			echo "<BR>Please fill out the below form. Details on meanings and choice details are <BR>available via help links in left column.\n";
			echo "Asterisk (*) indicates field is required.\n";
			echo "<BR>More information about submitting feedback is available via the\n";
			echo "<A HREF=/help.php?table_name=feedback&field_name=Overview&Action=Help>overview</A>\n";
			echo "</TD></TR>\n";
			echo "</TABLE>\n";

			//
			// Data Entry Form
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			echo "<TH COLSPAN=2 class=ths>Feedback or Help Request</TH>\n";

			foreach ($NEWFIELD as $fieldname ) {
			    if ( $fieldname == 'feedback_id'){
                    continue;
                }
				$val = $fieldlabel[$fieldname];
				
				echo "<TR>\n";

                echo "<TD CLASS=tdls>";
				echo "<A HREF=/help.php?table_name=feedback&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$val</A>";
				if ( isset($RequiredField[$fieldname]) ){
					echo '&nbsp;*&nbsp;';
				}
				echo "</TD>\n";

				echo "<TD class=tdsm>";

				$what = '';
				$choose = 'Choose';

				if (isset($_REQUEST[$fieldname])){
					$what = $_REQUEST[$fieldname];
					$choose = $what;
				}else{
					if ( isset($Default[$fieldname]) ){
						$what = $Default[$fieldname];
						$choose = $what;
					}
				}

				if (array_key_exists($fieldname, $FieldType)) {
					if ( $FieldType[$fieldname] == "Menu" ) {
						$menusql = $Menu[$fieldname];
						$menulist = get_menu($menusql);
						sort($menulist);
						spew_select_menu($fieldname, $what,$choose,$menulist);
						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_${fieldname}>";
						}
					}

					if ($FieldType[$fieldname] == "MenuArray" ) {
						$menusql = $Menu[$fieldname];
						$menulist = get_menu_array($menusql);
						asort($menulist);
						$menulist['Choose'] = 'Choose';
						spew_select_hash_menu($fieldname, $what,$choose,$menulist);
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA COLS=50 ROWS=20 NAME=$fieldname>$what</TEXTAREA>\n";
					}

					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT SIZE=50 NAME=$fieldname VALUE=\"$what\">";
					}

					if ($FieldType[$fieldname] == "People" ) {
						spew_select_menu($fieldname, $what,$choose,$People);
					}

				}else{
					echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$what\">";
				}
				echo "</TD>\n";
			}
			echo "</TABLE>\n";

			echo "<p class=tdsc>We most appreciate feedback from humans.";
			echo "<BR>Please enter the numbers shown in the below 'captcha' image into the box below.</p>\n";
			echo "<p><img src='kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
			echo "<p><input type=text name=keystring></p>\n";

			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Submit Feedback\">\n";
			echo "</FORM>\n";
			echo "</CENTER>\n";
		}//End if ($_REQUEST['Action'] == "New" ) 

		//----------------------------------------------------------------------
	  	// Edit 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Edit") {

			$table='feedback';

			if ( array_key_exists('feedback_id', $_REQUEST)) {
				if ( isset($_REQUEST['feedback_id'] ) ) {
					$feedback_id = stripslashes( $_REQUEST['feedback_id']);
				}else{
					die ("NO Feedback ID in edit function.") ;
				}
				if ( ! is_numeric($feedback_id) ) {
					die ("Feedback ID ($feedback_id) is not an integer.") ;
				}
			}else{
				die ("No Feedback Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// People Rosterselect 
			//
			$People = array();
			$PeopleEmail = array();
			$sql = "SELECT people_id, first_name, last_name, nickname, email_1, full_name from people";
			// TODO: limit scope: $sql .= " WHERE ....";
			$result = $dbh->query($sql);

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				$pid = $row['people_id'];
                if ( isset ($People['nickname'])){
				    $People[$pid] = $row['nickname'] . " " . $row['last_name'];
                }else{
				    $People[$pid] = $row['first_name'] . " " . $row['last_name'];
                }
				//$People[$pid] = $row['full_name'];
				$PeopleEmail[$pid] = $row['email_1'];
			}

			$menulist = array();
			$row = array();

			$sql = 'SELECT * FROM feedback WHERE feedback_id = ';
			$sql .= $dbh->quote($feedback_id);
			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Links in left column field names show more information about that field and its choices.\n";
            if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
			    echo "<BR>Change desired entries and click <B>Update</B> at bottom of form.\n";
            }else{
			    echo "<BR>You do not have sufficient permissions to edit this form.\n";
            }
			echo "</P>\n";


			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				echo "<TR><TD VALIGN=TOP class=tdls>";
				echo "<A HREF=/help.php?table_name=feedback&field_name=$fieldname&Action=Help target=\"_blank\">";
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
							spew_select_menu($fieldname, $_REQUEST[$fieldname],'',$People);
						}

						if ( $FieldType[$fieldname] == "TextArea" ) {
							echo "<TEXTAREA NAME=$fieldname COLS=50 ROWS=20>$row[$fieldname]</TEXTAREA>\n";
						}

						if ( $FieldType[$fieldname] == "LongText" ) {
							echo "<INPUT TYPE=TEXT NAME=$fieldname SIZE=50 VALUE=\"$row[$fieldname]\">\n";
						}

					}else{	// No fieldtype
						echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$row[$fieldname]\"><BR>";
					}
						
				}//Endif NoEdit
				echo "</TD>\n";
			}//Endforeach fieldname
			echo "</TABLE>\n";

			// CAPTCHA
			//echo "<p>I prefer to work with humans, please enter the numbers shown in ";
			//echo " the below 'captcha' image into the box below:</p>\n";
			//echo "<p><img src='kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
			//echo "<p><input type=text name=keystring></p>\n";

			echo "<INPUT TYPE=HIDDEN NAME=feedback_id VALUE=$feedback_id>\n";
			// SECURITY
            if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
			}
			echo "</FORM>\n";
			echo "</CENTER>\n";

			show_journal_history($feedback_id, $table);

	  	}//if ($_REQUEST['Action'] == "Edit") 


		//----------------------------------------------------------------------
	  	// View
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "View"
	  		|| $_REQUEST['Action'] == "View Details" ) {

			$table = 'feedback';

			if ( array_key_exists('feedback_id', $_REQUEST)) {
				if ( isset ( $_REQUEST['feedback_id'] ) ) {
					$feedback_id = $_REQUEST['feedback_id'];
				}else{
					die ("No Feedback ID in view function") ;
				}
				if ( ! is_numeric($feedback_id) ) {
					die ("Feedback ID ($feedback_id) is not an integer.") ;
				}
			}else{
				die ("No Feedback Id Set") ;
			}

			$menulist = array();
			$row = array();

			$dbh = ggsd_pdo_connect();

			$sql = 'SELECT * FROM feedback WHERE feedback_id = ';
			$sql .= $dbh->quote($feedback_id);
			$result= $dbh->query($sql);

			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Field explanation available via link in left column field labels.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<H2>$row[feedback_name]</H2>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				echo "<TR>\n";
				echo "<TD class=tdls>";
				echo "<A HREF=/help.php?table_name=$table&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$label</A></TD>\n";
				echo "<TD class=tds>";

				$display = stripslashes($row[$fieldname]);

				//
				// View Entry Lookup Map Translations (id -> othertable.name for foreign keys)
				//
				//if ( $fieldname == "feedback_id" ) {
					//$sql = "SELECT feedback_name from othertable where feedback_id = '$row[$fieldname]'";
					//$display = get_value($sql);
				//}

				echo "$display<BR>";
				echo "</TD>\n";
			}//Endforeach fieldname
			echo "</TABLE>\n";
			echo "<INPUT TYPE=HIDDEN NAME=feedback_id VALUE=$feedback_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";

            if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Edit\">\n";
			}
			echo "</FORM>\n";

			//
			// Show Journal History
			//
			echo "<FORM ACTION=/journal.php TYPE=POST>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_id VALUE=$feedback_id>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_table VALUE=$table>\n";
			echo "<INPUT TYPE=HIDDEN NAME=journal_type VALUE=Note>\n";
			//echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Add Journal\">\n";
			echo "</FORM>\n";

			show_journal_history($feedback_id, $table);

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
		global $FDBKCFG;
        $list = array();
		$dbh = ggsd_pdo_connect();

		echo "<P class=trace>";
		echo "Help and explanations available at ";
		echo "<A HREF=/help.php?table_name=feedback&field_name=Overview&Action=Help target=\"_blank\">Overview</A>.";
		echo "</P>\n";

		echo "<CENTER>\n";
		echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
		echo "<TABLE BORDER>\n";
		echo "<TH class=ths>State</TH>\n";
		echo "<TH class=ths>Feedback Type</TH>\n";
		echo "<TH class=ths>Requester Type</TH>\n";
		echo "<TH class=ths>Sort By</TH>\n";

		echo "<TR>\n";

		// Name
		//echo "<TD class=tds>\n";
		//$sql = "SELECT DISTINCT feedback_summary from feedback order by feedback_summary";
		//$list = get_menu($sql);
		//spew_select_menu('feedback_summary','','All',$list);
		//echo "</TD>\n";

		// State
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT feedback_state from feedback order by feedback_state";
		$list = get_menu($sql);
		spew_select_menu('feedback_state','','All',$list);
		echo "</TD>\n";

		// Feedback Type 
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct feedback_type from  feedback order by feedback_type" ;
		$list = get_menu($sql);
		spew_select_menu('feedback_type','All','All',$list);
		echo "</TD>\n";

		// Requester Type 
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct requester_type from  feedback order by requester_type" ;
		$list = get_menu($sql);
		spew_select_menu('requester_type','All','All',$list);
		echo "</TD>\n";

		// Sort By

		// Sort By
		echo "<TD class=tds>\n";
		$sortby = array (
			'Name',
			'Requester Type',
			'Feedback Type',
			'State',
			'Date'
			);
		sort($sortby);
		spew_select_menu('Sortmeby','','Date',$sortby);
		echo "</TD>\n";

		// End Table

		echo "</TR>\n";
		echo "</TABLE>\n";

		// End Form
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";

		// SECURITY
        if ( check_access($FDBKCFG['APPTAG'], $FDBKCFG['EDITLEVEL'])){
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"New\">\n";
		}
		echo "</FORM>\n";
		echo "</CENTER>\n";
	}

	//----------------------------------------------------------------
	// Function send_email_ack
	//----------------------------------------------------------------
	function send_email_ack ( array $Data ) {
		global $GGSDCFG;
		//
		// Default config parameters for email acknowledgement
		//
		$LOGINEMAIL = array (
			'subject'		=>	"GGSD Info Feedback", 
			'fromemail'		=>	"info@haywarddanceclub.com",
			'toemail'		=>	"jerbowes@yahoo.com",
			'bccemail'		=>	"jerbowes@gmail.com",
			'fromname'		=>	"GGSD Info" 
		);

		$LOGINEMAIL['subject'] = 'GGSD: ' . $Data['feedback_summary'];

		if ( $Data['requester_email'] ) {
			$LOGINEMAIL['toemail'] = $Data['requester_email'];
			echo "<CENTER>\n";
			echo "<H2>Acknowledgement has been emailed to $Data[requester_email]</H2>\n";
			echo "</CENTER>\n";
		}


		$fd = popen($GGSDCFG[MAILER],"w"); 
		//
		// Construct Mail Headers
		//
		fputs($fd, "From: $LOGINEMAIL[fromname] <$LOGINEMAIL[fromemail]>\n"); 
		fputs($fd, "To: $LOGINEMAIL[toname] <$LOGINEMAIL[toemail]>\n"); 
		fputs($fd, "Bcc: $LOGINEMAIL[bccemail]\n"); 

		if ( $Data[cc_list] ) {
			fputs($fd, "Cc: $Data[cc_list]\n"); 
			echo "<CENTER>\n";
			echo "<H2>A copy has also been sent to  $Data[cc_list]</H2>\n";
			echo "</CENTER>\n";
		}
		//
		// Subject
		//
		fputs($fd, "Subject: $LOGINEMAIL[subject]\n\n"); 

		fputs($fd, "This is an acknowledgement of your feedback from our website.\n\n");
		$fieldlabel = get_field_labels('feedback','feedback',$GGSDCFG['DBNAME']);

		$SHOWFIELD = array(
        	'feedback_category',
        	'feedback_type',
        	'requester_type',
        	'feedback_summary',
        	'feedback_detail',
		);

		foreach ($SHOWFIELD as $f){
			if ( isset($Data[$f] ) ) {
				fputs($fd, "$fieldlabel[$f] :  $Data[$f]\n\n");
			}
		}

		//
		// Body
		//
		fputs($fd, "We will address your question or issue very soon, thanks.\n\n");
		fputs($fd, "Please do not reply to this email.\n");

		pclose($fd); 
	}//End function send_email_ack

	//----------------------------------------------------------------
	// Function now_what
	//----------------------------------------------------------------
	function now_what () {
		global $GGSDCFG;
		echo "<CENTER>\n";
		echo "<TABLE>\n";
		echo "<TH class=ths>Related links that might interest you.</TH>\n";
		echo "<TR><TD>";
		echo "<UL>\n";
		echo "<LI><A HREF=/faq.php>GGSD Info FAQ</A></LI>\n";
		echo "<LI><A HREF=http://goldengatedancers.org>Golden Gate Smooth Dancers in South San Francisco</A></LI>\n";
		echo "</UL>\n";
		echo "</TD></TR></TABLE>\n";
		echo "</CENTER>\n";
	}//End function now_what
	//----------------------------------------------------------------
	// END FUNCTIONS
	//----------------------------------------------------------------
		
?>
