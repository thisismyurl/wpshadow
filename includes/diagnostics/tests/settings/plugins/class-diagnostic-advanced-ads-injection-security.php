<?php
/**
 * Advanced Ads Injection Security Diagnostic
 *
 * Advanced Ads vulnerable to script injection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.289.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Injection Security Diagnostic Class
 *
 * @since 1.289.0000
 */
class Diagnostic_AdvancedAdsInjectionSecurity extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-injection-security';
	protected static $title = 'Advanced Ads Injection Security';
	protected static $description = 'Advanced Ads vulnerable to script injection';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-injection-security',
			);
		}
		
		return null;
	}
}
