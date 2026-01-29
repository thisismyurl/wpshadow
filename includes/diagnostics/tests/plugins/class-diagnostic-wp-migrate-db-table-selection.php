<?php
/**
 * WP Migrate DB Table Selection Diagnostic
 *
 * WP Migrate DB migrating unnecessary tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.385.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Table Selection Diagnostic Class
 *
 * @since 1.385.0000
 */
class Diagnostic_WpMigrateDbTableSelection extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-table-selection';
	protected static $title = 'WP Migrate DB Table Selection';
	protected static $description = 'WP Migrate DB migrating unnecessary tables';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-table-selection',
			);
		}
		
		return null;
	}
}
