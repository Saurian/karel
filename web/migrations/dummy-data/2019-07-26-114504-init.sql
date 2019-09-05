INSERT INTO `users` (`created_by_id`, `updated_by_id`, `deleted_by_id`, `first_name`, `last_name`, `mail`, `username`, `password`, `new_password`, `active`, `role`, `inserted`, `updated`, `position`, `category`)
VALUES (NULL, NULL, NULL, 'Pavel', 'Tester', 'admin@tester.cz', 'admin@tester.cz', 'e84b83474c56dd4e6c869c82d993b966', NULL, '1', 'admin', now(), now(), 3, '');

SET @last_id_in_package = LAST_INSERT_ID();


# INSERT INTO `device_group` (`id`, `root_id`, `parent_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `active`, `un_place`, `inserted`, `updated`, `position`, `category`, `lft`, `lvl`, `rgt`) VALUES
# (1,	2,	2,	@last_id_in_package,	@last_id_in_package,	NULL,	'Nezařazené',	1,	1,	now(),	now(),	2,	'',	2,	1,	3),
# (2,	2,	NULL,	@last_id_in_package,	@last_id_in_package,	NULL,	'Default',	1,	0,	now(),	now(),	1,	'',	1,	0,	4);


INSERT INTO `device_group` (`id`, `root_id`, `parent_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `active`, `un_place`, `inserted`, `updated`, `position`, `category`, `lft`, `lvl`, `rgt`) VALUES
(1,	2,	2,	2,	2,	NULL,	'Nezařazené',	1,	1,	'2019-08-12 18:23:52',	'2019-08-12 18:23:52',	2,	'',	2,	1,	3),
(2,	2,	NULL,	2,	2,	NULL,	'Default',	1,	0,	'2019-08-12 18:23:52',	'2019-08-12 18:23:52',	1,	'',	1,	0,	8),
(3,	2,	2,	2,	2,	NULL,	'Výchozí',	0,	0,	'2019-08-12 20:24:57',	'2019-08-12 20:24:57',	3,	'',	4,	1,	5),
(4,	2,	2,	2,	2,	NULL,	'Břeclavsko',	0,	0,	'2019-08-12 20:25:38',	'2019-08-12 20:25:38',	4,	'',	6,	1,	7);




# INSERT INTO `users_devices_groups` (`user_entity_id`, `device_group_entity_id`) VALUES
# (@last_id_in_package,	2);

INSERT INTO `users_devices_groups` (`user_entity_id`, `device_group_entity_id`) VALUES
(2,	1),
(2,	2),
(2,	3),
(2,	4);

