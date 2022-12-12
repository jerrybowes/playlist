#------------------------------------------------------------------------
# Build message
#------------------------------------------------------------------------
# Status:	development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-message.sql,v $
# $Id: build-message.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS message;
CREATE TABLE message (
	message_id		int(11) unsigned NOT NULL auto_increment PRIMARY KEY,
	meta_id 		int(11) unsigned NOT NULL,
	language		varchar(30) NOT NULL default 'US_English',
	table_name		varchar(30),
	field_name		varchar(30),
	message_type	varchar(15),
	module			varchar(30),
	context			varchar(30) NOT NULL default 'Default',
	type			varchar(30) NOT NULL default 'Default',
	short_message	text,
	long_message	text,
	last_modified	TIMESTAMP
	)    CHARACTER SET utf8
	;
