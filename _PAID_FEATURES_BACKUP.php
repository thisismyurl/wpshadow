<?php
/**
 * BACKUP: All Paid Feature Requires and Registrations
 * This file contains the code extracted from wpshadow.php during plugin split
 * Use this as reference for building wpshadow-pro.php
 * 
 * DO NOT include this file - it's for reference only
 * @deprecated Use wpshadow-pro.php instead
 */

// ============================================================================
// PAID FEATURE REQUIRES (27 Features - License Levels 3-5)
// ============================================================================
// Copy these into wpshadow-pro.php load_pro_features() function

// License Level 3 (Business - 11 features)
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-asset-minification.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-brute-force-protection.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-cdn-integration.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-conditional-loading.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-critical-css.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-database-cleanup.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-hardening.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-image-optimizer.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-page-cache.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-script-deferral.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-script-optimizer.php' );

// License Level 4 (Professional - 10 features)
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-conflict-sandbox.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-firewall.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-malware-scanner.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-performance-alerts.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-troubleshooting-mode.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-uptime-monitor.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-visual-regression.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-vulnerability-watch.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-weekly-performance-report.php' );

// License Level 5 (Enterprise - 7 features)
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-auto-rollback.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-customization-audit.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-image-smart-focus.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-smart-recommendations.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-traffic-monitor.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-two-factor-auth.php' );
require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PRO_PATH . 'includes/features/class-wps-feature-vault-audit.php' );

// ============================================================================
// PAID FEATURE REGISTRATIONS (27 Features)
// ============================================================================
// Copy these into WPSHADOW_register_pro_features() function in wpshadow-pro.php

// License Level 3 (Business - 11 features)
register_WPSHADOW_feature( new WPSHADOW_Feature_Asset_Minification() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Brute_Force_Protection() );
register_WPSHADOW_feature( new WPSHADOW_Feature_CDN_Integration() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Conditional_Loading() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Critical_CSS() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Database_Cleanup() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Hardening() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Image_Optimizer() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Page_Cache() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Script_Deferral() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Script_Optimizer() );

// License Level 4 (Professional - 10 features)
register_WPSHADOW_feature( new WPSHADOW_Feature_Conflict_Sandbox() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Firewall() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Malware_Scanner() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Performance_Alerts() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Troubleshooting_Mode() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Uptime_Monitor() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Visual_Regression() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Vulnerability_Watch() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Weekly_Performance_Report() );

// License Level 5 (Enterprise - 7 features)
register_WPSHADOW_feature( new WPSHADOW_Feature_Auto_Rollback() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Customization_Audit() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Image_Smart_Focus() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Smart_Recommendations() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Traffic_Monitor() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Two_Factor_Auth() );
register_WPSHADOW_feature( new WPSHADOW_Feature_Vault_Audit() );

// ============================================================================
// PAID FEATURE USE STATEMENTS (at top of wpshadow.php - DELETE THESE)
// ============================================================================

use WPShadow\CoreSupport\WPSHADOW_Feature_Asset_Minification;
use WPShadow\CoreSupport\WPSHADOW_Feature_Auto_Rollback;
use WPShadow\CoreSupport\WPSHADOW_Feature_Brute_Force_Protection;
use WPShadow\CoreSupport\WPSHADOW_Feature_CDN_Integration;
use WPShadow\CoreSupport\WPSHADOW_Feature_Conflict_Sandbox;
use WPShadow\CoreSupport\WPSHADOW_Feature_Conditional_Loading;
use WPShadow\CoreSupport\WPSHADOW_Feature_Critical_CSS;
use WPShadow\CoreSupport\WPSHADOW_Feature_Customization_Audit;
use WPShadow\CoreSupport\WPSHADOW_Feature_Database_Cleanup;
use WPShadow\CoreSupport\WPSHADOW_Feature_Firewall;
use WPShadow\CoreSupport\WPSHADOW_Feature_Hardening;
use WPShadow\CoreSupport\WPSHADOW_Feature_Image_Optimizer;
use WPShadow\CoreSupport\WPSHADOW_Feature_Image_Smart_Focus;
use WPShadow\CoreSupport\WPSHADOW_Feature_Malware_Scanner;
use WPShadow\CoreSupport\WPSHADOW_Feature_Performance_Alerts;
use WPShadow\CoreSupport\WPSHADOW_Feature_Script_Deferral;
use WPShadow\CoreSupport\WPSHADOW_Feature_Script_Optimizer;
use WPShadow\CoreSupport\WPSHADOW_Feature_Smart_Recommendations;
use WPShadow\CoreSupport\WPSHADOW_Feature_Traffic_Monitor;
use WPShadow\CoreSupport\WPSHADOW_Feature_Troubleshooting_Mode;
use WPShadow\CoreSupport\WPSHADOW_Feature_Two_Factor_Auth;
use WPShadow\CoreSupport\WPSHADOW_Feature_Uptime_Monitor;
use WPShadow\CoreSupport\WPSHADOW_Feature_Visual_Regression;
use WPShadow\CoreSupport\WPSHADOW_Feature_Vault_Audit;
use WPShadow\CoreSupport\WPSHADOW_Feature_Vulnerability_Watch;
use WPShadow\CoreSupport\WPSHADOW_Feature_Weekly_Performance_Report;
