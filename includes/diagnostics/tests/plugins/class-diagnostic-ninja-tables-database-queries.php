<?php
/**
 * Ninja Tables Database Queries Diagnostic
 *
 * Ninja Tables queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.484.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Database Queries Diagnostic Class
 *
 * @since 1.484.0000
 */
class Diagnostic_NinjaTablesDatabaseQueries extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-database-queries';
	protected static $title = 'Ninja Tables Database Queries';
	protected static $description = 'Ninja Tables queries inefficient';
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-database-queries',
			);
		}
		
		return null;
	}
}
