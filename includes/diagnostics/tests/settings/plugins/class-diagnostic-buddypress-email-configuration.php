<?php
/**
 * BuddyPress Email Configuration Diagnostic
 *
 * BuddyPress email notifications not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.238.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Email Configuration Diagnostic Class
 *
 * @since 1.238.0000
 */
class Diagnostic_BuddypressEmailConfiguration extends Diagnostic_Base {

	protected static $slug = 'buddypress-email-configuration';
	protected static $title = 'BuddyPress Email Configuration';
	protected static $description = 'BuddyPress email notifications not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'buddypress' ) ) {
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
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-email-configuration',
			);
		}
		
		return null;
	}
}
