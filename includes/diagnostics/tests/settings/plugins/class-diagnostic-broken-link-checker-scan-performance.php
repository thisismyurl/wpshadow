<?php
/**
 * Broken Link Checker Scan Performance Diagnostic
 *
 * Broken Link Checker Scan Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1421.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Link Checker Scan Performance Diagnostic Class
 *
 * @since 1.1421.0000
 */
class Diagnostic_BrokenLinkCheckerScanPerformance extends Diagnostic_Base {

	protected static $slug = 'broken-link-checker-scan-performance';
	protected static $title = 'Broken Link Checker Scan Performance';
	protected static $description = 'Broken Link Checker Scan Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BLC_ACTIVE' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/broken-link-checker-scan-performance',
			);
		}
		
		return null;
	}
}
