INSERT INTO `users_group` (`name`, `inserted`, `updated`) VALUES ('develop-cms.pixatori.com', now(), now());
SET @last_users_group_id = LAST_INSERT_ID();

UPDATE `users` SET `group_id` = @last_users_group_id WHERE `id` = '2';
