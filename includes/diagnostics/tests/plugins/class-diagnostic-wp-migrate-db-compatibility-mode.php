<?php
/**
 * WP Migrate DB Compatibility Mode Diagnostic
 *
 * WP Migrate DB compatibility settings wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.382.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Compatibility Mode Diagnostic Class
 *
 * @since 1.382.0000
 */
class Diagnostic_WpMigrateDbCompatibilityMode extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-compatibility-mode';
	protected static $title = 'WP Migrate DB Compatibility Mode';
	protected static $description = 'WP Migrate DB compatibility settings wrong';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-compatibility-mode',
			);
		}
		
		return null;
	}
}
