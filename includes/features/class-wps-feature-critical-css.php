<?php
/**
 * Feature: Inline Critical CSS
 *
 * Inline above-the-fold CSS in the page head for instant rendering.
 * Defers full stylesheets to load after page render.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'name'               => __( 'Priority Styles for Instant Display', 'plugin-wpshadow' ),
				'description'        => __( 'Generates and serves critical CSS for the above-the-fold portion of each page so visitors see a styled layout immediately while remaining styles load in the background. Reduces perceived load time, lowers layout shifts, and improves Core Web Vitals without changing your theme. Works alongside caching and minification, and falls back safely if critical extraction cannot run, keeping pages consistent.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 2,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 15,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'inline_critical'    => __( 'Inline Critical CSS', 'plugin-wpshadow' ),
					'defer_stylesheets'  => __( 'Defer Non-Critical CSS', 'plugin-wpshadow' ),
					'auto_detection'     => __( 'Auto-Detect Critical Styles', 'plugin-wpshadow' ),
					'mobile_specific'    => __( 'Mobile-Specific Critical CSS', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'inline_critical'    => true,
						'defer_stylesheets'  => true,
						'auto_detection'     => false,
						'mobile_specific'    => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Critical CSS feature initialized', 'info' );
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

		if ( get_option( 'wpshadow_critical-css_inline_critical', true ) ) {
			add_action( 'wp_head', array( $this, 'inline_critical_css' ), 2 );
		}
		
		if ( get_option( 'wpshadow_critical-css_defer_stylesheets', true ) ) {
			add_filter( 'style_loader_tag', array( $this, 'defer_non_critical_css' ), 10, 4 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_critical_css'] = array(
			'label' => __( 'Critical CSS', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_critical_css' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for critical CSS.
	 *
	 * @return array<string, mixed>
	 */
	public function test_critical_css(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Critical CSS', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Critical CSS is not enabled. Enabling critical CSS can improve perceived page load speed by rendering above-the-fold content instantly.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_critical_css',
			);
		}

		// Check if critical CSS is configured.
		$critical_css = $this->get_setting( 'wpshadow_critical_css', '' );
		$has_critical_css = ! empty( $critical_css );

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_critical-css_inline_critical', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_critical-css_defer_stylesheets', true ) ) {
			++$enabled_features;
		}

		if ( ! $has_critical_css ) {
			return array(
				'label'       => __( 'Critical CSS', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Critical CSS is enabled but no critical CSS is configured. Add critical CSS to improve first paint performance.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_critical_css',
			);
		}

		return array(
			'label'       => __( 'Critical CSS', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: size of critical CSS in bytes, 2: number of enabled features */
				sprintf(
					__( 'Critical CSS is active with %1$d bytes configured and %2$d optimization features enabled.', 'plugin-wpshadow' ),
					strlen( $critical_css ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_critical_css',
		);
	}
}
