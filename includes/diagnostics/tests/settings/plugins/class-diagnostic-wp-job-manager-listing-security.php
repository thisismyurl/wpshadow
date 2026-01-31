<?php
/**
 * WP Job Manager Listing Security Diagnostic
 *
 * WP Job Manager listings not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.538.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Listing Security Diagnostic Class
 *
 * @since 1.538.0000
 */
class Diagnostic_WpJobManagerListingSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-listing-security';
	protected static $title = 'WP Job Manager Listing Security';
	protected static $description = 'WP Job Manager listings not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-listing-security',
			);
		}
		
		return null;
	}
}
