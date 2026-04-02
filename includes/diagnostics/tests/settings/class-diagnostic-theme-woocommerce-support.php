<?php
/**
 * Theme WooCommerce Support Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_WooCommerce_Support extends Diagnostic_Base {
	protected static $slug = 'theme-woocommerce-support';
	protected static $title = 'Theme WooCommerce Support';
	protected static $description = 'Verifies WooCommerce integration if applicable';
	protected static $family = 'ecommerce';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$theme = wp_get_theme();
		$supports_wc = current_theme_supports( 'woocommerce' );

		if ( ! $supports_wc ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'WooCommerce is active but theme "%s" does not declare WooCommerce support', 'wpshadow' ),
					$theme->get( 'Name' )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-woocommerce-support',
			);
		}
		return null;
	}
}
