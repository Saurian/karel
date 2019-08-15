DROP INDEX UNIQ_92FB68EEFC17495 ON device;
ALTER TABLE device ADD sn_rotate SMALLINT DEFAULT NULL, CHANGE default_campaign_id default_campaign_id INT DEFAULT NULL, CHANGE device_group_id device_group_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE deleted_by_id deleted_by_id INT DEFAULT NULL, CHANGE loop_campaign_id loop_campaign_id INT DEFAULT NULL, CHANGE sn sn VARCHAR(16) NOT NULL, CHANGE psw psw VARCHAR(32) DEFAULT NULL, CHANGE strno strno VARCHAR(255) DEFAULT NULL, CHANGE inserted inserted DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL;
CREATE UNIQUE INDEX sn_rotate_idx ON device (sn, sn_rotate);
ALTER TABLE `device` CHANGE `sn_rotate` `sn_rotate` smallint(6) NULL AFTER `sn`;
