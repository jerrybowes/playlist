<?php
	//#==================================================================
	//# Manage associating/disassociating entries: tweaking manifest table
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/include/RCS/ggsd-xref-inc.php,v $
	//# $Id: ggsd-xref-inc.php,v 1.2 2022/12/12 19:10:33 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================
	require_once('ggsd-msutils-inc.php');
	require_once('ggsd-config-inc.php');
			
	//----------------------------------------------------------------------
  	// Disassociate : Delete manifest entry that associates two entries
	//----------------------------------------------------------------------
	function disassociate( $manifest_id ){
		$dbh = ggsd_pdo_connect();

		if ( is_numeric( $manifest_id )) {

			$sql = "DELETE FROM manifest WHERE manifest_id = ";
			$sql .= $dbh->quote( $manifest_id ) ;

			$result = $dbh->query($sql);
		}else{
			die("Manifest ID non-integer in disassociate function.\n");
		}
	}

	//----------------------------------------------------------------------
	// Associate 
	//----------------------------------------------------------------------
	function associate($parent_table, $parent_id, $child_table, $child_id, $type){
		$dbh = ggsd_pdo_connect();

		if (! is_numeric( $parent_id )) {
			die("Parent id for table $parent_table in associate function is non-numeric\n");
		}

		if (! is_numeric( $child_id )) {
			die("Child id for table $child_table in associate function is non-numeric\n");
		}

		if (! isset( $child_table )) {
			die("Child table in associate function is not set\n");
		}

		if (! isset( $parent_table )) {
			die("Parent table in associate function is not set\n");
		}

		if (! isset( $type ) || $type == 'TBD') {
			$type = ucfirst($parent_table);
			$type .= ' ';
			$type .= ucfirst($child_table);
		}

		$sql = 'INSERT INTO manifest (';
		$sql .= "parent_id, parent_table, child_id, child_table, manifest_type";
		$sql .= ")VALUES(";
		$sql .= $dbh->quote($parent_id) . ',';
		$sql .= $dbh->quote($parent_table) . ',';
		$sql .= $dbh->quote($child_id) . ',';
		$sql .= $dbh->quote($child_table) . ',';
		$sql .= $dbh->quote($type) ;
		$sql .= ')';
		$result = $dbh->query($sql);
		$manifest_id =  $dbh->lastInsertId(); 
		return( $manifest_id );
	}

	//----------------------------------------------------------------------
	// Manage (Display and Associate) People
	// This is recursive, associating people to other people
	// So the parent_id and child_id both refer to people
	// Calling function needs to know parent table is called 'parent_' + table_name
	//----------------------------------------------------------------------
	function manage_peoples($parent_table, $parent_id, $edit_yesno = 'No', $recursive = 'No'){
		$dbh = ggsd_pdo_connect();

		$sql = "SELECT DISTINCT m.manifest_id, m.manifest_type, ";
		$sql .= " a.people_id, a.people_type, a.full_name ";
		$sql .= " FROM people a, manifest m ";
		$sql .= " WHERE m.parent_table = ";
		$sql .= $dbh->quote($parent_table);
		$sql .= " AND m.parent_id = ";
		$sql .= $dbh->quote($parent_id);
		$sql .= " AND m.child_id = a.people_id";
		$sql .= " AND m.child_table = 'people'";
		$result = $dbh->query($sql);
		$rowcount = $result->rowCount();

		if ($rowcount > 0 ){
			$COLCNT=2;
			if ( $edit_yesno == 'Yes' ){
				$COLCNT++;
			}

			// Blurb
			//echo "<P class=trace>\n";
			//echo "Name link shows details.\n";
			//echo "</P>\n";

			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			echo "<TH class=ths COLSPAN=$COLCNT>People</TH>\n";
	
			echo "<TR>\n";
			if ($edit_yesno == 'Yes'){
				echo "<TH class=ths>Dis</TH>\n";
			}
			echo "<TH class=ths>Type</TH>\n";
			echo "<TH class=ths>Name</TH>\n";


			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";

				if ($edit_yesno == 'Yes'){
					echo "<TD class=tdsc><INPUT TYPE=checkbox NAME=manifest_id VALUE=$row[manifest_id]></TD>";
				}

				echo "<TD class=tds>$row[people_type]</TD>\n";
				echo "<TD class=tds><A HREF=/people.php?people_id=$row[people_id]";
				echo "&Action=View>$row[full_name]</A></TD>\n";

				echo "</TR>\n";
			}
			echo "</TABLE>\n";
			if ($edit_yesno == 'Yes'){
				echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Disassociate Checked\">\n";
			}
			echo "</FORM>\n";
		}

		//
		// Form to associate some people with this parent
		//
		if ( $edit_yesno == 'Yes' ){

			$sql = "SELECT people_id, full_name from people";
			$sql .= " WHERE people_id > 101";
			$sql .= " ORDER BY full_name";
			$People = array();
			$People = get_menu_array($sql);

			echo "<P>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			echo "<TH class=ths>Associate People</TH>\n";
			echo "<TH class=ths>Experience with or Relationship to Me</TH>\n";

			echo "<TR>\n";
              	echo "<TD CLASS=tdcs>";
				spew_select_hash_menu('people_id','','',$People);
				echo "</TD>\n";


				$sql = "SELECT choice from menu where table_name = 'manifest' ";
				$sql .= " and field_name = 'people_relation_to_me' order by choice";
				$RelatesVia = array();
				$RelatesVia = get_menu($sql);

              	echo "<TD CLASS=tdcs>";
				spew_select_menu('relates_via','','',$RelatesVia);
				echo "</TD>\n";

			echo "</TR>\n";

			echo "</TABLE>\n";

			echo "<INPUT TYPE=HIDDEN NAME=parent_${parent_table}_id VALUE=$parent_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Associate Person\">\n";
			echo "</FORM>\n";
			echo "</P>\n";
		}
	}

	//----------------------------------------------------------------------
	// Manage Contacts
	//----------------------------------------------------------------------
	function manage_contacts($parent_table, $parent_id, $edit_yesno = 'No'){
		//echo "<P class=trace>Contacts</P>\n";
		$dbh = ggsd_pdo_connect();

		$sql = "SELECT DISTINCT m.manifest_id, m.manifest_type, ";
		$sql .= "p.people_id,  p.full_name ";
		$sql .= " FROM people p,  manifest m ";
		$sql .= " WHERE m.parent_table = ";
		$sql .= $dbh->quote($parent_table);
		$sql .= " AND m.parent_id = ";
		$sql .= $dbh->quote($parent_id);
		$sql .= " AND m.child_id = p.people_id";
		$sql .= " AND m.child_table = 'people'";


		$result = $dbh->query($sql);
		$rowcount = $result->rowCount();

		if ($rowcount > 0 ){
			$COLCNT=3;
			if ($edit_yesno == 'Yes'){
				$COLCNT++;
			}

			// Blurb
			//echo "<P class=trace>Name link shows details. </P>\n";

			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			//echo "<TABLE BORDER>\n";
			echo "<TABLE CELLPADDING=3>\n";
			echo "<TH class=ths COLSPAN=$COLCNT>Contacts</TH>\n";
	
			if ($edit_yesno == 'Yes'){
				echo "<TR>\n";
				echo "<TH class=ths>Dis</TH>\n";
				echo "<TH class=ths>Name</TH>\n";
				echo "<TH class=ths>Relationship To Me</TH>\n";
				echo "</TR>\n";
			}


			while ($row = $result->fetch(PDO::FETCH_ASSOC)){

				echo "<TR>\n";

				if ($edit_yesno == 'Yes'){
					echo "<TD class=tdsc><INPUT TYPE=checkbox NAME=manifest_id VALUE=$row[manifest_id]></TD>";

				}
				echo "<TD class=tds><A HREF=/people.php?people_id=$row[people_id]";
				echo "&Action=View>$row[full_name]</A></TD>\n";
				echo "<TD class=tds>$row[manifest_type]</TD>";

				echo "</TR>\n";
			}

			echo "</TABLE>\n";

			if ($edit_yesno == 'Yes'){
				echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Disassociate Checked\">\n";
			}
			echo "</FORM>\n";
		}

		//
		// Form to associate some contact with this parent person
		//
		if ( $edit_yesno == 'Yes'){

			$sql = "SELECT people_id, full_name from people";
			$sql .= " WHERE people_id > 101 ";
			$sql .= " ORDER BY full_name";
			$People = array();
			$People = get_menu_array($sql);

			echo "<P>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			//echo "<TABLE CELLPADDING=3>\n";
			echo "<TH class=ths>Contact Name</TH>\n";
			echo "<TH class=ths>Role or Relationship To Me</TH>\n";

			echo "<TR>\n";
              	echo "<TD CLASS=tdcs>";
				spew_select_hash_menu('people_id','','',$People);
				echo "</TD>\n";

				$sql = "SELECT choice from menu where table_name = 'manifest' ";
				$sql .= " and field_name = 'people_relation_to_me' order by choice";
				$RelatesVia = array();
				$RelatesVia = get_menu($sql);

              	echo "<TD CLASS=tdcs>";
				spew_select_menu('relates_via','','',$RelatesVia);
				echo "</TD>\n";


			echo "</TR>\n";

			echo "</TABLE>\n";

			echo "<INPUT TYPE=HIDDEN NAME=parent_${parent_table}_id VALUE=$parent_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Associate Contact\">\n";
			echo "</FORM>\n";
			echo "</P>\n";
		}
		echo "<HR>\n";
	}

	//----------------------------------------------------------------------
	// Manage (Display and Associate) Organizations
	//----------------------------------------------------------------------
	function manage_organizations($parent_table, $parent_id, $edit_yesno = 'No'){
		$dbh = ggsd_pdo_connect();
		//echo "<P class=trace>Organizations</P>\n";

		$sql = "SELECT DISTINCT m.manifest_id, m.manifest_type, ";
		$sql .= " x.organization_id, x.organization_name, x.organization_type ";
		$sql .= " FROM organization x, manifest m ";
		$sql .= " WHERE m.parent_table = ";
		$sql .= $dbh->quote($parent_table);
		$sql .= " AND m.parent_id = ";
		$sql .= $dbh->quote($parent_id);
		$sql .= " AND m.child_id = x.organization_id";
		$sql .= " AND m.child_table = 'organization'";


		$result = $dbh->query($sql);
		$rowcount = $result->rowCount();


		if ($rowcount > 0 ){
			$COLCNT=2;
			if ($edit_yesno == 'Yes'){
				$COLCNT++;
			}

			// Blurb
			//echo "<P class=trace>\n";
			//echo "Name link shows details.\n";
			//echo "</P>\n";

			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			//echo "<TABLE BORDER>\n";
			echo "<TABLE CELLSPACING=3>\n";
			echo "<TH class=ths COLSPAN=$COLCNT>Organizations</TH>\n";
	
			echo "<TR>\n";
			if ($edit_yesno == 'Yes'){
				echo "<TH class=ths>Dis</TH>\n";
				echo "<TH class=ths>Name</TH>\n";
				echo "<TH class=ths>Role</TH>\n";
			}


			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";

				if ($edit_yesno == 'Yes'){
					echo "<TD class=tdsc><INPUT TYPE=checkbox NAME=manifest_id VALUE=$row[manifest_id]></TD>";
				}

				echo "<TD class=tds><A HREF=/organization.php?organization_id=$row[organization_id]";
				echo "&Action=View>$row[organization_name]</A></TD>\n";

				echo "<TD class=tds>$row[manifest_type]</TD>\n";

				echo "</TR>\n";
			}
			echo "</TABLE>\n";

			if ($edit_yesno == 'Yes'){
				echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Disassociate Checked\">\n";
			}

			echo "</FORM>\n";
		}

		//
		// Form to associate some organization with this parent
		//
		if ($edit_yesno == 'Yes'){

			$sql = "SELECT organization_id, organization_name from organization";
			$sql .= " WHERE organization_class = 'Dance Organization'";
			$sql .= " ORDER BY organization_name";

			$Orgs = array();
			$Orgs = get_menu_array($sql);
			$Orgs[0] = 'Choose';

			echo "<P>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			echo "<TH class=ths>Associate Organization</TH>\n";
			echo "<TH class=ths>My Role</TH>\n";

			echo "<TR>\n";
              	echo "<TD CLASS=tdcs>";
				spew_select_hash_menu('organization_id','Choose','Choose',$Orgs);
				echo "</TD>\n";

				$sql = "SELECT choice from menu where table_name = 'manifest' ";
				$sql .= " AND field_name = 'org_role' order by choice";
				$OrgRole = array();
				$OrgRole = get_menu($sql);

              	echo "<TD CLASS=tdcs>";
				spew_select_menu('relates_via','','',$OrgRole);
				echo "</TD>\n";
			echo "</TR>\n";

			echo "</TABLE>\n";

			echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Associate Organization\">\n";
			echo "</FORM>\n";
			echo "</P>\n";
		}
		echo "<HR>\n";
	}


	//----------------------------------------------------------------------
	// Manage (Display and Associate) Venues
	//----------------------------------------------------------------------
	function manage_venues($parent_table, $parent_id, $edit_yesno = 'No'){
		$dbh = ggsd_pdo_connect();
		//echo "<P class=trace>Venues</P>\n";

		$sql = "SELECT DISTINCT m.manifest_id, m.manifest_type, ";
		$sql .= " x.venue_id, x.venue_type, x.venue_name ";
		$sql .= " FROM venue x, manifest m ";
		$sql .= " WHERE m.parent_table = ";
		$sql .= $dbh->quote($parent_table);
		$sql .= " AND m.parent_id = ";
		$sql .= $dbh->quote($parent_id);
		$sql .= " AND m.child_id = x.venue_id";
		$sql .= " AND m.child_table = 'venue'";



		$result = $dbh->query($sql);
		$rowcount = $result->rowCount();

		if ($rowcount > 0 ){
			$COLCNT=2;
			if ($edit_yesno == 'Yes'){
				$COLCNT++;
			}

			// Blurb
			//echo "<P class=trace>\n";
			//echo "Name link shows details.\n";
			//echo "</P>\n";

			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			//echo "<TABLE BORDER>\n";
			echo "<TABLE CELLSPACING=3>\n";
			echo "<TH class=ths COLSPAN=$COLCNT>Venues</TH>\n";
	
			echo "<TR>\n";
			if ($edit_yesno == 'Yes'){
				echo "<TH class=ths>Dis</TH>\n";
				echo "<TH class=ths>Name</TH>\n";
				//echo "<TH class=ths>Type</TH>\n";
				echo "<TH class=ths>Experience</TH>\n";
			}
			echo "</TR>\n";


			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				echo "<TR>\n";

				if ($edit_yesno == 'Yes'){
					echo "<TD class=tdsc><INPUT TYPE=checkbox NAME=manifest_id VALUE=$row[manifest_id]></TD>";
				}

				echo "<TD class=tds><A HREF=/venue.php?venue_id=$row[venue_id]";
				echo "&Action=View>$row[venue_name]</A></TD>\n";

				//echo "<TD class=tds>$row[venue_type]</TD>\n";

				echo "<TD class=tds>$row[manifest_type]</TD>\n";

				echo "</TR>\n";
			}
			echo "</TABLE>\n";

			if ($edit_yesno == 'Yes'){
				echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
				echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Disassociate Checked\">\n";
			}
			echo "</FORM>\n";
		}

		//
		// Form to associate some Venue with this parent
		//
		if ($edit_yesno == 'Yes'){

			$sql = "SELECT venue_id, venue_name from venue";
			$sql .= " ORDER BY venue_name";
			$Venues = array();
			$Venues = get_menu_array($sql);
			$Venues[0] = 'Choose';

			echo "<P>\n";
			echo "<FORM ACTION=$_SERVER[PHP_SELF] TYPE=POST>\n";
			echo "<TABLE BORDER>\n";
			echo "<TH class=ths>Venue Name</TH>\n";
			echo "<TH class=ths>My Experience With Venue</TH>\n";


			echo "<TR>\n";
              	echo "<TD CLASS=tdcs>";
				spew_select_hash_menu('venue_id','Choose','Choose',$Venues);
				echo "</TD>\n";

				$sql = "SELECT choice from menu where table_name = 'manifest' ";
				$sql .= " and field_name = 'venue_relation_to_me' order by choice";
				$RelatesVia = array();
				$RelatesVia = get_menu($sql);

              	echo "<TD CLASS=tdcs>";
				spew_select_menu('relates_via','','',$RelatesVia);
				echo "</TD>\n";

			echo "</TR>\n";

			echo "</TABLE>\n";

			echo "<INPUT TYPE=HIDDEN NAME=${parent_table}_id VALUE=$parent_id>\n";
			echo "<INPUT TYPE=SUBMIT NAME=Action VALUE=\"Associate Venue\">\n";
			echo "</FORM>\n";
			echo "</P>\n";
		}
		echo "<HR>\n";
	}

	//----------------------------------------------------------------------
	//----------------------------------------------------------------------

?>

