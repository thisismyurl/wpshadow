<?php
/**
 * WPShadow Hooks Integration Example
 *
 * This file demonstrates how to use WPShadow hooks to extend functionality.
 * Place this in wp-content/mu-plugins/ or load it from your theme/plugin.
 *
 * @package WPShadow
 * @subpackage Examples
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Example 1: Log all treatment applications
 */
add_action( 'wpshadow_before_treatment_apply', function( $class, $finding_id, $dry_run ) {
error_log( sprintf(
'[WPShadow] About to apply treatment: %s for finding: %s',
$class,
$finding_id
) );
}, 10, 3 );

/**
 * Example 2: Send email notification on critical findings
 */
add_action( 'wpshadow_finding_detected', function( $finding_id, $severity ) {
if ( $severity === 'critical' ) {
$admin_email = get_option( 'admin_email' );
wp_mail( $admin_email, 'Critical Issue', "Finding: {$finding_id}" );
}
}, 10, 2 );

/**
 * Example 3: Suppress diagnostics in development
 */
add_filter( 'wpshadow_diagnostic_result', function( $finding, $class, $slug ) {
if ( wp_get_environment_type() === 'development' && $slug === 'debug-mode' ) {
return null; // Suppress this finding
}
return $finding;
}, 10, 3 );
