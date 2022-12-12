#-----------------------------------------------------------------------
# Feedback (and feedback)
#-----------------------------------------------------------------------
# Status:	development
#-----------------------------------------------------------------------
# $Source: /home/jbowes/ggsdinfo/src/sql/build-feedback.sql,v $
# $Id: build-feedback.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#-----------------------------------------------------------------------
DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback (
	feedback_id				INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	cc_list					VARCHAR(240),
	contact_info			VARCHAR(120),
	feedback_category		VARCHAR(30),
	feedback_detail			TEXT,
	feedback_state			VARCHAR(30) 	DEFAULT 'New',
	feedback_status			VARCHAR(120),
	feedback_type			VARCHAR(30),
	feedback_resolution		TEXT,
	feedback_summary		VARCHAR(80),
	feedback_effort 	    VARCHAR(20),
	date_created			DATE,
	last_modified			TIMESTAMP default NOW(),
	requester_id			INTEGER UNSIGNED DEFAULT 0,
	assignee_id				INTEGER UNSIGNED DEFAULT 0,
	requester_email			VARCHAR(40),
	requester_type			VARCHAR(30),
	feedback_priority		VARCHAR(30) 	DEFAULT 'P3: Routine',
	#feedback_rank			SMALLINT UNSIGNED default 50,
	#feedback_urgency		VARCHAR(30) 	DEFAULT 'P3: Routine',
	date_closed				DATE,
	resolution_type			VARCHAR(30)
)    CHARACTER SET utf8
;






