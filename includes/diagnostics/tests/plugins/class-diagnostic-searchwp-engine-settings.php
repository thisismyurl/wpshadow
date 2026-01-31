<?php
/**
 * SearchWP Engine Settings Diagnostic
 *
 * SearchWP engine not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.406.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Engine Settings Diagnostic Class
 *
 * @since 1.406.0000
 */
class Diagnostic_SearchwpEngineSettings extends Diagnostic_Base {

	protected static $slug = 'searchwp-engine-settings';
	protected static $title = 'SearchWP Engine Settings';
	protected static $description = 'SearchWP engine not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-engine-settings',
			);
		}
		
		return null;
	}
}
