-- WPShadow Production Memory Issue Diagnostic
-- Run on wpshadow.com database to identify wp_options autoload bloat
-- Date: 2026-01-29

-- ============================================================
-- STEP 1: IDENTIFY THE PROBLEM
-- ============================================================

-- Count autoloaded options (should be <500)
SELECT 
    COUNT(*) as autoload_count,
    SUM(LENGTH(option_value)) as autoload_bytes,
    ROUND(SUM(LENGTH(option_value)) / 1024 / 1024, 2) as autoload_mb
FROM wp_options 
WHERE autoload = 'yes';

-- Find the 20 largest autoloaded options (main culprits)
SELECT 
    option_name,
    LENGTH(option_value) as size_bytes,
    ROUND(LENGTH(option_value) / 1024, 2) as size_kb
FROM wp_options 
WHERE autoload = 'yes'
ORDER BY size_bytes DESC 
LIMIT 20;

-- ============================================================
-- STEP 2: IDENTIFY SAFE-TO-DISABLE AUTOLOAD
-- ============================================================

-- Common plugin options that should NOT autoload:
-- - Transients (temporary cache, should never autoload)
-- - Widget settings (only needed on widget render)
-- - Large serialized arrays (>100KB)
-- - Inactive plugin settings

-- Find transients incorrectly set to autoload
SELECT 
    option_name,
    LENGTH(option_value) as size_bytes
FROM wp_options 
WHERE autoload = 'yes'
AND (
    option_name LIKE '_transient_%' 
    OR option_name LIKE '_site_transient_%'
)
ORDER BY size_bytes DESC;

-- Find large options (>100KB) that should likely not autoload
SELECT 
    option_name,
    LENGTH(option_value) as size_bytes,
    ROUND(LENGTH(option_value) / 1024, 2) as size_kb
FROM wp_options 
WHERE autoload = 'yes'
AND LENGTH(option_value) > 102400
ORDER BY size_bytes DESC;

-- ============================================================
-- STEP 3: THE FIX (Run AFTER reviewing above results)
-- ============================================================

-- BACKUP FIRST! Run this:
-- CREATE TABLE wp_options_backup_20260129 AS SELECT * FROM wp_options;

-- Fix 1: Disable autoload for ALL transients (they should NEVER autoload)
UPDATE wp_options 
SET autoload = 'no'
WHERE autoload = 'yes'
AND (
    option_name LIKE '_transient_%' 
    OR option_name LIKE '_site_transient_%'
);

-- Fix 2: Common plugin options that should NOT autoload
UPDATE wp_options 
SET autoload = 'no'
WHERE autoload = 'yes'
AND option_name IN (
    'widget_recent-posts',
    'widget_recent-comments',
    'widget_archives',
    'widget_meta',
    'widget_search',
    'widget_text',
    'widget_categories',
    'widget_calendar',
    'widget_nav_menu',
    'widget_tag_cloud',
    'widget_pages',
    'widget_media_audio',
    'widget_media_image',
    'widget_media_video',
    'widget_media_gallery',
    'widget_custom_html',
    'sidebars_widgets',
    -- Yoast SEO (large serialized arrays)
    'wpseo_titles',
    'wpseo_social',
    'wpseo_xml',
    -- WooCommerce (large transactional data)
    'woocommerce_meta_box_errors',
    'woocommerce_single_image_width',
    'woocommerce_thumbnail_image_width',
    -- Elementor (large cache arrays)
    'elementor_global_css',
    'elementor_container_width',
    -- Wordfence (large IP lists)
    'wordfence_blockedIPs',
    'wordfence_whitelisted',
    -- Akismet (stats, not needed on every load)
    'akismet_spam_count',
    'akismet_comment_count'
);

-- Fix 3: Disable autoload for any option >100KB
-- (Large options should be loaded on-demand, not every page)
UPDATE wp_options 
SET autoload = 'no'
WHERE autoload = 'yes'
AND LENGTH(option_value) > 102400;

-- ============================================================
-- STEP 4: VERIFY THE FIX
-- ============================================================

-- Re-check autoload size (should now be <1MB)
SELECT 
    COUNT(*) as autoload_count,
    SUM(LENGTH(option_value)) as autoload_bytes,
    ROUND(SUM(LENGTH(option_value)) / 1024 / 1024, 2) as autoload_mb
FROM wp_options 
WHERE autoload = 'yes';

-- ============================================================
-- STEP 5: CLEAN UP EXPIRED TRANSIENTS (Bonus)
-- ============================================================

-- Delete expired transients to free space
DELETE FROM wp_options 
WHERE option_name LIKE '_transient_timeout_%' 
AND option_value < UNIX_TIMESTAMP();

-- Delete orphaned transient values
DELETE FROM wp_options 
WHERE option_name LIKE '_transient_%' 
AND option_name NOT LIKE '_transient_timeout_%'
AND option_name NOT IN (
    SELECT REPLACE(option_name, '_transient_timeout_', '_transient_') 
    FROM wp_options 
    WHERE option_name LIKE '_transient_timeout_%'
);

-- ============================================================
-- EXPECTED RESULTS
-- ============================================================

-- BEFORE FIX: Typical problematic sites
-- - Autoload count: 1,000-3,000 options
-- - Autoload size: 5-15MB
-- - Memory exhaustion during wp_load_alloptions()

-- AFTER FIX: Healthy site
-- - Autoload count: 200-500 options
-- - Autoload size: 200KB-1MB
-- - Memory usage drops by 80-90%
-- - Page load time improves by 30-50%

-- ============================================================
-- NOTES
-- ============================================================

-- 1. ALWAYS backup before running UPDATE queries
-- 2. Test on staging first if available
-- 3. After fix, clear all caches (object cache, page cache, CDN)
-- 4. Monitor error logs for any issues
-- 5. Common culprits: Yoast SEO titles, WooCommerce product cache,
--    Elementor CSS cache, transients from old plugins
