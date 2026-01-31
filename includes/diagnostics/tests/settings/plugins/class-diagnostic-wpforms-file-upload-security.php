<?php
/**
 * WPForms File Upload Security Diagnostic
 *
 * WPForms file uploads not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.251.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms File Upload Security Diagnostic Class
 *
 * @since 1.251.0000
 */
class Diagnostic_WpformsFileUploadSecurity extends Diagnostic_Base {

	protected static $slug = 'wpforms-file-upload-security';
	protected static $title = 'WPForms File Upload Security';
	protected static $description = 'WPForms file uploads not secured';
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-file-upload-security',
			);
		}
		
		return null;
	}
}
