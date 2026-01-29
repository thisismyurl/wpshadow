<?php
/**
 * Ithemes Security Two Factor Diagnostic
 *
 * Ithemes Security Two Factor misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.860.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security Two Factor Diagnostic Class
 *
 * @since 1.860.0000
 */
class Diagnostic_IthemesSecurityTwoFactor extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-two-factor';
	protected static $title = 'Ithemes Security Two Factor';
	protected static $description = 'Ithemes Security Two Factor misconfiguration';
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ithemes-security-two-factor',
			);
		}
		
		return null;
	}
}
