ALTER TABLE calendar ADD users_groups_id INT DEFAULT NULL, ADD datetime DATETIME NOT NULL;
ALTER TABLE calendar ADD CONSTRAINT FK_6EA9A146694A472E FOREIGN KEY (users_groups_id) REFERENCES users_group (id);
ALTER TABLE calendar ADD percentage SMALLINT DEFAULT 0 NOT NULL;

CREATE INDEX IDX_6EA9A146694A472E ON calendar (users_groups_id);
CREATE INDEX datetime_idx ON calendar (datetime);
ALTER TABLE campaign ADD users_groups_id INT DEFAULT NULL;
ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD694A472E FOREIGN KEY (users_groups_id) REFERENCES users_group (id);
CREATE INDEX IDX_1F1512DD694A472E ON campaign (users_groups_id);

CREATE TABLE campaigns_metrics (campaign_entity_id INT NOT NULL, metric_entity_id INT NOT NULL, INDEX IDX_77EC5241AD6126 (campaign_entity_id), INDEX IDX_77EC52D79C98E4 (metric_entity_id), PRIMARY KEY(campaign_entity_id, metric_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE campaigns_metrics ADD CONSTRAINT FK_77EC5241AD6126 FOREIGN KEY (campaign_entity_id) REFERENCES campaign (id) ON DELETE CASCADE;
ALTER TABLE campaigns_metrics ADD CONSTRAINT FK_77EC52D79C98E4 FOREIGN KEY (metric_entity_id) REFERENCES metric (id) ON DELETE CASCADE;
ALTER TABLE device DROP FOREIGN KEY FK_92FB68ED78257DA;
DROP INDEX IDX_92FB68ED78257DA ON device;
ALTER TABLE device DROP loop_campaign_id;

-- reorganize positions structure --
ALTER TABLE device CHANGE `tag` `tag` varchar(32) COLLATE 'utf8_unicode_ci' NULL COMMENT 'štítek' AFTER `name`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `version`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `version`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `version`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;

ALTER TABLE `metric_statistic` CHANGE `block_time` `block_time` time NOT NULL AFTER `block_day`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `value`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `value`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `value`;

ALTER TABLE `calendar` CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `percentage`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `percentage`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `percentage`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;

ALTER TABLE `device_group` CHANGE `tag` `tag` varchar(32) COLLATE 'utf8_unicode_ci' NULL COMMENT 'štítek' AFTER `active`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `keywords`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `keywords`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `keywords`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;

ALTER TABLE `medium` CHANGE `type` `type` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;

ALTER TABLE `metric` CHANGE `users_group_id` `users_group_id` int(11) NULL AFTER `metric_param_id`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;

ALTER TABLE `metric_param` CHANGE `name` `name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;

ALTER TABLE `shop` CHANGE `users_group_id` `users_group_id` int(11) NULL AFTER `id`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `close_day_of_week`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `close_day_of_week`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `close_day_of_week`;

ALTER TABLE `target_group` CHANGE `name` `name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;
ALTER TABLE `target_group_param` CHANGE `name` `name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;
ALTER TABLE `target_group_value` CHANGE `name` `name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;

ALTER TABLE `users` CHANGE `group_id` `group_id` int(11) NULL AFTER `id`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `category`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `category`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `category`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;

ALTER TABLE campaign CHANGE `users_groups_id` `users_groups_id` int(11) NULL AFTER `id`,
CHANGE `deleted_by_id` `deleted_by_id` int(11) NULL AFTER `version`,
CHANGE `updated_by_id` `updated_by_id` int(11) NULL AFTER `version`,
CHANGE `created_by_id` `created_by_id` int(11) NULL AFTER `version`,
CHANGE `updated` `updated` datetime NULL AFTER `deleted_by_id`,
CHANGE `inserted` `inserted` datetime NULL AFTER `deleted_by_id`;
-- reorganize positions structure --
