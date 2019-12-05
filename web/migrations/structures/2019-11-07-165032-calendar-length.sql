DROP INDEX datetime_idx ON calendar;
ALTER TABLE calendar CHANGE `datetime` `from` datetime NOT NULL AFTER `users_groups_id`, ADD `to` DATETIME NOT NULL AFTER `from`;
CREATE INDEX from_to_idx ON calendar (`from`, `to`);

ALTER TABLE medium_data DROP time_type, CHANGE time time VARCHAR(32) DEFAULT NULL;
