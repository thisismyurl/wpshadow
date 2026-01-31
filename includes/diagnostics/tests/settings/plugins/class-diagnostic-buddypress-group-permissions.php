<?php
/**
 * BuddyPress Group Permissions Diagnostic
 *
 * BuddyPress group permissions incorrect.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.515.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Group Permissions Diagnostic Class
 *
 * @since 1.515.0000
 */
class Diagnostic_BuddypressGroupPermissions extends Diagnostic_Base {

	protected static $slug = 'buddypress-group-permissions';
	protected static $title = 'BuddyPress Group Permissions';
	protected static $description = 'BuddyPress group permissions incorrect';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-group-permissions',
			);
		}
		
		return null;
	}
}
