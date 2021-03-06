SET NAMES utf8;
SET time_zone = '+00:00';
-- SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


INSERT INTO `campaign` (`id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `users_groups_id`, `name`, `realized_from`, `realized_to`, `active`, `tag`, `keywords`, `inserted`, `updated`, `position`, `category`, `version`) VALUES
(1,	2,	2,	NULL, 1,	'Letáky KH 1',	'2019-08-12 07:53:00',	'2019-08-14 09:53:00',	1,	'tagColor5',	NULL,	'2019-08-13 07:54:06',	'2019-08-13 08:11:51',	1,	'',	3),
(2,	2,	2,	NULL, 1,	'Lékárny Rokycany',	'2019-08-13 08:11:00',	'2019-08-15 10:11:00',	1,	'tagColor5',	NULL,	'2019-08-13 08:11:43',	'2019-08-16 13:55:55',	2,	'',	2),
(3,	2,	2,	NULL, 1,	'Kampaň barev',	'2019-09-14 08:12:00',	'2019-09-30 08:12:00',	1,	'tagColor2',	NULL,	'2019-08-13 08:12:38',	'2019-08-13 08:13:03',	3,	'',	3),
(4,	2,	2,	NULL, 1,	'Leto2019',	'2019-08-13 08:10:00',	'2019-08-13 12:15:00',	0,	'tagColor3',	NULL,	'2019-08-13 08:13:34',	'2019-08-13 09:43:42',	4,	'',	4),
(5,	2,	2,	NULL, 1,	'Derby pražských S',	'2019-10-03 08:15:00',	'2019-10-04 08:15:00',	1,	'tagColor1',	NULL,	'2019-08-13 08:19:40',	'2019-08-13 09:45:11',	5,	'',	7),
(6,	2,	2,	NULL, 1,	'Hokus pokus',	'2019-08-13 16:50:00',	'2019-08-13 16:50:00',	0,	NULL,	NULL,	'2019-08-13 16:51:12',	'2019-08-13 16:51:12',	6,	'',	1),
(7,	2,	2,	NULL, 1,	'Děkovačka',	'2019-10-03 16:00:00',	'2019-10-03 18:50:00',	1,	'tagColor1', NULL, '2019-08-13 16:51:12', '2019-08-13 16:51:12', 7,	'',	1);

INSERT INTO `campaigns_devices` (`campaign_entity_id`, `device_entity_id`) VALUES
(1,	1),
(4,	5),
(4,	6),
(5,	6),
(3,	2),
(3,	4),
(7,	6);

INSERT INTO `campaigns_devices_groups` (`campaign_entity_id`, `device_group_entity_id`) VALUES
(2,	7),
(3,	5),
(3,	7),
(3,	8),
(3,	10),
(5,	5),
(6,	15),
(6,	16);

INSERT INTO `campaigns_metrics` (`campaign_entity_id`, `metric_entity_id`) VALUES
-- (1,	3);
(1,	3),
(1,	2);

INSERT INTO `campaigns_target_groups` (`campaign_entity_id`, `target_group_entity_id`) VALUES
(1,	2);


INSERT INTO `device` (`id`, users_groups_id, `default_campaign_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `sn`, `sn_rotate`, `psw`, `name`, `active`, `keywords`, `inserted`, `updated`, `position`, `category`, `tag`) VALUES
(1, 1,	NULL,	2,	2,	NULL,	'KKH-01',	NULL,	NULL,	'Kaufland KH',	1,	NULL,	'2019-08-13 07:52:19',	'2019-08-15 20:46:27',	1,	'',	NULL),
(2, 1,	NULL,	2,	2,	NULL,	'bb-01',	NULL,	NULL,	'Bilovice letáky 1',	0,	NULL,	'2019-08-13 08:03:36',	'2019-08-13 08:03:36',	2,	'',	NULL),
(3, 1,	NULL,	2,	2,	NULL,	'KKol-01',	NULL,	NULL,	'Kaufland Kolín',	0,	NULL,	'2019-08-13 08:04:22',	'2019-08-13 08:04:22',	3,	'', NULL),
(4, 1,	NULL,	2,	2,	NULL,	'BIL-01',	NULL,	NULL,	'Billa Rokycanská',	1,	NULL,	'2019-08-13 08:04:50',	'2019-08-15 20:46:33',	4,	'',	NULL),
(5, 1,	NULL,	2,	2,	NULL,	'SkLArny-110',	NULL,	NULL,	'Sklárny',	1,	NULL,	'2019-08-13 08:05:33',	'2019-08-15 20:46:08',	5,	'',	NULL),
(6, 1,	NULL,	2,	2,	NULL,	'SLA-0100110',	NULL,	NULL,	'Na Slávii',	1,	NULL,	'2019-08-13 08:05:59',	'2019-08-15 20:46:23',	6,	'',	NULL);

INSERT INTO `devices_groups` (`device_group_entity_id`, `device_entity_id`) VALUES
(1,	2),
(1,	3),
(1,	4),
(1,	6),
(6,	5),
(9,	1),
(10,	2),
(11,	2),
(12,	2),
(17,	3),
(18,	1),
(19,	1);

