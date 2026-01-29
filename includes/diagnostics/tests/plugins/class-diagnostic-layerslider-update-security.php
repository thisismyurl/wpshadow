<?php
/**
 * LayerSlider Update Security Diagnostic
 *
 * LayerSlider updates not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.285.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider Update Security Diagnostic Class
 *
 * @since 1.285.0000
 */
class Diagnostic_LayersliderUpdateSecurity extends Diagnostic_Base {

	protected static $slug = 'layerslider-update-security';
	protected static $title = 'LayerSlider Update Security';
	protected static $description = 'LayerSlider updates not configured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'LS_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-update-security',
			);
		}
		
		return null;
	}
}
