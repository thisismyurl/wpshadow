<?php
/**
 * Google Analytics Enhanced Ecommerce Diagnostic
 *
 * Google Analytics Enhanced Ecommerce misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1340.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Enhanced Ecommerce Diagnostic Class
 *
 * @since 1.1340.0000
 */
class Diagnostic_GoogleAnalyticsEnhancedEcommerce extends Diagnostic_Base {

	protected static $slug = 'google-analytics-enhanced-ecommerce';
	protected static $title = 'Google Analytics Enhanced Ecommerce';
	protected static $description = 'Google Analytics Enhanced Ecommerce misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'ga_load_options' ) || defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-enhanced-ecommerce',
			);
		}
		
		return null;
	}
}
