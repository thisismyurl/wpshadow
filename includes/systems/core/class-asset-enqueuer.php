<?php
/**
 * Asset Enqueuer - Centralized CSS/JS loading with conditional loading
 *
 * Handles enqueueing styles and scripts only when needed, avoiding
 * loading assets on pages where they're not required.
 *
 * @since 1.6093.1200
 * @package WPShadow\Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset_Enqueuer Class
 *
 * Manages conditional asset loading for WPShadow admin pages.
 * Ensures CSS/JS only load when actually needed.
 *
 * @since 1.6093.1200
 */
class Asset_Enqueuer {

	/**
	 * Registered assets
	 *
	 * @var array
	 */
	private static array $registered_assets = array();

	/**
	 * Initialize asset loading
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
	}

	/**
	 * Register an asset for conditional loading
	 *
	 * @since 1.6093.1200
	 * @param string $handle        Asset handle.
	 * @param string $type          'style' or 'script'.
	 * @param string $src           Asset source URL.
	 * @param array  $pages         Admin pages to load on (empty = all wpshadow pages).
	 * @param array  $dependencies Dependencies array.
	 * @param bool   $in_footer     For scripts, load in footer.
	 * @return void
	 */
	public static function register(
		string $handle,
		string $type,
		string $src,
		array $pages = array(),
		array $dependencies = array(),
		bool $in_footer = true
	): void {
		self::$registered_assets[ $handle ] = array(
			'type'         => $type,
			'src'          => $src,
			'pages'        => $pages,
			'dependencies' => $dependencies,
			'in_footer'    => $in_footer,
		);
	}

	/**
	 * Enqueue assets conditionally based on current page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function maybe_enqueue_assets(): void {
		// Only load on WPShadow admin pages.
		if ( ! self::is_wpshadow_page() ) {
			return;
		}

		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		foreach ( self::$registered_assets as $handle => $asset ) {
			if ( 'style' === $asset['type'] ) {
				self::enqueue_style( $handle, $asset, $current_page );
			} else {
				self::enqueue_script( $handle, $asset, $current_page );
			}
		}
	}

	/**
	 * Check if current page is a WPShadow admin page
	 *
	 * @since 1.6093.1200
	 * @return bool
	 */
	private static function is_wpshadow_page(): bool {
		return isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'wpshadow' ) === 0;
	}

	/**
	 * Conditionally enqueue a style
	 *
	 * @since 1.6093.1200
	 * @param string $handle  Asset handle.
	 * @param array  $asset   Asset configuration.
	 * @param string $current Current page slug.
	 * @return void
	 */
	private static function enqueue_style( string $handle, array $asset, string $current ): void {
		// If pages array is empty, load on all wpshadow pages.
		if ( empty( $asset['pages'] ) || in_array( $current, $asset['pages'], true ) ) {
			wp_enqueue_style(
				$handle,
				$asset['src'],
				$asset['dependencies'],
				WPSHADOW_VERSION
			);
		}
	}

	/**
	 * Conditionally enqueue a script
	 *
	 * @since 1.6093.1200
	 * @param string $handle  Asset handle.
	 * @param array  $asset   Asset configuration.
	 * @param string $current Current page slug.
	 * @return void
	 */
	private static function enqueue_script( string $handle, array $asset, string $current ): void {
		// If pages array is empty, load on all wpshadow pages.
		if ( empty( $asset['pages'] ) || in_array( $current, $asset['pages'], true ) ) {
			wp_enqueue_script(
				$handle,
				$asset['src'],
				$asset['dependencies'],
				WPSHADOW_VERSION,
				$asset['in_footer']
			);
		}
	}

	/**
	 * Get asset URL helper
	 *
	 * @since 1.6093.1200
	 * @param string $type   'css' or 'js'.
	 * @param string $file   File name without extension.
	 * @return string Asset URL.
	 */
	public static function get_asset_url( string $type, string $file ): string {
		$path = WPSHADOW_URL . 'assets/' . $type . '/' . $file . '.' . $type;
		return apply_filters( 'wpshadow_asset_url', $path, $type, $file );
	}
}
