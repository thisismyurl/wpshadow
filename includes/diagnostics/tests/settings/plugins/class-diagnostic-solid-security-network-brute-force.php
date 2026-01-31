<?php
/**
 * Solid Security Network Brute Force Diagnostic
 *
 * Solid Security Network Brute Force misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.881.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Network Brute Force Diagnostic Class
 *
 * @since 1.881.0000
 */
class Diagnostic_SolidSecurityNetworkBruteForce extends Diagnostic_Base {

	protected static $slug = 'solid-security-network-brute-force';
	protected static $title = 'Solid Security Network Brute Force';
	protected static $description = 'Solid Security Network Brute Force misconfiguration';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-network-brute-force',
			);
		}
		
		return null;
	}
}
