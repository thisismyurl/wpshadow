<?php
/**
 * LifterLMS Engagement Emails Diagnostic
 *
 * LifterLMS email triggers misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.368.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Engagement Emails Diagnostic Class
 *
 * @since 1.368.0000
 */
class Diagnostic_LifterlmsEngagementEmails extends Diagnostic_Base {

	protected static $slug = 'lifterlms-engagement-emails';
	protected static $title = 'LifterLMS Engagement Emails';
	protected static $description = 'LifterLMS email triggers misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-engagement-emails',
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
