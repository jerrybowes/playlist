#------------------------------------------------------------------------
# Build manifest
#------------------------------------------------------------------------
# Status:	development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-manifest.sql,v $
# $Id: build-manifest.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS manifest;
CREATE TABLE manifest (
	manifest_id 		INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	parent_id			INTEGER UNSIGNED NOT NULL,
	parent_table		VARCHAR(30) NOT NULL,
	child_id			INTEGER UNSIGNED NOT NULL,
	child_table			VARCHAR(30) NOT NULL,
	manifest_type		VARCHAR(50) NOT NULL,
	notes				TEXT
)	
CHARACTER SET utf8;

