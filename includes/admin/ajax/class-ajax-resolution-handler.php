<?php
/**
 * Resolution Centre AJAX Handler
 *
 * Handles two AJAX endpoints:
 *   1. wpshadow_resolution_save    — mark a diagnostic resolved / skipped / pending
 *   2. wpshadow_resolution_update_option — update a single whitelisted WP option
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 * @since 0.7000.0001
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Handles Resolution Centre save and option-update AJAX actions.
 */
class Ajax_Resolution_Handler extends AJAX_Handler_Base {

/**
 * Register both AJAX endpoints.
 */
public static function register(): void {
add_action( 'wp_ajax_wpshadow_resolution_save',         [ self::class, 'handle_save' ] );
add_action( 'wp_ajax_wpshadow_resolution_update_option', [ self::class, 'handle_update_option' ] );
}

/**
 * Save a resolution status (resolved|skipped|pending) for a diagnostic slug.
 *
 * POST params: nonce, diagnostic_slug, status, note (optional)
 */
public static function handle_save(): void {
self::verify_request( 'wpshadow_resolution' );

$slug   = isset( $_POST['diagnostic_slug'] ) ? sanitize_key( wp_unslash( $_POST['diagnostic_slug'] ) ) : '';
$status = isset( $_POST['status'] )           ? sanitize_key( wp_unslash( $_POST['status'] ) )           : '';
$note   = isset( $_POST['note'] )             ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) )  : '';

if ( ! $slug ) {
wp_send_json_error( [ 'message' => __( 'Invalid diagnostic slug.', 'wpshadow' ) ], 400 );
}

$allowed_statuses = [ 'resolved', 'skipped', 'pending' ];
if ( ! in_array( $status, $allowed_statuses, true ) ) {
wp_send_json_error( [ 'message' => __( 'Invalid status.', 'wpshadow' ) ], 400 );
}

// Update resolution records.
$records          = get_option( 'wpshadow_resolution_records', [] );
$records[ $slug ] = [
'status'      => $status,
'note'        => $note,
'resolved_at' => current_time( 'mysql' ),
'resolved_by' => get_current_user_id(),
];
update_option( 'wpshadow_resolution_records', $records );

// Keep excluded-findings in sync when skipping / un-skipping.
$excluded = get_option( 'wpshadow_excluded_findings', [] );
if ( 'skipped' === $status ) {
$excluded[ $slug ] = [
'reason'    => 'user_skipped',
'timestamp' => time(),
'user'      => get_current_user_id(),
];
} elseif ( 'pending' === $status && isset( $excluded[ $slug ] ) ) {
unset( $excluded[ $slug ] );
}
update_option( 'wpshadow_excluded_findings', $excluded );

wp_send_json_success( [ 'message' => __( 'Status saved.', 'wpshadow' ) ] );
}

/**
 * Update a single whitelisted WordPress option.
 *
 * POST params: nonce, option_name, option_value
 */
public static function handle_update_option(): void {
self::verify_request( 'wpshadow_resolution' );

$option_name  = isset( $_POST['option_name'] )  ? sanitize_key( wp_unslash( $_POST['option_name'] ) )  : '';
$option_value = isset( $_POST['option_value'] ) ? sanitize_text_field( wp_unslash( $_POST['option_value'] ) ) : '';

/**
 * Whitelist of options this handler is permitted to update.
 * Keys   = option name.
 * Values = sanitisation type: 'bool', 'open_closed', 'update_core', 'textarea'.
 */
$allowed = [
'users_can_register'      => 'bool',
'default_comment_status'  => 'open_closed',
'comment_moderation'      => 'bool',
'blog_public'             => 'bool',
'ping_sites'              => 'textarea',
'wp_auto_update_core'     => 'update_core',
'auto_update_core_minor'  => 'bool',
'auto_update_core_major'  => 'bool',
];

if ( ! array_key_exists( $option_name, $allowed ) ) {
wp_send_json_error( [ 'message' => __( 'Option not permitted.', 'wpshadow' ) ], 403 );
}

// Sanitise the value according to its type.
switch ( $allowed[ $option_name ] ) {
case 'bool':
$clean_value = in_array( $option_value, [ '1', 'true', 'yes' ], true ) ? '1' : '0';
break;
case 'open_closed':
$clean_value = ( 'open' === $option_value ) ? 'open' : 'closed';
break;
case 'update_core':
$allowed_vals = [ 'true', 'false', 'minor-only' ];
$clean_value  = in_array( $option_value, $allowed_vals, true ) ? $option_value : 'minor-only';
break;
case 'textarea':
$clean_value = sanitize_textarea_field( $option_value );
break;
default:
$clean_value = sanitize_text_field( $option_value );
}

update_option( $option_name, $clean_value );

wp_send_json_success( [
'message' => sprintf(
/* translators: %s: option name */
__( '%s updated.', 'wpshadow' ),
$option_name
),
] );
}
}
