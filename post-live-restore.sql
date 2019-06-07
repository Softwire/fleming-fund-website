-- Run this a on databse that has been restored from live in order to get it in a state ready for dev/test/staging use.
-- Replace _PASSWORD_ with the admin password for that environment.

-- Disable Updraft plus automatic backups
UPDATE wordpress.wp_options SET option_value = 'manual' WHERE option_name IN ( 'updraft_interval', 'updraft_interval_database' );
-- And cancel any scheduled backups
DELETE FROM wp_options WHERE option_name = 'cron';

-- Update the password for the Softwire admin user
UPDATE wordpress.wp_users SET user_pass = md5('_PASSWORD_') WHERE user_login LIKE 'Softwire%';

-- Block search engines in robots.txt
UPDATE wordpress.wp_options SET option_value = '0' WHERE option_name = 'blog_public';
