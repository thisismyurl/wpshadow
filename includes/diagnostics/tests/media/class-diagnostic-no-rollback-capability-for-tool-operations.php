<?php
/**
 * No Rollback Capability for Tool Operations Diagnostic
 *
 * Tests for operation rollback support.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Rollback Capability for Tool Operations Diagnostic Class
 *
 * Tests for operation rollback support.
 *
 * @since 1.26033.0000
 */
class Diagnostic_No_Rollback_Capability_For_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rollback-capability-for-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Rollback Capability for Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for operation rollback support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for database transaction support (used for rollback).
		$has_transactions = method_exists( $wpdb, 'query' );

		if ( ! $has_transactions ) {
			$issues[] = __( 'Database transaction support not detected', 'wpshadow' );
		}

		// Check for backup mechanism.
		$has_backup = function_exists( 'wp_remote_post' ) && function_exists( 'wp_generate_backup' );

		if ( ! $has_backup && ! has_action( 'wpshadow_before_tool_operation' ) ) {
			$issues[] = __( 'No pre-operation backup mechanism available', 'wpshadow' );
		}

		// Check for rollback handler.
		if ( ! has_action( 'wpshadow_operation_failed_rollback' ) ) {
			$issues[] = __( 'No rollback handler registered', 'wpshadow' );
		}

		// Check for undo action history.
		$undo_log = get_option( '_wpshadow_operation_undo_log' );

		if ( empty( $undo_log ) || ! is_array( $undo_log ) ) {
			$issues[] = __( 'No undo log maintained - cannot trace changes for rollback', 'wpshadow' );
		}

		// Check for media backup directory.
		$backup_dir = WP_CONTENT_DIR . '/wpshadow-backups';

		if ( ! is_dir( $backup_dir ) ) {
			$issues[] = __( 'No backup directory exists - rollback may not be possible', 'wpshadow' );
		} else {
			// Check backup directory is writable.
			if ( ! wp_is_writable( $backup_dir ) ) {
				$issues[] = __( 'Backup directory not writable - rollback not possible', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/no-rollback-capability-for-tool-operations',
			);
		}

		return null;
	}
}
