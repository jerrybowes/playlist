<?php
	//#==================================================================
	//# GGSD FAQ
	//# Jerry Bowes, jerbowes@yahoo.com
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/faq.php,v $
	//# $Id: faq.php,v 1.1 2022/12/12 16:43:54 jbowes Exp $
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

   	if (session_status() == PHP_SESSION_NONE) {
       	session_start();
   	}

	//------------------------------------------------------------------------
	// Formatting and navbar options for looknfeel-inc header/footer functions
	//------------------------------------------------------------------------
	//
	$FMT = array (
		'BANNER'		=>	"GGSD Info FAQ",
		'BANNER2'		=>	"Frequently asked questions (and answers!)",
		'TITLE'			=>	"GGSD Info FAQ",
		'MODULENAME'	=>	"faq.php",
		'NAV1'			=>	"INFO"	// Level 1 menu navigation group
	);

	//------------------------------------------------------------------------
	// Local configuration parameters
	//------------------------------------------------------------------------
	$FAQCFG = array (
		'EDITLEVEL'		=>	'5'			// Access level to get edit screen
	);

	//------------------------------------------------------------------------
	// Database Fields
	//------------------------------------------------------------------------
	$ALLFIELD = array(
		'faq_id',
		'faq_class',
		'faq_topic',
		'faq_category',
		'faq_state',
		'faq_type',
		'faq_subcategory',
		'faq_audience',
		'faq_keywords',
		'faq_summary',
		'assignee_id',
		'shelf_life',
		'more_info',
		'last_modified',
		'faq_content'
	);

	//
	//	Fields visible in query output list
	//
	$SHOW = array(
		'faq_topic',
		'faq_category',
		'faq_class',
		'faq_audience',
		//'last_modified',
		//'faq_type'
		'faq_summary',
	);

	//
	// Fields that can have query drill down links on display
	//
	$LINK = array(
		'faq_type',
		'faq_class',
		'faq_audience',
		'faq_category',
		'faq_topic'
	);
	//
	// Fields that are from a Menu Picklist that can have new members
	//
	$EXTEND = array(
		'faq_type',
		'faq_category',
		'faq_topic'
	);

	//
	// Required for New Entry
	//
	$RequiredField = array(
		'faq_summary'	=>	'enter faq summary',
		'faq_content'	=>	'enter faq content in html',
		'assignee_id'	=>	'choose name of author or maintainer',
		'faq_class'		=>	'select class from list',
		'faq_type'		=>	'select type from list or enter new faq type'
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
		'faq_id',
		'last_modified'
	);

	$FieldType = array(
		'assignee_id'		=>	'MenuArray',
		'faq_audience'		=>	'Menu',
		'faq_category'		=>	'Menu',
		'faq_class'			=>	'Menu',
		'faq_content'		=>	'TextArea',
		'faq_keywords'		=>	'LongText',
		'faq_state'			=>	'Menu',
		'faq_summary'		=>	'LongText',
		'faq_topic'			=>	'Menu',
		'faq_type'			=>	'Menu',
		'shelf_life'		=>	'MenuArray'
	);

	$BASE = "SELECT choice FROM menu WHERE table_name = 'faq' AND ";
	$LBASE = "SELECT choice, description FROM menu WHERE table_name = 'faq' AND ";

	$Menu = array(
		"assignee_id"	=> "SELECT people_id, full_name from people order by last_name, first_name",
		"faq_type"		=> "SELECT DISTINCT faq_type from faq order by faq_type",
		"faq_state"		=> "$BASE field_name = 'faq_state' order by choice",
		"faq_topic"		=> "SELECT distinct faq_topic from faq order by faq_topic",
		"faq_category"	=> "SELECT distinct faq_category from faq order by faq_category",
		"faq_audience"	=> "$BASE field_name = 'faq_audience' order by choice",
		"shelf_life"	=> "$LBASE field_name = 'shelf_life' order by choice",
		"faq_class"		=> "$BASE field_name = 'faq_class' order by choice"
	);

	//
	// Display exceptions from default tdcs centered display table cell
	//
	$JustifyCss = array(
		'faq_audience'		=>	'tds',
		'faq_summary'		=>	'tds',
		'faq_topic'			=>	'tds',
		'faq_category'		=>	'tds',
		'faq_type'			=>	'tds',
		'faq_class'			=>	'tds',
	);

	//------------------------------------------------------------------------
	// BEGIN Program
	//------------------------------------------------------------------------

	spew_header($FMT);

	if (!array_key_exists('Action', $_REQUEST)) {
		//$dbh = ggsd_pdo_connect();
		//$sql = "SELECT count(*) from faq";
		//$faq_count = get_value($sql);
		//if ( $faq_count > 12) {
			//$_REQUEST['Action'] = 'List';
		//}else{
			$_REQUEST['Action'] = 'ShowAll';
		//}
	}

	if (array_key_exists('Action', $_REQUEST)) {


		if ($_SESSION['access_level'] >=5 ){
			spew_query_form();
		}

		//----------------------------------------------------------------------
	  	//  Show: User read version
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Show") {

			if (! array_key_exists('faq_id', $_REQUEST)) {
				die ("No FAQ Id Set in Show function") ;
			}else{
				$faq_id = $_REQUEST['faq_id'];
			}
			if (! is_numeric( $faq_id ) ) {
				die ("ERROR: Attempt to update Faq requires faq_id to be integer. It is not.");
			}

			$dbh = ggsd_pdo_connect();

			$fieldlabel = array ();
			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);

			$sql = "SELECT f.*, p.full_name FROM faq f, people p ";
			$sql .= " WHERE faq_id = '$faq_id'";
			$sql .= " AND f.assignee_id = p.people_id ";

			$result = $dbh->query($sql);
			$row =  array();
			$row = $result->fetch(PDO::FETCH_ASSOC);

			echo "<CENTER>\n";
			echo "<P>&nbsp;</P>\n";
			echo "<TABLE BORDER=0 CELLPADDING=5 CELLSPACING=5>\n";
			//echo "<TH COLSPAN=2>Categorization Summary</TH>\n";
			$viewlist = array (
				'faq_class',
				'faq_category',
				//'faq_subcategory',
				'faq_type'
				//'faq_type',
				//'faq_audience',
				//'last_modified'
				);

			foreach ($viewlist as $f) {
				echo "<TH class=ths>$fieldlabel[$f]</TH>\n";
			}

			echo "<TR>";
			foreach ($viewlist as $f) {
				if (isset( $row[$f] )) {
					$display = $row[$f];
					if ( $f == 'assignee_id' ) {
						$aid = $row[$f];
						$display = $row['full_name'];
					}
					echo "<TD class=tds><A HREF=$_SERVER[PHP_SELF]?$f=";
					echo urlencode($row[$f]); 
					echo "&Action=List><B>$display</B></A></TD>\n";
				}
			}//Endforeach ($viewlist as $f) {

			echo "</TABLE>\n";
			echo "<P>\n";

			echo "<TABLE  WIDTH=50% CELLPADDING=5 CELLSPACING=4>\n";
			//echo "<TABLE BORDER=1 WIDTH=50% CELLPADDING=5 CELLSPACING=4>\n";
			echo "<TH>$row[faq_summary]</TH>\n";
			echo "<TR>\n";
			echo "<TD COLSPAN=2>\n";
			echo "$row[faq_content]";
			echo "</TD>\n";
			echo "</TABLE>\n";
			echo "</P>\n";

			echo "</CENTER>\n";
	  	}//Endif ($_REQUEST['Action'] == "Show")
	  	//End Show


		//----------------------------------------------------------------------
	  	// Insert New Entry
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Insert New Entry" ) {
			$dbh = ggsd_pdo_connect();
			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabel);

			//
			// Define default values
			//
			$Default = array (
				'faq_state'	 =>  'New'
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
					if (in_array($_REQUEST[$altkey], $InValidChoice)) {
						unset ($_REQUEST[$altkey]);
					}else{
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
			unset ($_REQUEST['faq_id']);

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

			$InsertData = array();
			$sql = 'INSERT INTO faq (';
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					$sql .= $f . ',';
				}
			}
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					$InsertData[$f] = $val;
					$val = $dbh->quote($_REQUEST[$f]);
					$sql2 .=  $val . ',';
				}
			}
			// TODO SECURITY : prepare, etc for sql injection

			$finalsql = rtrim($sql, ",") .  ') VALUES (' .  rtrim($sql2, ",") . ")";
			//$result = $dbh->query($finalsql);
            //$_REQUEST['faq_id'] = $dbh->lastInsertId() ;
            $_REQUEST['Action'] = 'View';

            $_REQUEST['faq_id'] = insertArray('faq',$InsertArray);


			echo "<CENTER>\n";
			echo "<H2>Record successfully added</H2>\n";
			echo "</CENTER>\n";
		}

		//----------------------------------------------------------------------
	  	// Update Existing Entry 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Update" ) {

			if ( array_key_exists('faq_id', $_REQUEST)) {
				$faq_id = $_REQUEST['faq_id'];
				if (! is_numeric( $faq_id ) ) {
					die ("ERROR: Attempt to update Faq requires faq_id to be integer. It is not.");
				}
			}else{
				die ("No Faq Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// Get Original Record
			//
			$Original = array();
			$sql = "SELECT * FROM faq WHERE faq_id = '$faq_id'";
			$result = $dbh->query($sql);

			$Original = $result->fetch(PDO::FETCH_ASSOC);


			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('faq','aaaaaaaaaa',$GGSDCFG['DBNAME']);


			$fields = array_keys($fieldlabel);

			//
			// Eliminate all keys that have invalid answers
			// Overwrite entries with NEW_... entries for Open Menus
			//
			foreach ($fields as $f) {
				$altkey = "NEW_" . $f;

				if (isset($_REQUEST[$altkey])) {

					if (in_array($_REQUEST[$altkey], $InValidChoice)) {
						unset ($_REQUEST[$altkey]);
					}else{
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
			$sql = 'UPDATE faq SET ';
			$sqlentry = array ();
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					$val = $_REQUEST[$f];

					if ( $_REQUEST[$f] != $Original[$f] ) {
						$val = $dbh->quote($_REQUEST[$f]);
						$sqlentry[] = $f . " = " . $val ;
					}
				}
			}

			if (count($sqlentry) > 0){
				$sql .= implode (', ', $sqlentry);
				$sql .= " WHERE faq_id = '$faq_id'";


				$result = $dbh->query($sql);
				echo "<H3>Update successful</H3>\n";
			}else{
				echo "<H3>No Changes Made</H3>\n";
			}

			$_REQUEST['faq_id'] = $faq_id;
			$_REQUEST['Action'] = "View";
			//$_REQUEST['Action'] = "Show";
		}

		//----------------------------------------------------------------------
	  	//  ShowAll
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "ShowAll") {

			$Where = array();

			$dbh = ggsd_pdo_connect();

			$sql = "SELECT * from faq ";

			if ( isset($_SESSION['access_level']) ){
				if ( $_SESSION['access_level'] < $FAQCFG['EDITLEVEL']){
					$Where[] = "faq_audience != 'BOD'" ;
				}
			}else{
				$Where[] = "faq_access = 'Public'";
			}
			if (sizeof($Where) > 0) {
				$sql .= ' WHERE ' . implode(' AND ', $Where);
			}
			$sql .= " order by faq_audience, faq_id";


			$result = $dbh->query($sql);


			//---------------------------------------------------
			// Spew table
			//---------------------------------------------------
			echo "<CENTER>\n";

			echo "<TABLE WIDTH=70%>\n";
			echo "<TR><TD>\n";
			$old_audience = 'All';
			echo "<H4>General</H4>\n";
			echo "<OL>\n";

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				if ( $old_audience != $row['faq_audience'] ){
					if ( isset ($old_audience)){
						echo "</OL>";
					}
					echo "<H4>$row[faq_audience]</H4>\n";
					echo "<OL>\n";
					$old_audience = $row['faq_audience'];
				}
				$faqid = $row['faq_id'];
				echo "<LI><A HREF=/faq.php?faq_id=$faqid&Action=Show>$row[faq_summary]</A></LI>\n";
			}

			echo "</OL>\n";
			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";


			$result = $dbh->query($sql);

			echo "<TABLE WIDTH=70%>\n";
			echo "<TR><TD>\n";

			$old_audience = '';
			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				if ( $old_audience != $row['faq_audience'] ){
					echo "<H4>$row[faq_audience]</H4>\n";
					$cnt=0;
					$old_audience = $row['faq_audience'];
				}
				$faqid = $row['faq_id'];
				$cnt++;
				echo "<P><B>${cnt}.&nbsp;" . "$row[faq_summary]</B></P>\n";
				echo "$row[faq_content]\n";
			}

			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";

			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "ShowAll"))  

		//----------------------------------------------------------------------
	  	//  List
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "List"
	  		|| $_REQUEST['Action'] == "List Summary" ) {


			$dbh = ggsd_pdo_connect();
			$fieldlabel = array();

			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);
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
				'f.*', 
				'p.full_name'
			);

			$WhereVal = array();

			$Where = array(
				'f.assignee_id = p.people_id'
			);

			$From = array(
				'faq'		=>	'f',
				'people'	=>	'p'
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
						$Where[] = "f." . $f . '= ' . $dbh->quote($_REQUEST[$f]) ;
					}
				}
			}
			// 
			// SECURITY
			// 
			if ( isset($_SESSION['access_level'] )){
				if ( $_SESSION['access_level'] < $FAQCFG['EDITLEVEL']){
					$Where[] = "faq_audience != 'BOD'" ;
				}
			}else{
				$Where[] = "faq_access = 'Public'";
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
				'Author'		=>	'p.nickname, f.faq_summary',
				'Category'		=>	'f.faq_category, f.faq_summary',
				'Audience'		=>	'f.faq_audience, f.faq_summary',
				'Name'			=>	'f.faq_summary',
				'Topic'			=>	'f.faq_topic, f.faq_summary',
				'Type'			=>	'f.faq_type, f.faq_summary'
			);

			$sortby = $_REQUEST['Sortmeby'];
			$sby = $OrderBy[$sortby];

			if (empty ($sby)){
				$sql .= ' ORDER BY f.faq_summary';
			}else{
				$sql .= ' ORDER BY ' . $sby;
			}

			$result = $dbh->query($sql);

			// Blurb
			echo "<CENTER>\n";
			echo "<P>\n";
			echo "Column entry links will &quot;drill down&quot; to refine your query.\n";
			echo "<BR>Click on summary link to see entire FAQ.\n";
			echo "</P>\n";

			//---------------------------------------------------
			// Spew table
			//---------------------------------------------------
			echo "<TABLE BORDER>\n";

			if ($_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {
				echo "<TH class=ths >Edit</TH>\n";		// SECURITY
				echo "<TH  class=ths>View</TH>\n";
			}

			foreach ($ALLFIELD as $f) {
				if (in_array($f, $SHOW)) {
					echo "<TH class=ths>$fieldlabel[$f]</TH>\n";
				}
			}

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				$css = "tdc";
				echo "<TR>\n";

				// Edit if authorized
				// SECURITY
				if ($_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {	
					echo "<TD ALIGN=CENTER VALIGN=TOP class=$css>";
					echo "<A HREF=$_SERVER[PHP_SELF]?faq_id=$row[faq_id]";
					echo "&Action=Edit>";
					echo "<IMG SRC=/images/smallballs/greenball.gif BORDER=0></A>";
					echo "</TD>\n";
	
					// View
					echo "<TD ALIGN=CENTER VALIGN=TOP class=$css>";
					echo "<A HREF=$_SERVER[PHP_SELF]?faq_id=$row[faq_id]";
					echo "&Action=View>";
					echo "<IMG SRC=/images/smallballs/yellowball.gif BORDER=0></A>";
					echo "</TD>\n";
				}
	
				foreach ($ALLFIELD as $f) {
					$css = "tdc";
					$display = stripslashes($row[$f]); 

					//
					// Display Exceptions (lookup)
					//
					if ( $f  == 'faq_summary' ) {
						$display = "<A HREF=$_SERVER[PHP_SELF]?faq_id=$row[faq_id]&Action=Show>";
						$display .= "$row[$f]</A>\n";
					}
					if ( $f  == 'maintainer_id' ) {
						$display = $row['full_name'];
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

			if (isset($_SESSION['access_level'])){
				if ( $_SESSION['access_level'] < $FAQCFG['EDITLEVEL'] ) {
					echo "<P class=trace>Not logged in or sufficiently privileged to edit</P>\n";
					spew_footer($FMT);
					exit;
				}
			}else{
				echo "<P class=trace>Not logged in. Edit functions require login.</P>\n";
				spew_footer($FMT);
				exit;
			}

			$menulist = array();

			$dbh = ggsd_pdo_connect();

			// Blurb
			echo "<P class=trace>\n";
			echo "Enter entries. Details on meanings and choice details available via help links in left column.\n";
			echo "Asterisk (*) indicates field is required.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);


			foreach ($ALLFIELD as $fieldname ) {
			    if ( in_array($fieldname, $NoEdit)){
                    continue;
                }
				$val = $fieldlabel[$fieldname];
				
				echo "<TR>\n";

                echo "<TD CLASS=tdls>";
				echo "<A HREF=/help.php?table_name=faq&field_name=$fieldname&Action=Help target=\"_blank\">";
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
						$menulist[] = 'Choose';
						spew_select_menu($fieldname, 'Choose','Choose',$menulist);

						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_${fieldname}>";
						}
					}

					if ($FieldType[$fieldname] == "MenuArray" ) {
						$menusql = $Menu[$fieldname];
						$menulist = get_menu_array($menusql);
						$menulist['Choose'] = 'Choose';
						spew_select_hash_menu($fieldname, 'Choose','Choose',$menulist);
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA COLS=70 ROWS=50 NAME=$fieldname></TEXTAREA>\n";
					}

					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT SIZE=70 NAME=$fieldname>";
					}

					if ($FieldType[$fieldname] == "People" ) {
						spew_select_menu($fieldname, $_REQUEST[$fieldname],'',$People);
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
			// SECURITY
			if (isset ($_SESSION['access_level'])){
				if ( $_SESSION['access_level'] < $FAQCFG['EDITLEVEL'] ) {
					echo "<P class=trace>Not logged in or privileged to edit</P>\n";
					spew_footer($FMT);
					exit;
				}
			}else{
				echo "<P class=trace>Not logged in. Edit functions require login.</P>\n";
				spew_footer($FMT);
				exit;
			}


			if ( array_key_exists('faq_id', $_REQUEST)) {
				if ( isset($_REQUEST['faq_id'] ) ) {
					$faq_id = stripslashes( $_REQUEST['faq_id']);
				}else{
					die ("NO Faq ID in edit function.") ;
				}
				if ( ! is_numeric($faq_id) ) {
					die ("Faq ID ($faq_id) is not an integer.") ;
				}
			}else{
				die ("No Faq Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// People Rosterselect 
			//
			$People = array();
			$sql = "SELECT people_id, full_name from people order by last_name, first_name";
			$People = get_menu_array();

			$menulist = array();

			$sql = "SELECT * FROM faq WHERE faq_id = '$faq_id'";
			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Change desired entries and click <B>Update</B> at bottom of form.\n";
			echo "</P>\n";


			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				echo "<TR><TD VALIGN=TOP class=tdls>";
				echo "<A HREF=/help.php?source_table=faq&field_name=$fieldname&Action=Help target=\"_blank\">";
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
			echo "<INPUT TYPE=HIDDEN NAME=faq_id VALUE=$_REQUEST[faq_id]>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=View>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Show>\n";

			// SECURITY
			if ( $_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
			}

			echo "</FORM>\n";
			echo "</CENTER>\n";

			show_journal_history($faq_id, 'faq');

	  	}//if ($_REQUEST['Action'] == "Edit") 


		//----------------------------------------------------------------------
	  	// View
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "View"
	  		|| $_REQUEST['Action'] == "View Details" ) {


			if ( array_key_exists('faq_id', $_REQUEST)) {
				if ( isset ( $_REQUEST['faq_id'] ) ) {
					$faq_id = $_REQUEST['faq_id'];
				}else{
					die ("No Faq ID in view function") ;
				}
				if ( ! is_numeric($faq_id) ) {
					die ("Faq ID ($faq_id) is not an integer.") ;
				}
			}else{
				die ("No Faq Id Set") ;
			}

			$menulist = array();
			$row = array();

			$dbh = ggsd_pdo_connect();

			$sql = "SELECT f.*, p.full_name FROM faq f, people p ";
			$sql .= " WHERE f.faq_id = '$faq_id'";
			$sql .= " AND f.assignee_id = p.people_id";

			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('faq','faq',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Field explanation available via link in left column field labels.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<H2>$row[summary]</H2>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFIELD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				echo "<TR>\n";
				echo "<TD class=tdls>";
				echo "<A HREF=/help.php?source_table=faq&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$label</A></TD>\n";
				echo "<TD class=tds>";

				$display = stripslashes($row[$fieldname]);

				//
				// View Entry Lookup Map Translations (id -> othertable.name for foreign keys)
				//
				if ( $fieldname == "assingee_id" ) {
					$display = "<A HREF=/people.php?people_id=$row[assignee_id]&Action=View>$row[full_name]</A";
				}

				echo "$display<BR>";
				echo "</TD>\n";
			}//Endforeach fieldname
			echo "</TABLE>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Show\">\n";
			echo "<INPUT TYPE=HIDDEN NAME=faq_id VALUE=$faq_id>\n";
			if ( $_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Edit\">\n";
			}
			echo "</FORM>\n";

			//
			// Show Journal History
			//
			echo "<FORM ACTION=/journal.php TYPE=POST>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_id VALUE=$faq_id>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_table VALUE=faq>\n";
			echo "<INPUT TYPE=HIDDEN NAME=journal_type VALUE=Note>\n";
			if ( $_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Add Journal\">\n";
			}
			echo "</FORM>\n";

			show_journal_history($faq_id, 'faq');

			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "View")

		//----------------------------------------------------------------------
		// END 'Action' Processing Options
		//----------------------------------------------------------------------

	}else{	// No Action Field
		if ($_SESSION['access_level'] >=5 ){
			spew_query_form();
		}
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
		echo "<A HREF=/help.php?table_name=faq&field_name=Overview&Action=Help target=\"_blank\">Overview</A>.";
		echo "</P>\n";

		echo "<CENTER>\n";
		echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
		echo "<TABLE BORDER>\n";
		echo "<TH class=ths>Category</TH>\n";
		echo "<TH class=ths>Class</TH>\n";
		echo "<TH class=ths>Topic</TH>\n";
		echo "<TH class=ths>Type</TH>\n";
		echo "<TH class=ths>Sort By</TH>\n";

		echo "<TR>\n";

		// Category
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct faq_category from  faq order by faq_category" ;
		$list = get_menu($sql);
		spew_select_menu('faq_category','All','All',$list);
		echo "</TD>\n";

		// Class
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct faq_class from  faq order by faq_class" ;
		$list = get_menu($sql);
		spew_select_menu('faq_class','All','All',$list);
		echo "</TD>\n";

		// Topic
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct faq_topic from  faq order by faq_topic" ;
		$list = get_menu($sql);
		spew_select_menu('faq_topic','All','All',$list);
		echo "</TD>\n";


		// Type 
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct faq_type from  faq order by faq_type" ;
		$list = get_menu($sql);
		spew_select_menu('faq_type','All','All',$list);
		echo "</TD>\n";


		// Author
		//echo "<TD class=tds>\n";
		//$sql = "SELECT p.people_id, p.nickname from people p, faq f";
		//$sql .= " WHERE p.people_id = f.assignee_id";
		//$list = get_menu_array($sql);
		//spew_select_hash_menu('assignee_id','All','All',$list);
		//echo "</TD>\n";

		// Sort By
		echo "<TD class=tds>\n";
		$sortby = array (
			'Name',
			'Topic',
			'Category',
			'Audience',
			'Type'
			);
		sort($sortby);
		spew_select_menu('Sortmeby','','Topic',$sortby);
		echo "</TD>\n";

		// End Table

		echo "</TR>\n";
		echo "</TABLE>\n";

		// End Form
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";

		// SECURITY
		if ($_SESSION['access_level'] >= $FAQCFG['EDITLEVEL'] ) {
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"New\">\n";
		}
		echo "</FORM>\n";
		echo "</CENTER>\n";
	}


	//----------------------------------------------------------------
	// END FUNCTIONS
	//----------------------------------------------------------------
		
?>
