<?php
/**
 * Cookie Notice Custom Scripts Diagnostic
 *
 * Cookie Notice custom scripts not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.424.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Notice Custom Scripts Diagnostic Class
 *
 * @since 1.424.0000
 */
class Diagnostic_CookieNoticeCustomScripts extends Diagnostic_Base {

	protected static $slug = 'cookie-notice-custom-scripts';
	protected static $title = 'Cookie Notice Custom Scripts';
	protected static $description = 'Cookie Notice custom scripts not validated';
	protected static $family = 'security';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cookie-notice-custom-scripts',
			);
		}
		
		return null;
	}
}
