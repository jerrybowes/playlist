
#------------------------------------------------------------------------
### Feedback
# $Source: /home/jbowes/ggsdinfo/src/sql/load-menu-feedback.sql,v $
# $Id: load-menu-feedback.sql,v 1.1 2022/12/12 17:10:46 jbowes Exp $
#------------------------------------------------------------------------

#------------------------------------------------------------------------
## Feedback: feedback_category
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Places and Venues', 'N', 'Places and Venues');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Website', 'N', 'Website');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Personal Preferences', 'N', 'Personal Preferences');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Email Notifications', 'N', 'Email Notifications');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Menu Selections', 'N', 'Menu Selections');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Dance Logistics', 'N', 'Dance Logistics');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Music', 'N', 'Music');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Dues and Payments', 'N', 'Dues and Payments');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Event Calendar', 'N', 'Event Calendar');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_category', 'Volunteering', 'N', 'Volunteering');



#------------------------------------------------------------------------
## Feedback: State,  feedback_state
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'New', 'N', 'New');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Assigned', 'N', 'Assigned');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Hold for Requester', 'N', 'Hold, Waiting Customer Input');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Complete', 'N', 'Complete');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Waiting Clarification', 'N', 'Waiting Clarification');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Closed', 'N', 'Closed');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_state', 'Stuck', 'N', 'Stuck waiting on other factors');

#------------------------------------------------------------------------
## Feedback: feedback_type
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Event Suggestion', 'N', 'Event Suggestion');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Event Feedback', 'N', 'Event Feedback');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Website Feedback', 'N', 'Website Feedback');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Other', 'N', 'Other');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Information Request', 'N', 'Information Request');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Lost and Found', 'N', 'Lost and Found');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_type', 'Action Request', 'N', 'Action Request');

#------------------------------------------------------------------------
## Feedback: requester_type
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'requester_type', 'Club Member', 'N', 'Club Member');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'requester_type', 'Club Guest', 'N', 'Club Guest');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'requester_type', 'Prospective Visitor', 'N', 'Prospective Visitor');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'requester_type', 'Internet Citizen', 'N', 'Internet Citizen');



#------------------------------------------------------------------------
## Feedback: Priority
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_priority', 'P1: Critical', 'N', 'P1: Critical');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_priority', 'P2: Urgent', 'N', 'P2: Urgent');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_priority', 'P3: Routine', 'N', 'P3: Routine');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_priority', 'P4: Future', 'N', 'P4: Future');


#------------------------------------------------------------------------
## Feedback: Resolution
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Phoned information', 'N', 'Phoned information');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Emailed information', 'N', 'Emailed information');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Updated admin', 'N', 'Updated admin');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Updated website data', 'N', 'Updated website data');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Updated website', 'N', 'Updated website');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Placed on BOD agenda', 'N', 'Placed on BOD agenda');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'Could Not Reproduce', 'N', 'Could Not Reproduce');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'resolution_type', 'No Action', 'N', 'No Action');


#------------------------------------------------------------------------
## Feedback: Urgency
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_urgency', 'P1: Critical', 'N', 'P1: Critical');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_urgency', 'P2: Urgent', 'N', 'P2: Urgent');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_urgency', 'P3: Routine', 'N', 'P3: Routine');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'feedback', 'feedback_urgency', 'P4: Future', 'N', 'P4: Future');






