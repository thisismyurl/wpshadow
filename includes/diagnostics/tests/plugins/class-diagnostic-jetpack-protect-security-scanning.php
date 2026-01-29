<?php
/**
 * Jetpack Protect Security Scanning Diagnostic
 *
 * Jetpack Protect Security Scanning misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.877.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Security Scanning Diagnostic Class
 *
 * @since 1.877.0000
 */
class Diagnostic_JetpackProtectSecurityScanning extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-security-scanning';
	protected static $title = 'Jetpack Protect Security Scanning';
	protected static $description = 'Jetpack Protect Security Scanning misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-security-scanning',
			);
		}
		
		return null;
	}
}
