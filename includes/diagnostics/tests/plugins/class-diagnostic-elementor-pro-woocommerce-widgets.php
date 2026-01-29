<?php
/**
 * Elementor Pro Woocommerce Widgets Diagnostic
 *
 * Elementor Pro Woocommerce Widgets issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.791.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Woocommerce Widgets Diagnostic Class
 *
 * @since 1.791.0000
 */
class Diagnostic_ElementorProWoocommerceWidgets extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-woocommerce-widgets';
	protected static $title = 'Elementor Pro Woocommerce Widgets';
	protected static $description = 'Elementor Pro Woocommerce Widgets issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-woocommerce-widgets',
			);
		}
		
		return null;
	}
}
