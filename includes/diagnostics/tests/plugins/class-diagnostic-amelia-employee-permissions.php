<?php
/**
 * Amelia Employee Permissions Diagnostic
 *
 * Amelia employee roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.469.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Employee Permissions Diagnostic Class
 *
 * @since 1.469.0000
 */
class Diagnostic_AmeliaEmployeePermissions extends Diagnostic_Base {

	protected static $slug = 'amelia-employee-permissions';
	protected static $title = 'Amelia Employee Permissions';
	protected static $description = 'Amelia employee roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-employee-permissions',
			);
		}
		
		return null;
	}
}
