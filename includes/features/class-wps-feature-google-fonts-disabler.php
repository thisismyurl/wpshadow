<?php
/**
 * Feature: Google Fonts Disabler
 *
 * Disable Google Fonts loading to improve privacy and performance.
 * Useful when themes load Google Fonts but you prefer system fonts.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

/**
 * WPS_Feature_Google_Fonts_Disabler
 *
 * Remove Google Fonts from frontend output.
 */
final class WPS_Feature_Google_Fonts_Disabler extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'google-fonts-disabler',
				'name'                => __( 'Disable Google Fonts', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Remove Google Fonts for better privacy and faster load times', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'resource-optimization',
				'widget_label'        => __( 'Resource Optimization', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Optimize how resources are loaded and delivered', 'plugin-wp-support-thisismyurl' ),
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

		// Remove Google Fonts from wp_head.
		add_filter( 'style_loader_src', array( $this, 'remove_google_fonts_from_styles' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_google_fonts' ), 999 );

		// Remove from output buffer.
		add_action( 'template_redirect', array( $this, 'start_output_buffer' ), 1 );
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
			if ( isset( $style->src ) && ( strpos( $style->src, 'fonts.googleapis.com' ) !== false || strpos( $style->src, 'fonts.gstatic.com' ) !== false ) ) {
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
}
