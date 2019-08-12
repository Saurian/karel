INSERT INTO `users` (`id`, `first_name`, `last_name`, `mail`, `username`, `password`, `new_password`, `category`, `active`, `role`, `inserted`, `updated`, `position`, `created_by_id`, `updated_by_id`, `deleted_by_id`) VALUES
(1,	'Supervisor',	'Prvníh',	'admin.supervisor@carl.cz',	'admin',	'24cd7cd1550e836272cd3265600988ee', NULL, '',	1,	'supervisor',	'2018-06-06 11:42:41',	'2019-02-26 14:53:43',	1,	NULL,	1,	NULL);

INSERT INTO `medium` (`type`, `inserted`, `updated`) VALUES ('image', now(), now());
INSERT INTO `medium` (`type`, `inserted`, `updated`) VALUES ('video', now(), now());

