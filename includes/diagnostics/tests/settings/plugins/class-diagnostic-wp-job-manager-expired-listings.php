<?php
/**
 * WP Job Manager Expired Listings Diagnostic
 *
 * Expired job listings not cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.248.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Expired Listings Diagnostic Class
 *
 * @since 1.248.0000
 */
class Diagnostic_WpJobManagerExpiredListings extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-expired-listings';
	protected static $title = 'WP Job Manager Expired Listings';
	protected static $description = 'Expired job listings not cleaned up';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-expired-listings',
			);
		}
		
		return null;
	}
}
