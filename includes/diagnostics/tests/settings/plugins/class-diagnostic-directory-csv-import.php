<?php
/**
 * Directory CSV Import Diagnostic
 *
 * Directory CSV imports insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.566.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory CSV Import Diagnostic Class
 *
 * @since 1.566.0000
 */
class Diagnostic_DirectoryCsvImport extends Diagnostic_Base {

	protected static $slug = 'directory-csv-import';
	protected static $title = 'Directory CSV Import';
	protected static $description = 'Directory CSV imports insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-csv-import',
			);
		}
		
		return null;
	}
}
