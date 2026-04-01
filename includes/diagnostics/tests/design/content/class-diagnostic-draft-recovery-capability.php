<?php
/**
 * Draft Recovery Capability Diagnostic
 *
 * Tests if users can recover unsaved drafts after crashes or timeouts.
 * Verifies browser storage and WordPress recovery mechanisms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Draft Recovery Capability Diagnostic Class
 *
 * Checks if draft recovery features are properly configured
 * to prevent content loss during crashes or timeouts.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Draft_Recovery_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'draft-recovery-capability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Draft Recovery Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if users can recover unsaved drafts after crashes or timeouts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if auto-save is disabled (required for draft recovery).
		if ( defined( 'AUTOSAVE_INTERVAL' ) && AUTOSAVE_INTERVAL === false ) {
			$issues[] = __( 'Auto-save disabled - draft recovery unavailable', 'wpshadow' );
		}

		// Check if Heartbeat API is disabled (required for recovery).
		if ( defined( 'WP_ADMIN_HEARTBEAT_DISABLE' ) && WP_ADMIN_HEARTBEAT_DISABLE ) {
			$issues[] = __( 'Heartbeat API disabled - draft recovery unavailable', 'wpshadow' );
		}

		// Check browser storage requirements.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_template_directory();
		$header_file = $theme_dir . '/header.php';

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			// Check for Content Security Policy that might block localStorage.
			if ( strpos( $header_content, "Content-Security-Policy" ) !== false &&
			     strpos( $header_content, "script-src 'unsafe-inline'" ) === false ) {
				$issues[] = __( 'Strict CSP may block browser draft storage', 'wpshadow' );
			}
		}

		// Check for post locks functionality.
		$has_post_locks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edit_lock'
				AND meta_value > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))"
			)
		);

		if ( $has_post_locks === null ) {
			$issues[] = __( 'Post locking mechanism may not be working', 'wpshadow' );
		}

		// Check if there are orphaned autosaves that users can't recover.
		$orphaned_autosaves = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} r
				LEFT JOIN {$wpdb->posts} p ON r.post_parent = p.ID
				WHERE r.post_type = 'revision'
				AND r.post_name LIKE %s
				AND (p.ID IS NULL OR p.post_status = 'trash')
				AND r.post_modified > DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'%-autosave-%'
			)
		);

		if ( $orphaned_autosaves > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned autosaves */
				__( '%d orphaned autosaves (parent posts deleted/trashed)', 'wpshadow' ),
				$orphaned_autosaves
			);
		}

		// Check PHP session timeout that might affect recovery.
		$session_timeout = ini_get( 'session.gc_maxlifetime' );
		if ( $session_timeout && $session_timeout < 1800 ) { // Less than 30 minutes.
			$issues[] = sprintf(
				/* translators: %d: timeout in seconds */
				__( 'PHP session timeout is short (%d seconds)', 'wpshadow' ),
				$session_timeout
			);
		}

		// Check admin ajax availability.
		$admin_ajax_url = admin_url( 'admin-ajax.php' );
		$ajax_test = wp_remote_get(
			add_query_arg( 'action', 'heartbeat', $admin_ajax_url ),
			array( 'timeout' => 5 )
		);

		if ( is_wp_error( $ajax_test ) ) {
			$issues[] = __( 'Admin AJAX endpoint not accessible (blocks recovery)', 'wpshadow' );
		}

		// Check if browser autofill might interfere.
		$has_autocomplete_off = false;
		if ( file_exists( $header_file ) ) {
			$has_autocomplete_off = strpos( $header_content, 'autocomplete="off"' ) !== false;
		}

		if ( $has_autocomplete_off ) {
			$issues[] = __( 'Autocomplete disabled globally (may affect recovery)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/draft-recovery-capability?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
