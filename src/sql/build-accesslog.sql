#------------------------------------------------------------------------
# Build accesslog
#------------------------------------------------------------------------
# Status:	development
#------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-accesslog.sql,v $
# $Id: build-accesslog.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------
DROP TABLE IF EXISTS accesslog;
CREATE TABLE accesslog ( 	
	accesslog_id		INTEGER PRIMARY KEY AUTO_INCREMENT,
	access_login		VARCHAR(60),
	access_timestamp	TIMESTAMP,
	access_action		VARCHAR(30),
	access_result		VARCHAR(15),
	access_status		VARCHAR(30) NOT NULL default 'Unknown',
	people_id			INTEGER NOT NULL DEFAULT 0,
	#proxyuser_id		INTEGER NOT NULL DEFAULT 0,
	#source_ip			VARCHAR(40),	# IPV6
	source_ip			VARCHAR(15),	# IPV4
	context				TEXT
	);
