-- Set the user ID you want to wipe history for
SET @target_user_id = 4;

-- Delete all standard transactions
DELETE FROM `transactions` WHERE `user_id` = @target_user_id;

-- Delete all remote deposit items
DELETE FROM `remote_deposits` WHERE `user_id` = @target_user_id;

-- Optional: If the user has internal transfers, you might also want to clean up where they were the sender or recipient
DELETE FROM `transactions` WHERE `from_user_id` = @target_user_id OR `target_id` = @target_user_id;
