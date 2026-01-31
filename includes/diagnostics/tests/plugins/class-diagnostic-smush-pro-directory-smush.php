<?php
/**
 * Smush Pro Directory Smush Diagnostic
 *
 * Smush Pro Directory Smush detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.759.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Directory Smush Diagnostic Class
 *
 * @since 1.759.0000
 */
class Diagnostic_SmushProDirectorySmush extends Diagnostic_Base {

	protected static $slug = 'smush-pro-directory-smush';
	protected static $title = 'Smush Pro Directory Smush';
	protected static $description = 'Smush Pro Directory Smush detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/smush-pro-directory-smush',
			);
		}
		
		return null;
	}
}
