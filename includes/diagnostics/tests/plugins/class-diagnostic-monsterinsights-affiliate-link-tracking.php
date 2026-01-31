<?php
/**
 * MonsterInsights Affiliate Link Tracking Diagnostic
 *
 * MonsterInsights affiliate link tracking not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.232.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Affiliate Link Tracking Diagnostic Class
 *
 * @since 1.232.0000
 */
class Diagnostic_MonsterinsightsAffiliateLinkTracking extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-affiliate-link-tracking';
	protected static $title = 'MonsterInsights Affiliate Link Tracking';
	protected static $description = 'MonsterInsights affiliate link tracking not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-affiliate-link-tracking',
			);
		}
		
		return null;
	}
}
