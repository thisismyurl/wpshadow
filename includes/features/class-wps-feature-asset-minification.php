<?php
/**
 * Feature: Asset Minification
 *
 * Compress and minify CSS/JS assets for improved page load performance.
 * Uses WordPress native minification when available and provides compression hints.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Asset_Minification
 *
 * Optimize CSS and JavaScript delivery for better performance.
 */
final class WPSHADOW_Feature_Asset_Minification extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'asset-minification',
			'name'               => __( 'Compress Stylesheet and Script Files', 'plugin-wpshadow' ),
			'description'        => __( 'Compresses stylesheet and script files by removing whitespace and unused code, then serves them in smaller bundles so pages download faster and start rendering sooner. Helps repeat visitors by lowering total bytes transferred, reduces bandwidth costs on heavy pages, and keeps behavior identical because it preserves functionality while trimming bloat. This works automatically once enabled and respects dependency order to avoid breaking features.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance Optimization', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize images and page load performance', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Enable script concatenation if available.
		if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
			define( 'CONCATENATE_SCRIPTS', true );
		}

		// Enable CSS compression.
		if ( ! defined( 'COMPRESS_CSS' ) ) {
			define( 'COMPRESS_CSS', true );
		}

		// Enable script compression.
		if ( ! defined( 'COMPRESS_SCRIPTS' ) ) {
			define( 'COMPRESS_SCRIPTS', true );
		}

		// Remove query strings from static resources.
		add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );
		add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );

		// Add async/defer attributes to scripts.
		add_filter( 'script_loader_tag', array( $this, 'optimize_script_loading' ), 10, 3 );

		// Minify inline CSS and JS if option is enabled.
		add_filter( 'wp_inline_style', array( $this, 'minify_css' ), 10, 1 );
	}

	/**
	 * Remove query strings from static resources to improve caching.
	 *
	 * @param string $src Resource URL.
	 * @return string Modified URL.
	 */
	public function remove_query_strings( string $src ): string {
		if ( strpos( $src, '?ver=' ) !== false ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Optimize script loading with async/defer attributes.
	 *
	 * @param string $tag    Script tag HTML.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 * @return string Modified script tag.
	 */
	public function optimize_script_loading( string $tag, string $handle, string $src ): string {
		// Skip admin and login pages.
		if ( is_admin() || wp_doing_ajax() ) {
			return $tag;
		}

		// Get configured async/defer scripts from options.
		$async_handles = (array) $this->get_setting( 'wpshadow_async_script_handles', array( ) );
		$defer_handles = (array) $this->get_setting( 'wpshadow_defer_script_handles', array( ) );

		// Allow filtering.
		$async_handles = apply_filters( 'wpshadow_async_script_handles', $async_handles );
		$defer_handles = apply_filters( 'wpshadow_defer_script_handles', $defer_handles );

		// Don't add defer/async to scripts that shouldn't have it (like jQuery).
		$skip_optimization = array( 'jquery', 'jquery-core', 'jquery-migrate' );
		if ( in_array( $handle, $skip_optimization, true ) ) {
			return $tag;
		}

		// Add async attribute.
		if ( in_array( $handle, $async_handles, true ) && strpos( $tag, 'async' ) === false ) {
			$tag = str_replace( ' src=', ' async src=', $tag );
		}

		// Add defer attribute.
		if ( in_array( $handle, $defer_handles, true ) && strpos( $tag, 'defer' ) === false ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}

		return $tag;
	}

	/**
	 * Minify inline CSS.
	 *
	 * @param string $css CSS string.
	 * @return string Minified CSS.
	 */
	public function minify_css( string $css ): string {
		// Skip if minification is disabled in options.
		if ( ! get_option( 'wpshadow_minify_inline_css', true ) ) {
			return $css;
		}

		// Remove comments.
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove whitespace.
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );

		// Remove unnecessary spaces.
		$css = preg_replace( '/\s*([{}|:;,])\s+/', '$1', $css );
		$css = preg_replace( '/\s+/', ' ', $css );

		return trim( $css );
	}
}
