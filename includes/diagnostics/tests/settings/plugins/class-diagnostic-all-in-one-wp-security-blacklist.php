<?php
/**
 * All In One Wp Security Blacklist Diagnostic
 *
 * All In One Wp Security Blacklist misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.867.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Blacklist Diagnostic Class
 *
 * @since 1.867.0000
 */
class Diagnostic_AllInOneWpSecurityBlacklist extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-blacklist';
	protected static $title = 'All In One Wp Security Blacklist';
	protected static $description = 'All In One Wp Security Blacklist misconfiguration';
	protected static $family = 'security';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-blacklist',
			);
		}
		
		return null;
	}
}
