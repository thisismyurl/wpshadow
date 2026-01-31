<?php
/**
 * WPForms Spam Protection Diagnostic
 *
 * WPForms anti-spam settings not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.250.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms Spam Protection Diagnostic Class
 *
 * @since 1.250.0000
 */
class Diagnostic_WpformsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'wpforms-spam-protection';
	protected static $title = 'WPForms Spam Protection';
	protected static $description = 'WPForms anti-spam settings not configured';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-spam-protection',
			);
		}
		
		return null;
	}
}
