<?php

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Dashboard_Assets {

	private static string $plugin_path = '';

	private static string $plugin_url = '';

	private static array $assets = array(
		'icon_32' => '/assets/images/icon-32x32.png',
		'icon_64' => '/assets/images/icon-64x64.png',
		'banner'  => '/assets/images/banner-594x198.png',
	);

	public static function init( string $plugin_path, string $plugin_url ): void {
		self::$plugin_path = trailingslashit( $plugin_path );
		self::$plugin_url  = trailingslashit( $plugin_url );

		if ( is_admin() ) {
			add_action( 'admin_head', array( __CLASS__, 'inject_icon_css' ) );

		}
	}

	public static function inject_icon_css(): void {
		if ( ! self::asset_exists( 'icon_32' ) ) {
			return;
		}

		$icon_url = self::get_asset_url( 'icon_32' );

		echo '<style id="wps-plugin-icon-css" type="text/css">';
		printf(
			'#toplevel_page_WPSHADOW_features .wp-menu-image::before { background: url("%s") no-repeat center / contain; content: ""; display: block; width: 20px; height: 20px; margin: 2px auto; } ',
			esc_url( $icon_url )
		);
		printf(
			'.wps-plugin-icon { background: url("%s") no-repeat center / contain; width: 32px; height: 32px; display: inline-block; margin-right: 8px; vertical-align: middle; } ',
			esc_url( $icon_url )
		);
		echo '</style>' . "\n";
	}

	public static function inject_banner_in_list( array $links, string $plugin_file ): array {

		if ( strpos( $plugin_file, 'wpshadow' ) === false ) {
			return $links;
		}

		if ( ! self::asset_exists( 'banner' ) ) {
			return $links;
		}

		array_unshift(
			$links,
			'<img src="' . esc_url( self::get_asset_url( 'banner' ) ) .
			'" alt="WPS Banner" style="max-width: 100%; height: auto; margin: 8px 0; display: block; border-radius: 4px;" />'
		);

		return $links;
	}

	private static function asset_exists( string $asset_key ): bool {
		if ( ! isset( self::$assets[ $asset_key ] ) ) {
			return false;
		}

		$file_path = self::$plugin_path . ltrim( self::$assets[ $asset_key ], '/' );
		return file_exists( $file_path );
	}

	private static function get_asset_url( string $asset_key ): string {
		if ( ! isset( self::$assets[ $asset_key ] ) ) {
			return '';
		}

		return self::$plugin_url . ltrim( self::$assets[ $asset_key ], '/' );
	}

	public static function get_icon_html( string $size = '32' ): string {
		$asset_key = 'icon_' . $size;

		if ( ! self::asset_exists( $asset_key ) ) {
			return '';
		}

		return sprintf(
			'<img src="%s" alt="WPS Plugin Icon" width="%s" height="%s" class="wps-plugin-icon" />',
			esc_url( self::get_asset_url( $asset_key ) ),
			esc_attr( $size ),
			esc_attr( $size )
		);
	}

	public static function get_banner_html(): string {
		if ( ! self::asset_exists( 'banner' ) ) {
			return '';
		}

		return sprintf(
			'<img src="%s" alt="WPS Plugin Banner" style="max-width: 100%%; height: auto; display: block; border-radius: 4px;" />',
			esc_url( self::get_asset_url( 'banner' ) )
		);
	}
}
