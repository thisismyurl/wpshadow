<?php
/**
 * BuddyPress Group Privacy Diagnostic
 *
 * BuddyPress groups have weak privacy settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.236.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Group Privacy Diagnostic Class
 *
 * @since 1.236.0000
 */
class Diagnostic_BuddypressGroupPrivacy extends Diagnostic_Base {

	protected static $slug = 'buddypress-group-privacy';
	protected static $title = 'BuddyPress Group Privacy';
	protected static $description = 'BuddyPress groups have weak privacy settings';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-group-privacy',
			);
		}
		
		return null;
	}
}
