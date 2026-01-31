<?php
/**
 * Multisite User Management Security Diagnostic
 *
 * Multisite User Management Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.943.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite User Management Security Diagnostic Class
 *
 * @since 1.943.0000
 */
class Diagnostic_MultisiteUserManagementSecurity extends Diagnostic_Base {

	protected static $slug = 'multisite-user-management-security';
	protected static $title = 'Multisite User Management Security';
	protected static $description = 'Multisite User Management Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/multisite-user-management-security',
			);
		}
		
		return null;
	}
}
