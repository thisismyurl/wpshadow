<?php
/**
 * Feature: Inline Critical CSS
 *
 * Inline above-the-fold CSS in the page head for instant rendering.
 * Defers full stylesheets to load after page render.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPSHADOW_Feature_Critical_CSS
 *
 * Inline critical CSS for faster above-the-fold rendering.
 */
final class WPSHADOW_Feature_Critical_CSS extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'critical-css',
				'name'               => __( 'Inline Critical CSS', 'plugin-wpshadow' ),
				'description'        => __( 'Inline above-the-fold CSS for instant page rendering', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Resource Optimization', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize how resources are loaded and delivered', 'plugin-wpshadow' ),
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

		add_action( 'wp_head', array( $this, 'inline_critical_css' ), 2 );
		add_filter( 'style_loader_tag', array( $this, 'defer_non_critical_css' ), 10, 4 );
	}

	/**
	 * Output critical CSS inline in head.
	 *
	 * @return void
	 */
	public function inline_critical_css(): void {
		// Get critical CSS from options.
		$critical_css = $this->get_setting( 'wpshadow_critical_css', ''  );

		// Allow filtering.
		$critical_css = apply_filters( 'wpshadow_critical_css', $critical_css );

		if ( empty( $critical_css ) ) {
			return;
		}

		// Sanitize and minify CSS.
		$critical_css = wp_strip_all_tags( $critical_css );
		$critical_css = $this->minify_css( $critical_css );

		echo '<style id="wps-critical-css">' . $critical_css . '</style>' . "\n";
	}

	/**
	 * Defer non-critical stylesheets.
	 *
	 * @param string $html   The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 * @return string Modified link tag.
	 */
	public function defer_non_critical_css( string $html, string $handle, string $href, string $media ): string {
		// Get list of stylesheets to defer.
		$defer_styles = (array) $this->get_setting( 'wpshadow_defer_stylesheets', array( ) );

		// Allow filtering.
		$defer_styles = apply_filters( 'wpshadow_defer_stylesheets', $defer_styles );

		// Skip if not in defer list and not in auto mode.
		$auto_defer = $this->get_setting( 'wpshadow_auto_defer_css', false  );

		if ( ! in_array( $handle, $defer_styles, true ) && ! $auto_defer ) {
			return $html;
		}

		// Don't defer admin-bar or other critical styles.
		$critical_handles = array( 'admin-bar', 'dashicons' );
		if ( in_array( $handle, $critical_handles, true ) ) {
			return $html;
		}

		// Default media if not set.
		if ( empty( $media ) ) {
			$media = 'all';
		}

		// Use print media to load without blocking, then switch to media type on load.
		$html = preg_replace(
			'/media=["\']([^"\']+)["\']/i',
			"media='print' onload=\"this.media='$1'\"",
			$html
		);

		// Add noscript fallback.
		$noscript = sprintf( '<noscript><link rel="stylesheet" href="%s" media="%s"></noscript>', esc_url( $href ), esc_attr( $media ) );
		$html    .= $noscript;

		return $html;
	}

	/**
	 * Minify CSS string.
	 *
	 * @param string $css CSS content.
	 * @return string Minified CSS.
	 */
	private function minify_css( string $css ): string {
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
