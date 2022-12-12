<?php
	//#==================================================================
	//# Help
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/help.php,v $
	//# $Id: help.php,v 1.2 2022/12/12 19:15:20 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	require_once("./include/ggsd-config-inc.php");
	require_once("./include/ggsd-auth-inc.php");
	require_once("./include/ggsd-looknfeel-inc.php");
	require_once("./include/ggsd-msutils-inc.php");
	require_once("./include/ggsd-session-inc.php");
	require_once("./include/ggsd-journal-inc.php");

	//--------------------------------------------------------------------------
	// If you are not authenticated (no people_id in $_SESSION), 
	// Construct return url and redirect to login for authentication
	//--------------------------------------------------------------------------
	//
	global $GGSDCFG;

   	if (session_status() == PHP_SESSION_NONE) {
       	session_start();
   	}

    //
	//--------------------------------------------------------------------------
    // Look and feel formatting
	//--------------------------------------------------------------------------
    //
	$FMT = array (
		'BANNER'		=>	'Help',
		'BANNER2'		=>	'Explanations and notes about the website',
		'TITLE'			=>	'Help',
		'MODULENAME'	=>	'help.php',
		'NAV1'			=>	'HELP'
	);

	$HELPCFG = array(
		'ADMINLEVEL'	=>	'4'
	);

	//--------------------------------------------------------------------------
	// BEGIN Field Definitions, Arrays, Variables, etc
	//--------------------------------------------------------------------------
	$ALLFIELD = array(
		'help_id',
		'meta_id',
		'contact_id',
		'language',
		'topic',
		'subtopic',
		'application',
		'context',
		'module',
		'keywords',
		'sequence',
		'level',
		'table_name',
		'field_name',
		'help_type',
		'short_help',
		'long_help',
		'summary',
		'last_modified'
	);

	$SHOW = array(
		'topic',
		'subtopic',
		'application',
		'context',
		'module',
		'table_name',
		'field_name',
		'help_type',
		'summary'
	);

	// Fields that can have query drill down links on display
	$LINK = array(
		'contact_id',
		'language',
		'topic',
		'subtopic',
		'application',
		'module',
		'table_name',
		'field_name',
		'help_type'
	);

	// Fields that are from a Menu Picklist that can have new members
	$EXTEND = array(
		'help_type',
		'subtopic'
	);

	// Required for New Entry
	$RequiredField = array(
		'contact_id'	=>		'enter contact id',
		'language'		=>		'enter language, default is US_English',
		'topic'			=>		'enter topic',
		'application'	=>		'enter application',
		'context'		=>		'enter context',
		'module'		=>		'enter php module name',
		'summary'		=>		'enter help summary',
		'help_type'		=>		'enter help type',
		'short_help'	=>		'enter short version or quick help',
		'long_help'		=>		'enter long version or detailed help'

	);
	// Global query choices
	$InValidChoice = array(
		'All',
		'None',
		'',
		' ',
		'Choose'
	);
	// Edit record fields with edit disabled
	$NoEdit = array(
		'help_id',
        'last_modified'
	);

	$FieldType = array(
		'help_type'		=>	'Menu',
		'language'		=>	'Menu',
		'contact_id'	=>	'People',
		'keywords'		=>	'LongText',
		'summary'		=>	'LongText',
		'short_help'	=>	'TextArea',
		'long_help'		=>	'TextArea'
	);

	$MenuBase = "SELECT choice from menu where table_name = 'help' ";
	$Menu = array(
		"help_type" => "$MenuBase AND field_name = 'help_type'",
		"language" => "$MenuBase AND field_name = 'language'"
	);

	// Display exceptions from default tdcs centered display table cell
	$JustifyCss = array(
		'summary',	'tds'	// small left justified
	);
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	spew_header($FMT);

	if (array_key_exists('Action', $_REQUEST)) {

		//----------------------------------------------------------------------
	  	// Insert New Entry
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Insert New Entry" ) {
			// Get list of fields for this table
			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabels);

			// Eliminate all keys that have invalid answers
			foreach ($fields as $f) {
				$altkey = "NEW_" . $f;
				if (in_array($_REQUEST[$f], $InValidChoice)) {
					unset ($_REQUEST[$f]);
				}
				if (in_array($_REQUEST[$altkey], $InValidChoice)) {
					unset ($_REQUEST[$altkey]);
				}else{
					if ( isset($_REQUEST[$altkey])){
						$_REQUEST[$key] = $_REQUEST[$altkey];
						unset ($_REQUEST[$altkey]);
					}
				}
			}

			// Delete auto_increment primary keys
			unset ($goodfields['help_id']);


			// Requred fields gauntlet
			foreach ($RequiredField as $key => $val) {
				if (! array_key_exists($key, $goodfields)) {
					$err .= '<LI>Please ' . $RequiredField[$key] . '.</LI>';
				}
			}
			if ( $err ) {
				echo "<CENTER>\n";
				echo "<H3>Incomplete Information</H3>\n";
				echo "<TABLE id=error_form_incomplete BORDER CELLPADDING=5>\n";
				echo "<TR><TD><UL>$err</UL></TD></TABLE>\n";
				echo "</TABLE>\n";
				echo "</CENTER>\n";
				spew_footer($FMT);
				exit;
			}

			$sql = 'INSERT INTO help (';
			foreach ($fields as $f) {
				if (isset($_REQUEST[$f] )){
					$sql .= $f . ',';
				}
			}
			foreach ($fields as $f) {
				if (isset($_REQUEST[$f] )){
					$val = $dbh->quote($_REQUEST[$f]);
					$sql2 .=  $val . ',';
				}
			}
			//chop($sql);
			$finalsql = rtrim($sql, ",") .  ') VALUES (' .  rtrim($sql2, ",") . ")";
			echo "<p class=trace>$finalsql</p>\n";	//DEBUG
			$result = $dbh->query($finalsql);

			echo "<CENTER>\n";
			echo "<H2>Record successfully added</H2>\n";
			echo "</CENTER>\n";
		}

		//----------------------------------------------------------------------
	  	// Update Existing Entry
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Update" ) {

			if (! array_key_exists('help_id', $_REQUEST)) {
				die ("No Help record Id Set") ;
			}else{
				$help_id = $_REQUEST['help_id'];
			}

			if (! is_numeric($help_id)){
				die ("No Help record id is non numeric in update function") ;
			}

			$dbh = ggsd_pdo_connect();

			// Get list of fields for this table
			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabels);


			//
			// Query Original for comparision, only update deltas
			//
			$sql = "SELECT * from help WHERE help_id = " . $dbh->quote($help_id);
			$result = $dbh->query($sql);
			$Original = array();
			$Original = $result->fetch(PDO::FETCH_ASSOC);


			//
			// Eliminate all keys that have invalid answers
			//
			foreach ($fields as $f) {
				$altkey = "NEW_" . $f;
				if ( isset($_REQUEST[$altkey])){
					if (!in_array($_REQUEST[$altkey], $InValidChoice)) {
						$_REQUEST[$key] = $_REQUEST[$altkey];
						unset($_REQUEST[$altkey]);
					}
				}

				if ( in_array($_REQUEST[$f], $InValidChoice) ) {
					unset ($_REQUEST[$f]);
				}
			}

			$sql = 'UPDATE help SET ';
			$sqlentry = array ();
			$modcnt = count($sqlentry);
			echo "<p class=trace>Initial modcount: $modcnt</p>\n";	//DEBUG

			foreach ($fields as $f) {
				if ( isset($_REQUEST[$f]) ){
					if ( $Original[$f] != $_REQUEST[$f] ){
						$val = $dbh->quote($_REQUEST[$f]);
						$sqlentry[] =   $f . " = " . $val ;
					}
				}
			}
			$modcnt = count($sqlentry);
			echo "<p class=trace>Final  modcount: $modcnt</p>\n";	//DEBUG
			if ( $modcnt != 0) {
				$sql .= implode (', ', $sqlentry);
				$sql .= " WHERE help_id = " . $dbh->quote($help_id);
				echo "<p class=trace>$sql</p>\n";	//DEBUG

				$result = $dbh->query($sql);
				echo "<H3>Update successful</H3>\n";
			}else{
				echo "<H3>No changes detected.</H3>\n";
			}

			$_REQUEST['Action'] = "View";
			$_REQUEST['help_id'] = $help_id;
		}

		//----------------------------------------------------------------------
	  	// Targeted Help Display
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Help") {

            global $GGSDCFG;
			$dbh = ggsd_pdo_connect();

            //
            // Get Field Labels
            //
			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabels);

            //
            // Build Query
            //
			$sql = 'SELECT * FROM help ';
			$where = array();

			foreach ($fields as $f) {
				if (array_key_exists($f, $_REQUEST)) { 
					if (isset($_REQUEST[$f])) {
						if (!in_array($_REQUEST[$f], $InValidChoice)) {
							$where[] = $f . " = " .  $dbh->quote($_REQUEST[$f]);
						}
					}
				}
			}

			if ( count($where) ) {
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}


			$result = $dbh->query($sql);
			$count = $result->rowCount();

            if ( $count == 1 ) {
				$row = $result->fetch(PDO::FETCH_ASSOC);

			    echo "<CENTER>\n";
                echo "<H3>$row[summary]</H3>\n";
			    echo "<TABLE id=help_display_form BORDER WIDTH=80% CELLPADDING=7>\n";
                    echo "<TH>Quick Help</TH>\n";
				    echo "<TR>\n";
				    //echo "<TD VALIGN=TOP class=tds>";
				    echo "<TD VALIGN=TOP>";
				    echo "$row[short_help]<BR>";
				    echo "</TD>\n";
				    echo "<TR>\n";
                    echo "<TH>Detailed Help</TH>\n";
				    echo "<TR>\n";
				    echo "<TD VALIGN=TOP>";
				    echo "$row[long_help]<BR>";
				    echo "</TD>\n";
			    echo "</TABLE>\n";
			    echo "</CENTER>\n";

            }else{
                $_REQUEST['Action'] = "Query";
            }

	  	}//if ($_REQUEST['Action'] == "Help")) {

		//----------------------------------------------------------------------
	  	// Query or List
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Query" 
			|| $_REQUEST['Action'] == "List") {

            if ( $_SESSION['access'] >= $HELPCFG['ADMINLEVEL'] ) {
		        spew_query_form();
            }

			$dbh = ggsd_pdo_connect();
			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabels);

			$sql = 'SELECT * FROM help ';

			$where = array();

			foreach ($fields as $f) {
				if (array_key_exists($f, $_REQUEST)) { 
					$val = $_REQUEST[$f];

					if (in_array($val, $InValidChoice)){
						unset($val) ;
					}

					if ( isset($val) ) {
						$val = $dbh->quote($_REQUEST[$f]);
						if ( isset($val) ) {
							$where[] = $f . " = " . $val  ;
						}
					}
				}
			}

			if ( count($where) ) {
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}

			$result = $dbh->query($sql);
			$count = $result->rowCount();

			$COLCOUNT =  count($SHOW);

			$COLCOUNT += 2;


			echo "<CENTER>\n";
			echo "<TABLE id=help_query_results BORDER>\n";
			echo "<TH class=ths>Edit</TH>\n";
			echo "<TH class=ths>View</TH>\n";

			foreach ($ALLFIELD as $f) {
				if (in_array($f, $SHOW)) {
					echo "<TH class=ths>$fieldlabels[$f]</TH>\n";
				}
			}

			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";
				// Edit
				echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
				echo "<A HREF=$_SERVER[PHP_SELF]?help_id=$row[help_id]";
				echo "&Action=Edit>";
				echo "<IMG SRC=/images/smallballs/greenball.gif BORDER=0></A></TD>\n";
	
				// Show
				echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs>";
				echo "<A HREF=$_SERVER[PHP_SELF]?help_id=$row[help_id]";
				echo "&Action=View>";
				echo "<IMG SRC=/images/smallballs/yellowball.gif BORDER=0></A></TD>\n";
	
				// Record entry row
				foreach ($ALLFIELD as $f) {
					$display = $row[$f];
					// Re-Map foreign key table lookups
					//if ($f == 'contact_id'){
						//$contact_id = $row[$f];
						//$display = $People[$contact_id];
					//}

					if (in_array($f, $SHOW)) {
						$css = "tdcs";
						if (array_key_exists($f, $JustifyCss)) {
							$css = $JustifyCss[$f];
						}
						echo "<TD VALIGN=TOP class=$css>";
						if (in_array($f, $LINK)) {
							echo "<A HREF=";
							echo "$_SERVER[PHP_SELF]";
							echo '?';
							echo "$f=$row[$f]"; 
							echo "&Action=$_REQUEST[Action]>";
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
			echo "<P><HR></P>\n";

	  	}//if ($_REQUEST['Action'] == "Query")) {

		//----------------------------------------------------------------------
	  	// New Entry Form
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "New" ) {

			$People = array();
			$peoplesql = " SELECT people_id, full_name FROM people ";
			if (isset($_SESSION['organization_id'] ) ){
				$peoplesql .= " WHERE organization_id = '$_SESSION[organization_id]'";
			}
			$People = get_menu_array($peoplesql);

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE id=help_new_form BORDER>\n";

			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);

			unset($fieldlabels['help_id']);

			foreach ($fieldlabels as $fieldname => $val) {
				
				echo "<TR><TD VALIGN=TOP CLASS=tdl>$val</TD>\n";

				echo "<TD VALIGN=TOP>";

				if (array_key_exists($fieldname, $FieldType)) {
					if ( $FieldType[$fieldname] == "Menu" ) {
						$menusql = $Menu[$fieldname];
						$menuitems = get_menu($menusql);
						sort($menuitems);
						spew_select_menu($fieldname, $_REQUEST[$fieldname],$_REQUEST[$fieldname],$menuitems);

						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_$fieldname>";
						}
					}

					if ($FieldType[$fieldname] == "People" ) {
						spew_select_hash_menu($fieldname, $_REQUEST[$fieldname],$_REQUEST[$fieldname],$People);
					}

					if ($FieldType[$fieldname] == "MenuArray" ) {
						$menusql = $Menu[$fieldname];
						$menuitems = get_menu_array($menusql);
						spew_select_hash_menu($fieldname, $_REQUEST[$fieldname],$_REQUEST[$fieldname],$menulist);

						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_$fieldname>";
						}
					}


					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$_REQUEST[$fieldname]\">";
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA ROWS=20 COLS=70 NAME=$fieldname>$_REQUEST[$fieldname]</TEXTAREA>";
					}

				}else{
					echo "<INPUT TYPE=TEXT SIZE=60 NAME=$fieldname VALUE=\"$_REQUEST[$fieldname]\">";
				}
				echo "</TD>\n";
			}
			echo "</TABLE>\n";
			if ($_SESSION['access'] >= $HELPCFG['ADMINLEVEL'] ) {
			    echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Insert New Entry\">\n";
            }else{
                echo "<P class=trace>You must have access level of $HELPCFG[ADMINLEVEL] to add or update</P>\n";
            }
			echo "</FORM>\n";
			echo "</CENTER>\n";
		}
		//----------------------------------------------------------------------
	  	// Edit 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Edit")  {

			if (! array_key_exists('help_id', $_REQUEST)) {
				die ("No help record Id Set") ;
			}else{
				$help_id = $_REQUEST['help_id'];
				if (! is_numeric($help_id)){
					die ("Help Id is non numeric for edit function, exiting.") ;
				}
			}

			$People = array();
			$peoplesql = " SELECT people_id, full_name FROM people ";
			if (isset($_SESSION['organization_id'] ) ){
				$peoplesql .= " WHERE organization_id = '$_SESSION[organization_id]'";
			}
			$People = get_menu_array($peoplesql);

			$dbh = ggsd_pdo_connect();

			echo "<CENTER>\n";
			$sql = 'SELECT * FROM help WHERE help_id = ' . $dbh->quote($_REQUEST[help_id]);

			$result = $dbh->query($sql);
			$count = $result->rowCount();
			$row = $result->fetch(PDO::FETCH_ASSOC);

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE id=help_edit_form BORDER>\n";

			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);

			foreach ($fieldlabels as $fieldname => $val) {

				echo "<TR><TD VALIGN=TOP>$val</TD>\n";
				echo "<TD VALIGN=TOP>";

				if (array_key_exists($fieldname, $FieldType)) {
					if ( $FieldType[$fieldname] == "Menu" ) {
						$menusql = $Menu[$fieldname];
						$menuitems = get_menu($menusql);
						sort($menuitems);
						spew_select_menu($fieldname, $val,'All',$menuitems);
						if (in_array($fieldname, $EXTEND)) {
							echo "-OR- <INPUT TYPE=TEXT NAME=NEW_$fieldname>";
						}
					}

					if ($FieldType[$fieldname] == "MenuArray" ) {
						$menusql = $Menu[$fieldname];
						$menuitems = get_menu_array($menusql);
						spew_select_hash_menu($fieldname, $row[$fieldname],'All',$menulist);
					}

					if ($FieldType[$fieldname] == "People" ) {
						spew_select_hash_menu($fieldname, $_REQUEST[$fieldname],$_REQUEST[$fieldname],$People);
					}

					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT SIZE=60 NAME=$fieldname VALUE=\"$row[$fieldname]\"><BR>";
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA ROWS=20 COLS=70 NAME=$fieldname>$row[$fieldname]</TEXTAREA>";
					}


				}else{
					if (in_array($fieldname, $NoEdit)) {
						echo "$row[$fieldname]<BR>";
					}else{
						echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$row[$fieldname]\"><BR>";
					}
				}
				echo "</TD>\n";
			}
			echo "</TABLE>\n";
			echo "<INPUT TYPE=HIDDEN NAME=help_id VALUE=$help_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
			echo "</FORM>\n";
			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "Edit")

		//----------------------------------------------------------------------
	  	// View
		//----------------------------------------------------------------------
	  	if (($_REQUEST['Action'] == "View" )) {

			if (! array_key_exists('help_id', $_REQUEST)) {
				die ("No help record Id Set") ;
			}else{
				$help_id = $_REQUEST['help_id'];
				if (! is_numeric($help_id)){
					die ("Help Id is non numeric for edit function, exiting.") ;
				}
			}

			$dbh = ggsd_pdo_connect();
			$sql = "SELECT h.*, p.full_name FROM help h, people p ";
			$sql .= " WHERE help_id = " . $dbh->quote($help_id);
			$sql .= " AND h.contact_id = p.people_id ";

			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE id=help_edit_form BORDER>\n";

			$fieldlabels = get_field_labels('help','help',$GGSDCFG['DBNAME']);
			foreach ($fieldlabels as $fieldname => $val) {
				$display = $row[$fieldname];
				if ($fieldname == 'contact_id'){
					$display = $row['full_name'];
				}

				echo "<TR>\n";
                echo "<TD VALIGN=TOP class=tdls>$val</TD>\n";
				echo "<TD VALIGN=TOP class=tds>";
				echo "$display<BR>";
				echo "</TD>\n";
			}
			echo "</TABLE>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Query>\n";
			// SECURITY
			echo "<INPUT TYPE=HIDDEN NAME=help_id VALUE=$help_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Edit>\n";
			echo "</FORM>\n";
			echo "</CENTER>\n";

	  	}//if ($_REQUEST['Action'] == "View" )

		//----------------------------------------------------------------------
		// End of Action Routing
		//----------------------------------------------------------------------

	}else{
		spew_query_form();
	}

	spew_footer($FMT);
	//----------------------------------------------------------------
	// Function spew_query_form
	//----------------------------------------------------------------
	function spew_query_form() {
        global $GGSDCFG;
		$dbh = ggsd_pdo_connect();
		$list = array();

		echo "<CENTER>\n";
		echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
		echo "<TABLE id=help_query_form BORDER>\n";
		echo "<TH class=ths>Topic</TH>\n";
		echo "<TH class=ths>Table</TH>\n";
		echo "<TH class=ths>Application</TH>\n";
		echo "<TH class=ths>Module</TH>\n";

		echo "<TR>\n";

		// Category
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT topic from help";
		$list = get_menu($sql);
		sort ($list);
		spew_select_menu('topic','','All',$list);
		echo "</TD>\n";

		// Table Name
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT table_name from help";
		$list = get_menu($sql);
		sort ($list);
		spew_select_menu('table_name','','All',$list);
		echo "</TD>\n";

		// Application
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct application from help";
		$list = get_menu($sql);
		sort ($list);
		spew_select_menu('application','','All',$list);
		echo "</TD>\n";

		// Module
		echo "<TD class=tds>\n";
		$sql = "SELECT distinct module from help";
		$list = get_menu($sql);
		sort ($list);
		spew_select_menu('module','','All',$list);
		echo "</TD>\n";
		// End Table

		echo "</TR>\n";
		echo "</TABLE>\n";
		// End Form
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Query>\n";
		echo "</FORM>\n";
		echo "</CENTER>\n";

	}
?>
