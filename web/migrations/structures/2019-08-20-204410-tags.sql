ALTER TABLE device ADD tag VARCHAR(32) DEFAULT NULL COMMENT 'štítek';
CREATE INDEX tag_idx ON device (tag);
DROP INDEX name_idx ON device_group;
ALTER TABLE device_group ADD keywords MEDIUMTEXT DEFAULT NULL COMMENT 'fullSearch keyword', ADD tag VARCHAR(32) DEFAULT NULL COMMENT 'štítek';
CREATE INDEX tag_idx ON device_group (tag);
CREATE FULLTEXT INDEX keywords_name_idx ON device_group (name, keywords);
