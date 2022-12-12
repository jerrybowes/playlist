#------------------------------------------------------------------------
### Access
#------------------------------------------------------------------------

#------------------------------------------------------------------------
## Access: Class
#------------------------------------------------------------------------

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'access', 'access_class', 'User', 'N', 'User');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'access', 'access_class', 'Admin', 'N', 'Admin');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
  VALUES ( 'access', 'access_class', 'Staff', 'N', 'Staff');

#------------------------------------------------------------------------
## Access: Level
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '1', 'N', '1');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '2', 'N', '2');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '3', 'N', '3');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '4', 'N', '4');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '5', 'N', '5');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '6', 'N', '6');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '7', 'N', '7');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '8', 'N', '8');
INSERT INTO menu (table_name, field_name, choice, is_default, description ) 
VALUES ( 'access', 'access_level', '9', 'N', '9');


#------------------------------------------------------------------------
## Access: Role
#------------------------------------------------------------------------

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'Volunteer', 'N', 'Volunteer');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'Guest', 'N', 'Guest');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'Member', 'Y', 'Member');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'Admin', 'N', 'Admin');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'SysAdmin', 'N', 'SysAdmin');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'access', 'access_role', 'Treasurer', 'N', 'Treasurer');
