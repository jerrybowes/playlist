
#------------------------------------------------------------------------
### FAQ
#------------------------------------------------------------------------
#------------------------------------------------------------------------
## Faq: Audience
#------------------------------------------------------------------------

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_audience', 'Vendors', 'N', 'Instructors');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_audience', 'Members', 'N', 'Members');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_audience', 'Guests', 'N', 'Guests');

#------------------------------------------------------------------------
## Faq: Access
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_access', 'Public', 'N', 'Public');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_access', 'Private', 'N', 'Private');

#------------------------------------------------------------------------
## Faq: Class
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Purchasing', 'N', 'Purchasing');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'User Accounts', 'N', 'Account Management, and Security');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Dance Technique', 'N', 'Dance Technique');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Team Policy', 'N', 'Team Policy');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Facilities', 'N', 'Facilities');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Social Protocol', 'N', 'Social Protocol');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Admin', 'N', 'General Administration');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'faq', 'faq_class', 'Scheduling', 'N', 'Scheduling');

#------------------------------------------------------------------------
## Faq: Shelf Life
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'shelf_life', '15', 'N', '15');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'shelf_life', '30', 'N', '30');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'shelf_life', '45', 'Y', '45');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'shelf_life', '60', 'N', '60');

#------------------------------------------------------------------------
## Faq: State
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_state', 'New', 'Y', 'New');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_state', 'Draft', 'N', 'Draft');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_state', 'Review', 'N', 'Review');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_state', 'Released', 'N', 'Released');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'faq', 'faq_state', 'Stale', 'N', 'Stale');

