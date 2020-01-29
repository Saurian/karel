ALTER TABLE device DROP FOREIGN KEY FK_92FB68E70608067;
DROP INDEX IDX_92FB68E70608067 ON device;
ALTER TABLE device DROP street, DROP strno, DROP zip, DROP city, CHANGE device_group_id users_groups_id INT DEFAULT NULL;
ALTER TABLE device ADD CONSTRAINT FK_92FB68E694A472E FOREIGN KEY (users_groups_id) REFERENCES users_group (id);
CREATE INDEX IDX_92FB68E694A472E ON device (users_groups_id);
ALTER TABLE device_group ADD users_groups_id INT DEFAULT NULL AFTER id;
ALTER TABLE device_group ADD CONSTRAINT FK_AB45A4A2694A472E FOREIGN KEY (users_groups_id) REFERENCES users_group (id);
CREATE INDEX IDX_AB45A4A2694A472E ON device_group (users_groups_id);
ALTER TABLE shop ADD city VARCHAR(255) DEFAULT NULL AFTER name, ADD street VARCHAR(255) DEFAULT NULL AFTER city, ADD strno VARCHAR(16) DEFAULT NULL AFTER street, ADD zip VARCHAR(255) DEFAULT NULL AFTER strno;

CREATE TABLE device_metric (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', device_id INT DEFAULT NULL, device_group_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, block_day SMALLINT NOT NULL, block_time TIME NOT NULL, inserted DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, INDEX IDX_54C65FF894A4C7D4 (device_id), INDEX IDX_54C65FF870608067 (device_group_id), INDEX IDX_54C65FF8B03A8386 (created_by_id), INDEX IDX_54C65FF8896DBBDE (updated_by_id), INDEX IDX_54C65FF8C76F1F52 (deleted_by_id), INDEX block_day_time_idx (block_time, block_day), UNIQUE INDEX device_metric_idx (device_id, device_group_id, block_day, block_time), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE devices_target_groups (device_metric_entity_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', target_group_entity_id INT NOT NULL, INDEX IDX_3D4AF86499A1D9E (device_metric_entity_id), INDEX IDX_3D4AF864398C3DF (target_group_entity_id), PRIMARY KEY(device_metric_entity_id, target_group_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE device_metric ADD CONSTRAINT FK_54C65FF894A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE;
ALTER TABLE device_metric ADD CONSTRAINT FK_54C65FF870608067 FOREIGN KEY (device_group_id) REFERENCES device_group (id) ON DELETE CASCADE;
ALTER TABLE device_metric ADD CONSTRAINT FK_54C65FF8B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id);
ALTER TABLE device_metric ADD CONSTRAINT FK_54C65FF8896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id);
ALTER TABLE device_metric ADD CONSTRAINT FK_54C65FF8C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES users (id);
ALTER TABLE devices_target_groups ADD CONSTRAINT FK_3D4AF86499A1D9E FOREIGN KEY (device_metric_entity_id) REFERENCES device_metric (id) ON DELETE CASCADE;
ALTER TABLE devices_target_groups ADD CONSTRAINT FK_3D4AF864398C3DF FOREIGN KEY (target_group_entity_id) REFERENCES target_group (id) ON DELETE CASCADE;

CREATE TABLE campaigns_target_groups (campaign_entity_id INT NOT NULL, target_group_entity_id INT NOT NULL, INDEX IDX_2B16AD1B41AD6126 (campaign_entity_id), INDEX IDX_2B16AD1B398C3DF (target_group_entity_id), PRIMARY KEY(campaign_entity_id, target_group_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE campaigns_target_groups ADD CONSTRAINT FK_2B16AD1B41AD6126 FOREIGN KEY (campaign_entity_id) REFERENCES campaign (id) ON DELETE CASCADE;
ALTER TABLE campaigns_target_groups ADD CONSTRAINT FK_2B16AD1B398C3DF FOREIGN KEY (target_group_entity_id) REFERENCES target_group (id) ON DELETE CASCADE;

CREATE TABLE calendar_devices (calendar_entity_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', device_entity_id INT NOT NULL, INDEX IDX_7370355DC249F617 (calendar_entity_id), INDEX IDX_7370355DBAC569CC (device_entity_id), PRIMARY KEY(calendar_entity_id, device_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE calendar_devices_groups (calendar_entity_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', device_group_entity_id INT NOT NULL, INDEX IDX_9FB32F27C249F617 (calendar_entity_id), INDEX IDX_9FB32F27FC8D9E51 (device_group_entity_id), PRIMARY KEY(calendar_entity_id, device_group_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE calendar_devices ADD CONSTRAINT FK_7370355DC249F617 FOREIGN KEY (calendar_entity_id) REFERENCES calendar (id) ON DELETE CASCADE;
ALTER TABLE calendar_devices ADD CONSTRAINT FK_7370355DBAC569CC FOREIGN KEY (device_entity_id) REFERENCES device (id) ON DELETE CASCADE;
ALTER TABLE calendar_devices_groups ADD CONSTRAINT FK_9FB32F27C249F617 FOREIGN KEY (calendar_entity_id) REFERENCES calendar (id) ON DELETE CASCADE;
ALTER TABLE calendar_devices_groups ADD CONSTRAINT FK_9FB32F27FC8D9E51 FOREIGN KEY (device_group_entity_id) REFERENCES device_group (id) ON DELETE CASCADE;
