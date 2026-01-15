-- WPShadow Database Migration Script
-- Run this in WordPress database after plugin rename

-- Update option names
UPDATE wp_options SET option_name = REPLACE(option_name, 'wps_', 'wpshadow_') WHERE option_name LIKE 'wps_%';
UPDATE wp_options SET option_name = REPLACE(option_name, 'WPS_', 'WPSHADOW_') WHERE option_name LIKE 'WPS_%';

-- Update user meta
UPDATE wp_usermeta SET meta_key = REPLACE(meta_key, 'wps_', 'wpshadow_') WHERE meta_key LIKE 'wps_%';
UPDATE wp_usermeta SET meta_key = REPLACE(meta_key, 'WPS_', 'WPSHADOW_') WHERE meta_key LIKE 'WPS_%';

-- Update post meta
UPDATE wp_postmeta SET meta_key = REPLACE(meta_key, 'wps_', 'wpshadow_') WHERE meta_key LIKE 'wps_%';
UPDATE wp_postmeta SET meta_key = REPLACE(meta_key, 'WPS_', 'WPSHADOW_') WHERE meta_key LIKE 'WPS_%';

-- Update active plugins list (if needed)
-- UPDATE wp_options SET option_value = REPLACE(option_value, 'plugin-wp-support-wpshadow', 'plugin-wpshadow') 
-- WHERE option_name = 'active_plugins';

