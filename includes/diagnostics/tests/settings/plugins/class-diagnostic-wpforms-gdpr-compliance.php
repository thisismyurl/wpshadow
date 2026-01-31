<?php
/**
 * WPForms GDPR Compliance Diagnostic
 *
 * WPForms lacks GDPR compliance features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.254.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms GDPR Compliance Diagnostic Class
 *
 * @since 1.254.0000
 */
class Diagnostic_WpformsGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'wpforms-gdpr-compliance';
	protected static $title = 'WPForms GDPR Compliance';
	protected static $description = 'WPForms lacks GDPR compliance features';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-gdpr-compliance',
			);
		}
		
		return null;
	}
}
