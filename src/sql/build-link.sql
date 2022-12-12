#-------------------------------------------------------------------------
# Build Links
#-------------------------------------------------------------------------
#-------------------------------------------------------------------------
# $Id: build-link.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS link;
CREATE TABLE link ( 	
	link_id				INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	link_audience		VARCHAR(40),
	contributor_id		INTEGER UNSIGNED,
	link_url			VARCHAR(120),
	link_topic			VARCHAR(40),
	link_order			INTEGER UNSIGNED,
	link_importance		TINYINT UNSIGNED,
	link_type			VARCHAR(40),
	link_access			VARCHAR(30),
	link_keywords		VARCHAR(160),
	link_state			VARCHAR(40),
	link_name			VARCHAR(80),
	last_modified		TIMESTAMP
);
