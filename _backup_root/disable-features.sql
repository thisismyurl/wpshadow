-- Temporary SQL to disable all features except asset-version-removal
-- This allows focus on perfecting that feature as a prototype

-- Step 1: Get the current serialized feature toggles
SELECT option_value FROM wp_options WHERE option_name = 'wpshadow_feature_toggles';

-- Step 2: We'll need to update this via PHP since it's serialized
-- Creating a companion PHP script instead...
