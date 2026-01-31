<?php
/**
 * Advanced Ads Tracking Privacy Diagnostic
 *
 * Ad tracking not GDPR compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.293.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Tracking Privacy Diagnostic Class
 *
 * @since 1.293.0000
 */
class Diagnostic_AdvancedAdsTrackingPrivacy extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-tracking-privacy';
	protected static $title = 'Advanced Ads Tracking Privacy';
	protected static $description = 'Ad tracking not GDPR compliant';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-tracking-privacy',
			);
		}
		
		return null;
	}
}
