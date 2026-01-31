<?php
/**
 * Advanced Ads Visitor Conditions Diagnostic
 *
 * Visitor conditions causing database strain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.292.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Visitor Conditions Diagnostic Class
 *
 * @since 1.292.0000
 */
class Diagnostic_AdvancedAdsVisitorConditions extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-visitor-conditions';
	protected static $title = 'Advanced Ads Visitor Conditions';
	protected static $description = 'Visitor conditions causing database strain';
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-visitor-conditions',
			);
		}
		
		return null;
	}
}
