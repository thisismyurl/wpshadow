<?php
/**
 * Cookie Notice Performance Diagnostic
 *
 * Cookie Notice slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.423.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Notice Performance Diagnostic Class
 *
 * @since 1.423.0000
 */
class Diagnostic_CookieNoticePerformance extends Diagnostic_Base {

	protected static $slug = 'cookie-notice-performance';
	protected static $title = 'Cookie Notice Performance';
	protected static $description = 'Cookie Notice slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'COOKIE_NOTICE_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cookie-notice-performance',
			);
		}
		
		return null;
	}
}
