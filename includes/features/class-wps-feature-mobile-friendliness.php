<?php declare(strict_types=1);
/**
 * Feature: Mobile Friendliness
 *
 * Checks mobile responsiveness and touch-friendly design patterns.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Mobile_Friendliness extends WPSHADOW_Abstract_Feature {

	const MAX_HEADER_SIZE = 10240;
	const MAX_CSS_SIZE    = 500000;

	public function __construct() {
		parent::__construct( array(
			'id'          => 'mobile-friendliness',
			'name'        => __( 'Mobile Phone Checker', 'wpshadow' ),
			'description' => __( 'Check if your site works well on phones and tablets - text should be readable and buttons easy to tap.', 'wpshadow' ),
			'sub_features' => array(
				'viewport_check'    => __( 'Check if site fits phone screens', 'wpshadow' ),
				'touch_targets'     => __( 'Check if buttons are big enough to tap', 'wpshadow' ),
				'font_sizes'        => __( 'Check if text is readable', 'wpshadow' ),
				'tap_spacing'       => __( 'Check if buttons aren\'t too close together', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'viewport_check'    => true,
			'touch_targets'     => true,
			'font_sizes'        => true,
			'tap_spacing'       => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Check viewport meta tag.
	 */
	private function check_viewport(): array {
		$header_file = get_template_directory() . '/header.php';

		if ( ! file_exists( $header_file ) ) {
			return array(
				'pass'    => false,
				'message' => __( 'Theme header file not found.', 'wpshadow' ),
			);
		}

		$size = filesize( $header_file );
		if ( $size > self::MAX_HEADER_SIZE ) {
			return array(
				'pass'    => true,
				'message' => __( 'Header file too large to check thoroughly.', 'wpshadow' ),
			);
		}

		$content = file_get_contents( $header_file );
		if ( ! $content ) {
			return array(
				'pass'    => false,
				'message' => __( 'Could not read header file.', 'wpshadow' ),
			);
		}

		$has_viewport = (bool) preg_match( '/viewport.*width=device-width/i', $content );

		if ( ! $has_viewport ) {
			// Check for HTML5 theme support
			$has_viewport = current_theme_supports( 'html5' );
		}

		return array(
			'pass'    => $has_viewport,
			'message' => $has_viewport
				? __( 'Viewport meta tag configured.', 'wpshadow' )
				: __( 'Viewport meta tag missing.', 'wpshadow' ),
		);
	}

	/**
	 * Check touch target sizes.
	 */
	private function check_touch_targets(): array {
		$css = $this->get_theme_css();

		// Look for button/link sizes
		$matches = array();
		preg_match_all( '/(?:button|\.button|\.btn|input\[type=["\']?submit["\']?\])\s*\{[^}]*height\s*:\s*(\d+)px/i', $css, $matches, PREG_PATTERN_ORDER );

		if ( empty( $matches[1] ) ) {
			return array(
				'pass'    => true,
				'message' => __( 'Touch target sizes appear adequate.', 'wpshadow' ),
			);
		}

		$min_size = min( array_map( 'intval', $matches[1] ) );
		$pass = $min_size >= 48; // WCAG 2.1 minimum

		return array(
			'pass'    => $pass,
			'message' => $pass
				? __( 'Touch targets meet WCAG minimum (48px).', 'wpshadow' )
				: sprintf( __( 'Some touch targets (%dpx) are below 48px minimum.', 'wpshadow' ), $min_size ),
		);
	}

	/**
	 * Check font sizes for mobile readability.
	 */
	private function check_font_sizes(): array {
		$css = $this->get_theme_css();

		// Look for body font size
		if ( preg_match( '/body\s*\{[^}]*font-size\s*:\s*(\d+)px/i', $css, $matches ) ) {
			$font_size = (int) $matches[1];
			$pass = $font_size >= 14; // Readable minimum

			return array(
				'pass'    => $pass,
				'message' => $pass
					? sprintf( __( 'Base font size (%dpx) is readable.', 'wpshadow' ), $font_size )
					: sprintf( __( 'Base font size (%dpx) may be too small for mobile.', 'wpshadow' ), $font_size ),
			);
		}

		return array(
			'pass'    => true,
			'message' => __( 'Using default font size (typically 16px).', 'wpshadow' ),
		);
	}

	/**
	 * Check tap spacing between interactive elements.
	 */
	private function check_tap_spacing(): array {
		$css = $this->get_theme_css();

		// Check for menu item spacing
		if ( preg_match( '/\.menu\s+li\s*\{[^}]*padding\s*:\s*(\d+)px/i', $css, $matches ) ) {
			$padding = (int) $matches[1];
			$pass = $padding >= 8;

			return array(
				'pass'    => $pass,
				'message' => $pass
					? __( 'Navigation item spacing is adequate.', 'wpshadow' )
					: __( 'Menu items may be too close for comfortable tapping.', 'wpshadow' ),
			);
		}

		return array(
			'pass'    => true,
			'message' => __( 'Unable to determine tap spacing.', 'wpshadow' ),
		);
	}

	/**
	 * Get theme CSS content.
	 */
	private function get_theme_css(): string {
		$cache_key = 'wpshadow_theme_css_' . md5( get_template() );
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$css = '';
		$stylesheet = get_stylesheet_directory() . '/style.css';

		if ( file_exists( $stylesheet ) ) {
			$size = filesize( $stylesheet );
			if ( $size > 0 && $size < self::MAX_CSS_SIZE ) {
				$content = file_get_contents( $stylesheet );
				if ( $content ) {
					$css .= $content;
				}
			}
		}

		set_transient( $cache_key, $css, HOUR_IN_SECONDS );

		return $css;
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['mobile_friendliness'] = array(
			'label'  => __( 'Mobile Friendliness', 'wpshadow' ),
			'test'   => array( $this, 'test_mobile' ),
		);

		return $tests;
	}

	public function test_mobile(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Mobile Friendliness', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable mobile friendliness checks.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'mobile_friendliness',
			);
		}

		$results = array();
		$pass_count = 0;

		if ( $this->is_sub_feature_enabled( 'viewport_check', true ) ) {
			$result = $this->check_viewport();
			if ( $result['pass'] ) {
				$pass_count++;
			}
			$results[] = $result['message'];
		}

		if ( $this->is_sub_feature_enabled( 'touch_targets', true ) ) {
			$result = $this->check_touch_targets();
			if ( $result['pass'] ) {
				$pass_count++;
			}
			$results[] = $result['message'];
		}

		if ( $this->is_sub_feature_enabled( 'font_sizes', true ) ) {
			$result = $this->check_font_sizes();
			if ( $result['pass'] ) {
				$pass_count++;
			}
			$results[] = $result['message'];
		}

		if ( $this->is_sub_feature_enabled( 'tap_spacing', true ) ) {
			$result = $this->check_tap_spacing();
			if ( $result['pass'] ) {
				$pass_count++;
			}
			$results[] = $result['message'];
		}

		$total = count( $results );
		$status = $pass_count === $total ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Mobile Friendliness', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of %d mobile checks passed.', 'wpshadow' ),
				$pass_count,
				$total
			),
			'actions'     => '',
			'test'        => 'mobile_friendliness',
		);
	}
}
