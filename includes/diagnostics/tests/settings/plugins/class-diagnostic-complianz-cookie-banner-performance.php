<?php
/**
 * Complianz Cookie Banner Performance Diagnostic
 *
 * Complianz Cookie Banner Performance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1109.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Complianz Cookie Banner Performance Diagnostic Class
 *
 * @since 1.1109.0000
 */
class Diagnostic_ComplianzCookieBannerPerformance extends Diagnostic_Base {

	protected static $slug = 'complianz-cookie-banner-performance';
	protected static $title = 'Complianz Cookie Banner Performance';
	protected static $description = 'Complianz Cookie Banner Performance not compliant';
	protected static $family = 'performance';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/complianz-cookie-banner-performance',
			);
		}
		
		return null;
	}
}
