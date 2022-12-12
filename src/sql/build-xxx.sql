
#-------------------------------------------------------------------------
# Build test table xxx
#-------------------------------------------------------------------------
# Status: development
#-------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/RCS/build-xxx.sql,v $
# $Id: build-xxx.sql,v 1.1 2022/12/12 18:54:42 jbowes Exp $
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS xxx;
CREATE TABLE xxx ( 	
	xxx_id			INTEGER PRIMARY KEY AUTO_INCREMENT,
	xxx_class			VARCHAR(10),
	xxx_topic			VARCHAR(40),
	xxx_category		VARCHAR(40),
	xxx_name			VARCHAR(40),
	xxx_type			VARCHAR(40),
	xxx_keywords		VARCHAR(80),
	xxx_summary			VARCHAR(80),
	xxx_content			TEXT,
	last_modified	TIMESTAMP
	);
