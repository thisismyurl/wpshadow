<?php
/**
 * Beaver Builder Custom Modules Diagnostic
 *
 * Beaver Builder custom modules insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.342.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Custom Modules Diagnostic Class
 *
 * @since 1.342.0000
 */
class Diagnostic_BeaverBuilderCustomModules extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-custom-modules';
	protected static $title = 'Beaver Builder Custom Modules';
	protected static $description = 'Beaver Builder custom modules insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-custom-modules',
			);
		}
		
		return null;
	}
}
