<?php
/**
 * MonsterInsights eCommerce Tracking Diagnostic
 *
 * MonsterInsights eCommerce not tracking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.426.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights eCommerce Tracking Diagnostic Class
 *
 * @since 1.426.0000
 */
class Diagnostic_MonsterinsightsEcommerceTracking extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-ecommerce-tracking';
	protected static $title = 'MonsterInsights eCommerce Tracking';
	protected static $description = 'MonsterInsights eCommerce not tracking';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-ecommerce-tracking',
			);
		}
		
		return null;
	}
}
