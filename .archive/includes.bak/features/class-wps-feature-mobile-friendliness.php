<?php declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Mobile_Friendliness extends WPSHADOW_Abstract_Feature {

	const MAX_HEADER_SIZE = 10240;
	const MAX_CSS_SIZE    = 500000;

	public function __construct() {
		parent::__construct( array(
			'id'          => 'mobile-friendliness',
			'name'        => __( 'Mobile Phone Checker', 'wpshadow' ),
			'description' => __( 'Check if your site works well on phones and tablets - text should be readable and buttons easy to tap.', 'wpshadow' ),
			'aliases'     => array( 'responsive', 'mobile check', 'mobile test', 'responsive design', 'mobile optimization', 'viewport', 'touch targets', 'mobile friendly', 'phone optimization', 'tablet optimization', 'responsive check', 'mobile usability' ),
			'sub_features' => array(
				'viewport_check'    => array(
					'name'               => __( 'Viewport Configuration Check', 'wpshadow' ),
					'description_short'  => __( 'Verify site fits on phone screens', 'wpshadow' ),
					'description_long'   => __( 'Checks that your site has proper viewport meta tags so it displays correctly on mobile devices. The viewport tag tells mobile browsers how to scale your content. Without it, phones zoom out to show the full page width, making content tiny and unreadable. This check verifies the tag is present and properly configured.', 'wpshadow' ),
					'description_wizard' => __( 'Essential for mobile displays. Without viewport tags, your site will be zoomed out and unreadable on phones. This verifies it\'s configured correctly.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'touch_targets'     => array(
					'name'               => __( 'Touch Target Size Check', 'wpshadow' ),
					'description_short'  => __( 'Verify buttons are large enough to tap', 'wpshadow' ),
					'description_long'   => __( 'Checks that interactive elements (buttons, links, form fields) are large enough to tap accurately on touch screens. Minimum recommended size is 48x48 pixels for touch targets. Too-small buttons are frustrating on phones and violate accessibility standards. This detects buttons that are too small for easy mobile use.', 'wpshadow' ),
					'description_wizard' => __( 'Small buttons are hard to tap on phones. This checks that your interactive elements meet the 48px minimum size for easy touch interaction.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'font_sizes'        => array(
					'name'               => __( 'Font Size Readability Check', 'wpshadow' ),
					'description_short'  => __( 'Verify text is readable on phones', 'wpshadow' ),
					'description_long'   => __( 'Checks that your base font size is large enough to read on mobile screens without zooming. Minimum recommended size is 14px for comfortable reading. Text smaller than this requires visitors to zoom in, which is a poor mobile experience. This helps identify readability issues.', 'wpshadow' ),
					'description_wizard' => __( 'Text too small to read on phones is a common mobile usability problem. This verifies your text size is large enough for comfortable reading.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'tap_spacing'       => array(
					'name'               => __( 'Tap Spacing Check', 'wpshadow' ),
					'description_short'  => __( 'Verify buttons aren\'t too close together', 'wpshadow' ),
					'description_long'   => __( 'Checks that interactive elements have sufficient spacing between them so touches don\'t accidentally hit the wrong button. Buttons too close together cause user frustration. Recommended minimum spacing is about 8px between touch targets. Helps prevent accidental clicks and improves mobile usability.', 'wpshadow' ),
					'description_wizard' => __( 'Buttons crammed too close together cause accidental clicks on phones. This verifies adequate spacing for accurate touch targets.', 'wpshadow' ),
					'default_enabled'    => true,
				),
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

			$has_viewport = current_theme_supports( 'html5' );
		}

		return array(
			'pass'    => $has_viewport,
			'message' => $has_viewport
				? __( 'Viewport meta tag configured.', 'wpshadow' )
				: __( 'Viewport meta tag missing.', 'wpshadow' ),
		);
	}

	private function check_touch_targets(): array {
		$css = $this->get_theme_css();

		$matches = array();
		preg_match_all( '/(?:button|\.button|\.btn|input\[type=["\']?submit["\']?\])\s*\{[^}]*height\s*:\s*(\d+)px/i', $css, $matches, PREG_PATTERN_ORDER );

		if ( empty( $matches[1] ) ) {
			return array(
				'pass'    => true,
				'message' => __( 'Touch target sizes appear adequate.', 'wpshadow' ),
			);
		}

		$min_size = min( array_map( 'intval', $matches[1] ) );
		$pass = $min_size >= 48; 

		return array(
			'pass'    => $pass,
			'message' => $pass
				? __( 'Touch targets meet WCAG minimum (48px).', 'wpshadow' )
				: sprintf( __( 'Some touch targets (%dpx) are below 48px minimum.', 'wpshadow' ), $min_size ),
		);
	}

	private function check_font_sizes(): array {
		$css = $this->get_theme_css();

		if ( preg_match( '/body\s*\{[^}]*font-size\s*:\s*(\d+)px/i', $css, $matches ) ) {
			$font_size = (int) $matches[1];
			$pass = $font_size >= 14; 

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

	private function check_tap_spacing(): array {
		$css = $this->get_theme_css();

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
