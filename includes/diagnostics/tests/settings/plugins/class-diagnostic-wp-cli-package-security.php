<?php
/**
 * Wp Cli Package Security Diagnostic
 *
 * Wp Cli Package Security issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1049.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Cli Package Security Diagnostic Class
 *
 * @since 1.1049.0000
 */
class Diagnostic_WpCliPackageSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-cli-package-security';
	protected static $title = 'Wp Cli Package Security';
	protected static $description = 'Wp Cli Package Security issue detected';
	protected static $family = 'security';

	public static function check() {
		
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-cli-package-security',
			);
		}
		
		return null;
	}
}
