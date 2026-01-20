<?php
/**
 * TIMEZONE SYSTEM - QUICK REFERENCE
 * 
 * This file documents the complete timezone detection and alignment system.
 * Read this if you need to extend or debug timezone functionality.
 * 
 * ============================================================================
 * ARCHITECTURE OVERVIEW
 * ============================================================================
 * 
 * The timezone system aligns WordPress to the admin's actual timezone, not
 * the server's timezone (which is often UTC or a different location).
 * 
 * COMPONENTS:
 * 1. Timezone_Manager (PHP class) - Core logic
 * 2. JavaScript detection script - Runs on every admin page load
 * 3. AJAX endpoints - Server-side handlers
 * 4. Settings tool - UI for manual override
 * 
 * ============================================================================
 * DATA FLOW
 * ============================================================================
 * 
 * AUTOMATIC (On page load):
 * ├─ Admin page loads
 * ├─ timezone-detection.js enqueued (if user can manage_options)
 * ├─ JS detects browser timezone: new Intl.DateTimeFormat().resolvedOptions().timeZone
 * ├─ JS sends via AJAX to wpshadow_detect_timezone
 * ├─ PHP validates timezone with DateTimeZone and is_valid_timezone()
 * ├─ Stores in wp_options[wpshadow_admin_timezone]
 * ├─ Updates wp_options[timezone_string] for WordPress core
 * └─ (Optional) Stores in user_meta for per-user tracking
 * 
 * MANUAL (Via settings tool):
 * ├─ Admin navigates to Timezone Alignment tool
 * ├─ Shows current WordPress tz vs Server tz
 * ├─ Option to "Detect & Apply My Timezone" (same as above)
 * ├─ Or manually select from US timezone dropdown
 * ├─ Selection sends to wpshadow_set_timezone AJAX endpoint
 * ├─ Same validation and storage as auto-detection
 * └─ Page reloads to show updated timezone
 * 
 * ============================================================================
 * KEY FUNCTIONS
 * ============================================================================
 * 
 * Timezone_Manager::init()
 *   ├─ Enqueues timezone detection script on admin pages
 *   └─ Registers AJAX endpoints
 * 
 * Timezone_Manager::get_admin_timezone()
 *   └─ Returns current admin timezone (with fallbacks)
 *   └─ Check order: wpshadow_admin_timezone option → timezone_string option → 'UTC'
 * 
 * Timezone_Manager::set_admin_timezone($timezone)
 *   ├─ Validates timezone with DateTimeZone
 *   ├─ Updates wpshadow_admin_timezone option
 *   ├─ Updates timezone_string option (WordPress core)
 *   └─ Updates user meta for current admin
 * 
 * Timezone_Manager::is_valid_timezone($timezone)
 *   ├─ Quick check against common US timezones
 *   └─ Falls back to DateTimeZone validation for others
 * 
 * Timezone_Manager::get_timezone_abbreviation($timezone)
 *   └─ Returns 'MST', 'EST', 'PDT', etc. for display
 * 
 * Timezone_Manager::get_timezone_suggestion()
 *   └─ Detects if timezone is way off from server
 *   └─ Useful for suggesting admin that update is needed
 * 
 * ============================================================================
 * TIMEZONE VALIDATION
 * ============================================================================
 * 
 * WHY VALIDATION MATTERS:
 * - JavaScript sends timezone as IANA string (America/Denver)
 * - Must be valid PHP timezone identifier
 * - Some browsers might send garbage data
 * - Protection against injection or invalid input
 * 
 * VALIDATION PROCESS:
 * 1. Check against common US timezones (fast path)
 * 2. Fall back to DateTimeZone construction (comprehensive)
 * 3. Throws exception if invalid → caught and returns false
 * 
 * VALID TIMEZONE EXAMPLES:
 * - America/Denver (MST/MDT)
 * - America/New_York (EST/EDT)
 * - America/Phoenix (MST, no DST)
 * - America/Los_Angeles (PST/PDT)
 * - Pacific/Honolulu (HST)
 * - UTC (fallback)
 * 
 * ============================================================================
 * WORDPRESS INTEGRATION
 * ============================================================================
 * 
 * THIS SYSTEM UPDATES:
 * - wp_options[timezone_string] ← WordPress uses this for all dates
 * - wp_options[wpshadow_admin_timezone] ← WPShadow tracking
 * - wp_usermeta[wpshadow_timezone] ← Per-user tracking (optional)
 * 
 * RESULT: All timestamps displayed in admin use admin's timezone, not server's
 * 
 * ============================================================================
 * EXTENDING THE SYSTEM
 * ============================================================================
 * 
 * TO ADD A NEW TIMEZONE:
 * 1. Add to get_us_timezones() array in Timezone_Manager
 * 2. Validate against DateTimeZone (automatic in is_valid_timezone)
 * 3. JavaScript auto-detection handles all IANA zones
 * 
 * TO SUPPORT INTERNATIONAL TIMEZONES:
 * 1. Modify get_us_timezones() to include other regions
 * 2. System already supports all IANA timezone identifiers
 * 3. Just add more options to the dropdown
 * 
 * TO HOOK INTO TIMEZONE CHANGES:
 * 1. add_option/update_option hooks on wpshadow_admin_timezone
 * 2. or do_action on timezone changes (could be added)
 * 3. Or use 'wp_timezone_choice' filter if extending timezone_string
 * 
 * ============================================================================
 * SECURITY CONSIDERATIONS
 * ============================================================================
 * 
 * INPUT VALIDATION:
 * ✓ All timezone inputs sanitized with sanitize_text_field()
 * ✓ Validated against DateTimeZone before storage
 * ✓ AJAX endpoints verify nonce (wpshadow_timezone_nonce)
 * ✓ All AJAX endpoints check manage_options capability
 * 
 * STORED VALUES:
 * ✓ wp_options[timezone_string] is WordPress standard
 * ✓ No user input stored as-is, always validated
 * ✓ AJAX responses don't expose sensitive data
 * 
 * TIMING SAFETY:
 * - Could add rate limiting if needed
 * - Currently no limits on AJAX calls
 * 
 * ============================================================================
 * DEBUGGING TIPS
 * ============================================================================
 * 
 * IF TIMEZONE ISN'T DETECTING:
 * 1. Check browser console for JavaScript errors
 * 2. Check Network tab for AJAX request to wpshadow_detect_timezone
 * 3. Verify user has manage_options capability
 * 4. Check wp_options for wpshadow_admin_timezone value
 * 
 * IF TIMEZONE IS WRONG:
 * 1. Use Timezone Alignment tool to manually set
 * 2. Check Server Timezone vs WordPress Timezone in tool
 * 3. May need to regenerate token if JS isn't running
 * 
 * TO VIEW CURRENT TIMEZONE:
 * 1. In settings tool: Shows both server and WP timezone
 * 2. Via code: echo \WPShadow\Core\Timezone_Manager::get_admin_timezone();
 * 3. Via wp-cli: wp option get timezone_string
 * 
 * ============================================================================
 * FILE LOCATIONS
 * ============================================================================
 * 
 * Core Class:
 * - includes/core/class-timezone-manager.php
 * 
 * JavaScript:
 * - assets/js/timezone-detection.js
 * 
 * UI Tool:
 * - includes/views/tools/timezone-alignment.php
 * 
 * Initialization:
 * - wpshadow.php line ~714 (Timezone_Manager::init() call)
 * 
 * ============================================================================
 */
