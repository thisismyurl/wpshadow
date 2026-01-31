<?php
/**
 * Ithemes Security File Permissions Diagnostic
 *
 * Ithemes Security File Permissions misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.858.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security File Permissions Diagnostic Class
 *
 * @since 1.858.0000
 */
class Diagnostic_IthemesSecurityFilePermissions extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-file-permissions';
	protected static $title = 'Ithemes Security File Permissions';
	protected static $description = 'Ithemes Security File Permissions misconfiguration';
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
				'severity'    => 70,
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ithemes-security-file-permissions',
			);
		}
		
		return null;
	}
}
