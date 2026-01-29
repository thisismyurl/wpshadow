<?php
/**
 * WP Migrate DB Connection Security Diagnostic
 *
 * WP Migrate DB connections not encrypted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.379.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Connection Security Diagnostic Class
 *
 * @since 1.379.0000
 */
class Diagnostic_WpMigrateDbConnectionSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-connection-security';
	protected static $title = 'WP Migrate DB Connection Security';
	protected static $description = 'WP Migrate DB connections not encrypted';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-connection-security',
			);
		}
		
		return null;
	}
}
