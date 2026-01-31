<?php
/**
 * ACF JSON Sync Diagnostic
 *
 * ACF JSON sync not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.451.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF JSON Sync Diagnostic Class
 *
 * @since 1.451.0000
 */
class Diagnostic_AcfJsonSync extends Diagnostic_Base {

	protected static $slug = 'acf-json-sync';
	protected static $title = 'ACF JSON Sync';
	protected static $description = 'ACF JSON sync not enabled';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/acf-json-sync',
			);
		}
		
		return null;
	}
}
