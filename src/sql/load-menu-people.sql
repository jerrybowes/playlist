#------------------------------------------------------------------------
### People
#------------------------------------------------------------------------

#------------------------------------------------------------------------
## People: Gender
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'people', 'gender', 'M', 'N', 'Male');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'people', 'gender', 'F', 'N', 'Female');
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'people', 'gender', 'X', 'N', 'Other');

#------------------------------------------------------------------------
## People: Status
# people_status:	#  Active, Inactive
#------------------------------------------------------------------------
INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'people', 'people_status', 'Active', 'N', 'Active');

INSERT INTO menu (table_name, field_name, choice, is_default, description )
VALUES ( 'people', 'people_status', 'Inactive', 'N', 'Inactive');



