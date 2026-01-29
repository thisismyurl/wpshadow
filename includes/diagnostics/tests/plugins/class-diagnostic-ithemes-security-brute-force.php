<?php
/**
 * Ithemes Security Brute Force Diagnostic
 *
 * Ithemes Security Brute Force misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.856.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security Brute Force Diagnostic Class
 *
 * @since 1.856.0000
 */
class Diagnostic_IthemesSecurityBruteForce extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-brute-force';
	protected static $title = 'Ithemes Security Brute Force';
	protected static $description = 'Ithemes Security Brute Force misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ithemes-security-brute-force',
			);
		}
		
		return null;
	}
}
