#------------------------------------------------------------------------
# Build Transaction Journal
#------------------------------------------------------------------------
# Status: development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-journal.sql,v $
# $Id: build-journal.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS journal;
CREATE TABLE journal (
	journal_id			INTEGER PRIMARY KEY AUTO_INCREMENT,
	source_table		VARCHAR(30) NOT NULL,
	source_id			INTEGER NOT NULL,
	author_id			INTEGER NOT NULL,
	#event_id			INTEGER DEFAUlt 0,
	#couple_id			INTEGER DEFAUlt 0,
	journal_type		VARCHAR(30),
	journal_access		VARCHAR(30),
	notification		VARCHAR(30),
	reference_url		VARCHAR(120),
	cc_list				VARCHAR(200),
	journal_summary		VARCHAR(80),
	date_created		TIMESTAMP,
	follow_up	 		VARCHAR(15),	
	notify				VARCHAR(15),
	journal_detail			TEXT
) ;


