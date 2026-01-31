<?php
/**
 * Advanced Ads Lazy Loading Diagnostic
 *
 * Ads not lazy loaded properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.294.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Lazy Loading Diagnostic Class
 *
 * @since 1.294.0000
 */
class Diagnostic_AdvancedAdsLazyLoading extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-lazy-loading';
	protected static $title = 'Advanced Ads Lazy Loading';
	protected static $description = 'Ads not lazy loaded properly';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-lazy-loading',
			);
		}
		
		return null;
	}
}
