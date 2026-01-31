<?php
/**
 * TablePress Cache Configuration Diagnostic
 *
 * TablePress not using transient caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.414.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Cache Configuration Diagnostic Class
 *
 * @since 1.414.0000
 */
class Diagnostic_TablepressCacheConfiguration extends Diagnostic_Base {

	protected static $slug = 'tablepress-cache-configuration';
	protected static $title = 'TablePress Cache Configuration';
	protected static $description = 'TablePress not using transient caching';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-cache-configuration',
			);
		}
		
		return null;
	}
}
