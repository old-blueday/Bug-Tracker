ALTER TABLE filter_table RENAME condition TO real_condition;
INSERT INTO filter_table(filter_id, user_id, filter_name, real_condition, text_condition, share_filter) values(-1, -1, '-Assigned to me', 'assign_to=@UID@', 'assign_to=@UID@', 'f');
INSERT INTO filter_table(filter_id, user_id, filter_name, real_condition, text_condition, share_filter) values(-2, -1, '-Fixed by me last week', 'fixed_by=@UID@ and (fixed_date >= @LAST_SUN@ and fixed_date <= @LAST_SAT@)', 'fixed_by=@UID@ and (fixed_date >= @LAST_SUN@ and fixed_date <= @LAST_SAT@)', 'f');
