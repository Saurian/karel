UPDATE `device` SET `users_groups_id` = '1' WHERE `users_groups_id` is null;
UPDATE `device_group` SET `users_groups_id` = '1' WHERE `users_groups_id` is null;
