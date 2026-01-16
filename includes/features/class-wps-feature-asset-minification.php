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
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 20,
			)
		);

		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'minify_css'         => __( 'Minify CSS Files', 'plugin-wpshadow' ),
					'minify_js'          => __( 'Minify JavaScript Files', 'plugin-wpshadow' ),
					'combine_css'        => __( 'Combine CSS Files', 'plugin-wpshadow' ),
					'combine_js'         => __( 'Combine JavaScript Files', 'plugin-wpshadow' ),
					'remove_query_strings' => __( 'Remove Version Query Strings', 'plugin-wpshadow' ),
					'minify_inline'      => __( 'Minify Inline Styles/Scripts', 'plugin-wpshadow' ),
				)
			);
		}

		if ( method_exists( $this, 'set_default_sub_features' ) ) {
			$this->set_default_sub_features(
				array(
					'minify_css'         => true,
					'minify_js'          => true,
					'combine_css'        => false,
					'combine_js'         => false,
					'remove_query_strings' => true,
					'minify_inline'      => true,
				)
			);
		}
		
		$this->log_activity( 'feature_initialized', 'Asset Minification feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Enable script concatenation if combining is enabled.
		if ( get_option( 'wpshadow_asset-minification_combine_js', false ) && ! defined( 'CONCATENATE_SCRIPTS' ) ) {
			define( 'CONCATENATE_SCRIPTS', true );
		}

		// Enable CSS compression if minification is enabled.
		if ( get_option( 'wpshadow_asset-minification_minify_css', true ) && ! defined( 'COMPRESS_CSS' ) ) {
			define( 'COMPRESS_CSS', true );
		}

		// Enable script compression if minification is enabled.
		if ( get_option( 'wpshadow_asset-minification_minify_js', true ) && ! defined( 'COMPRESS_SCRIPTS' ) ) {
			define( 'COMPRESS_SCRIPTS', true );
		}

		// Remove query strings from static resources if enabled.
		if ( get_option( 'wpshadow_asset-minification_remove_query_strings', true ) ) {
			add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );
			add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ), 15, 1 );
		}

		// Add async/defer attributes to scripts.
		add_filter( 'script_loader_tag', array( $this, 'optimize_script_loading' ), 10, 3 );

		// Minify inline CSS and JS if option is enabled.
		if ( get_option( 'wpshadow_asset-minification_minify_inline', true ) ) {
			add_filter( 'wp_inline_style', array( $this, 'minify_css' ), 10, 1 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_asset_minification'] = array(
			'label' => __( 'Asset Minification', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_asset_minification' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for asset minification.
	 *
	 * @return array<string, mixed>
	 */
	public function test_asset_minification(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Asset Minification', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Asset Minification is not enabled. Minifying CSS and JavaScript can reduce file sizes and improve page load times.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_asset_minification',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_asset-minification_minify_css', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_asset-minification_minify_js', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_asset-minification_remove_query_strings', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_asset-minification_minify_inline', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Asset Minification', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: %d: number of enabled minification features */
				sprintf(
					__( 'Asset Minification is active with %d compression features enabled.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_asset_minification',
		);
	}
}
