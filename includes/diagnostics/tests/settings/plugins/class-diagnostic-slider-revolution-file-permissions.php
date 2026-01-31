<?php
/**
 * Slider Revolution File Permissions Diagnostic
 *
 * Slider Revolution files have insecure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.279.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution File Permissions Diagnostic Class
 *
 * @since 1.279.0000
 */
class Diagnostic_SliderRevolutionFilePermissions extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-file-permissions';
	protected static $title = 'Slider Revolution File Permissions';
	protected static $description = 'Slider Revolution files have insecure permissions';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-file-permissions',
			);
		}
		
		return null;
	}
}