DELETE FROM `device_group` WHERE `id` IN  (1, 2, 3, 4);
INSERT INTO `device_group` (`id`, users_groups_id, `root_id`, `parent_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `active`, `un_place`, `inserted`, `updated`, `position`, `category`, `lft`, `lvl`, `rgt`, `keywords`, `tag`) VALUES
(1, 1,	2,	2,	NULL,	2,	NULL,	'Nezařazené',	0,	1,	'2019-08-13 07:43:21',	'2019-08-13 08:40:29',	1,	'',	2,	1,	3,	NULL,	NULL),
(2, 1,	2,	NULL,	2,	NULL,	NULL,	'Default',	0,	0,	'2019-08-13 07:43:21',	'2019-08-13 07:43:21',	1,	'',	1,	0,	36,	NULL,	NULL),
(5, 1,	2,	2,	2,	2,	NULL,	'Středočechy',	0,	0,	'2019-08-13 07:50:11',	'2019-08-13 07:50:11',	3,	'',	4,	1,	29,	NULL,	NULL),
(6, 1,	2,	5,	2,	2,	NULL,	'Příbramsko',	0,	0,	'2019-08-13 07:50:31',	'2019-08-13 08:40:28',	4,	'',	5,	2,	12,	NULL,	NULL),
(7, 1,	2,	8,	2,	2,	NULL,	'Rokycansko',	0,	0,	'2019-08-13 07:50:40',	'2019-08-13 07:51:00',	5,	'',	18,	3,	23,	NULL,	NULL),
(8, 1,	2,	5,	2,	2,	NULL,	'Plzeň',	0,	0,	'2019-08-13 07:50:51',	'2019-08-13 09:59:39',	6,	'',	17,	2,	28,	NULL,	NULL),
(9, 1,	2,	5,	2,	2,	NULL,	'Bolka',	0,	0,	'2019-08-13 07:54:50',	'2019-09-11 09:48:31',	7,	'',	13,	2,	14,	NULL,	NULL),
(10, 1,	2,	2,	2,	2,	NULL,	'Morava Naše',	0,	0,	'2019-08-13 07:55:43',	'2019-09-11 09:34:42',	8,	'',	30,	1,	35,	'',	'tagColor2'),
(11, 1,	2,	10,	3,	2,	NULL,	'Břeclavsko',	0,	0,	'2019-08-13 07:57:54',	'2019-08-13 08:22:01',	9,	'',	31,	2,	32,	NULL,	NULL),
(12, 1,	2,	10,	2,	2,	NULL,	'Mikulovsko',	0,	0,	'2019-08-13 07:59:02',	'2019-08-13 08:22:01',	10,	'',	33,	2,	34,	NULL,	NULL),
(13, 1,	2,	8,	2,	2,	NULL,	'Dobřív',	0,	0,	'2019-08-13 07:59:34',	'2019-08-13 07:59:34',	11,	'',	24,	3,	25,	NULL,	NULL),
(14, 1,	2,	8,	2,	2,	NULL,	'Stříbro',	0,	0,	'2019-08-13 08:00:20',	'2019-08-13 08:00:20',	12,	'',	26,	3,	27,	NULL,	NULL),
(15, 1,	2,	7,	2,	2,	NULL,	'Hrádek',	0,	0,	'2019-08-13 08:00:50',	'2019-08-13 08:00:50',	13,	'',	19,	4,	20,	NULL,	NULL),
(16, 1,	2,	7,	2,	2,	NULL,	'Mýto',	0,	0,	'2019-08-13 08:01:00',	'2019-08-13 08:01:00',	14,	'',	21,	4,	22,	NULL,	NULL),
(17, 1,	2,	5,	2,	2,	NULL,	'Kolínsko',	0,	0,	'2019-08-13 08:01:13',	'2019-08-13 08:06:38',	15,	'',	15,	2,	16,	NULL,	NULL),
(18, 1,	2,	6,	2,	2,	NULL,	'Ká-Háčko',	0,	0,	'2019-08-13 08:01:36',	'2019-08-13 16:49:36',	16,	'',	6,	3,	11,	NULL,	NULL),
(19, 1,	2,	18,	2,	2,	NULL,	'Čelákovice',	0,	0,	'2019-08-13 08:01:53',	'2019-08-13 08:39:45',	17,	'',	7,	4,	10,	NULL,	NULL),
(20, 1,	2,	19,	2,	2,	NULL,	'Ještě vnořenější :)',	0,	0,	'2019-08-13 10:04:00',	'2019-08-13 10:04:00',	18,	'',	8,	5,	9,	NULL,	NULL);


INSERT INTO `medium_data` (`id`, `medium_id`, `campaign_id`, `time`, `identifier`, `file_name`, `file_path`, `sound`, `url`, `type`, `keywords`, `position`, `category`) VALUES
(1,	1,	1,	'8 minutes',	'1/70/krajina-7.jpg',	'krajina-7.jpg',	'media/1/70/krajina-7.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	3,	'1'),
(2,	1,	1,	'12 minutes',	'1/30/3731111-170208145715612-o.jpg',	'3731111-170208145715612-o.jpg',	'media/1/30/3731111-170208145715612-o.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	5,	'1'),
(3,	1,	1,	'10 minutes',	'1/f0/img_4670.jpg',	'img_4670.jpg',	'media/1/f0/img_4670.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	2,	'1'),
(4,	1,	1,	'10 minutes',	'1/db/nikon.jpeg',	'nikon.jpeg',	'media/1/db/nikon.jpeg',	NULL,	NULL,	'image/jpeg',	NULL,	0,	'1'),
(5,	1,	1,	'10 minutes',	'1/25/kamera.jpeg',	'kamera.jpeg',	'media/1/25/kamera.jpeg',	NULL,	NULL,	'image/jpeg',	NULL,	1,	'1'),
(6,	1,	4,	'10 minutes',	'4/c7/594342.jpg',	'594342.jpg',	'media/4/c7/594342.jpg',	NULL,	NULL,	'image/jpeg',	'klid před bouří',	1,	'4'),
(7,	1,	4,	'10 minutes',	'4/b2/sun-sunset-beach-ocean-sky-cloud.jpg',	'sun-sunset-beach-ocean-sky-cloud.jpg',	'media/4/b2/sun-sunset-beach-ocean-sky-cloud.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	2,	'4'),
(8,	1,	4,	'10 minutes',	'4/88/beautiful-sky-wallpaper.jpg',	'beautiful-sky-wallpaper.jpg',	'media/4/88/beautiful-sky-wallpaper.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	3,	'4'),
(9,	1,	4,	'10 minutes',	'4/aa/ocean-images-223.jpg',	'ocean-images-223.jpg',	'media/4/aa/ocean-images-223.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	4,	'4'),
(10,	1,	4,	'6 minutes',	'4/07/lj_2014_03_benin-1972.jpg',	'lj_2014_03_benin-1972.jpg',	'media/4/07/lj_2014_03_benin-1972.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	6,	'4'),
(11,	1,	4,	'10 minutes',	'4/a8/beautiful-lake-pictures-1.jpg',	'beautiful-lake-pictures-1.jpg',	'media/4/a8/beautiful-lake-pictures-1.jpg',	NULL,	NULL,	'image/jpeg',	'jezero',	8,	'4'),
(12,	1,	4,	'10 minutes',	'4/50/wallpaper.wiki-beautiful-full-hd-images-pic-wpe0011754.jpg',	'wallpaper.wiki-beautiful-full-hd-images-pic-wpe0011754.jpg',	'media/4/50/wallpaper.wiki-beautiful-full-hd-images-pic-wpe0011754.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	9,	'4'),
(13,	2,	4,	'10 minutes',	NULL,	'intro-czmangames.mp4',	'media/4/intro-czmangames.mp4',	NULL,	NULL,	'video/mp4',	'slovo\nhledat',	5,	'4'),
(14,	2,	4,	'10 minutes',	NULL,	'big_buck_bunny.mp4',	'media/4/big_buck_bunny.mp4',	NULL,	NULL,	'video/mp4',	'video dne',	7,	'4'),
(15,	1,	6,	'10 minutes',	'6/8b/mamma-mia-1.jpg',	'mamma-mia-1.jpg',	'media/6/8b/mamma-mia-1.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	1,	'6');

INSERT INTO `medium_data` (`id`, `medium_id`, `campaign_id`, `time`, `identifier`, `file_name`, `file_path`, `sound`, `url`, `type`, `keywords`, `position`, `category`) VALUES
(16,	1,	7,	'10 minutes',	'7/58/rou69c5bc_183755_3604314.jpg',	'rou69c5bc_183755_3604314.jpg',	'media/7/58/rou69c5bc_183755_3604314.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	1,	'7'),
(17,	1,	7,	'20 minutes',	'7/2c/5654744_sport-fotbal-chelsea-slavia-evropska-liga-v0.jpg',	'5654744_sport-fotbal-chelsea-slavia-evropska-liga-v0.jpg',	'media/7/2c/5654744_sport-fotbal-chelsea-slavia-evropska-liga-v0.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	2,	'7'),
(18,	1,	7,	'15 minutes',	'7/33/5664296_sport-fotbal-evropska-liga-slavia-chelsea-soucek-v0.jpg',	'5664296_sport-fotbal-evropska-liga-slavia-chelsea-soucek-v0.jpg',	'media/7/33/5664296_sport-fotbal-evropska-liga-slavia-chelsea-soucek-v0.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	3,	'7'),
(19,	1,	7,	'20 minutes',	'7/7e/m2u5mjk2mze2mzrkmwviy6qnv3tsvzfoiz0ymb13qt5qe5cb75zavkfsw0kf32t8xonhkbodp7mo21bf6bv27a2xirmfhwype6t3iixkwbcv8aztgivbsrho4j3_90ezhmuzrst7kepefgnkomg-zq70h5z3pix6aylhkgmpypho2rql2zaphmq0t7cutunijwtdjv5o6_f_e3t8xm8ikw.jpg',	'm2u5mjk2mze2mzrkmwviy6qnv3tsvzfoiz0ymb13qt5qe5cb75zavkfsw0kf32t8xonhkbodp7mo21bf6bv27a2xirmfhwype6t3iixkwbcv8aztgivbsrho4j3_90ezhmuzrst7kepefgnkomg-zq70h5z3pix6aylhkgmpypho2rql2zaphmq0t7cutunijwtdjv5o6_f_e3t8xm8ikw.jpg',	'media/7/7e/m2u5mjk2mze2mzrkmwviy6qnv3tsvzfoiz0ymb13qt5qe5cb75zavkfsw0kf32t8xonhkbodp7mo21bf6bv27a2xirmfhwype6t3iixkwbcv8aztgivbsrho4j3_90ezhmuzrst7kepefgnkomg-zq70h5z3pix6aylhkgmpypho2rql2zaphmq0t7cutunijwtdjv5o6_f_e3t8xm8ikw.jpg',	NULL,	NULL,	'image/jpeg',	NULL,	4,	'7');

INSERT INTO `metric` (`id`, `shop_id`, `target_group_id`, `metric_param_id`, `name`, `inserted`, `updated`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `users_group_id`) VALUES
(1,	1,	2,	1,	'Matky s dětmi kočárek, Matky s dětmi do 5 let, Důchodci, Babičky (nakupují pro vnoučata)',	'2019-09-12 14:13:13',	'2019-09-12 14:13:13',	2,	2,	NULL,	1),
(2,	1,	3,	5,	'Bonita zákazníků',	'2019-09-12 14:16:17',	'2019-09-12 14:16:17',	2,	2,	NULL,	1),
(3,	1,	4,	1,	'Office people',	'2019-09-12 14:21:05',	'2019-09-12 14:21:05',	2,	2,	NULL,	1),
(4,	1,	3,	6,	'Počet nákupů v daný čas',	'2019-09-12 14:23:05',	'2019-09-12 14:23:05',	2,	2,	NULL,	1);

INSERT INTO `metric_param` (`id`, `users_group_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `inserted`, `updated`) VALUES
(1,	1,	2,	2,	NULL,	'Návštěvnost',	'2019-09-11 09:40:40',	'2019-09-11 09:40:40'),
(2,	1,	2,	2,	NULL,	'Doba návštěvy',	'2019-09-11 09:40:40',	'2019-09-11 09:40:40'),
(3,	1,	2,	2,	NULL,	'Zaplnění prodejny',	'2019-09-12 14:04:53',	'2019-09-12 14:04:53'),
(4,	1,	2,	2,	NULL,	'Hluk prodejny',	'2019-09-12 14:04:53',	'2019-09-12 14:04:53'),
(5,	1,	2,	2,	NULL,	'Bonita zákazníka',	'2019-09-12 14:15:46',	'2019-09-12 14:15:46'),
(6,	1,	2,	2,	NULL,	'Nákupy',	'2019-09-12 14:22:19',	'2019-09-12 14:22:19');

