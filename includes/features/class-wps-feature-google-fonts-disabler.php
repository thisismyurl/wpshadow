<?php
/**
 * Feature: Google Fonts Disabler
 *
 * Disable Google Fonts loading to improve privacy and performance.
 * Useful when themes load Google Fonts but you prefer system fonts.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Google_Fonts_Disabler
 *
 * Remove Google Fonts from frontend output.
 */
final class WPSHADOW_Feature_Google_Fonts_Disabler extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'google-fonts-disabler',
			'name'               => __( 'Block Google Fonts Loading', 'plugin-wpshadow' ),
			'description'        => __( 'Protect your visitors' privacy - we block Google Fonts tracking and keep your fonts loading fast.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-removeformatting',
				'category'           => 'performance',
				'priority'           => 20,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'block_googleapis'  => __( 'Block fonts.googleapis.com', 'plugin-wpshadow' ),
					'block_gstatic'     => __( 'Block fonts.gstatic.com', 'plugin-wpshadow' ),
					'remove_preconnect' => __( 'Remove Google Fonts Preconnect', 'plugin-wpshadow' ),
					'buffer_cleanup'    => __( 'Remove from HTML Output', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'block_googleapis'  => true,
						'block_gstatic'     => true,
						'remove_preconnect' => true,
						'buffer_cleanup'    => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Google Fonts Disabler feature initialized', 'info' );
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

		// Remove Google Fonts from wp_head.
		if ( get_option( 'wpshadow_google-fonts-disabler_block_googleapis', true ) || get_option( 'wpshadow_google-fonts-disabler_block_gstatic', true ) ) {
			add_filter( 'style_loader_src', array( $this, 'remove_google_fonts_from_styles' ), 10, 1 );
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_google_fonts' ), 999 );
		}

		// Remove from output buffer if buffer cleanup is enabled.
		if ( get_option( 'wpshadow_google-fonts-disabler_buffer_cleanup', true ) ) {
			add_action( 'template_redirect', array( $this, 'start_output_buffer' ), 1 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Remove Google Fonts from style URLs.
	 *
	 * @param string $src Style source URL.
	 * @return string|false Modified URL or false to remove.
	 */
	public function remove_google_fonts_from_styles( string $src ) {
		if ( strpos( $src, 'fonts.googleapis.com' ) !== false || strpos( $src, 'fonts.gstatic.com' ) !== false ) {
			return false;
		}

		return $src;
	}

	/**
	 * Dequeue known Google Fonts handles.
	 *
	 * @return void
	 */
	public function dequeue_google_fonts(): void {
		global $wp_styles;

		if ( ! $wp_styles || ! is_array( $wp_styles->registered ) ) {
			return;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( isset( $style->src ) && is_string( $style->src ) && ( strpos( $style->src, 'fonts.googleapis.com' ) !== false || strpos( $style->src, 'fonts.gstatic.com' ) !== false ) ) {
				wp_dequeue_style( $handle );
				wp_deregister_style( $handle );
			}
		}
	}

	/**
	 * Start output buffering to remove Google Fonts from HTML.
	 *
	 * @return void
	 */
	public function start_output_buffer(): void {
		if ( is_admin() ) {
			return;
		}

		ob_start( array( $this, 'remove_google_fonts_from_html' ) );
	}

	/**
	 * Remove Google Fonts from HTML output.
	 *
	 * @param string $html HTML content.
	 * @return string Modified HTML.
	 */
	public function remove_google_fonts_from_html( string $html ): string {
		// Remove link tags pointing to Google Fonts.
		$html = preg_replace(
			'/<link[^>]*href=["\']https?:\/\/fonts\.googleapis\.com[^>]*>/i',
			'',
			$html
		);

		// Remove preconnect hints for Google Fonts.
		$html = preg_replace(
			'/<link[^>]*href=["\']https?:\/\/fonts\.gstatic\.com[^>]*>/i',
			'',
			$html
		);

		return $html;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_google_fonts_disabler'] = array(
			'label' => __( 'Google Fonts Privacy', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_google_fonts_disabler' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for Google Fonts disabler.
	 *
	 * @return array<string, mixed>
	 */
	public function test_google_fonts_disabler(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Google Fonts Privacy', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Privacy', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Google Fonts blocking is not enabled. Blocking Google Fonts improves privacy by preventing third-party tracking.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_google_fonts_disabler',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_google-fonts-disabler_block_googleapis', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_google-fonts-disabler_block_gstatic', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_google-fonts-disabler_buffer_cleanup', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Google Fonts Privacy', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Privacy', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: %d: number of enabled blocking features */
				sprintf(
					__( 'Google Fonts blocking is active with %d protection layers enabled. Your site is not loading fonts from Google servers.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_google_fonts_disabler',
		);
	}
}
