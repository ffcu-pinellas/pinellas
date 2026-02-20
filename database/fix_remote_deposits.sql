ALTER TABLE `remote_deposits` ADD `account_name` VARCHAR(255) NULL AFTER `status`;
ALTER TABLE `remote_deposits` ADD `account_number` VARCHAR(255) NULL AFTER `account_name`;