INSERT INTO `metric_statistic` (`id`, `metric_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `block_time`, `block_day`, `value`, `inserted`, `updated`) VALUES
('00e3d82e-f379-468f-a56e-d36bf5a3cdf6',	2,	2,	2,	NULL,	'19:00:00',	1,	3,	'2019-09-12 14:16:49',	'2019-09-12 14:16:49'),
('0114fe6d-4906-4fc8-8c38-2b85e05c3bae',	2,	2,	2,	NULL,	'15:00:00',	2,	3,	'2019-09-12 14:17:05',	'2019-09-12 14:17:05'),
('0147bef2-1f1a-484c-9b8f-ad22452b0ced',	4,	2,	2,	NULL,	'09:00:00',	1,	5,	'2019-09-12 14:23:54',	'2019-09-12 14:23:54'),
('02983ef3-8615-4539-9dad-8b690cdc42fd',	3,	2,	2,	NULL,	'17:00:00',	3,	1,	'2019-09-12 14:21:35',	'2019-09-12 14:21:35'),
('057d42db-639c-4cf6-afb5-6c93a0115661',	2,	2,	2,	NULL,	'17:00:00',	3,	3,	'2019-09-12 14:17:20',	'2019-09-12 14:17:20'),
('068e1f98-f9c8-429a-a327-d3bb8a5cc3a6',	4,	2,	2,	NULL,	'07:00:00',	5,	2,	'2019-09-12 14:23:34',	'2019-09-12 14:23:34'),
('07705043-5d0e-4f53-bec7-bcce51748dc7',	2,	2,	2,	NULL,	'11:00:00',	2,	3,	'2019-09-12 14:17:01',	'2019-09-12 14:17:01'),
('08cf34b2-2184-4797-8e3d-dafdddf37878',	4,	2,	2,	NULL,	'19:00:00',	5,	3,	'2019-09-12 14:26:07',	'2019-09-12 14:26:07'),
('0a6064f9-30ac-4a4e-a126-241d78794ff4',	4,	2,	2,	NULL,	'16:00:00',	1,	12,	'2019-09-12 14:25:27',	'2019-09-12 14:25:27'),
('0b7a0525-38da-4e25-96fb-7a3ad14f677e',	4,	2,	2,	NULL,	'12:00:00',	5,	11,	'2019-09-12 14:24:38',	'2019-09-12 14:24:38'),
('0e404230-96eb-41fd-a029-7e0a7da140d3',	4,	2,	2,	NULL,	'15:00:00',	3,	10,	'2019-09-12 14:25:17',	'2019-09-12 14:25:17'),
('11401f9b-c208-4ac9-b54b-14e0d20bb715',	4,	2,	2,	NULL,	'16:00:00',	5,	12,	'2019-09-12 14:25:32',	'2019-09-12 14:25:32'),
('122f5920-f9e4-4215-9601-5b0bc7762b55',	2,	2,	2,	NULL,	'10:00:00',	4,	3,	'2019-09-12 14:17:27',	'2019-09-12 14:17:27'),
('124a3e99-a6f8-4071-bb1d-065803077534',	2,	2,	2,	NULL,	'09:00:00',	6,	3,	'2019-09-12 14:18:07',	'2019-09-12 14:18:07'),
('13e2bc24-634c-4ea3-8351-e79f7e9b3305',	4,	2,	2,	NULL,	'18:00:00',	3,	13,	'2019-09-12 14:25:53',	'2019-09-12 14:25:53'),
('1643973a-9bc9-4115-afca-1918c1e152cc',	2,	2,	2,	NULL,	'20:00:00',	5,	3,	'2019-09-12 14:17:47',	'2019-09-12 14:17:47'),
('16af3c0b-3da3-43f2-9775-d4b0ce2c561a',	4,	2,	2,	NULL,	'20:00:00',	2,	1,	'2019-09-12 14:26:12',	'2019-09-12 14:26:12'),
('17a660d3-760e-4d4f-9089-078a126969d7',	2,	2,	2,	NULL,	'12:00:00',	5,	3,	'2019-09-12 14:17:41',	'2019-09-12 14:17:41'),
('1829b63b-570e-43a6-98bb-043a3852c84b',	4,	2,	2,	NULL,	'15:00:00',	1,	10,	'2019-09-12 14:25:14',	'2019-09-12 14:25:14'),
('18dd324d-dc5a-48a2-827d-f11655831313',	4,	2,	2,	NULL,	'11:00:00',	6,	7,	'2019-09-12 14:24:26',	'2019-09-12 14:24:26'),
('18f2e09c-599f-4b1e-9690-0983acaa378a',	4,	2,	2,	NULL,	'15:00:00',	2,	10,	'2019-09-12 14:25:16',	'2019-09-12 14:25:16'),
('1b5f251e-cbb4-418c-9d16-ca0dc56f14fb',	4,	2,	2,	NULL,	'14:00:00',	2,	9,	'2019-09-12 14:24:59',	'2019-09-12 14:24:59'),
('1c7858b2-7666-434f-a476-da3335ef11b7',	2,	2,	2,	NULL,	'14:00:00',	4,	3,	'2019-09-12 14:17:31',	'2019-09-12 14:17:31'),
('1d3ef56f-67a2-4f21-be33-11978b42de60',	4,	2,	2,	NULL,	'11:00:00',	2,	7,	'2019-09-12 14:24:21',	'2019-09-12 14:24:21'),
('1d84c1ff-c3a7-4a6f-ad71-a3b5870657f8',	4,	2,	2,	NULL,	'18:00:00',	2,	13,	'2019-09-12 14:25:52',	'2019-09-12 14:25:52'),
('1df96472-6abc-4a89-aaff-63c7b10d793d',	2,	2,	2,	NULL,	'11:00:00',	5,	3,	'2019-09-12 14:17:40',	'2019-09-12 14:17:40'),
('1e31cc94-6569-4711-8319-73c2ce8371d3',	4,	2,	2,	NULL,	'10:00:00',	2,	6,	'2019-09-12 14:24:09',	'2019-09-12 14:24:09'),
('1f44f912-b3bd-4a66-a30a-de49824b3bd3',	4,	2,	2,	NULL,	'15:00:00',	5,	10,	'2019-09-12 14:25:20',	'2019-09-12 14:25:20'),
('20efcc0f-22c9-4b07-8ede-b0feef6f811e',	2,	2,	2,	NULL,	'14:00:00',	6,	3,	'2019-09-12 14:18:11',	'2019-09-12 14:18:11'),
('21249b1d-7d3e-48c5-9a08-f483cf682f28',	4,	2,	2,	NULL,	'07:00:00',	1,	2,	'2019-09-12 14:23:28',	'2019-09-12 14:23:28'),
('2277a2e5-66ed-464a-9d14-7d4cfe92a3b6',	4,	2,	2,	NULL,	'09:00:00',	6,	3,	'2019-09-12 14:24:01',	'2019-09-12 14:24:01'),
('2291d31e-2a65-4045-890d-cd426f0c5adf',	4,	2,	2,	NULL,	'07:00:00',	2,	2,	'2019-09-12 14:23:29',	'2019-09-12 14:23:29'),
('242c6db3-d19e-471b-a622-50cd9eb16fe4',	2,	2,	2,	NULL,	'09:00:00',	1,	2,	'2019-09-12 14:16:39',	'2019-09-12 14:16:39'),
('28430c03-96c7-4ccd-91dd-56cba94bbb0a',	1,	2,	2,	NULL,	'11:00:00',	2,	1,	'2019-09-12 14:13:45',	'2019-09-12 14:13:45'),
('28cca22c-7c18-4dba-9b25-d9926e8b7724',	3,	2,	2,	NULL,	'17:00:00',	5,	1,	'2019-09-12 14:21:44',	'2019-09-12 14:21:44'),
('29ecc56f-a8d4-4e62-90dd-dc0c8b98b142',	4,	2,	2,	NULL,	'15:00:00',	4,	10,	'2019-09-12 14:25:18',	'2019-09-12 14:25:18'),
('2bec1a82-0f00-4810-be7d-e83df466e2f8',	4,	2,	2,	NULL,	'20:00:00',	4,	1,	'2019-09-12 14:26:14',	'2019-09-12 14:26:14'),
('2dda8842-2d67-49c6-b3fc-85646d90eace',	3,	2,	2,	NULL,	'12:00:00',	1,	1,	'2019-09-12 14:21:26',	'2019-09-12 14:21:26'),
('2ed68a07-6e23-4b88-b6f5-d06a07e43a5b',	2,	2,	2,	NULL,	'15:00:00',	1,	3,	'2019-09-12 14:16:46',	'2019-09-12 14:16:46'),
('31229a3e-6355-4a5e-891f-6ca8c06a05f9',	2,	2,	2,	NULL,	'15:00:00',	6,	3,	'2019-09-12 14:18:12',	'2019-09-12 14:18:12'),
('381b6031-defe-4782-af83-9f96f2532b71',	4,	2,	2,	NULL,	'16:00:00',	2,	12,	'2019-09-12 14:25:28',	'2019-09-12 14:25:28'),
('3b4bab13-9d3f-4039-a189-8be199ef2fdb',	2,	2,	2,	NULL,	'13:00:00',	3,	3,	'2019-09-12 14:17:17',	'2019-09-12 14:17:17'),
('3e292075-558e-4ff4-94f8-9cc145ce630f',	4,	2,	2,	NULL,	'17:00:00',	2,	14,	'2019-09-12 14:25:41',	'2019-09-12 14:25:41'),
('4204df0e-d7d3-4c1e-8b85-d109aa18982d',	1,	2,	2,	NULL,	'11:00:00',	1,	1,	'2019-09-12 14:13:44',	'2019-09-12 14:13:44'),
('42c5f489-1211-4f67-8137-3a059cbac489',	4,	2,	2,	NULL,	'10:00:00',	3,	6,	'2019-09-12 14:24:10',	'2019-09-12 14:24:10'),
('43ff7fd6-972e-4948-8c28-7504458a88e4',	3,	2,	2,	NULL,	'09:00:00',	5,	1,	'2019-09-12 14:21:41',	'2019-09-12 14:21:41'),
('463d985b-c6e7-47ee-88bb-1cc176301cb7',	4,	2,	2,	NULL,	'20:00:00',	5,	1,	'2019-09-12 14:26:15',	'2019-09-12 14:26:15'),
('464f170e-c81c-4588-8a9f-da894c189862',	2,	2,	2,	NULL,	'11:00:00',	3,	3,	'2019-09-12 14:17:15',	'2019-09-12 14:17:15'),
('469f9329-abbc-45b3-92b3-3e3663191d2e',	4,	2,	2,	NULL,	'14:00:00',	4,	9,	'2019-09-12 14:25:02',	'2019-09-12 14:25:02'),
('47b91d0a-455b-4176-9276-298814c3b98d',	2,	2,	2,	NULL,	'18:00:00',	5,	3,	'2019-09-12 14:17:46',	'2019-09-12 14:17:46'),
('4915d040-9f72-42e6-b771-177ce2cdf9a1',	2,	2,	2,	NULL,	'12:00:00',	1,	3,	'2019-09-12 14:16:42',	'2019-09-12 14:16:42'),
('4a90a27b-9dd3-4698-a344-54e4018855db',	2,	2,	2,	NULL,	'09:00:00',	2,	2,	'2019-09-12 14:16:58',	'2019-09-12 14:16:58'),
('4bbc3e44-24fe-4f26-868d-684af5fa8995',	4,	2,	2,	NULL,	'14:00:00',	5,	9,	'2019-09-12 14:25:03',	'2019-09-12 14:25:03'),
('4c0ca010-e677-4e5d-8394-1de45bb4a86b',	4,	2,	2,	NULL,	'10:00:00',	6,	6,	'2019-09-12 14:24:14',	'2019-09-12 14:24:14'),
('4c5e92df-6839-414f-8125-72be39a16be2',	4,	2,	2,	NULL,	'13:00:00',	2,	8,	'2019-09-12 14:24:47',	'2019-09-12 14:24:47'),
('4c67b297-4901-4229-9789-0c97f7ed8d9e',	2,	2,	2,	NULL,	'20:00:00',	1,	3,	'2019-09-12 14:16:51',	'2019-09-12 14:16:51'),
('4e343b30-d46a-419b-9e5f-47fb02339858',	4,	2,	2,	NULL,	'11:00:00',	1,	7,	'2019-09-12 14:24:20',	'2019-09-12 14:24:20'),
('4f3ae1c2-ba15-4475-a40d-f31851a24560',	2,	2,	2,	NULL,	'13:00:00',	5,	3,	'2019-09-12 14:17:42',	'2019-09-12 14:17:42'),
('5263f511-6221-487c-aed1-0684a76654dc',	2,	2,	2,	NULL,	'07:00:00',	1,	1,	'2019-09-12 14:16:34',	'2019-09-12 14:16:34'),
('535bbbdf-988a-440d-9b54-a1943d3b1ba3',	4,	2,	2,	NULL,	'20:00:00',	1,	1,	'2019-09-12 14:26:11',	'2019-09-12 14:26:11'),
('54dcacbb-315a-424e-adea-67be8a122d39',	2,	2,	2,	NULL,	'16:00:00',	5,	33,	'2019-09-12 14:17:44',	'2019-09-12 14:17:44'),
('561fc4ec-75fe-47fe-8e6f-d45deba46df3',	2,	2,	2,	NULL,	'08:00:00',	2,	2,	'2019-09-12 14:16:54',	'2019-09-12 14:16:54'),
('56924a1f-5bcc-4420-b5b0-62075e7cd974',	2,	2,	2,	NULL,	'09:00:00',	4,	2,	'2019-09-12 14:17:27',	'2019-09-12 14:17:27'),
('576a562f-6689-4ac3-ac42-76cd9e70524c',	4,	2,	2,	NULL,	'12:00:00',	6,	4,	'2019-09-12 14:24:40',	'2019-09-12 14:24:40'),
('5a29ae6f-7c58-4a4a-b319-6877d17d1e52',	2,	2,	2,	NULL,	'18:00:00',	4,	3,	'2019-09-12 14:17:33',	'2019-09-12 14:17:33'),
('5b03bbc2-0692-45e0-a0d8-6accbb347495',	2,	2,	2,	NULL,	'13:00:00',	4,	3,	'2019-09-12 14:17:30',	'2019-09-12 14:17:30'),
('5b8eb022-2b33-4113-b440-b74166998895',	4,	2,	2,	NULL,	'19:00:00',	1,	3,	'2019-09-12 14:26:02',	'2019-09-12 14:26:02'),
('5cf4c3dd-caa5-4568-8f21-d54d7a50dc38',	4,	2,	2,	NULL,	'18:00:00',	1,	13,	'2019-09-12 14:25:51',	'2019-09-12 14:25:51'),
('5d1d28ba-1b99-4285-946c-2c60bc855988',	2,	2,	2,	NULL,	'19:00:00',	2,	3,	'2019-09-12 14:17:08',	'2019-09-12 14:17:08'),
('5d665884-d069-4d10-8b2f-c0e967959e58',	4,	2,	2,	NULL,	'10:00:00',	4,	6,	'2019-09-12 14:24:12',	'2019-09-12 14:24:12'),
('5e79b8bb-9209-4c8d-8c01-3ce3d96e19b7',	4,	2,	2,	NULL,	'20:00:00',	3,	1,	'2019-09-12 14:26:13',	'2019-09-12 14:26:13'),
('5f4be527-2ddc-44a4-9047-afb92e9168c0',	2,	2,	2,	NULL,	'16:00:00',	4,	3,	'2019-09-12 14:17:32',	'2019-09-12 14:17:32'),
('5f5fedc9-347d-467f-8c1d-af7f693c63f7',	4,	2,	2,	NULL,	'14:00:00',	1,	9,	'2019-09-12 14:24:58',	'2019-09-12 14:24:58'),
('6e8a345b-d079-40d5-adb2-1375a9eab324',	4,	2,	2,	NULL,	'08:00:00',	6,	1,	'2019-09-12 14:23:48',	'2019-09-12 14:23:48'),
('6f30748b-a9c8-4cdc-86ce-ad018e42db5b',	3,	2,	2,	NULL,	'09:00:00',	3,	1,	'2019-09-12 14:21:32',	'2019-09-12 14:21:32'),
('6f69fcf0-a00f-401f-95aa-7a071450bd26',	4,	2,	2,	NULL,	'11:00:00',	5,	7,	'2019-09-12 14:24:24',	'2019-09-12 14:24:24'),
('7433686b-76c0-4261-a1db-4cbc09d8a446',	3,	2,	2,	NULL,	'17:00:00',	4,	1,	'2019-09-12 14:21:40',	'2019-09-12 14:21:40'),
('74794b49-cae3-43bc-b188-3c5a36022ad0',	4,	2,	2,	NULL,	'14:00:00',	6,	2,	'2019-09-12 14:25:04',	'2019-09-12 14:25:04'),
('754d5c34-b840-4157-a6f5-933ccc981466',	2,	2,	2,	NULL,	'10:00:00',	3,	3,	'2019-09-12 14:17:14',	'2019-09-12 14:17:14'),
('75b9f1eb-dc68-4ef3-9dc1-6e1a89d36707',	2,	2,	2,	NULL,	'08:00:00',	4,	2,	'2019-09-12 14:17:26',	'2019-09-12 14:17:26'),
('76863091-9abe-4e2d-8fe6-d2487d2cc9a4',	3,	2,	2,	NULL,	'12:00:00',	4,	1,	'2019-09-12 14:21:38',	'2019-09-12 14:21:38'),
('774f979f-6360-4409-b132-509a4bfe1044',	3,	2,	2,	NULL,	'12:00:00',	2,	1,	'2019-09-12 14:21:30',	'2019-09-12 14:21:30'),
('7802db95-5637-40d1-a292-d904268f0007',	3,	2,	2,	NULL,	'09:00:00',	2,	1,	'2019-09-12 14:21:28',	'2019-09-12 14:21:28'),
('7846d0b1-e1fc-455d-93a7-701e460aa555',	4,	2,	2,	NULL,	'09:00:00',	3,	5,	'2019-09-12 14:23:56',	'2019-09-12 14:23:56'),
('79965556-1dfe-4dbc-b1b4-b13e39299149',	3,	2,	2,	NULL,	'17:00:00',	1,	1,	'2019-09-12 14:21:27',	'2019-09-12 14:21:27'),
('7a98f109-7ee6-4abb-86bb-1165f267e26d',	4,	2,	2,	NULL,	'17:00:00',	3,	14,	'2019-09-12 14:25:42',	'2019-09-12 14:25:42'),
('7ae4799a-6a36-4cdb-b251-41773ddd171c',	1,	2,	2,	NULL,	'10:00:00',	2,	1,	'2019-09-12 14:13:36',	'2019-09-12 14:13:36'),
('7b38d9a1-e303-4fd5-b297-413eeab070e2',	1,	2,	2,	NULL,	'11:00:00',	3,	1,	'2019-09-12 14:13:46',	'2019-09-12 14:13:46'),
('7b4bf122-31f5-4cbe-bad4-3278a9a906c7',	2,	2,	2,	NULL,	'16:00:00',	3,	3,	'2019-09-12 14:17:19',	'2019-09-12 14:17:19'),
('7c449058-186c-41ac-82ce-6ff2c71d34b9',	4,	2,	2,	NULL,	'11:00:00',	4,	7,	'2019-09-12 14:24:23',	'2019-09-12 14:24:23'),
('815c3f09-4b6a-4e32-8c14-8027c5b81807',	2,	2,	2,	NULL,	'19:00:00',	5,	3,	'2019-09-12 14:17:47',	'2019-09-12 14:17:47'),
('829ff9f4-aa93-488c-bf2b-ac3d88964eb0',	2,	2,	2,	NULL,	'10:00:00',	6,	3,	'2019-09-12 14:18:08',	'2019-09-12 14:18:08'),
('84e6ee21-9f53-4c7b-bbfa-29e00b845981',	4,	2,	2,	NULL,	'12:00:00',	3,	11,	'2019-09-12 14:24:36',	'2019-09-12 14:24:36'),
('85255b2a-6310-408d-8818-125465537ac4',	3,	2,	2,	NULL,	'12:00:00',	3,	1,	'2019-09-12 14:21:34',	'2019-09-12 14:21:34'),
('85bfd6f5-038b-4562-8544-9db014c5da18',	2,	2,	2,	NULL,	'08:00:00',	3,	2,	'2019-09-12 14:17:12',	'2019-09-12 14:17:12'),
('886a2722-5cb9-447a-985a-c8604106a1e4',	4,	2,	2,	NULL,	'10:00:00',	1,	6,	'2019-09-12 14:24:08',	'2019-09-12 14:24:08'),
('88bec94f-dda9-49e5-a83c-611e95541471',	4,	2,	2,	NULL,	'17:00:00',	4,	14,	'2019-09-12 14:25:43',	'2019-09-12 14:25:43'),
('8d51f0f8-6aec-476c-8aa4-dbec0def6e1e',	4,	2,	2,	NULL,	'09:00:00',	2,	5,	'2019-09-12 14:23:55',	'2019-09-12 14:23:55'),
('8d58ef30-740d-47f8-af87-1cfef3d98d2d',	2,	2,	2,	NULL,	'11:00:00',	4,	3,	'2019-09-12 14:17:28',	'2019-09-12 14:17:28'),
('8d6d499f-6800-4980-b6fb-98951438e76d',	2,	2,	2,	NULL,	'12:00:00',	4,	3,	'2019-09-12 14:17:29',	'2019-09-12 14:17:29'),
('90d9cd4b-781c-42b3-929e-aecdac65609f',	2,	2,	2,	NULL,	'17:00:00',	4,	3,	'2019-09-12 14:17:33',	'2019-09-12 14:17:33'),
('91b247d2-e2e2-4d69-bd83-fc73e037b479',	4,	2,	2,	NULL,	'12:00:00',	2,	11,	'2019-09-12 14:24:35',	'2019-09-12 14:24:35'),
('93658f7f-4979-4d01-95ae-ec882ff3bfe4',	2,	2,	2,	NULL,	'15:00:00',	4,	3,	'2019-09-12 14:17:31',	'2019-09-12 14:17:31'),
('9778b107-5a0f-45cc-affd-bde7971d031a',	2,	2,	2,	NULL,	'15:00:00',	3,	3,	'2019-09-12 14:17:18',	'2019-09-12 14:17:18'),
('9865448e-bc4a-4ffc-877e-07e7ab44d618',	1,	2,	2,	NULL,	'10:00:00',	6,	1,	'2019-09-12 14:13:40',	'2019-09-12 14:13:40'),
('988434c4-ca84-45a3-b81b-44e389b49fcb',	2,	2,	2,	NULL,	'20:00:00',	2,	3,	'2019-09-12 14:17:09',	'2019-09-12 14:17:09'),
('9bc56e41-5e8c-4508-b890-9f7958cf0909',	4,	2,	2,	NULL,	'13:00:00',	3,	8,	'2019-09-12 14:24:47',	'2019-09-12 14:24:47'),
('9f52e67f-5dbb-4ef2-98b6-d5d67774f841',	4,	2,	2,	NULL,	'12:00:00',	4,	11,	'2019-09-12 14:24:37',	'2019-09-12 14:24:37'),
('a11c6d7a-fad7-4e1d-b259-5643b956ce09',	2,	2,	2,	NULL,	'10:00:00',	1,	3,	'2019-09-12 14:16:40',	'2019-09-12 14:16:40'),
('a12e3a39-e912-4067-a591-9fd3d42b0a4e',	2,	2,	2,	NULL,	'17:00:00',	1,	3,	'2019-09-12 14:16:47',	'2019-09-12 14:16:47'),
('a2322803-6f99-4cac-85e6-c489a361390d',	2,	2,	2,	NULL,	'14:00:00',	2,	3,	'2019-09-12 14:17:04',	'2019-09-12 14:17:04'),
('a377d8cd-02d8-4c12-9936-e71ff0ab43d0',	2,	2,	2,	NULL,	'07:00:00',	2,	1,	'2019-09-12 14:16:53',	'2019-09-12 14:16:53'),
('a40bc51e-08a8-49f1-a99a-8410694f00df',	4,	2,	2,	NULL,	'07:00:00',	4,	2,	'2019-09-12 14:23:31',	'2019-09-12 14:23:31'),
('a449af0a-e749-43dd-8c1b-a8abfedf1462',	4,	2,	2,	NULL,	'12:00:00',	1,	11,	'2019-09-12 14:24:34',	'2019-09-12 14:24:34'),
('a67042ff-ae12-4ca1-8178-60cca40249ad',	3,	2,	2,	NULL,	'17:00:00',	2,	1,	'2019-09-12 14:21:31',	'2019-09-12 14:21:31'),
('a72ec661-38fa-4e24-861c-147fea97614c',	2,	2,	2,	NULL,	'15:00:00',	5,	3,	'2019-09-12 14:17:49',	'2019-09-12 14:17:49'),
('a7ffc1f0-f01a-4c62-bad0-a07e2158710a',	4,	2,	2,	NULL,	'19:00:00',	2,	3,	'2019-09-12 14:26:03',	'2019-09-12 14:26:03'),
('a8891e37-3573-4d7c-b80e-641be968e4f4',	2,	2,	2,	NULL,	'17:00:00',	5,	3,	'2019-09-12 14:17:45',	'2019-09-12 14:17:45'),
('ac9542ea-c603-493f-a56e-5068defea881',	3,	2,	2,	NULL,	'09:00:00',	4,	1,	'2019-09-12 14:21:37',	'2019-09-12 14:21:37'),
('ac968e2e-e31c-4f22-a4f5-c8aefb0adaeb',	4,	2,	2,	NULL,	'17:00:00',	5,	14,	'2019-09-12 14:25:45',	'2019-09-12 14:25:45'),
('ae99f040-6a8e-4131-a076-5266bcb22486',	1,	2,	2,	NULL,	'10:00:00',	7,	NULL,	'2019-09-12 14:13:41',	'2019-09-12 14:13:58'),
('b04030ed-aafd-4e5c-a649-98aa145c7de3',	4,	2,	2,	NULL,	'17:00:00',	1,	14,	'2019-09-12 14:25:40',	'2019-09-12 14:25:40'),
('b0e2f731-0c0e-4838-99c0-ca0095b112f2',	3,	2,	2,	NULL,	'09:00:00',	1,	1,	'2019-09-12 14:21:24',	'2019-09-12 14:21:24'),
('b3487710-3176-47de-9bd9-8cab294aa60d',	4,	2,	2,	NULL,	'08:00:00',	3,	4,	'2019-09-12 14:23:44',	'2019-09-12 14:23:44'),
('b76038fc-de68-4035-8fae-1d5bf979a0c8',	2,	2,	2,	NULL,	'13:00:00',	6,	3,	'2019-09-12 14:18:10',	'2019-09-12 14:18:10'),
('b9084560-268c-4178-b751-440935d521aa',	3,	2,	2,	NULL,	'12:00:00',	5,	1,	'2019-09-12 14:21:42',	'2019-09-12 14:21:42'),
('b968d5f8-b392-4cb0-8e23-cc0e96869788',	2,	2,	2,	NULL,	'10:00:00',	2,	3,	'2019-09-12 14:17:00',	'2019-09-12 14:17:00'),
('bb12ddd9-0618-4573-9bb1-b910d17cbcae',	4,	2,	2,	NULL,	'07:00:00',	3,	2,	'2019-09-12 14:23:30',	'2019-09-12 14:23:30'),
('bdb0d20c-862d-4b37-b58e-7ef383377258',	2,	2,	2,	NULL,	'14:00:00',	5,	3,	'2019-09-12 14:17:42',	'2019-09-12 14:17:42'),
('bf970d42-f4fe-42b7-9400-123b9fdd1aeb',	4,	2,	2,	NULL,	'13:00:00',	6,	5,	'2019-09-12 14:24:51',	'2019-09-12 14:24:51'),
('c1371b03-1a51-4255-9505-96ed3ca7b9bb',	4,	2,	2,	NULL,	'13:00:00',	5,	8,	'2019-09-12 14:24:49',	'2019-09-12 14:24:49'),
('c1fc8d20-ab4c-4193-96f1-b88add30940c',	4,	2,	2,	NULL,	'19:00:00',	4,	3,	'2019-09-12 14:26:05',	'2019-09-12 14:26:05'),
('c24ab47a-9cc6-4d39-a91b-cb367414ac66',	1,	2,	2,	NULL,	'11:00:00',	4,	1,	'2019-09-12 14:13:47',	'2019-09-12 14:13:47'),
('c3d8a38b-8cae-491b-a877-c7034edc15c3',	4,	2,	2,	NULL,	'14:00:00',	3,	9,	'2019-09-12 14:25:00',	'2019-09-12 14:25:00'),
('c83917b6-60bc-4919-b882-09a5d302b3c6',	2,	2,	2,	NULL,	'07:00:00',	4,	1,	'2019-09-12 14:17:25',	'2019-09-12 14:17:25'),
('c8e1b958-e745-4d9a-8e87-625cf1af9dbe',	1,	2,	2,	NULL,	'10:00:00',	5,	1,	'2019-09-12 14:13:39',	'2019-09-12 14:13:39'),
('c947116c-363f-4f12-87b7-e90d778ff23c',	2,	2,	2,	NULL,	'14:00:00',	1,	3,	'2019-09-12 14:16:45',	'2019-09-12 14:16:45'),
('ca415710-e778-426b-a364-213b43bfb877',	4,	2,	2,	NULL,	'13:00:00',	4,	8,	'2019-09-12 14:24:48',	'2019-09-12 14:24:48'),
('ca4bf3e2-3abc-46cc-911c-6f40ae129ef2',	2,	2,	2,	NULL,	'20:00:00',	4,	3,	'2019-09-12 14:17:36',	'2019-09-12 14:17:36'),
('cb340935-d724-487b-9be2-bdb1b7ddb843',	4,	2,	2,	NULL,	'19:00:00',	3,	3,	'2019-09-12 14:26:04',	'2019-09-12 14:26:04'),
('cc37e828-ffb9-4ffa-bd43-ed05373cee4b',	2,	2,	2,	NULL,	'11:00:00',	1,	3,	'2019-09-12 14:16:41',	'2019-09-12 14:16:41'),
('cf01c2f7-3b6c-4631-8416-be320edbfdcf',	1,	2,	2,	NULL,	'10:00:00',	1,	1,	'2019-09-12 14:13:35',	'2019-09-12 14:13:35'),
('d05a50f2-9a29-4477-bac1-3db1d873faa0',	4,	2,	2,	NULL,	'08:00:00',	5,	4,	'2019-09-12 14:23:45',	'2019-09-12 14:23:45'),
('d0a1446a-2f20-483a-8bee-cd50f9055bb4',	2,	2,	2,	NULL,	'13:00:00',	1,	3,	'2019-09-12 14:16:44',	'2019-09-12 14:16:44'),
('d2cc617e-92f9-4c0e-b507-b01a31f4a249',	1,	2,	2,	NULL,	'10:00:00',	4,	1,	'2019-09-12 14:13:39',	'2019-09-12 14:13:39'),
('d3138938-f489-4be6-a408-eacff8e96e46',	2,	2,	2,	NULL,	'09:00:00',	3,	2,	'2019-09-12 14:17:13',	'2019-09-12 14:17:13'),
('d3e15df5-ae6a-448b-bede-ff1cacd6861d',	2,	2,	2,	NULL,	'16:00:00',	1,	3,	'2019-09-12 14:16:46',	'2019-09-12 14:16:46'),
('d3fa46f0-3644-4c83-8535-20d83659c4f1',	1,	2,	2,	NULL,	'10:00:00',	3,	1,	'2019-09-12 14:13:37',	'2019-09-12 14:13:37'),
('d47248b8-7c45-4973-80ec-80d7088613da',	2,	2,	2,	NULL,	'19:00:00',	3,	3,	'2019-09-12 14:17:21',	'2019-09-12 14:17:21'),
('d59463ff-efde-4e57-b948-367cf7d4dbc2',	4,	2,	2,	NULL,	'16:00:00',	3,	12,	'2019-09-12 14:25:29',	'2019-09-12 14:25:29'),
('d831f817-2bb0-4382-ae89-42e50a9a4a04',	1,	2,	2,	NULL,	'11:00:00',	5,	1,	'2019-09-12 14:13:50',	'2019-09-12 14:13:50'),
('d8694418-3102-4306-896a-d9d5509f1e36',	2,	2,	2,	NULL,	'09:00:00',	5,	2,	'2019-09-12 14:17:39',	'2019-09-12 14:17:39'),
('d93e2207-174e-4ddf-aa43-dec5252c93db',	4,	2,	2,	NULL,	'11:00:00',	3,	7,	'2019-09-12 14:24:22',	'2019-09-12 14:24:22'),
('db55ea05-b3c4-407e-93c0-09b9075ad2f2',	2,	2,	2,	NULL,	'18:00:00',	2,	3,	'2019-09-12 14:17:07',	'2019-09-12 14:17:07'),
('db5af889-d310-43a6-a187-03ed4a8fa9da',	2,	2,	2,	NULL,	'17:00:00',	2,	3,	'2019-09-12 14:17:06',	'2019-09-12 14:17:06'),
('ddb284f7-21e8-46ee-b1a5-ccaea35805ae',	2,	2,	2,	NULL,	'07:00:00',	5,	1,	'2019-09-12 14:17:37',	'2019-09-12 14:17:37'),
('ddfdd6e0-44a8-4e55-86a5-dd6edbf7b9ce',	4,	2,	2,	NULL,	'13:00:00',	1,	8,	'2019-09-12 14:24:46',	'2019-09-12 14:24:46'),
('e1e84e17-03a5-4d0c-9100-97042dd0e313',	4,	2,	2,	NULL,	'09:00:00',	4,	5,	'2019-09-12 14:23:57',	'2019-09-12 14:23:57'),
('e1f945cc-2d7e-40fb-ae59-70360a8f4968',	2,	2,	2,	NULL,	'12:00:00',	6,	3,	'2019-09-12 14:18:10',	'2019-09-12 14:18:10'),
('e2b2729d-805d-4cbc-a641-45691d0110e4',	2,	2,	2,	NULL,	'19:00:00',	4,	3,	'2019-09-12 14:17:34',	'2019-09-12 14:17:34'),
('e2c96f19-4578-49e1-bd80-9fa1c74217d5',	2,	2,	2,	NULL,	'08:00:00',	5,	2,	'2019-09-12 14:17:38',	'2019-09-12 14:17:38'),
('e313f38d-226e-4a92-87f1-0f1cdbe6308d',	4,	2,	2,	NULL,	'10:00:00',	5,	6,	'2019-09-12 14:24:13',	'2019-09-12 14:24:13'),
('e3e01aa3-0eed-4274-b5c7-c161aba7c812',	4,	2,	2,	NULL,	'08:00:00',	1,	4,	'2019-09-12 14:23:42',	'2019-09-12 14:23:42'),
('e43126e4-3ca9-4e79-8f52-8666012b1247',	2,	2,	2,	NULL,	'07:00:00',	3,	1,	'2019-09-12 14:17:11',	'2019-09-12 14:17:11'),
('e5b2a844-492d-489b-bd34-db0fe0141bf7',	2,	2,	2,	NULL,	'13:00:00',	2,	3,	'2019-09-12 14:17:03',	'2019-09-12 14:17:03'),
('e63a2264-17f9-4acb-952e-a9f8dce990ee',	2,	2,	2,	NULL,	'20:00:00',	3,	3,	'2019-09-12 14:17:23',	'2019-09-12 14:17:23'),
('e6931ecf-e9ee-4c45-a0f3-dc1f5c3bdfbf',	2,	2,	2,	NULL,	'12:00:00',	3,	3,	'2019-09-12 14:17:16',	'2019-09-12 14:17:16'),
('e6edecaa-a29a-42dd-bf30-7428fc5537f0',	4,	2,	2,	NULL,	'16:00:00',	4,	12,	'2019-09-12 14:25:31',	'2019-09-12 14:25:31'),
('e762041b-e3bc-4ed0-a014-da8cc2266656',	4,	2,	2,	NULL,	'08:00:00',	2,	4,	'2019-09-12 14:23:43',	'2019-09-12 14:23:43'),
('e7a9987e-4e11-4b36-9490-9b567da25c69',	4,	2,	2,	NULL,	'18:00:00',	4,	13,	'2019-09-12 14:25:54',	'2019-09-12 14:25:54'),
('e9e6ed71-4eee-47ed-b413-e15c8b34e248',	2,	2,	2,	NULL,	'08:00:00',	6,	2,	'2019-09-12 14:18:06',	'2019-09-12 14:18:06'),
('ea110ba5-e3b5-4b19-8fdc-1ace131e3e4a',	1,	2,	2,	NULL,	'11:00:00',	6,	1,	'2019-09-12 14:13:56',	'2019-09-12 14:13:56'),
('ea8a0a16-103d-49ad-927a-0b0b3359f330',	2,	2,	2,	NULL,	'11:00:00',	6,	3,	'2019-09-12 14:18:09',	'2019-09-12 14:18:09'),
('ee4a0fcd-419b-4f98-b92c-2751178a8bc5',	2,	2,	2,	NULL,	'08:00:00',	1,	2,	'2019-09-12 14:16:38',	'2019-09-12 14:16:38'),
('ee5d4967-66f0-4879-b82f-279d4e8bf9b9',	2,	2,	2,	NULL,	'12:00:00',	2,	3,	'2019-09-12 14:17:02',	'2019-09-12 14:17:02'),
('f1547b89-eb48-48c4-9095-40f966fead8b',	2,	2,	2,	NULL,	'16:00:00',	2,	3,	'2019-09-12 14:17:05',	'2019-09-12 14:17:05'),
('f282cb8b-01d0-4fea-9811-101566db5949',	2,	2,	2,	NULL,	'10:00:00',	5,	3,	'2019-09-12 14:17:40',	'2019-09-12 14:17:40'),
('f48061e6-c6a0-4b42-b943-71d893e3c854',	4,	2,	2,	NULL,	'08:00:00',	4,	4,	'2019-09-12 14:23:45',	'2019-09-12 14:23:45'),
('f4aaf704-beaa-4a57-8a8c-464757bf1d2d',	2,	2,	2,	NULL,	'18:00:00',	3,	3,	'2019-09-12 14:17:21',	'2019-09-12 14:17:21'),
('fbef4e13-e29c-4584-98d2-6834ca18fd35',	4,	2,	2,	NULL,	'09:00:00',	5,	5,	'2019-09-12 14:23:58',	'2019-09-12 14:23:58'),
('fc99ac47-aa7f-45c1-aa92-93f2f815776d',	2,	2,	2,	NULL,	'14:00:00',	3,	3,	'2019-09-12 14:17:17',	'2019-09-12 14:17:17'),
('fd989f6a-9686-4606-9584-695cc326e0e6',	2,	2,	2,	NULL,	'18:00:00',	1,	3,	'2019-09-12 14:16:48',	'2019-09-12 14:16:48'),
('ffb26992-b15d-4a04-9af2-a51f2819d590',	4,	2,	2,	NULL,	'18:00:00',	5,	13,	'2019-09-12 14:25:56',	'2019-09-12 14:25:56');

INSERT INTO `devices_target_groups` (`device_metric_entity_id`, `target_group_entity_id`) VALUES
('70b33bd3-9b01-478d-966c-42c4b6efe69b',	2),
('90d3da0c-7131-491a-bdef-86c5067981ee',	2),
('9472b7ec-8f72-4827-ab61-752c87e2e131',	2),
('0fe3e40d-efe2-4455-b18f-befb224e4502',	4);

INSERT INTO `device_metric` (`id`, `device_id`, `device_group_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `block_day`, `block_time`, `inserted`, `updated`) VALUES
('0fe3e40d-efe2-4455-b18f-befb224e4502',	1,	NULL,	2,	2,	NULL,	1,	'10:00:00',	'2020-01-16 16:22:21',	'2020-01-16 16:22:21'),
('70b33bd3-9b01-478d-966c-42c4b6efe69b',	1,	NULL,	2,	2,	NULL,	1,	'09:00:00',	'2020-01-16 16:22:09',	'2020-01-16 16:22:09'),
('90d3da0c-7131-491a-bdef-86c5067981ee',	1,	NULL,	2,	2,	NULL,	1,	'08:00:00',	'2020-01-16 16:22:03',	'2020-01-16 16:22:03'),
('9472b7ec-8f72-4827-ab61-752c87e2e131',	1,	NULL,	2,	2,	NULL,	2,	'08:00:00',	'2020-01-16 16:22:27',	'2020-01-16 16:22:27');

