<?php
/**
 * Elementor Pro Css Generation Diagnostic
 *
 * Elementor Pro Css Generation issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.798.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Css Generation Diagnostic Class
 *
 * @since 1.798.0000
 */
class Diagnostic_ElementorProCssGeneration extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-css-generation';
	protected static $title = 'Elementor Pro Css Generation';
	protected static $description = 'Elementor Pro Css Generation issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-css-generation',
			);
		}
		
		return null;
	}
}
