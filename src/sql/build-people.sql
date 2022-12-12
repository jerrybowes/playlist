#------------------------------------------------------------------------
# Build people
#------------------------------------------------------------------------
# Status: development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-people.sql,v $
# $Id: build-people.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS people;
CREATE TABLE people (
	people_id			integer unsigned NOT NULL primary key auto_increment,
	email_1				varchar(60),
	first_name			varchar(40),
	full_name			varchar(60),
	gender				enum('M','F','X') default 'X',
	headshot_url		varchar(120),
	home_city			varchar(40),
	home_country		varchar(5)  NOT NULL default 'US',
	home_state			varchar(50),
	home_street			varchar(65),
	home_zip			varchar(10),
	last_name			varchar(40),

	mobile_phone		varchar(20),
	nickname			varchar(40),
	primary_phone		varchar(20),

	people_occupation	varchar(40),
	people_status		varchar(20)  NOT NULL default 'Active',
	people_notes  		TEXT
) DEFAULT CHARSET=utf8;
