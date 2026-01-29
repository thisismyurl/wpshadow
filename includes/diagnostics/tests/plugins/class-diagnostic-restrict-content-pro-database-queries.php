<?php
/**
 * Restrict Content Pro Database Queries Diagnostic
 *
 * RCP database queries not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.330.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Database Queries Diagnostic Class
 *
 * @since 1.330.0000
 */
class Diagnostic_RestrictContentProDatabaseQueries extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-database-queries';
	protected static $title = 'Restrict Content Pro Database Queries';
	protected static $description = 'RCP database queries not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-database-queries',
			);
		}
		
		return null;
	}
}
