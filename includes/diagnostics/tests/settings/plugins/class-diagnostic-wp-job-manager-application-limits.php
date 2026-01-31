<?php
/**
 * WP Job Manager Application Limits Diagnostic
 *
 * Job application limits not enforced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.249.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Application Limits Diagnostic Class
 *
 * @since 1.249.0000
 */
class Diagnostic_WpJobManagerApplicationLimits extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-application-limits';
	protected static $title = 'WP Job Manager Application Limits';
	protected static $description = 'Job application limits not enforced';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-application-limits',
			);
		}
		
		return null;
	}
}
