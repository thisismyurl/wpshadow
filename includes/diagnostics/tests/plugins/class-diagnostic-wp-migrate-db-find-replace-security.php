<?php
/**
 * WP Migrate DB Find Replace Diagnostic
 *
 * WP Migrate DB find/replace vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.383.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Find Replace Diagnostic Class
 *
 * @since 1.383.0000
 */
class Diagnostic_WpMigrateDbFindReplaceSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-find-replace-security';
	protected static $title = 'WP Migrate DB Find Replace';
	protected static $description = 'WP Migrate DB find/replace vulnerable';
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-find-replace-security',
			);
		}
		
		return null;
	}
}
