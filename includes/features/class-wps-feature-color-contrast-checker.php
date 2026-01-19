<?php
/**
 * Feature: Color Contrast Checker
 *
 * Provides a utility to check color contrast ratios between text and background
 * colors to ensure WCAG accessibility compliance. Essential for readability
 * and legal compliance regarding inclusive web design.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WPSHADOW_Feature_Color_Contrast_Checker
 *
 * Ensures text and background colors meet WCAG accessibility standards.
 */
final class WPSHADOW_Feature_Color_Contrast_Checker extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'color-contrast-checker',
				'name'            => __( 'Text Readability Checker', 'wpshadow' ),
				'description'     => __( 'Check if your text colors stand out enough from the background so everyone can read them easily.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'accessibility',
				'aliases'         => array( 'accessibility', 'wcag', 'readability', 'contrast ratio', 'color contrast', 'text contrast', 'a11y', 'color accessibility', 'wcag compliance', 'contrast checker', 'color legibility', 'ada compliance' ),
				'sub_features'    => array(
					'report_wcag_aaa'   => array(
						'name'               => __( 'Strictest Standards', 'wpshadow' ),
						'description_short'  => __( 'Use the highest readability requirements', 'wpshadow' ),
						'description_long'   => __( 'Check colors against WCAG AAA standards, which are the strictest accessibility guidelines. These rules ensure text is readable by people with different types of color blindness and weak eyesight. Most websites use AA standards, but AAA is better for maximum accessibility.', 'wpshadow' ),
						'description_wizard' => __( 'Use the strictest readability standards (WCAG AAA) for maximum accessibility.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'log_violations'    => array(
						'name'               => __( 'Record Problems', 'wpshadow' ),
						'description_short'  => __( 'Save a log of readability issues', 'wpshadow' ),
						'description_long'   => __( 'Keeps detailed records of color contrast problems found on your site, including where they are, what the colors are, and why they don\'t meet accessibility standards. Useful for tracking which areas need color adjustments and for proving you\'re working to fix accessibility issues.', 'wpshadow' ),
						'description_wizard' => __( 'Keep records of readability problems found on your site.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'suggest_compliant' => array(
						'name'               => __( 'Suggest Better Colors', 'wpshadow' ),
						'description_short'  => __( 'Recommend colors that meet standards', 'wpshadow' ),
						'description_long'   => __( 'When readability problems are found, automatically suggests alternative colors that would meet accessibility standards. Takes the guesswork out of finding colors that work together while looking good and being accessible to everyone.', 'wpshadow' ),
						'description_wizard' => __( 'Get suggestions for colors that would work better.', 'wpshadow' ),
						'default_enabled'    => false,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'report_wcag_aaa'   => false,
				'log_violations'    => false,
				'suggest_compliant' => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'Color Contrast Checker initialized', 'info' );
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

		add_action( 'wp_ajax_wpshadow_check_contrast', array( $this, 'ajax_check_contrast' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * AJAX handler for checking contrast.
	 *
	 * @return void
	 */
	public function ajax_check_contrast(): void {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpshadow_check_contrast', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh and try again.', 'wpshadow' ) ) );
			return;
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that.', 'wpshadow' ) ) );
			return;
		}

		// Get and sanitize input.
		$text_color       = isset( $_POST['text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['text_color'] ) ) : '';
		$background_color = isset( $_POST['background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['background_color'] ) ) : '';
		$is_large_text    = isset( $_POST['is_large_text'] ) && $_POST['is_large_text'] === '1';

		// Validate colors.
		if ( empty( $text_color ) || empty( $background_color ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter both colors.', 'wpshadow' ) ) );
			return;
		}

		// Validate hex color format.
		$text_color       = ltrim( $text_color, '#' );
		$background_color = ltrim( $background_color, '#' );

		if ( ! preg_match( '/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $text_color ) ) {
			wp_send_json_error( array( 'message' => __( 'That text color doesn\'t look right. Try a hex color like #FFFFFF.', 'wpshadow' ) ) );
			return;
		}

		if ( ! preg_match( '/^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $background_color ) ) {
			wp_send_json_error( array( 'message' => __( 'That background color doesn\'t look right. Try a hex color like #FFFFFF.', 'wpshadow' ) ) );
			return;
		}

		// Add back # prefix.
		$text_color       = '#' . $text_color;
		$background_color = '#' . $background_color;

		// Calculate contrast ratio.
		$ratio = $this->calculate_contrast_ratio( $text_color, $background_color );
		if ( $ratio === 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid color provided.', 'wpshadow' ) ) );
			return;
		}

		// Determine compliance.
		$aa_normal   = 4.5;
		$aa_large    = 3.0;
		$aaa_normal  = 7.0;
		$aaa_large   = 4.5;

		$aa_required  = $is_large_text ? $aa_large : $aa_normal;
		$aaa_required = $is_large_text ? $aaa_large : $aaa_normal;

		$result = array(
			'formatted_ratio' => number_format( $ratio, 2 ) . ':1',
			'ratio'           => $ratio,
			'aa'              => array(
				'required' => $aa_required,
				'passes'   => $ratio >= $aa_required,
			),
			'aaa'             => array(
				'required' => $aaa_required,
				'passes'   => $ratio >= $aaa_required,
			),
		);

		wp_send_json_success( $result );
	}

	/**
	 * Calculate contrast ratio between two colors.
	 *
	 * @param string $color1 First color hex.
	 * @param string $color2 Second color hex.
	 * @return float Contrast ratio.
	 */
	private function calculate_contrast_ratio( string $color1, string $color2 ): float {
		$rgb1 = $this->hex_to_rgb( $color1 );
		$rgb2 = $this->hex_to_rgb( $color2 );

		if ( ! $rgb1 || ! $rgb2 ) {
			return 0;
		}

		$lum1 = $this->get_luminance( $rgb1 );
		$lum2 = $this->get_luminance( $rgb2 );

		$lighter = max( $lum1, $lum2 );
		$darker  = min( $lum1, $lum2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Convert hex color to RGB array.
	 *
	 * @param string $hex Hex color code.
	 * @return array|false RGB array or false on error.
	 */
	private function hex_to_rgb( string $hex ) {
		$hex = ltrim( $hex, '#' );

		if ( strlen( $hex ) === 3 ) {
			$hex = implode( '', array_map( static fn( $c ) => $c . $c, str_split( $hex ) ) );
		}

		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return false;
		}

		return array(
			'r' => (int) hexdec( substr( $hex, 0, 2 ) ),
			'g' => (int) hexdec( substr( $hex, 2, 2 ) ),
			'b' => (int) hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Calculate relative luminance of a color.
	 *
	 * @param array $rgb RGB color array.
	 * @return float Luminance value.
	 */
	private function get_luminance( array $rgb ): float {
		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['color_contrast'] = array(
			'label' => __( 'Color Contrast Checker', 'wpshadow' ),
			'test'  => array( $this, 'test_color_contrast' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for color contrast.
	 *
	 * @return array Test result.
	 */
	public function test_color_contrast(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Color Contrast Checker', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Accessibility', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Color contrast checker is disabled.', 'wpshadow' ),
				'test'        => 'color_contrast',
			);
		}

		$enabled_sub_features = 0;

		if ( $this->is_sub_feature_enabled( 'report_wcag_aaa', false ) ) {
			$enabled_sub_features++;
		}
		if ( $this->is_sub_feature_enabled( 'log_violations', false ) ) {
			$enabled_sub_features++;
		}
		if ( $this->is_sub_feature_enabled( 'suggest_compliant', false ) ) {
			$enabled_sub_features++;
		}

		$status = $enabled_sub_features > 0 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Color Contrast Checker Active', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Accessibility', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled sub-features */
					__( '%d color contrast sub-features enabled.', 'wpshadow' ),
					(int) $enabled_sub_features
				)
			),
			'test'        => 'color_contrast',
		);
	}
}
