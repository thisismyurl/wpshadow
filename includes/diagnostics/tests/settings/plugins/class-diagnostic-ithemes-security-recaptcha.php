<?php
/**
 * Ithemes Security Recaptcha Diagnostic
 *
 * Ithemes Security Recaptcha misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.861.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security Recaptcha Diagnostic Class
 *
 * @since 1.861.0000
 */
class Diagnostic_IthemesSecurityRecaptcha extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-recaptcha';
	protected static $title = 'Ithemes Security Recaptcha';
	protected static $description = 'Ithemes Security Recaptcha misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ithemes-security-recaptcha',
			);
		}
		
		return null;
	}
}
