<?php
	//#==================================================================
	//# GGSD People
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/root/RCS/people.php,v $
	//# $Id: people.php,v 1.2 2022/12/12 19:19:36 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	require_once("./include/ggsd-config-inc.php");
	require_once("./include/ggsd-journal-inc.php");
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
		'BANNER'		=>	"Users",
		'TITLE'			=>	"Users",
		'MODULENAME'	=>	"people.php",
		'NAV1'			=>	"INFO"	// Level 1 menu navigation group
	);

	//------------------------------------------------------------------------
	// Local configuration parameters
	//------------------------------------------------------------------------
	$PEOPLECFG = array (
		'ADMINLEVEL'	=>	'6',
		'EDITLEVEL'		=>	'5',
	);

	global $PEOPLECFG;

	//------------------------------------------------------------------------
	// Database Fields
	//------------------------------------------------------------------------

	$NEWFIELD = array(
		'first_name',
		'last_name',
		'nickname',
		'gender',
		'primary_phone',
		'mobile_phone',
		'email_1',
		'home_city',
		'home_zip',
	);

	$ALLFIELD = array(
		'people_id',
		'full_name',
		'gender',
		'first_name',
		'last_name',
		'nickname',
		'people_occupation',
		'people_status',
		'home_street',
		'home_city',
		'home_state',
		'home_zip',
		'home_country',
		'latitude',
		'longitude',
		'email_1',
		'email_2',
		'primary_phone',
		'mobile_phone',
		'headshot_url',
		'people_notes',
	);

	//
	//	Fields visible in query output list
	//
	$SHOW = array(
		'full_name',
		'gender',
		'people_status',
		'home_city',
	);
	
	if ($_SESSION['access_level'] >= $PEOPLECFG['ADMINLEVEL'] ) {
		$SHOW[] = 'people_id';
	}

	//
	// Fields that can have query drill down links on display
	//
	$LINK = array(
		'people_type',
		'people_status',
		'gender',
		'home_city',
		'home_zip',
	);
	//
	// Fields that are from a Menu Picklist 
	//
	$EXTEND = array(
		'people_type',
		'people_status',
		'people_source',
	);

	//
	// Required for New Entry
	//
	$RequiredField = array(
		'first_name'	=>	'enter first name',
		'last_name'		=>	'enter last name',
		'email_1'		=>	'enter primary email',
		'people_type'	=>	'select type or category of new person',
		'people_status'	=>	'select status of new person',
		'gender'		=>	'select gender',
		'home_city'		=>	'enter city in which you live',
		'home_zip'		=>	'enter postal zip code where you live',
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
		'people_id',
		'last_updated',
		'latitude',
		'longitude',
	);

	$FieldType = array(
		'birth_month'			=>	'Menu',
		'birth_day'				=>	'Menu',
		'people_notes'				=>	'TextArea',
		'people_occupation'		=>	'LongText',
		'headshot_url'			=>	'LongText',
		'email_1'				=>	'LongText',
		'email_2'				=>	'LongText',
		'gender'				=>	'Menu',
		'people_type'			=>	'Menu',
		'people_status'			=>	'Menu',
		'people_source'			=>	'Menu',
	);

	$BASE = "SELECT choice FROM menu WHERE table_name = 'people' AND ";

	$Menu = array(
		"birth_month"		=> "$BASE field_name = 'birth_month' ORDER BY choice",
		"birth_day"			=> "$BASE field_name = 'birth_day' ORDER BY choice",
		"gender"			=> "$BASE field_name = 'gender' ORDER BY choice",
		"people_source"		=> "$BASE field_name = 'people_source' ORDER BY choice",
		"people_status"		=> "$BASE field_name = 'people_status' ORDER BY choice",
		"people_type"		=> "$BASE field_name = 'people_type' ORDER BY choice",
	);

	//
	// Display exceptions from default tdcs centered display table cell
	//
	$JustifyCss = array(
		'email_1'			=>	'tds',
		'home_street'		=>	'tds',
		'home_city'			=>	'tds',
		'full_name'			=>	'tds',
		'home_city'			=>	'tds',
		'people_type'		=>	'tds',
	);

	//------------------------------------------------------------------------
	// BEGIN Program
	//------------------------------------------------------------------------

	spew_header($FMT);

	if (array_key_exists('Action', $_REQUEST)) {

		spew_query_form();



		//----------------------------------------------------------------------
	  	// Delete
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Delete") {

			if ( array_key_exists('people_id', $_REQUEST)) {
				if ( isset($_REQUEST['people_id'] ) ) {
					$people_id = stripslashes( $_REQUEST['people_id']);
				}else{
					die ("NO People ID in delete function.") ;
				}
				if ( ! is_numeric($people_id) ) {
					die ("People ID ($people_id) is not an integer.") ;
				}
			}else{
				die ("No People Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			if ($people_id < 101){
				die("People ID out of range\n");
			}

			//
			// Fetch Name
			//
			$sql = "SELECT full_name from people where people_id = $people_id";
			$name = get_value($sql);

			echo "<CENTER>\n";
			echo "<H3>Deleting $name from all website accounts</H3>\n";
			echo "</CENTER>\n";

			$sql = "DELETE from people where people_id = $people_id";
			run_sql($sql);

			$sql = "DELETE from access where people_id = $people_id";
			run_sql($sql);

			$_REQUEST = array();
			$_REQUEST['Action'] = "List";
		}// Endsub Delete

		//----------------------------------------------------------------------
	  	// Insert New Entry
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Insert New Entry With Website Login"
	  		|| $_REQUEST['Action'] == "Insert New Entry" ) {
			$dbh = ggsd_pdo_connect();

			$pcnt = count($_POST);
			$rcnt = count($_REQUEST);

			// CAPTCHA
			if( count($_REQUEST) > 1 ){
				if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $_REQUEST['keystring']){
					echo "<P class=trace>Excellent, you are a human!</P>";
				}else{
					echo "<P class=trace>The numbers from the captcha box do match those in the image.";
					echo "<BR>Please press the back button and try again.\n";
					echo "</P>";
				}
			}else{
					echo "<P class=trace>No Data </P>";
					spew_footer($FMT);
					exit;
			}


			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);
			$fields = array_keys($fieldlabel);
			$fieldlabel['dob'] = 'Birth Date (YYYY-MM-DD)';
			$fieldlabel['people_height'] = 'Height in inches<BR>(6 feet is 72 inches)';

			//
			// Define default values
			//
			$Default = array (
				'people_state'	 	=>  'New',
				'people_status'		=>	'Active',
				'people_type'		=>	'Dancer',
				'home_state'		=>	'CA',
				'home_country'		=>  'US',
				'birth_year'		=>  '1999',
				'dob'				=>  '1999-09-09',
				'people_notes'	 => '<BR>For Work: ...\n<BR>Family: ...\n<BR>Dancing Details: ...\n<BR>For Fun: ...\n<BR>My Mission: ...\n<BR>My Superpower: ...\n<BR>Favorite	   Vacations: ...\n<BR>Favorite Quote: ....\n',
				'timezone'		 =>  'PST-8',
			);

			$Default['full_name'] = $_REQUEST['first_name'] . ' ' . $_REQUEST['last_name'];

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
			unset ($_REQUEST['people_id']);

			//
			// Required fields gauntlet
			//
			foreach ( $RequiredField as $key => $val) {
				if (! array_key_exists($key, $_REQUEST)) {
					$err .= '<LI>Please ' . $RequiredField[$key] . '.</LI>';
				}
			}

			//
			// Data Quality Gauntlet
			//
			if ( ! empty($_REQUEST['birth_day'])){
				if ( ! is_numeric($_REQUEST['birth_day'])){
					$err .= "modify Birth Day to be a number in the range [1-31]";
				}
				if ( ($_REQUEST['birth_day'] > 31 || $_REQUEST['birth_day'] < 1 )){
					$err .= "modify Birth Day to be a number in the range [1-31]";
				}
			}
			if ( ! empty($_REQUEST['birth_month']) ){
				if ( ! is_numeric($_REQUEST['birth_month']) ){
					$err .= "modify Birth Month to be a number in the range [1-12]";
				}
				if ( ($_REQUEST['birth_month'] > 12 || $_REQUEST['birth_month'] < 1 )){
					$err .= "modify Birth Month to be a number in the range [1-12]";
				}
			}
			if ( ! empty($_REQUEST['birth_year'])){
				if ( ! is_numeric($_REQUEST['birth_year'])){
					$err .= "modify Birth Year to be a numeric and a 4 digit number > 1900";
				}
				if (  $_REQUEST['birth_year'] < 1900 ){
					$err .= "modify Birth Year to be a 4 digit number > 1900";
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

			$Data = array();

			$sql = "INSERT INTO people (";
			foreach ($fields as $f) {
				if ( array_key_exists($f, $_REQUEST)) {
					if (preg_match('/_phone$/', $f)){
						$_REQUEST[$f] = format_phone_number($_REQUEST[$f]);
					}
					$Data[$f] = $dbh->quote($_REQUEST[$f]);
				}
			}

			$sql .= implode(',', array_keys($Data) );
			$sql .= ") VALUES (";
			$sql .= implode(',', array_values($Data) );
			$sql .= ")";


			$dbh->query($sql);

			$people_id = $dbh->lastInsertId() ;
			if ( is_numeric($people_id) && $people_id > 100 ){
				$_REQUEST['people_id'] = $people_id;


				echo "<CENTER>\n";
				echo "<H2>Record successfully added</H2>\n";
				echo "</CENTER>\n";
			}else{
				die("Record entry failed: [$sql]\n");
			}

			//
			// Initialize User Preferences
			//
			init_mypreference($people_id);


			//
			// Add Access entry for website login
			//
	  		if ($_REQUEST['Action'] == "Insert New Entry With Website Login"){
				$login = strtolower($_REQUEST['first_name']) . '.' . strtolower($_REQUEST['last_name']);
				$today = date('Y-m-d');
				$sql = "SELECT allcal_id from allcal where calendar_date >= '$today' order by allcal_id LIMIT ";
				$sql .= $GGSDCFG['NEWPASSWDEXP'];
				$days = array();
				$days = get_menu($sql);
				$idx = max($days);
				$sql = "SELECT calendar_date from allcal where allcal_id = $idx";
				$expdate = get_value($sql);
			
				$sql = "INSERT INTO access ( people_id', metagroup_id', access_login', access_password',";
				$sql .= " access_class', access_role', access_level', expiration_date') VALUES (";
				$sql .= "$people_id, $people_id, '$login', ";
				$sql .= $dbh->quote($GGSDCFG['GUESTPWENC']);
				$sql .= ",'Guest,'Guest',2,";
				$sql .= $dbh->quote($expdate);

				$dbh->query($sql);
				$access_id = $dbh->lastInsertId();
			}
			$_REQUEST['Action'] = 'View';
		}

		//----------------------------------------------------------------------
	  	// Update Existing Entry 
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Update" ) {

			if ( array_key_exists('people_id', $_REQUEST)) {
				$people_id = $_REQUEST['people_id'];
				if (! is_numeric( $people_id ) ) {
					die ("ERROR: Attempt to update People requires people_id to be integer. It is not.");
				}
			}else{
				die ("No People Id Set. Unable to update") ;
			}

			if ($_SESSION['access_level'] <= $PEOPLECFG['EDITLEVEL'] ) {
				if ( $_SESSION['people_id'] != $_REQUEST['people_id'] ){
					die("You only have permission to update your own information.\n");
				}
			}

			$dbh = ggsd_pdo_connect();
			$today = date('Y-m-d H:i');

			//
			// Get Original Record
			//
			$Original = array();

			$sql = "SELECT full_name from people where people_id = ";
			$sql .= $dbh->quote($_SESSION['people_id']);
			$who = get_value($sql);

			$sql = 'SELECT * FROM people WHERE people_id = ';
			$sql .= $dbh->quote($people_id);
			$result = $dbh->query($sql);

			$Original = $result->fetch(PDO::FETCH_ASSOC);

			//
			// Get list of fields for this table
			//
			$fieldlabel = get_field_labels('people','aaaaaaaaaa',$GGSDCFG['DBNAME']);


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


			$changelog = "Changed $today by $who ($_SESSION[people_id]):<UL>\n";
			foreach ($fields as $f) {
				if (in_array($f, $NoEdit)) {
					continue;
				}
				if ( array_key_exists($f, $_REQUEST)) {
					$val = $_REQUEST[$f];


					if (preg_match('/_phone$/', $f)){
						$oldpn = $_REQUEST[$f];
						$_REQUEST[$f] = format_phone_number($_REQUEST[$f]) ;
					}

					if ( $_REQUEST[$f] != $Original[$f] ) {
						$key =  $f;

						$UpdateData[] = "$key = " . $dbh->quote($_REQUEST[$f]);
						$changelog .= "<LI> Changed $f ($fieldlabel[$f])\n";
						$changelog .= " <UL>";
						$changelog .= "  <LI>From:  $Original[$f]\n";
						$changelog .= "  </LI>\n";
						$changelog .= "  <LI>To:  $_REQUEST[$f]\n";;
						$changelog .= "  </LI>";
						$changelog .= "</UL>\n";
						$changelog .= "</LI>";
					}

				}
			}
			$changelog .= "</UL>";


			if (count($UpdateData) > 0){

	
				$sql = 'UPDATE people SET ';
				$sql .= implode(', ', $UpdateData);
				$sql .= " WHERE people_id = ";
				$sql .= $dbh->quote($people_id);
	
				$result = $dbh->query($sql);
			}else{
				echo "<H3>No Changes Made</H3>\n";
			}

			$_REQUEST['people_id'] = $people_id;

			if ( isset ($_REQUEST['NextAction'] ) ) {
				$_REQUEST['Action'] = $_REQUEST['NextAction'];
			}else{
				$_REQUEST['Action'] = "View";
			}
		}

		//----------------------------------------------------------------------
	  	// Query or List
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Query"
	  		|| $_REQUEST['Action'] == "List Partners"
	  		|| $_REQUEST['Action'] == "List All"
	  		|| $_REQUEST['Action'] == "Address List"
	  		|| $_REQUEST['Action'] == "Email List"
	  		|| $_REQUEST['Action'] == "Contact Info"
	  		|| $_REQUEST['Action'] == "List" ) {


			if ($_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				$SHOW[] = 'people_type';
			}


			$dbh = ggsd_pdo_connect();
			$fieldlabel = array();

			//
			// Base sql query
			//
			$What = array(
				'p.*'
			);

			$Where = array(
				'p.people_id > 100'
			);

			$From = array(
				'people'	=>	'p'
			);

	  		if ( $_REQUEST['Action'] == "Contact Info" ){
				$SHOW[] = 'email_1';
				$SHOW[] = 'primary_phone';
				$SHOW[] = 'mobile_phone';
			}

	  		if ( $_REQUEST['Action'] != "List All" ){
				$Where[] = "p.people_status = 'Active'";
			}

	  		if ( $_REQUEST['Action'] == "Address List" ){
				$SHOW[] = 'home_street';
				$SHOW[] = 'home_city';
				$SHOW[] = 'home_state';
				$SHOW[] = 'home_zip';
			}

	  		if ( $_REQUEST['Action'] == "Email List" ){
				$SHOW[] = 'email_1';
			}

			if ($_REQUEST['event_id'] > 0 ){
				$What[] = 'e.event_name';

				$From['event'] = 'e';
				$From['attend'] = 'a';

				$Where[] = "a.event_id = " . $_REQUEST['event_id'] ;
				$Where[] = "a.people_id = p.people_id";
				$Where[] = "a.event_id = e.event_id";

				$esql = "SELECT event_name from event where event_id = " . $_REQUEST['event_id'];
				$event_attended = get_value($esql);
			}


			$Who = array();
			$sql = "SELECT people_id, full_name from people";

			if ( $_SESSION['access_level'] < $PEOPLECFG['ADMINLEVEL'] ) {
				$sql .= " WHERE people_type = 'Dancer'";
				$Where[] = "people_type = 'Dancer'";
			}

			$Who = get_menu_array($sql);

			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);
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
							$Where[] = "p." . $f . ' LIKE ' . $dbh->quote($_REQUEST[$f]);
						}else{
							$Where[] = "p." . $f . "=" . $dbh->quote($_REQUEST[$f]);
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
				'City'				=>	'p.home_city, p.full_name',
				'First Name'		=>	'p.first_name, p.last_name',
				'Last Name'			=>	'p.last_name, p.first_name',
				'Email'				=>	'p.email_1, p.full_name',
				'People Type'		=>	'p.people_type, p.full_name',
				'Status'			=>	'p.people_status, p.full_name',
				'Type'				=>	'p.people_type, p.full_name',
				'Zip'				=>	'p.people_zip, p.people_city, p.full_name',
			);

			$sortby = $_REQUEST['Sortmeby'];
			$sby = $OrderBy[$sortby];

			if (empty ($sby)){
				$sql .= ' ORDER BY p.full_name';
			}else{
				$sql .= ' ORDER BY ' . $sby;
			}


			$result = $dbh->query($sql);
			$rowcount = $result->rowCount();

			// Add synthetic names after query made
			$fields = array_keys($fieldlabel);
			
			// Blurb
			echo "<P class=trace>\n";
			if ($event_attended){
				echo "List for those who attended <B>$event_attended</B>.<BR>\n";
			}
			echo "Column entries that are links will &quot;drill down&quot; to refine your query.\n";
			echo "Name links show contact details.\n";
			echo "<BR>Green shading in <B>Status</B> column indicates valid email on record.\n";
			echo "<BR>Query returned $rowcount " . ( $rowcount ==1 ? 'entry.' : 'entries.');
			echo "</P>\n";

			$Map = array();

			//---------------------------------------------------
			// Spew table XXX
			//---------------------------------------------------
			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				$SHOW[] = 'people_status';
				$SHOW[] = 'people_type';
			}

			echo "<CENTER>\n";
			echo "<TABLE BORDER CELLPADDING=3>\n";

			if ($_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				echo "<TH class=ths>Edit</TH>\n";		// SECURITY
			}

			foreach ($ALLFIELD as $f) {
				if (in_array($f, $SHOW)) {
					echo "<TH class=ths>$fieldlabel[$f]</TH>\n";
				}
			}
			$row = array();

			$oldcid = 0;
			$ccnt=0;
			$BGC = '#FFF';
			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				if (isset($row['latitude']) &&  isset($row['longitude'])){
					$Map[] = $row['latitude'] . ',' . $row['longitude'];
				}


				echo "<TR>\n";

				// Edit if authorized
				// SECURITY
				if ($_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {	
					echo "<TD ALIGN=CENTER VALIGN=TOP class=tdcs BGCOLOR=$BGC>";
					echo "<A HREF=$_SERVER[PHP_SELF]?people_id=$row[people_id]";
					echo "&Action=Edit>";
					echo "<IMG SRC=/images/smallballs/greenball.gif BORDER=0></A>";
					echo "</TD>\n";
				}
	
				foreach ($ALLFIELD as $f) {
					$css = "tdcs";
					$display = stripslashes($row[$f]); 
					$BGC = $BIGBGC;

					//
					// Display Exceptions (lookup)
					//


					if ( $f  == 'full_name' ) {
						$pid = $row['people_id'];
						$display = "<A HREF=/people.php?people_id=$pid&Action=View>$row[$f]</A>";
					}

					if ( $f  == 'email_1' ) {
						$em = $row[$f];
						$display = "<A HREF=mailto:$em>$em</A>";
					}

					if (in_array($f, $SHOW)) {

						if (array_key_exists($f, $JustifyCss)) {
							$css = $JustifyCss[$f];
						}

						if ($f == 'people_status' ){
							if (preg_match('/@/', $row['email_1']) ) {
								echo "<TD VALIGN=TOP class=$css BGCOLOR=#AAFFAA>";
							}else{
								//echo "<TD VALIGN=TOP class=$css BGCOLOR=#FFAAAA>";	// Pink for "bad dog"
								echo "<TD VALIGN=TOP class=$css BGCOLOR=$BGC>";
							}
						}else{
							echo "<TD VALIGN=TOP class=$css BGCOLOR=$BGC>";
						}
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
			//$GoodMap = array();
			//$GoodMap = array_unique($Map);
			//$mapurl = 'https://www.google.com/maps/dir/' . implode('/@', $GoodMap);
			//echo "<P class=trace><A HREF=$mapurl>Google Map</A></P>\n";
			//echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "List"))  or 'Query'

		//----------------------------------------------------------------------
	  	// New Entry Form
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "New" 
	  		|| $_REQUEST['Action'] == "Register"
	  		|| $_REQUEST['Action'] == "Add Venue Contact" ) {
			$menulist = array();
			$ALLFIELD = array();
			$ALLFIELD = $NEWFIELD;

			$dbh = ggsd_pdo_connect();

			$Default = array (
				'people_state'		=>	'New',
				'people_status'		=>	'Active',
				'people_type'		=>	'Dancer',
				'home_state'		=>	'CA',
			);

	  		if ($_REQUEST['Action'] == "Add Venue Contact" ) {
				$Default['people_type'] = 'Vendor';
			}

			// Blurb
			echo "<P class=trace>\n";
			echo "Enter entries. Details on meanings and choice details available via help links in left column.\n";
			echo "Asterisk (*) indicates field is required.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";

			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);
			$fieldlabel['dob'] = 'Birth Date (YYYY-MM-DD)';
			$fieldlabel['people_height'] = 'Height in inches<BR>(5 feet is 60 inches)';

			foreach ($ALLFIELD as $fieldname ) {
				if ( $fieldname == 'people_id'){
					continue;
				}

				$val = $fieldlabel[$fieldname];
				
				echo "<TR>\n";

				echo "<TD CLASS=tdls>";
				echo "<A HREF=/help.php?table_name=people&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$val</A>";

				if ( isset($RequiredField[$fieldname]) ){
					echo '&nbsp;*&nbsp;';
				}

				echo "</TD>\n";

				echo "<TD class=tds>";

				$what = '';
				$choose = 'Choose';

				if (!in_array($_REQUEST[$fieldname], $InValidChoice)) {
					$what = $_REQUEST[$fieldname];
					$choose = $what;
				}else{
					if (isset($Default[$fieldname])){
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
						$menulist['Choose'] = 'Choose';
						spew_select_hash_menu($fieldname, $what,$choose,$menulist);
					}


					if ($FieldType[$fieldname] == "People" ) {
						$menusql = "SELECT people_id, full_name from people where people_id > 100 order by full_name";
						$menulist = get_menu_array($menusql);
						$menulist[0] = 'None';
						spew_select_hash_menu($fieldname, $what,$choose,$menulist);
					}

					if ($FieldType[$fieldname] == "TextArea" ) {
						echo "<TEXTAREA COLS=70 ROWS=20 NAME=$fieldname>$what</TEXTAREA>\n";
					}

					if ($FieldType[$fieldname] == "LongText" ) {
						echo "<INPUT TYPE=TEXT SIZE=70 NAME=$fieldname VALUE=\"$what\">";
					}

				}else{
					echo "<INPUT TYPE=TEXT NAME=$fieldname>";
				}
				echo "</TD>\n";
			}
			echo "</TABLE>\n";
			echo "<p>I prefer to work with humans, please enter the numbers shown in the below 'captcha' image into the box below:</p>\n";
			echo "<p><img src='kcaptcha-init.php' alt=\"Captcha Image\"></p>\n";
			echo "<p><input type=text name=keystring></p>\n";

			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Insert New Entry\">\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Insert New Entry With Website Login\">\n";
			echo "</FORM>\n";
			echo "</CENTER>\n";
		}//End if ($_REQUEST['Action'] == "New" ) 


		//----------------------------------------------------------------------
	  	// Show: My Info
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Show") {


			if ( array_key_exists('people_id', $_SESSION)) {
				if ( isset ( $_SESSION['people_id'] ) ) {
					$people_id = $_SESSION['people_id'];
				}else{
					die ("No People ID in Show function") ;
				}
				if ( ! is_numeric($people_id) ) {
					die ("People ID ($people_id) is not an integer.") ;
				}
			}else{
				die ("No People Id Set") ;
			}

			//
			// Get People roster for partner id
			//
			$Who = array();
			$sql = "SELECT people_id, full_name from people";
			$Who = get_menu_array($sql);

			$menulist = array();
			$row = array();

			$dbh = ggsd_pdo_connect();

			$sql = 'SELECT * FROM people WHERE people_id = ';
			$sql .= $dbh->quote($people_id);
			$result->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Field explanation available via link in left column field labels.\n";
			echo "Change them by clicking <B>Edit</B> button at bottom of page.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			echo "<H2>$row[full_name]</H2>\n";
			echo "<TABLE BORDER>\n";

			foreach ($ALLFLD as $fieldname ) {
				$label = $fieldlabel[$fieldname];
				echo "<TR>\n";
				echo "<TD class=tdls>";
				echo "<A HREF=/help.php?table_name=people&field_name=$fieldname&Action=Help target=\"_blank\">";
				echo "$label</A></TD>\n";
				echo "<TD class=tds>";

				$display = stripslashes($row[$fieldname]);

				//
				// View Entry Lookup Map Translations (id -> othertable.name for foreign keys)
				//


				echo "$display<BR>";
				echo "</TD>\n";

				echo "$display<BR>";
				echo "</TD>\n";
			}//Endforeach fieldname

			echo "</TABLE>\n";

			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<INPUT TYPE=HIDDEN NAME=people_id VALUE=$people_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";
			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Edit\">\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Address List\">\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Email List\">\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Contact Info\">\n";
			}
			echo "</FORM>\n";


			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "Show")


		//----------------------------------------------------------------------
	  	// Edit
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "Edit") {

			$SHOWEDIT = array();

			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				$SHOWEDIT = $ALLFIELD;
			}else{
				$SHOWEDIT = array(
					'full_name',
					'gender',
					'dance_role',
					'first_name',
					'last_name',
					'nickname',
					'people_occupation',
					'home_street',
					'home_city',
					'home_state',
					'home_zip',
					'home_country',
					'email_1',
					'email_2',
					'primary_phone',
					'mobile_phone',
					'people_notes',
				);
			}


			if ( array_key_exists('people_id', $_REQUEST)) {
				if ( isset($_REQUEST['people_id'] ) ) {
					$people_id = stripslashes( $_REQUEST['people_id']);
				}else{
					die ("NO People ID in edit function.") ;
				}
				if ( ! is_numeric($people_id) ) {
					die ("People ID ($people_id) is not an integer.") ;
				}

				if ( $people_id <= 100 ) {
					die ("People ID ($people_id) belongs to a special admin group and can not be edited via website.") ;
				}
			}else{
				die ("No People Id Set") ;
			}

			$dbh = ggsd_pdo_connect();

			//
			// Hardwire to update my stuff unless I have admin privileges
			//
			if ( $_SESSION['access_level'] < $PEOPLECFG['EDITLEVEL'] ) {
				echo "<P class=trace>You only have permission to edit your own information\n";

				$_REQUEST['people_id'] = $_SESSION['people_id'];

				$NoEdit[] = 'people_status';
				$NoEdit[] = 'headshot_url';
				$NoEdit[] = 'people_source';
				$NoEdit[] = 'people_type';
				$NoEdit[] = 'latitude';
				$NoEdit[] = 'longitude';
				$NoEdit[] = 'headshot_url';

				$NoShow = array(
					'headshot_url',
					'vaccination_record',
					'headshot_url',
					'people_id',
					'people_source',
					'latitude',
					'longitude',
					'alternate_email',
					'home_country',
				);
			}

			$menulist = array();

			$Me = array();
			$sql = "SELECT * FROM people WHERE people_id = '$people_id'";
			$result = $dbh->query($sql);
			$Me = $row = $result->fetch(PDO::FETCH_ASSOC);

			$Who = array();
			$sql = "SELECT people_id, full_name from people where people_id > 100";
			$Who = get_menu_array($sql);

			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);

			// Blurb
			echo "<P class=trace>\n";
			echo "Change desired entries and click <B>Update</B> at bottom of form.\n";
			echo "</P>\n";

			echo "<CENTER>\n";
			// Outer table
			echo "<TABLE BORDER=5 CELLPADDING=3>\n";
			echo "<TH class=ths>Change Personal Information</TH>\n";
			echo "<TH class=ths>Change Related Information</TH>\n";
			echo "<TR><TD ALIGN=CENTER WIDTH=50% VALIGN=TOP>\n";

				// Submit action buttons at top of form
				echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
				echo "<INPUT TYPE=HIDDEN NAME=people_id VALUE=$people_id>\n";

				if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] || $_SESSION['people_id'] == $people_id) {
					echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
				}

				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=View>\n";

				echo "<TABLE BORDER>\n";
	
				foreach ($ALLFIELD as $fieldname ) {
					if ( ! in_array($fieldname, $SHOWEDIT ) ){
						continue;
					}

					$label = $fieldlabel[$fieldname];
					echo "<TR><TD VALIGN=TOP class=tdls>";
					echo "<A HREF=/help.php?source_table=people&field_name=$fieldname&Action=Help>";
					echo "$label";
					echo "</A>";
					echo "</TD>\n";
					echo "<TD VALIGN=TOP class=tds>";
	
					if ( in_array($fieldname, $NoEdit) ) {
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
	
							if ( $FieldType[$fieldname] == "TextArea" ) {
								echo "<TEXTAREA NAME=$fieldname COLS=70 ROWS=20>$row[$fieldname]</TEXTAREA>\n";
							}
	
							if ( $FieldType[$fieldname] == "LongText" ) {
								echo "<INPUT TYPE=TEXT NAME=$fieldname SIZE=70 VALUE=\"$row[$fieldname]\">\n";
							}

							// Partner
							if ( $FieldType[$fieldname] == "Partner" ) {
								$ppid = $row[$fieldname];

								$menulist = array();
								$menusql = "SELECT people_id, full_name from people where people_id > 100 ";

								if ($row['dance_role'] == 'Lead' ){
									$menusql .= " AND ( dance_role = 'Follow' OR dance_role = 'Both')";
								}

								if ($row['dance_role'] == 'Follow' ){
									$menusql .= " AND ( dance_role = 'Lead' OR dance_role = 'Both')";
								}
								
								$menusql .= " order by full_name";
								$menulist = get_menu_array($menusql);
								$menulist[0] = 'None';

								spew_select_hash_menu($fieldname, $ppid, $ppid, $menulist);
						}
	
						}else{	// No fieldtype
							echo "<INPUT TYPE=TEXT NAME=$fieldname VALUE=\"$row[$fieldname]\"><BR>";
						}
							
					}//Endif NoEdit
					echo "</TD>\n";
				}//Endforeach fieldname
				echo "</TABLE>\n";
				// SECURITY

				if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] || $_SESSION['people_id'] == $people_id) {
					echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=Update>\n";
					//if ( $_SESSION['people_id'] == $people_id) {
						//echo "<INPUT TYPE=HIDDEN NAME=people_id VALUE=$people_id>\n";
					//}
				}
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=View>\n";

	  			if ( isset ( $_REQUEST['NextAction'] ) ) {
					echo "<INPUT TYPE=HIDDEN NAME=NextAction VALUE=\"$_REQUEST[NextAction]\">\n";
				}


				echo "</FORM>\n";

			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";

			//
			// Add Journal History
			//
			echo "<P>\n";
			echo "<FORM ACTION=/journal.php TYPE=POST>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_id VALUE=$people_id>\n";
			echo "<INPUT TYPE=HIDDEN NAME=source_table VALUE=people>\n";
			echo "<INPUT TYPE=HIDDEN NAME=journal_type VALUE=Note>\n";
			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Add Journal\">\n";
			}
			echo "</FORM>\n";
			echo "</P>\n";

			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				show_journal_history($people_id, 'people');
			}

			echo "</CENTER>\n";

	  	}//if ($_REQUEST['Action'] == "Edit") 


		//----------------------------------------------------------------------
	  	// View Verify
		//----------------------------------------------------------------------
	  	if ($_REQUEST['Action'] == "View"
	  		|| $_REQUEST['Action'] == "Verify") {

			$SHOWVIEW = array();
			if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
				$SHOWVIEW = $ALLFIELD;
			}else{
				$SHOWVIEW = $SHOW;
				$SHOWVIEW[] = 'dance_role';
			}

			//
			// I am looking at my stuff: Hardwire to me
			//
	  		if ( $_REQUEST['Action'] == "Verify" || $Me['people_id'] == $_SESSION['people_id']){
				$people_id = $_REQUEST['people_id'] = $_SESSION['people_id'];
				$SHOWVIEW[] = 'home_street';
				$SHOWVIEW[] = 'home_state';
				$SHOWVIEW[] = 'home_zip';
				$SHOWVIEW[] = 'primary_phone';
				$SHOWVIEW[] = 'mobile_phone';
				$SHOWVIEW[] = 'email_1';
			}

			$SHOWVIEW = array_unique( $SHOWVIEW ) ;


			if ( array_key_exists('people_id', $_REQUEST)) {
				if ( isset ( $_REQUEST['people_id'] ) ) {
					$people_id = $_REQUEST['people_id'];
				}else{
					die ("No People ID in view function") ;
				}
				if ( ! is_numeric($people_id) ) {
					die ("People ID ($people_id) is not an integer.") ;
				}
			}else{
				die ("No People Id Set in View Function") ;
			}

			$menulist = array();
			$row = array();

			$dbh = ggsd_pdo_connect();

			//
			// Get People roster for partners
			//
			$Who = array();
			$sql = "SELECT people_id, full_name from people WHERE people_id > 100";
			$Who = get_menu_array($sql);

			$menulist = array();
			$row = array();

			$dbh = ggsd_pdo_connect();

			//
			// Get info about this person
			//
			$Me = array();
			$sql = "SELECT p.* FROM people p";
			$sql .= " WHERE ";
			$sql .= " p.people_id = ";
			$sql .= $dbh->quote($people_id);
			$meresult = $dbh->query($sql);
			$Me = $meresult->fetch(PDO::FETCH_ASSOC);


			$Partner = array();


			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);

			// 
			// Figure out photos directory
			// 
			$Photo = array();
			$Photo = scandir($GGSDCFG['HEADSHOTDIR']);



			echo "<CENTER>\n";
			echo "<H2>$Me[full_name]</H2>\n";

			$row = array();
			$sql = "SELECT * from people WHERE people_id = $_REQUEST[people_id]  ";
			$result = $dbh->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$fieldlabel = get_field_labels('people','people',$GGSDCFG['DBNAME']);

			// Outer table
			//echo "<TABLE CELLPADDING=3>\n";
			echo "<TABLE BORDER=5 CELLPADDING=3>\n";

			//echo "<TABLE BORDER=5>\n";
			echo "<TH class=ths>View Photos, Etc.</TH>\n";
			echo "<TH class=ths>View Personal Information</TH>\n";


			//--------------------------------------------------------------
			// Left Margin Column: Photos and Role Info
			//--------------------------------------------------------------
			echo "<TR><TD VALIGN=TOP ALIGN=CENTER>";

				//---------------
				// Me
				//---------------
				echo "<P>" . $Me['full_name'] . "</P>\n";

				if ( isset( $Me['headshot_url']) ){
					//
					// Is my photo in the library?
					//
					$dirs = explode('/', $Me['headshot_url']);
					$headshot = end($dirs);


					//if ( in_array($headshot, $Photo) && in_array('headshot', $ALLFLD) ){
					if ( in_array($headshot, $Photo) ){
						echo "<IMG SRC=" . $Me['headshot_url'] . ">";
					}else{
						if ( $Me['gender'] == 'M' ){
							echo "<IMG SRC=/photos/headshots/_sfpg_data/thumb/male_nophoto_small.png>";
						}else{
							echo "<IMG SRC=/photos/headshots/_sfpg_data/thumb/female_nophoto_small.png>";
						}
					}
				}else{
					$headshot = strtolower($Me['full_name']) . '.jpg';
					$headshot = preg_replace('/\s+/', '_' , $photo);

					if ( in_array($headshot, $Photo) && in_array('headshot', $ALLFLD) ){
					//if ( in_array($headshot, $Photo)  ){
						echo "<IMG SRC=/photos/headshots/_sfpg_data/thumb/$headshot>";
					}else{
						if ( $Me['gender'] == 'M' ){
							echo "<IMG SRC=/photos/headshots/_sfpg_data/thumb/male_nophoto_small.png>";
						}else{
							echo "<IMG SRC=/photos/headshots/_sfpg_data/thumb/female_nophoto_small.png>";
						}
					}
				}



		//------------------------------------------------------------
		// Right Column
		//------------------------------------------------------------

			echo "<TD ALIGN=CENTER WIDTH=40% VALIGN=TOP>\n";
				// Blurb
				echo "<P class=trace>\n";
				echo "Field explanation available via link in left column field labels.\n";
				echo "</P>\n";

				// Inner left column table
				echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
				echo "<TABLE BORDER>\n";

				foreach ($ALLFIELD as $fieldname ) {
					if (in_array($fieldname, $SHOWVIEW)) {

						$label = $fieldlabel[$fieldname];
						echo "<TR>\n";
						echo "<TD class=tdl>";
						echo "<A HREF=/help.php?source_table=people&field_name=$fieldname&Action=Help>";
						echo "$label</A></TD>\n";
						echo "<TD class=td>";
		
						$display = stripslashes($row[$fieldname]);
		
						if ( $fieldname == "email_1" || $fieldname == "email_2" ) {
							$display = "<A HREF=\"mailto:$display?subject=Hello From GGSD Website Member\">$display</A>";
						}
		
						echo "$display<BR>";
						echo "</TD>\n";
					}//Endforeach in_array
				}//Endforeach fieldname


				echo "</TABLE>\n";

				echo "<INPUT TYPE=HIDDEN NAME=people_id VALUE=$people_id>\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";

	  			if ( $_REQUEST['Action'] == "Verify" || $Me['people_id'] == $_SESSION['people_id']){
					echo "<INPUT TYPE=HIDDEN NAME=NextAction VALUE=\"Verify\">\n";
				}

				if ( $_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] || $_SESSION['people_id'] == $people_id) {
					echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Edit\">\n";

				}
				echo "</FORM>\n";


			echo "</TD>\n";
			echo "</TR>\n";
			echo "</TABLE>\n";

			echo "</CENTER>\n";
	  	}//if ($_REQUEST['Action'] == "View" ||  $_REQUEST['Action'] == "Verify"  )

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
		global $PEOPLECFG;
		$list = array();
		$dbh = ggsd_pdo_connect();

		echo "<P class=trace>";
		echo "Help and explanations available at ";
		echo "<A HREF=/help.php?table_name=people&field_name=Overview&Action=Help target=\"_blank\">Overview</A>.";
		echo "</P>\n";

		echo "<CENTER>\n";

		echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";

		// First Table
		echo "<TABLE BORDER>\n";
		echo "<TH class=ths>First Name</TH>\n";
		echo "<TH class=ths>Last Name</TH>\n";
		echo "<TH class=ths>Sort By</TH>\n";

		// Second Row
		echo "<TR>\n";


		// First Name
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT first_name from people order by first_name";
		$list = get_menu($sql);
		spew_select_menu('first_name','','All',$list);
		echo "</TD>\n";

		// Last Name
		echo "<TD class=tds>\n";
		$sql = "SELECT DISTINCT last_name from people order by last_name";
		$list = get_menu($sql);
		spew_select_menu('last_name','','All',$list);
		echo "</TD>\n";


		// Sort By
		echo "<TD class=tds>\n";
		$sortby = array (
			'First Name',
			'Last Name',
			'City',
			'Zip',
			);

		if ($_SESSION['access_level'] >= $PEOPLECFG['ADMINLEVEL'] ) {

		}

		sort($sortby);
		spew_select_menu('Sortmeby','','First Name',$sortby);
		echo "</TD>\n";

		echo "</TR>\n";
		echo "</TABLE>\n";

		// End Table

		echo "</TR>\n";
		echo "</TABLE>\n";

		// End Form
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=List>\n";
		echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"List All\">\n";

		// SECURITY
		if ($_SESSION['access_level'] >= $PEOPLECFG['EDITLEVEL'] ) {
			//echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"New\">\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Address List\">\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Email List\">\n";
		}
		echo "</FORM>\n";
		echo "</CENTER>\n";
	}//End spew_query_form



	//----------------------------------------------------------------
	// Generate Year List
	//----------------------------------------------------------------
	function generate_year_array (){
		global $GGSDCFG;
		global $PEOPLECFG;
		$thisyear = date('Y');
		$year = 1920;
		$yrs = array();
		
		while ($year < $thisyear) {
			$yrs[] = $year;
			$year++;
		}

		return($yrs);
	}//End generate_year_array


	//----------------------------------------------------------------
	// END FUNCTIONS
	//----------------------------------------------------------------
		
?>