INSERT INTO `shop` (`id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `open_time`, `close_time`, `open_day_of_week`, `close_day_of_week`, `inserted`, `updated`, `users_group_id`) VALUES
(1,	2,	2,	NULL,	'Svojkova',	'07:00:00',	'18:00:00',	1,	5,	'2019-09-11 09:45:43',	'2019-09-11 09:45:43',	1),
(2,	2,	2,	NULL,	'Dejvická',	'07:00:00',	'18:00:00',	1,	5,	'2019-09-12 14:09:49',	'2019-09-12 14:09:49',	1),
(3,	2,	2,	NULL,	'Alza',	'07:00:00',	'18:00:00',	1,	5,	'2019-09-12 14:10:23',	'2019-09-12 14:10:23',	1);

INSERT INTO `targets_param_values` (`target_group_entity_id`, `target_group_param_value_entity_id`) VALUES
(1,	1),
(1,	6),
(1,	7),
(2,	2),
(2,	9),
(2,	10),
(4,	11);

INSERT INTO `target_group` (`id`, `users_group_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `inserted`, `updated`) VALUES
(1,	1,	2,	2,	NULL,	'Starší řidiči',	'2019-09-11 09:46:16',	'2019-09-11 09:47:36'),
(2,	1,	2,	2,	NULL,	'Děti a důchodkyně',	'2019-09-12 14:12:33',	'2019-09-12 14:12:54'),
(3,	1,	2,	2,	NULL,	'Všichni',	'2019-09-12 14:15:04',	'2019-09-12 14:15:04'),
(4,	1,	2,	2,	NULL,	'Produktivní věk',	'2019-09-12 14:20:37',	'2019-09-12 14:20:37');

