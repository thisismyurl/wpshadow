<?php
/**
 * SearchWP Indexing Throttle Diagnostic
 *
 * SearchWP indexing throttle misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.407.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Indexing Throttle Diagnostic Class
 *
 * @since 1.407.0000
 */
class Diagnostic_SearchwpIndexingThrottle extends Diagnostic_Base {

	protected static $slug = 'searchwp-indexing-throttle';
	protected static $title = 'SearchWP Indexing Throttle';
	protected static $description = 'SearchWP indexing throttle misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-indexing-throttle',
			);
		}
		
		return null;
	}
}
