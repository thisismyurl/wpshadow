<?php
/**
 * All-in-One WP Migration Storage Diagnostic
 *
 * AIO WP Migration backups publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.386.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Storage Diagnostic Class
 *
 * @since 1.386.0000
 */
class Diagnostic_AllInOneWpMigrationStorageSecurity extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-storage-security';
	protected static $title = 'All-in-One WP Migration Storage';
	protected static $description = 'AIO WP Migration backups publicly accessible';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-storage-security',
			);
		}
		
		return null;
	}
}
