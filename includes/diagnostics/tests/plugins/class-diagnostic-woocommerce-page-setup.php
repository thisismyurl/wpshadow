<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommercePageSetup extends Diagnostic_Base {
	protected static $slug = 'woocommerce-page-setup';
	protected static $title = 'WooCommerce Page Setup';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$pages = array( 'shop', 'cart', 'checkout', 'myaccount' );
		$missing = array();
		foreach ( $pages as $page ) {
			$page_id = get_option( 'woocommerce_' . $page . '_page_id', 0 );
			if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
				$missing[] = $page;
			}
		}
		if ( ! empty( $missing ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Missing pages: %s', 'wpshadow' ), implode( ', ', $missing ) ),
				'severity' => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-pages',
			);
		}
		return null;
	}
}
