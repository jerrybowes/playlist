#-------------------------------------------------------------------------
# Build menu
#-------------------------------------------------------------------------
# Status:	development
#-------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-menu.sql,v $
# $Id: build-menu.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS menu;
CREATE TABLE menu ( 	
	menu_id				INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	table_name			VARCHAR(20),
	field_name			VARCHAR(30),
	choice				VARCHAR(30),
	moreinfo_url		VARCHAR(80),
	sub_of				INTEGER UNSIGNED,
	is_default			ENUM('Y','N'),
	description			TEXT
	);
