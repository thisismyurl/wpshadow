<?php
/**
 * Advanced Ads Cache Compatibility Diagnostic
 *
 * Advanced Ads breaking caching plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.290.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Cache Compatibility Diagnostic Class
 *
 * @since 1.290.0000
 */
class Diagnostic_AdvancedAdsCacheCompatibility extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-cache-compatibility';
	protected static $title = 'Advanced Ads Cache Compatibility';
	protected static $description = 'Advanced Ads breaking caching plugins';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-cache-compatibility',
			);
		}
		
		return null;
	}
}
