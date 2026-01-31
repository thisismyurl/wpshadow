<?php
/**
 * Gravity Forms Partial Entries Diagnostic
 *
 * Gravity Forms Partial Entries issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1192.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Partial Entries Diagnostic Class
 *
 * @since 1.1192.0000
 */
class Diagnostic_GravityFormsPartialEntries extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-partial-entries';
	protected static $title = 'Gravity Forms Partial Entries';
	protected static $description = 'Gravity Forms Partial Entries issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify partial entries feature status
		$partial_entries_enabled = get_option( 'rg_gforms_enable_partial_entries', false );
		if ( ! $partial_entries_enabled ) {
			$issues[] = __( 'Partial entries feature not enabled', 'wpshadow' );
		}

		// Check 2: Check partial entry storage limit
		$storage_limit = get_option( 'gform_partial_entry_storage_limit', 0 );
		if ( $storage_limit === 0 || $storage_limit > 10000 ) {
			$issues[] = __( 'Partial entry storage limit not configured or too high', 'wpshadow' );
		}

		// Check 3: Verify automatic cleanup schedule
		$cleanup_schedule = wp_get_schedule( 'gform_partial_entries_cleanup' );
		if ( false === $cleanup_schedule ) {
			$issues[] = __( 'Partial entries cleanup not scheduled', 'wpshadow' );
		}

		// Check 4: Check expired partial entry purge
		$expiration_days = get_option( 'gform_partial_entry_expiration', 30 );
		if ( $expiration_days > 90 ) {
			$issues[] = __( 'Partial entry expiration period too long', 'wpshadow' );
		}

		// Check 5: Verify partial entry to submission conversion tracking
		$track_conversion = get_option( 'gform_track_partial_conversion', false );
		if ( ! $track_conversion ) {
			$issues[] = __( 'Partial entry conversion tracking not enabled', 'wpshadow' );
		}

		// Check 6: Check retention policy for completed entries
		$retention_enabled = get_option( 'gform_partial_entry_retention_enabled', false );
		if ( ! $retention_enabled ) {
			$issues[] = __( 'Partial entry retention policy not configured', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
