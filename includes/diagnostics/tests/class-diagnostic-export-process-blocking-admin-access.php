<?php
/**
 * Export Process Blocking Admin Access Diagnostic
 *
 * Tests whether export process blocks admin access.
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
 * Export Process Blocking Admin Access Diagnostic Class
 *
 * Tests whether export process may block or restrict admin access.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Export_Process_Blocking_Admin_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-process-blocking-admin-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Process Blocking Admin Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether export process blocks admin access';

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
		$issues = array();

		// Check for background export support.
		$has_background = function_exists( 'wp_is_background_update_running' );
		if ( ! $has_background ) {
			$issues[] = __( 'No background processing support - exports may block admin', 'wpshadow' );
		}

		// Check for AJAX export endpoints.
		$has_ajax_export = has_action( 'wp_ajax_export' );
		if ( ! $has_ajax_export ) {
			$issues[] = __( 'No AJAX export endpoint - export must run synchronously', 'wpshadow' );
		}

		// Check for export lock mechanism.
		$export_lock = get_transient( '_export_lock' );
		if ( ! empty( $export_lock ) ) {
			$issues[] = __( 'Export lock detected - may indicate stuck export process', 'wpshadow' );
		}

		// Check for loopback request support (for background processing).
		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), array(
			'blocking'  => false,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			$issues[] = __( 'Loopback requests not working - cannot run background exports', 'wpshadow' );
		}

		// Check for ALTERNATE_WP_CRON.
		if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
			// Good - external cron can handle exports.
		} else {
			$issues[] = __( 'ALTERNATE_WP_CRON not enabled - exports may block site cron', 'wpshadow' );
		}

		// Check for memory limit during export.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		if ( $memory_bytes > 0 && $memory_bytes < 128000000 ) { // Less than 128MB
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'Low memory limit (%s) - export may cause admin slowness', 'wpshadow' ),
				$memory_limit
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/export-process-blocking-admin-access',
			);
		}

		return null;
	}
}
