<?php
/**
 * Translatepress Database Optimization Diagnostic
 *
 * Translatepress Database Optimization misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1155.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Database Optimization Diagnostic Class
 *
 * @since 1.1155.0000
 */
class Diagnostic_TranslatepressDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'translatepress-database-optimization';
	protected static $title = 'Translatepress Database Optimization';
	protected static $description = 'Translatepress Database Optimization misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 55,
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-database-optimization',
			);
		}
		
		return null;
	}
}
