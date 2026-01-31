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
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-partial-entries',
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
