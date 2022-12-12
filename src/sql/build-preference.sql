#------------------------------------------------------------------------
# Build preference
#------------------------------------------------------------------------
# Status:	dev
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-preference.sql,v $
# $Id: build-preference.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS preference;
CREATE TABLE preference (
	preference_id 			integer unsigned PRIMARY KEY auto_increment,
	preference_name			varchar(80),
	preference_class		varchar(30),	# User, Application, System
	preference_type			varchar(30),	# Binary, Range, Choice, Query, QueryArray
	preference_tag			varchar(30),	# Cross Reference
	preference_default		varchar(30),
	preference_description	text,
	range_list				varchar(200),
	responds_to				integer unsigned,
	responds_how			varchar(15),	# Same, Opposite, Blank, Delete
	is_required				ENUM('Yes','No') default 'No',
	email_notify			varchar(240),
	contact_id				integer unsigned,
	url						varchar(240)
	)    CHARACTER SET utf8
	;

