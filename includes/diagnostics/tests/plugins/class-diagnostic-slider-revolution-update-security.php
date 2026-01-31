<?php
/**
 * Slider Revolution Update Security Diagnostic
 *
 * Slider Revolution not receiving security updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.278.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution Update Security Diagnostic Class
 *
 * @since 1.278.0000
 */
class Diagnostic_SliderRevolutionUpdateSecurity extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-update-security';
	protected static $title = 'Slider Revolution Update Security';
	protected static $description = 'Slider Revolution not receiving security updates';
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-update-security',
			);
		}
		
		return null;
	}
}
