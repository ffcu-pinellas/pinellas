-- Add scheduling columns to transactions table
ALTER TABLE `transactions` 
ADD COLUMN `scheduled_at` TIMESTAMP NULL DEFAULT NULL AFTER `purpose`,
ADD COLUMN `frequency` VARCHAR(20) NULL DEFAULT NULL AFTER `scheduled_at`;

-- Optional: Create a scheduled_tasks table if you want more robust handling, 
-- but adding column to transactions is simpler for now as per user request flow.
