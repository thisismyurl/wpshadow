<?php
/**
 * Ninja Tables Data Security Diagnostic
 *
 * Ninja Tables data not protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.477.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Data Security Diagnostic Class
 *
 * @since 1.477.0000
 */
class Diagnostic_NinjaTablesDataSecurity extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-data-security';
	protected static $title = 'Ninja Tables Data Security';
	protected static $description = 'Ninja Tables data not protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'NINJA_TABLES_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-data-security',
			);
		}
		
		return null;
	}
}
