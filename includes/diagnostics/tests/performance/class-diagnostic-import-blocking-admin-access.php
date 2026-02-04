<?php
/**
 * Import Blocking Admin Access Diagnostic
 *
 * Tests whether running imports block admin access to other features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Blocking Admin Access Diagnostic Class
 *
 * Tests whether running imports block or restrict admin access to other features.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Import_Blocking_Admin_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-blocking-admin-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Blocking Admin Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether running imports block admin access to other features';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for background import processing.
		$has_background = false;

		if ( function_exists( 'wp_is_background_update_running' ) ) {
			$has_background = true;
		}

		if ( ! $has_background ) {
			$issues[] = __( 'No background processing available - imports may block admin', 'wpshadow' );
		}

		// Check for async import options.
		$async_plugins = array(
			'import-xml-feeds/import-xml-feeds.php',
			'feedburner-feedsmith/feedburner-feedsmith.php',
		);

		$has_async = false;
		foreach ( $async_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_async = true;
				break;
			}
		}

		// Check for AJAX-based imports.
		$has_ajax_import = has_action( 'wp_ajax_import_items' );
		if ( ! $has_ajax_import ) {
			$issues[] = __( 'No AJAX-based import available - may block admin UI', 'wpshadow' );
		}

		// Check for loopback request capability.
		$response = Diagnostic_Request_Helper::post_result(
			admin_url( 'admin-ajax.php' ),
			array(
				'blocking'  => false,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			)
		);

		if ( ! $response['success'] ) {
			$issues[] = __( 'Loopback requests not available - cannot run background imports', 'wpshadow' );
		}

		// Check for import lock.
		$import_lock = get_transient( 'wpshadow_import_lock' );
		if ( ! empty( $import_lock ) ) {
			$issues[] = __( 'Import lock detected - import may be blocking operations', 'wpshadow' );
		}

		// Check for database write lock.
		if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
			// Good - uses cron for background tasks.
		} else {
			$issues[] = __( 'ALTERNATE_WP_CRON not enabled - imports use HTTP to lock database', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-blocking-admin-access',
			);
		}

		return null;
	}
}
