#-------------------------------------------------------------------------
# Build allcal
#-------------------------------------------------------------------------
# Status:	development
#-------------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-allcal.sql,v $
# $Id: build-allcal.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-------------------------------------------------------------------------
#ID|YYYYMMDD|YYYY-MM_DD|Year|Month|Day|
#WeekOfYear|WeekOfMonth|BOW_Month_of_Year|BOW_Day|
#Weekday|is_bow|Julian
#-------------------------------------------------------------------------
DROP TABLE IF EXISTS allcal;
CREATE TABLE allcal (
	allcal_id		INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	ymd_date		INTEGER UNSIGNED NOT NULL,	# YYYYMMDD
	calendar_date	DATE NOT NULL,	# YYYY-MM-DD
	year			SMALLINT NOT NULL,	# 4 digit year
	month			SMALLINT NOT NULL,	# 1-2 digit month
	day				SMALLINT NOT NULL,	# 1-2 digit day
	week_of_year	SMALLINT NOT NULL,	# 1-2 digit full week [1-53]
	week_of_month	SMALLINT NOT NULL,	# 1 digit full week [1-6]
	bow_month		SMALLINT NOT NULL,	# Beginning of current week, month
	bow_day			SMALLINT NOT NULL,	# Beginning of current week, day 
	weekday			VARCHAR(4) NOT NULL,	# Day of Week [Mon, Tue, Wed...]
	is_bow			TINYINT DEFAULT '0',
	julian			SMALLINT
)
DEFAULT CHARSET=utf8;


