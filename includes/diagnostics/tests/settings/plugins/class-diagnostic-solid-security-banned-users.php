<?php
/**
 * Solid Security Banned Users Diagnostic
 *
 * Solid Security Banned Users misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.885.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Banned Users Diagnostic Class
 *
 * @since 1.885.0000
 */
class Diagnostic_SolidSecurityBannedUsers extends Diagnostic_Base {

	protected static $slug = 'solid-security-banned-users';
	protected static $title = 'Solid Security Banned Users';
	protected static $description = 'Solid Security Banned Users misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-banned-users',
			);
		}
		
		return null;
	}
}