INSERT INTO `target_group_param` (`id`, `users_group_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `inserted`, `updated`) VALUES
(1,	1,	2,	2,	NULL,	'Pohlaví',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(2,	1,	2,	2,	NULL,	'Věk',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(3,	1,	2,	2,	NULL,	'Povolání',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48');

INSERT INTO `target_group_value` (`id`, `param_id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `name`, `inserted`, `updated`) VALUES
(1,	1,	2,	2,	NULL,	'muži',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(2,	1,	2,	2,	NULL,	'ženy',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(3,	2,	2,	2,	NULL,	'0-12 let',	'2019-09-11 09:43:48',	'2019-09-11 09:45:00'),
(4,	3,	2,	2,	NULL,	'Hasiči',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(5,	3,	2,	2,	NULL,	'Office people',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(6,	3,	2,	2,	NULL,	'Řidiči',	'2019-09-11 09:43:48',	'2019-09-11 09:43:48'),
(7,	2,	2,	2,	NULL,	'55-80 let',	'2019-09-11 09:44:24',	'2019-09-11 09:45:00'),
(8,	2,	2,	2,	NULL,	'18-25 let',	'2019-09-11 09:45:00',	'2019-09-11 09:45:00'),
(9,	2,	2,	2,	NULL,	'0-5 let',	'2019-09-12 14:08:15',	'2019-09-12 14:08:15'),
(10,	2,	2,	2,	NULL,	'65-99 let',	'2019-09-12 14:08:57',	'2019-09-12 14:08:57'),
(11,	2,	2,	2,	NULL,	'18-65 let',	'2019-09-12 14:19:45',	'2019-09-12 14:19:45');


INSERT INTO `users` (`id`, `created_by_id`, `updated_by_id`, `deleted_by_id`, `first_name`, `last_name`, `mail`, `username`, `password`, `new_password`, `active`, `role`, `inserted`, `updated`, `position`, `category`, `group_id`) VALUES
(3,	NULL,	3,	NULL,	'Pavel',	'Tester 2',	'admin@tester2.cz',	'admin-tester', md5('admin-tester123123'),	NULL,	1,	'admin',	'2019-08-13 07:44:03',	'2019-08-13 07:57:54',	3,	'',	1),
(5,	2, 2, null,	'Pavel',	'Editor Tester',	'editor-g1@tester.cz',	'editor-g1@tester.cz',	md5('editor-g1@tester.cz123123'),	NULL,	1,	'editor',	'2020-02-05 07:44:03', '2020-02-05 07:44:03',  4,	'',	1),
(6,	2, 2, null,	'Pavel',	'Master Tester',	'master-g1@tester.cz',	'master-g1@tester.cz',	md5('master-g1@tester.cz123123'),	NULL,	1,	'master',	'2020-02-05 07:44:03', '2020-02-05 07:44:03',  5,	'',	1),
(7,	2, 2, null,	'Pavel',	'Admin 2 Tester',	'admin-g1@tester.cz',	'admin-g1@tester.cz',	md5('admin-g1@tester.cz123123'),	NULL,	1,	'admin',	'2020-02-05 07:44:03', '2020-02-05 07:44:03',  6,	'',	1),
(8,	2, 2, null,	'Pavel',	'Watcher Tester',	'watcher-g1@tester.cz',	'watcher-g1@tester.cz',	md5('watcher-g1@tester.cz123123'),	NULL,	1,	'watcher',	'2020-02-05 07:44:03', '2020-02-05 07:44:03',  7,	'',	1),
(9,	2, 2, null,	'Pavel',	'Editor 2 Tester',	'editor2-g1@tester.cz',	'editor2-g1@tester.cz',	md5('editor2-g1@tester.cz123123'),	NULL,	0,	'editor',	'2020-02-05 07:44:03', '2020-02-05 07:44:03',  8,	'',	1);


INSERT INTO `users_devices` (`user_entity_id`, `device_entity_id`) VALUES
(2,	1),
(2,	2),
(2,	3),
(2,	4),
(2,	5),
(2,	6),

(5,	1),
(5,	4),
(6,	5),
(6,	6),
(7,	1),
(7,	2),
(8,	2),
(8,	3),
(9,	2),
(9,	3);

DELETE FROM `users_devices_groups` WHERE (`user_entity_id` = 2 AND `device_group_entity_id` IN (3, 4));
INSERT INTO `users_devices_groups` (`user_entity_id`, `device_group_entity_id`) VALUES
(2,	5),
(2,	6),
(2,	7),
(2,	8),
(2,	9),
(2,	10),
(2,	12),
(2,	13),
(2,	14),
(2,	15),
(2,	16),
(2,	17),
(2,	18),
(2,	19),
(2,	20),
(3,	3),
(3,	4),
(3,	11),

(5,	1),
(5,	5),
(5,	6),
(5,	7),
(5,	17),
(6,	5),
(6,	6),
(6,	7),
(6,	17);
