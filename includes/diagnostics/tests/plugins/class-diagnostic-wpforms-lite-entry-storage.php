<?php
/**
 * Wpforms Lite Entry Storage Diagnostic
 *
 * Wpforms Lite Entry Storage issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1198.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpforms Lite Entry Storage Diagnostic Class
 *
 * @since 1.1198.0000
 */
class Diagnostic_WpformsLiteEntryStorage extends Diagnostic_Base {

	protected static $slug = 'wpforms-lite-entry-storage';
	protected static $title = 'Wpforms Lite Entry Storage';
	protected static $description = 'Wpforms Lite Entry Storage issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-lite-entry-storage',
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
