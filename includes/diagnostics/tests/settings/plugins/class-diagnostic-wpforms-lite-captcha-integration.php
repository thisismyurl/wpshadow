<?php
/**
 * Wpforms Lite Captcha Integration Diagnostic
 *
 * Wpforms Lite Captcha Integration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1199.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpforms Lite Captcha Integration Diagnostic Class
 *
 * @since 1.1199.0000
 */
class Diagnostic_WpformsLiteCaptchaIntegration extends Diagnostic_Base {

	protected static $slug = 'wpforms-lite-captcha-integration';
	protected static $title = 'Wpforms Lite Captcha Integration';
	protected static $description = 'Wpforms Lite Captcha Integration issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-lite-captcha-integration',
			);
		}
		
		return null;
	}
}
