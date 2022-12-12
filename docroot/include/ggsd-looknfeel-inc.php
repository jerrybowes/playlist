<?php
	//#==================================================================
	//# GGSD Look And Feel (Headers and Footers)
	//#------------------------------------------------------------------
	//# $Source: /home/jbowes/ggsdinfo/src/php/include/RCS/ggsd-looknfeel-inc.php,v $
	//# $Id: ggsd-looknfeel-inc.php,v 1.4 2022/12/12 19:29:50 jbowes Exp $
	//#------------------------------------------------------------------
	//# SET EDITOR FOR 4 space TAB stops
	//# :set autoindent tabstop=4 showmatch	 (vi)
	//#==================================================================

	require_once("ggsd-config-inc.php");
	require_once("ggsd-nav-inc.php");

	//--------------------------------------------------------------------
	// Spew Header
	//--------------------------------------------------------------------
	function spew_header($FMT) {
		//
		// Set Defaults for FMT from GGSDCFG
		//
		global $GGSDCFG;

		foreach ($GGSDCFG as $key => $val ) {
			if (! empty ($FMT[$key]) ) {
				$FMT[$key] = $val;
			}
		}
		echo "<!DOCTYPE html>\n";
		echo "<HTML>\n";
		echo "<HEAD>\n";
		echo "<TITLE> $FMT[TITLE] </TITLE>\n";			// LIVEONLY

		if ( isset ( $FMT['DESCRIPTION'] )) {
			echo "<META NAME=\"DESCRIPTION\" VALUE=\"$FMT[DESCRIPTION]\">\n";
		}

		if ( isset ( $FMT['KEYWORDS'] )) {
			echo "<META NAME=\"KEYWORDS\" VALUE=\"$FMT[KEYWORDS]\">\n";
		}

		//
		// Language encoding
		//
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";

		if ( isset ( $FMT['CSS'] )) {
  			echo "<LINK HREF=/csslive/$FMT[CSS].css REL=stylesheet TYPE=text/css>\n";	// LIVEONLY
		}else{
  			echo "<LINK HREF=/csslive/ggsd.css REL=stylesheet TYPE=text/css>\n";		// LIVEONLY
		}

		//
		// Javascript files here
		//
		if (isset ($FMT['JSCHECKBOX'] ) ) {
			echo "<script type=\"text/javascript\" src=\"/jscriptlive/checkbox.js\">\n";	// LIVEONLY
			echo "</script>\n";
		}

		if (isset ($FMT['JSCHECKBOX2'] ) ) {
			echo "<script type=\"text/javascript\" src=\"/jscriptlive/checkbox2.js\">\n";	// LIVEONLY
			echo "</script>\n";
		}

		if (isset ($FMT['VALIDATE'] ) ) {
			echo "<script type=\"text/javascript\" src=\"/jscriptlive/gen_validatorv4.js\">\n";	// LIVEONLY
			echo "</script>\n";
		}

		echo "</HEAD>\n";

		echo "<CENTER>\n";
		echo "<TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 BORDER=0>\n";
		echo "<TR>\n";

			//
			// Masthead
			//

			echo "<TD ALIGN=LEFT WIDTH=10% class=masthead>\n";
			echo "<A HREF=/login.php>";
			echo "<IMG SRC=/images/ggsdinfo/logos/ggsdinfo-home-logo.png></A>";
			echo "</TD>\n";

			echo "<TD ALIGN=CENTER class=masthead>";					// LIVEONLY

			echo "<H1 class=banner>$FMT[BANNER]";


			echo "</H1>";

			if (isset ( $FMT['BANNER2'] ) ) {
				echo "<H2 class=banner>$FMT[BANNER2]</H2>";
			}

			if (isset ( $FMT['BANNER3'] ) ) {
				echo "<H3 class=banner>$FMT[BANNER3]</H3>";
			}

			if ( isset($_SESSION['full_name'])){

				if ( ! isset ( $FMT['NONAV'] ) ) {
					if ( isset($_SESSION['couple_id']) ){
						echo "<P class=trace>Oooh, that $_SESSION[full_name] is <u>such</u> a sexy dancer!</P>\n";
					}else{
						echo "<P class=trace>Welcome $_SESSION[member_type] $_SESSION[full_name]</P>\n";
					}
				}
			}

			if ( $_REQUEST['access_level'] > 8 ){
			}

			echo "</TD>\n";

			//
			// End Masthead
			//

		if ( isset ( $FMT['NONAV'] ) ) {
			// No nav bar
		}else{
			echo "<TR>\n";
			echo "<TD COLSPAN=2 class=navbar>";
			spew_navbar($FMT);
			echo "</TD>\n";
		}
		echo "</TABLE>\n";
		echo "</CENTER>\n";
		echo "<HR>\n";
		if ( isset ( $FMT['IGMAPS']) 
            || isset ( $FMT['MIGMAPS']) 
            || isset ( $FMT['LOCMAP'] )) {
			echo "<BODY onload=\"init_map()\">\n";
		}else{
			echo "<BODY>\n";
		}
	}//End spew_header

	//--------------------------------------------------------------------
	// Spew Footer
	//--------------------------------------------------------------------
	function spew_footer(array $FMT) {
		global $GGSDCFG;
		//
		// Set Defaults for FMT from GGSDCFG
		//
		foreach ($GGSDCFG as $key => $val ) {
			if ( empty( $FMT[$key]) ) {
				$FMT[$key] = $val;
			}
		}

		echo "</BODY>\n";
		// 
		echo "<CENTER>\n";
		echo "<HR>\n";

		echo "<TABLE WIDTH=100%>\n";
		echo "<TR>\n";

		if (! isset ( $FMT['NONAV'] ) ) {
			echo "<TD CLASS=navbar>";
			spew_navbar($FMT);
			echo "</TD>\n";
			echo "</TR><TR>\n";
		}
		echo "</TD></TABLE>\n";
		echo "<SMALL>\n";
		echo "Questions, feedback, or website issues; please let us know by ";
		echo "filling out the <A HREF=/feedback.php>feedback</A> form \n";
		echo " or emailing <A HREF=\"mailto:jerbowes@yahoo.com?subject=GGSD Website Feedback\">Webmaster</A>.\n";
		$me = $FMT['MODULENAME'];
		date_default_timezone_set('America/Los_Angeles');
		echo "<SMALL>\n";
		echo "<BR>Last Modified: " . date("F d Y @ H:i T", filemtime("$me"));
		echo "</SMALL>\n";
		echo "</SMALL>\n";

		echo "</CENTER>\n";
		echo "</HTML>\n";
	}//End spew_footer

?>
