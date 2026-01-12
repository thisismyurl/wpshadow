<?php
/**
 * Dashboard Asset Display Manager
 *
 * Automatically displays plugin icon and banner in WordPress dashboard
 * if asset files exist.
 *
 * @since 1.1.0
 * @package TIMU_WP_Support
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Asset Display Manager
 *
 * Automatically displays plugin icon and banner in WordPress dashboard
 * if asset files exist.
 *
 * @since 1.1.0
 * @package TIMU_WP_Support
 */
final class WPS_Dashboard_Assets {

	/**
	 * Plugin root path
	 */
	private static string $plugin_path = '';

	/**
	 * Plugin URL
	 */
	private static string $plugin_url = '';

	/**
	 * Asset paths
	 */
	private static array $assets = array(
		'icon_32'  => '/assets/images/icon-32x32.png',
		'icon_64'  => '/assets/images/icon-64x64.png',
		'banner'   => '/assets/images/banner-594x198.png',
	);

	/**
	 * Initialize dashboard assets
	 */
	public static function init( string $plugin_path, string $plugin_url ): void {
		self::$plugin_path = trailingslashit( $plugin_path );
		self::$plugin_url  = trailingslashit( $plugin_url );

		if ( is_admin() ) {
			add_action( 'admin_head', array( __CLASS__, 'inject_icon_css' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'inject_banner_in_list' ), 10, 2 );
		}
	}

	/**
	 * Inject CSS to display icon next to plugin name
	 */
	public static function inject_icon_css(): void {
		if ( ! self::asset_exists( 'icon_32' ) ) {
			return;
		}

		$icon_url = self::get_asset_url( 'icon_32' );

		echo '<style id="wps-plugin-icon-css" type="text/css">';
		printf(
			'#toplevel_page_wps_features .wp-menu-image::before { background: url("%s") no-repeat center / contain; content: ""; display: block; width: 20px; height: 20px; margin: 2px auto; } ',
			esc_url( $icon_url )
		);
		printf(
			'.wps-plugin-icon { background: url("%s") no-repeat center / contain; width: 32px; height: 32px; display: inline-block; margin-right: 8px; vertical-align: middle; } ',
			esc_url( $icon_url )
		);
		echo '</style>' . "\n";
	}

	/**
	 * Inject banner above license info in plugins list
	 *
	 * Hooks into plugin_row_meta to add custom display
	 */
	public static function inject_banner_in_list( array $links, string $plugin_file ): array {
		// Only inject for our plugin
		if ( strpos( $plugin_file, 'wp-support-thisismyurl' ) === false ) {
			return $links;
		}

		if ( ! self::asset_exists( 'banner' ) ) {
			return $links;
		}

		// Add banner before other links
		array_unshift(
			$links,
			'<img src="' . esc_url( self::get_asset_url( 'banner' ) ) . 
			'" alt="WPS Banner" style="max-width: 100%; height: auto; margin: 8px 0; display: block; border-radius: 4px;" />'
		);

		return $links;
	}

	/**
	 * Check if asset file exists
	 */
	private static function asset_exists( string $asset_key ): bool {
		if ( ! isset( self::$assets[ $asset_key ] ) ) {
			return false;
		}

		$file_path = self::$plugin_path . ltrim( self::$assets[ $asset_key ], '/' );
		return file_exists( $file_path );
	}

	/**
	 * Get asset URL
	 */
	private static function get_asset_url( string $asset_key ): string {
		if ( ! isset( self::$assets[ $asset_key ] ) ) {
			return '';
		}

		return self::$plugin_url . ltrim( self::$assets[ $asset_key ], '/' );
	}

	/**
	 * Get icon HTML with proper alt text
	 */
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

	/**
	 * Get banner HTML
	 */
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
