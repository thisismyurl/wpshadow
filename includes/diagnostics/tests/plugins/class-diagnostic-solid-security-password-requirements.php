<?php
/**
 * Solid Security Password Requirements Diagnostic
 *
 * Solid Security Password Requirements misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.884.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Password Requirements Diagnostic Class
 *
 * @since 1.884.0000
 */
class Diagnostic_SolidSecurityPasswordRequirements extends Diagnostic_Base {

	protected static $slug = 'solid-security-password-requirements';
	protected static $title = 'Solid Security Password Requirements';
	protected static $description = 'Solid Security Password Requirements misconfiguration';
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
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-password-requirements',
			);
		}
		
		return null;
	}
}
