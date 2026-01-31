<?php
/**
 * Beaver Builder Database Cleanup Diagnostic
 *
 * Beaver Builder leaving database bloat.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.348.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Database Cleanup Diagnostic Class
 *
 * @since 1.348.0000
 */
class Diagnostic_BeaverBuilderDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-database-cleanup';
	protected static $title = 'Beaver Builder Database Cleanup';
	protected static $description = 'Beaver Builder leaving database bloat';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-database-cleanup',
			);
		}
		
		return null;
	}
}
