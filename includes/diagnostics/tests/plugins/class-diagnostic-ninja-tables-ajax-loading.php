<?php
/**
 * Ninja Tables AJAX Loading Diagnostic
 *
 * Ninja Tables AJAX not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.479.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables AJAX Loading Diagnostic Class
 *
 * @since 1.479.0000
 */
class Diagnostic_NinjaTablesAjaxLoading extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-ajax-loading';
	protected static $title = 'Ninja Tables AJAX Loading';
	protected static $description = 'Ninja Tables AJAX not optimized';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-ajax-loading',
			);
		}
		
		return null;
	}
}
