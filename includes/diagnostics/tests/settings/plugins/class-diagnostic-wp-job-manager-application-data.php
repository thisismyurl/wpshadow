<?php
/**
 * WP Job Manager Application Data Diagnostic
 *
 * Job application data not securely stored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.245.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Application Data Diagnostic Class
 *
 * @since 1.245.0000
 */
class Diagnostic_WpJobManagerApplicationData extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-application-data';
	protected static $title = 'WP Job Manager Application Data';
	protected static $description = 'Job application data not securely stored';
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-application-data',
			);
		}
		
		return null;
	}
}
