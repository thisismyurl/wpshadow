<?php
/**
 * LayerSlider License Activation Diagnostic
 *
 * LayerSlider license not activated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.284.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider License Activation Diagnostic Class
 *
 * @since 1.284.0000
 */
class Diagnostic_LayersliderLicenseActivation extends Diagnostic_Base {

	protected static $slug = 'layerslider-license-activation';
	protected static $title = 'LayerSlider License Activation';
	protected static $description = 'LayerSlider license not activated';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-license-activation',
			);
		}
		
		return null;
	}
}
