<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommerceStockManagement extends Diagnostic_Base {
	protected static $slug = 'woocommerce-stock-management';
	protected static $title = 'WooCommerce Stock Management';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/woocommerce-stock-management',
		);
	}
	return null; }
		$manage_stock = get_option( 'woocommerce_manage_stock', 'no' );
		if ( 'yes' !== $manage_stock ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Stock management not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-stock',
			);
		}
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "woocommerce_stock_management_settings" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Settings available', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/woocommerce-stock-management',
		);
	}
	return null;
	}
}
