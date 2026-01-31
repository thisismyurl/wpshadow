<?php
/**
 * WP All Import File Upload Security Diagnostic
 *
 * Import file uploads not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.272.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import File Upload Security Diagnostic Class
 *
 * @since 1.272.0000
 */
class Diagnostic_WpAllImportFileUpload extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-file-upload';
	protected static $title = 'WP All Import File Upload Security';
	protected static $description = 'Import file uploads not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-file-upload',
			);
		}
		
		return null;
	}
}
