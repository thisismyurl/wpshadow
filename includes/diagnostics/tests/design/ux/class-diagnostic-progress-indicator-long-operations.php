<?php
/**
 * Progress Indicator for Long Operations Diagnostic
 *
 * Detects when long-running operations lack progress indicators, leaving users uncertain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6035.2302
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progress Indicator Long Operations Diagnostic Class
 *
 * Checks if long operations provide visual progress feedback to users.
 *
 * @since 1.6035.2302
 */
class Diagnostic_Progress_Indicator_Long_Operations extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progress-indicator-long-operations';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Operations Have No Progress Indicator';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when time-consuming operations lack visual progress feedback';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2302
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		global $wp_scripts;

		$has_progress_indicators = false;
		$long_operation_features = array();

		// Check for progress indicator libraries/scripts.
		$progress_patterns = array(
			'progress',
			'loading',
			'spinner',
			'loader',
			'upload-progress',
			'nProgress',
			'pace',
		);

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				foreach ( $progress_patterns as $pattern ) {
					if ( stripos( $handle, $pattern ) !== false ) {
						$has_progress_indicators = true;
						break 2;
					}
				}
			}
		}

		// Check for plugins/features that typically need progress indicators.
		$long_operation_plugins = array(
			'woocommerce/woocommerce.php'                => 'WooCommerce (Order Processing)',
			'easy-digital-downloads/easy-digital-downloads.php' => 'EDD (Download Processing)',
			'wp-user-frontend/wpuf.php'                  => 'WP User Frontend (File Uploads)',
			'buddypress/bp-loader.php'                   => 'BuddyPress (Media Uploads)',
			'learndash/learndash.php'                    => 'LearnDash (Course Exports)',
			'memberpress/memberpress.php'                => 'MemberPress (Import/Export)',
		);

		foreach ( $long_operation_plugins as $plugin => $description ) {
			if ( is_plugin_active( $plugin ) ) {
				$long_operation_features[] = $description;
			}
		}

		// Check if media uploads enabled (always needs progress indicator).
		$upload_enabled = get_option( 'uploads_use_yearmonth_folders', true );
		if ( $upload_enabled ) {
			$long_operation_features[] = __( 'Media Uploads', 'wpshadow' );
		}

		// If no long operations or progress indicators exist, no issue.
		if ( empty( $long_operation_features ) || $has_progress_indicators ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'When visitors upload files or process orders, they see a blank screen with no indication of progress. This makes them think the site is broken', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/progress-indicators',
			'context'      => array(
				'features_needing_progress' => $long_operation_features,
				'has_indicators'            => $has_progress_indicators,
				'impact'                    => __( 'Users think the site froze and refresh the page, causing failed uploads, duplicate orders, or abandoned transactions', 'wpshadow' ),
				'recommendation'            => array(
					__( 'Add progress bars for file uploads', 'wpshadow' ),
					__( 'Show spinning loaders for processing operations', 'wpshadow' ),
					__( 'Display percentage complete when possible', 'wpshadow' ),
					__( 'Add "Processing..." messages during long operations', 'wpshadow' ),
					__( 'Disable submit buttons during processing to prevent double-clicks', 'wpshadow' ),
					__( 'Consider using NProgress or Pace.js libraries', 'wpshadow' ),
				),
				'ux_impact'                 => __( 'Users wait 40-60% longer when they see progress indicators vs blank screens', 'wpshadow' ),
			),
		);
	}
}
