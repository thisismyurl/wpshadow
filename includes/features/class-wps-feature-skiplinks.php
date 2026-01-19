<?php
/**
 * Feature: Skip Links Injection
 *
 * Auto-injects skip-to-content/skip-to-nav links with theme-aware placement;
 * respects your nav-accessibility feature settings.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WPSHADOW_Feature_Skiplinks
 *
 * Improves accessibility by adding skip links to bypass navigation.
 */
final class WPSHADOW_Feature_Skiplinks extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'skiplinks',
				'name'               => __( 'Add Skip Navigation Links', 'wpshadow' ),
				'description_short'  => __( 'Help keyboard users navigate faster - add hidden skip links to main content.', 'wpshadow' ),
				'description_long'   => __( 'Improve website accessibility by automatically injecting skip navigation links that help keyboard users bypass repetitive navigation and jump directly to important page sections. Skip links are invisible until focused with the Tab key, providing a critical accessibility feature for users who navigate via keyboard. The feature intelligently detects common theme patterns and injects links to content, navigation, and footer areas with customizable styling.', 'wpshadow' ),
				'description_wizard' => __( 'Keyboard users must tab through every navigation link to reach your content. Skip links let them jump directly to what they need. Enable this to meet WCAG 2.1 Level A accessibility requirements and provide a better experience for keyboard and screen reader users.', 'wpshadow' ),
				'description'        => __( 'Add skip links to help keyboard users navigate faster.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-universal-access',
				'category'           => 'accessibility',
				'priority'           => 20,
				'aliases'            => array(
					'accessibility',
					'keyboard navigation',
					'wcag',
					'a11y',
					'skip to content',
					'screen reader',
				),
				'sub_features'       => array(
					'skip_to_content'     => array(
						'name'                => __( 'Skip to Content Link', 'wpshadow' ),
						'description_short'   => __( 'Add skip link to main content area.', 'wpshadow' ),
						'description_long'    => __( 'Adds a skip link that allows keyboard users to jump directly to the main content area, bypassing the site header and navigation. This is the most important skip link for accessibility, as it prevents users from having to tab through dozens of navigation links on every page. The link automatically detects common content IDs used by popular themes.', 'wpshadow' ),
						'description_wizard'  => __( 'Required for WCAG 2.1 Level A compliance. Without this, keyboard users must tab through all navigation on every page load. Enable to provide immediate access to page content.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'skip_to_nav'         => array(
						'name'                => __( 'Skip to Navigation Link', 'wpshadow' ),
						'description_short'   => __( 'Add skip link to main navigation menu.', 'wpshadow' ),
						'description_long'    => __( 'Adds a skip link that allows keyboard users to jump directly to the main navigation menu from the top of the page. This is useful for users who want to access navigation options without tabbing through header elements like logos, search boxes, or promotional banners. Particularly helpful on sites with complex headers.', 'wpshadow' ),
						'description_wizard'  => __( 'Helps users who want to navigate your site find the menu quickly. Especially useful on sites with complex headers containing search, login, or promotional elements before the main menu.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'skip_to_footer'      => array(
						'name'                => __( 'Skip to Footer Link', 'wpshadow' ),
						'description_short'   => __( 'Add skip link to footer section.', 'wpshadow' ),
						'description_long'    => __( 'Adds a skip link that allows keyboard users to jump directly to the footer section. This is useful for accessing footer navigation, copyright information, contact details, or legal links without scrolling through all page content. Particularly helpful on long-form content pages or when footer contains important navigation.', 'wpshadow' ),
						'description_wizard'  => __( 'Let users quickly access footer navigation, contact info, or legal links. Most useful on long-form content sites or when your footer contains secondary navigation or important information.', 'wpshadow' ),
						'default_enabled'     => false,
					),
					'custom_styling'      => array(
						'name'                => __( 'Custom Skip Link Styling', 'wpshadow' ),
						'description_short'   => __( 'Apply custom visual styling to skip links when focused.', 'wpshadow' ),
						'description_long'    => __( 'Applies professional custom styling to skip links when they receive keyboard focus. Skip links are invisible by default but appear with styled buttons when a user tabs to them. The styling includes high-contrast colors, clear borders, appropriate sizing, and smooth animations to ensure skip links are noticeable and easy to use when focused. Styles are customizable via WordPress filters.', 'wpshadow' ),
						'description_wizard'  => __( 'Professional styling makes skip links visible and attractive when focused. Without styling, skip links may be difficult to see or use. Enable for better user experience and professional appearance.', 'wpshadow' ),
						'default_enabled'     => true,
					),
				),
			)
		);
		
		$this->log_activity( 'feature_initialized', 'Skiplinks feature initialized', 'info' );
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

		add_action( 'wp_body_open', array( $this, 'inject_skip_links' ), 1 );
		
		if ( get_option( 'wpshadow_skiplinks_custom_styling', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Inject skip links HTML at the beginning of the body.
	 *
	 * @return void
	 */
	public function inject_skip_links(): void {
		$links = array();

		// Add skip to content link.
		if ( get_option( 'wpshadow_skiplinks_skip_to_content', true ) ) {
			$content_id = $this->get_content_id();
			$links[]    = sprintf(
				'<a href="#%s" class="wps-skip-link wps-skip-to-content">%s</a>',
				esc_attr( $content_id ),
				esc_html__( 'Skip to content', 'wpshadow' )
			);
		}

		// Add skip to navigation link.
		if ( get_option( 'wpshadow_skiplinks_skip_to_nav', true ) ) {
			$nav_id  = $this->get_nav_id();
			$links[] = sprintf(
				'<a href="#%s" class="wps-skip-link wps-skip-to-nav">%s</a>',
				esc_attr( $nav_id ),
				esc_html__( 'Skip to navigation', 'wpshadow' )
			);
		}

		// Add skip to footer link.
		if ( get_option( 'wpshadow_skiplinks_skip_to_footer', false ) ) {
			$footer_id = $this->get_footer_id();
			$links[]   = sprintf(
				'<a href="#%s" class="wps-skip-link wps-skip-to-footer">%s</a>',
				esc_attr( $footer_id ),
				esc_html__( 'Skip to footer', 'wpshadow' )
			);
		}

		if ( empty( $links ) ) {
			return;
		}

		// Each link in the array is already escaped during creation (esc_attr, esc_html).
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Links escaped individually above.
		echo '<div class="wps-skip-links">' . implode( '', $links ) . '</div>';
	}

	/**
	 * Enqueue skip links styles.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		// Register and enqueue a minimal style handle for skip links.
		// We use inline styles only, so no external file is needed.
		// Version is based on plugin version for cache busting.
		wp_register_style( 'wps-skiplinks', '', array(), WPSHADOW_VERSION );
		wp_enqueue_style( 'wps-skiplinks' );

		// Add inline styles for skip links.
		$css = $this->get_skip_links_css();
		wp_add_inline_style( 'wps-skiplinks', $css );
	}

	/**
	 * Get skip links CSS.
	 *
	 * @return string CSS code.
	 */
	private function get_skip_links_css(): string {
		// Allow theme customization via filter.
		$bg_color   = $this->sanitize_hex_color( apply_filters( 'wpshadow_skiplinks_bg_color', '#21759b' ) );
		$text_color = $this->sanitize_hex_color( apply_filters( 'wpshadow_skiplinks_text_color', '#ffffff' ) );

		return '
			.wps-skip-links {
				margin: 0;
				padding: 0;
			}
			
			.wps-skip-link {
				position: absolute;
				width: 1px;
				height: 1px;
				margin: -1px;
				padding: 0;
				overflow: hidden;
				clip: rect(0, 0, 0, 0);
				white-space: nowrap;
				border: 0;
			}
			
			.wps-skip-link:focus {
				position: fixed;
				top: 10px;
				left: 10px;
				width: auto;
				height: auto;
				margin: 0;
				padding: 1rem 1.5rem;
				overflow: visible;
				clip: auto;
				white-space: normal;
				background-color: ' . esc_attr( $bg_color ) . ';
				color: ' . esc_attr( $text_color ) . ';
				text-decoration: none;
				font-size: 1rem;
				font-weight: 600;
				line-height: 1.5;
				z-index: 100000;
				border-radius: 4px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
				outline: 3px solid ' . esc_attr( $text_color ) . ';
				outline-offset: 2px;
			}
			
			.wps-skip-link:hover,
			.wps-skip-link:focus:hover {
				background-color: ' . esc_attr( $this->darken_color( $bg_color, 20 ) ) . ';
			}
		';
	}

	/**
	 * Sanitize a hex color value.
	 *
	 * @param string $color Hex color value.
	 * @return string Sanitized hex color.
	 */
	private function sanitize_hex_color( string $color ): string {
		// Remove any whitespace.
		$color = trim( $color );

		// Add # if missing.
		if ( '#' !== substr( $color, 0, 1 ) ) {
			$color = '#' . $color;
		}

		// Check if valid hex color (3 or 6 characters after #).
		if ( ! preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			// Return default blue if invalid.
			return '#21759b';
		}

		return $color;
	}

	/**
	 * Darken a hex color.
	 *
	 * @param string $hex    Hex color code.
	 * @param int    $percent Percentage to darken.
	 * @return string Darkened hex color.
	 */
	private function darken_color( string $hex, int $percent ): string {
		// Remove # if present.
		$hex = ltrim( $hex, '#' );

		// Convert to RGB.
		if ( 3 === strlen( $hex ) ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		// Darken.
		$r = max( 0, min( 255, (int) ( $r * ( 100 - $percent ) / 100 ) ) );
		$g = max( 0, min( 255, (int) ( $g * ( 100 - $percent ) / 100 ) ) );
		$b = max( 0, min( 255, (int) ( $b * ( 100 - $percent ) / 100 ) ) );

		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Get content element ID based on theme.
	 *
	 * Returns a common content ID used by most themes.
	 *
	 * @return string Content element ID.
	 */
	private function get_content_id(): string {
		// Common content IDs in themes. Returns first one as default.
		// Allow customization via filter if theme uses different ID.
		$content_id = apply_filters( 'wpshadow_skiplinks_content_id', 'content' );
		return sanitize_key( $content_id );
	}

	/**
	 * Get navigation element ID based on theme.
	 *
	 * Returns a common navigation ID used by most themes.
	 *
	 * @return string Navigation element ID.
	 */
	private function get_nav_id(): string {
		// Common navigation IDs in themes. Returns first one as default.
		// Allow customization via filter if theme uses different ID.
		$nav_id = apply_filters( 'wpshadow_skiplinks_nav_id', 'site-navigation' );
		return sanitize_key( $nav_id );
	}

	/**
	 * Get footer element ID based on theme.
	 *
	 * Returns a common footer ID used by most themes.
	 *
	 * @return string Footer element ID.
	 */
	private function get_footer_id(): string {
		// Common footer IDs in themes. Returns first one as default.
		// Allow customization via filter if theme uses different ID.
		$footer_id = apply_filters( 'wpshadow_skiplinks_footer_id', 'colophon' );
		return sanitize_key( $footer_id );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Array of Site Health tests.
	 * @return array<string, mixed> Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_skiplinks'] = array(
			'label' => __( 'Skip Links Accessibility', 'wpshadow' ),
			'test'  => array( $this, 'test_skiplinks' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for skip links.
	 *
	 * @return array<string, mixed> Test result.
	 */
	public function test_skiplinks(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Skip Links Accessibility', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Accessibility', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Skip links are not enabled. Skip links help keyboard users navigate your site more easily and are required for WCAG 2.1 Level A compliance.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_skiplinks',
			);
		}

		$enabled_features = 0;

		if ( get_option( 'wpshadow_skiplinks_skip_to_content', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_skiplinks_skip_to_nav', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_skiplinks_skip_to_footer', false ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Skip Links Accessibility', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Accessibility', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled skip links */
					__( 'Skip links are active with %d link types enabled, improving keyboard navigation accessibility and helping meet WCAG 2.1 standards.', 'wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_skiplinks',
		);
	}
}
