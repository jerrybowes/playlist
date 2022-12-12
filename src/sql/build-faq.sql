#-------------------------------------------------------------------------
# Build FAQ
#-------------------------------------------------------------------------
# Status: development
#-------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-faq.sql,v $
# $Id: build-faq.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS faq;
CREATE TABLE faq ( 	
	faq_id			INTEGER PRIMARY KEY AUTO_INCREMENT,
	faq_class			VARCHAR(10),
	faq_topic			VARCHAR(40),
	faq_category		VARCHAR(40),
	faq_state			VARCHAR(40),
	faq_type			VARCHAR(40),
	faq_subcategory		VARCHAR(40),
	faq_audience		VARCHAR(40),
	faq_access			VARCHAR(30) NOT NULL default 'Private',
	faq_keywords		VARCHAR(80),
	faq_summary			VARCHAR(80),
	assignee_id		INTEGER,
	shelf_life		INTEGER,
	more_info		VARCHAR(140),
	faq_content			TEXT,
	last_modified	TIMESTAMP
	);
