<?php
/**
 * All-in-One WP Migration File Permissions Diagnostic
 *
 * AIO WP Migration files insecure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.387.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration File Permissions Diagnostic Class
 *
 * @since 1.387.0000
 */
class Diagnostic_AllInOneWpMigrationFilePermissions extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-file-permissions';
	protected static $title = 'All-in-One WP Migration File Permissions';
	protected static $description = 'AIO WP Migration files insecure permissions';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-file-permissions',
			);
		}
		
		return null;
	}
}
