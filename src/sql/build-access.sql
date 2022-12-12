#------------------------------------------------------------------------
# Build access
#------------------------------------------------------------------------
# Status:	development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-access.sql,v $
# $Id: build-access.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS access;
CREATE TABLE access ( 	
	access_id			INTEGER PRIMARY KEY AUTO_INCREMENT,
	people_id			INTEGER NOT NULL,
	couple_id			INTEGER NOT NULL default 0,
	access_login		VARCHAR(20) UNIQUE,
	access_password		VARCHAR(35),
	access_class		VARCHAR(20) NOT NULL,
	access_role			VARCHAR(20) NOT NULL,
	access_level		TINYINT UNSIGNED NOT NULL DEFAULT '1',
	last_updated		TIMESTAMP,
	expiration_date		DATE
	);
