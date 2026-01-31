<?php
/**
 * AddToAny Analytics Diagnostic
 *
 * AddToAny analytics not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.436.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AddToAny Analytics Diagnostic Class
 *
 * @since 1.436.0000
 */
class Diagnostic_AddtoanyAnalyticsIntegration extends Diagnostic_Base {

	protected static $slug = 'addtoany-analytics-integration';
	protected static $title = 'AddToAny Analytics';
	protected static $description = 'AddToAny analytics not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'A2A_SHARE_SAVE_init' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 35,
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/addtoany-analytics-integration',
			);
		}
		

		// Feature availability checks
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks unavailable', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
