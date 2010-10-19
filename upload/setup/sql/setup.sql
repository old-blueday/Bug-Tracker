INSERT INTO sysconf_table(program_name, version, date_format, auto_redirect, 
						  auth_method, imap_server, imap_port, 
						  mail_from_name, mail_from_email, mail_function,
						  mail_smtp_server, mail_smtp_port, mail_smtp_auth,
						  mail_smtp_user, mail_smtp_password, allow_subscribe,
						  max_area, max_minor_area, max_filter_per_user, max_shared_filter, 
						  max_syslog)
		VALUES('Bug Tracker', '', 'Y-m-d', 'f', 
			   'native', '127.0.0.1', 143, 
			   'Bug Tracker', 'root@localhost', 'mail',
			   '', 25, 'f', 
			   '', '', 'f', 
			   10, 5, 20, 5, 
			   1000);


INSERT INTO feedback_config_table(feedback_system_name, mail_from_name, mail_from_email,
								  login_mode, import_description, closed_description)
		VALUES('Customer Feedback', 'Feedback System', 'support@localhost',
			   'mode_both', 'Your report is being processed.', 
			   'Your report has been closed because the report is [__STATUS__].');

/* MySQL AUTO_INCREMENT does not allow insert 0. So we insert 1 and update to 0 */
INSERT INTO customer_table (customer_id, customer_name, created_date)
		VALUES(1, 'Anonymous', now());
UPDATE customer_table SET customer_id=0 WHERE customer_id=1;

INSERT INTO group_table (group_id, group_name, privilege) VALUES(1,'Admin', 4294967295);
UPDATE group_table SET group_id=0 WHERE group_id=1;

INSERT INTO user_table(user_id, username, password, group_id, language) values(1, 'admin', md5('admin'), 0, 'en');
UPDATE user_table SET user_id=0, created_date=now(), realname='System Administrator' WHERE user_id=1;

INSERT INTO filter_table(filter_id, user_id, filter_name, real_condition, text_condition, share_filter) values(-1, -1, '-Assigned to me', 'assign_to=@UID@', 'assign_to=@UID@', 'f');
INSERT INTO filter_table(filter_id, user_id, filter_name, real_condition, text_condition, share_filter) values(-2, -1, '-Fixed by me last week', 'fixed_by=@UID@ and (fixed_date >= @LAST_SUN@ and fixed_date <= @LAST_SAT@)', 'fixed_by=@UID@ and (fixed_date >= @LAST_SUN@ and fixed_date <= @LAST_SAT@)', 'f');

INSERT INTO status_table(status_name,status_color, status_type) values('By design', '#2f4f4f', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('Created for test', 'blue', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Could not reproduce', '#cc66cc', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('Could not verify', 'black', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Deferred', 'blue', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Duplicated', '#bdb76b', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('Fixed - need to verify', '#330099', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Fixed and verified', '#FF7F50', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('In process', '#A52A2A', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('New', 'red', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('No longer relevant', '#bdb76b', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('Not a bug', '#2f4f4f', 'closed');
INSERT INTO status_table(status_name,status_color, status_type) values('Re-do', 'red', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Re-test', 'red', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Under investigation', '#330099', 'active');
INSERT INTO status_table(status_name,status_color, status_type) values('Will not be fixed', '#2f4f4f', 'closed');

