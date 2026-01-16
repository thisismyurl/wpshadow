<?php
/**
 * Feature: Skip Links Injection
 *
 * Auto-injects skip-to-content/skip-to-nav links with theme-aware placement;
 * respects your nav-accessibility feature settings.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'name'               => __( 'Add Skip Navigation Links', 'plugin-wpshadow' ),
				'description'        => __( 'Help keyboard users navigate faster - add hidden skip links to main content.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-universal-access',
				'category'           => 'accessibility',
				'priority'           => 20,
			)
		);

		// Register default settings.
		$this->register_default_settings(
			array(
				'wpshadow_skiplinks_options' => $this->get_default_options(),
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'skip_to_content'     => __( 'Skip to Content Link', 'plugin-wpshadow' ),
					'skip_to_nav'         => __( 'Skip to Navigation Link', 'plugin-wpshadow' ),
					'skip_to_footer'      => __( 'Skip to Footer Link', 'plugin-wpshadow' ),
					'custom_styling'      => __( 'Custom Skip Link Styling', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'skip_to_content'     => true,
						'skip_to_nav'         => true,
						'skip_to_footer'      => false,
						'custom_styling'      => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Skiplinks feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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
		$options = (array) $this->get_setting( 'wpshadow_skiplinks_options', $this->get_default_options() );

		if ( ! ( $options['enable_skiplinks'] ?? true ) ) {
			return;
		}

		$links = array();

		// Add skip to content link.
		if ( $options['skip_to_content'] ?? true ) {
			$content_id = $this->get_content_id();
			$links[]    = sprintf(
				'<a href="#%s" class="wps-skip-link wps-skip-to-content">%s</a>',
				esc_attr( $content_id ),
				esc_html( $options['content_label'] ?? __( 'Skip to content', 'plugin-wpshadow' ) )
			);
		}

		// Add skip to navigation link.
		if ( $options['skip_to_nav'] ?? true ) {
			$nav_id  = $this->get_nav_id();
			$links[] = sprintf(
				'<a href="#%s" class="wps-skip-link wps-skip-to-nav">%s</a>',
				esc_attr( $nav_id ),
				esc_html( $options['nav_label'] ?? __( 'Skip to navigation', 'plugin-wpshadow' ) )
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
		$options = (array) $this->get_setting( 'wpshadow_skiplinks_options', $this->get_default_options() );

		if ( ! ( $options['enable_skiplinks'] ?? true ) ) {
			return;
		}

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
		$options = (array) $this->get_setting( 'wpshadow_skiplinks_options', $this->get_default_options() );

		// Allow theme customization via filter.
		$bg_color   = $this->sanitize_hex_color( apply_filters( 'wpshadow_skiplinks_bg_color', $options['bg_color'] ?? '#21759b' ) );
		$text_color = $this->sanitize_hex_color( apply_filters( 'wpshadow_skiplinks_text_color', $options['text_color'] ?? '#ffffff' ) );

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
	 * Returns a common content ID used by most themes. If the default doesn't match
	 * your theme, set a custom content_id via the WPSHADOW_skiplinks_options.
	 *
	 * @return string Content element ID.
	 */
	private function get_content_id(): string {
		$options    = (array) $this->get_setting( 'wpshadow_skiplinks_options', $this->get_default_options() );
		$content_id = $options['content_id'] ?? '';

		if ( ! empty( $content_id ) ) {
			return sanitize_key( $content_id );
		}

		// Common content IDs in themes. Returns first one as default.
		// Users should customize via options if their theme uses a different ID.
		$common_ids = array( 'content', 'main', 'main-content', 'primary', 'site-content' );

		return $common_ids[0];
	}

	/**
	 * Get navigation element ID based on theme.
	 *
	 * Returns a common navigation ID used by most themes. If the default doesn't match
	 * your theme, set a custom nav_id via the WPSHADOW_skiplinks_options.
	 *
	 * @return string Navigation element ID.
	 */
	private function get_nav_id(): string {
		$options = (array) $this->get_setting( 'wpshadow_skiplinks_options', $this->get_default_options() );
		$nav_id  = $options['nav_id'] ?? '';

		if ( ! empty( $nav_id ) ) {
			return sanitize_key( $nav_id );
		}

		// Common navigation IDs in themes. Returns first one as default.
		// Users should customize via options if their theme uses a different ID.
		$common_ids = array( 'site-navigation', 'primary-navigation', 'nav', 'navigation', 'primary-menu' );

		return $common_ids[0];
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['skiplinks'] = array(
			'label' => __( 'Skip Links Accessibility', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_skiplinks' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for skip links.
	 *
	 * @return array Test result.
	 */
	public function test_skiplinks(): array {
		$enabled_features = 0;

		if ( get_option( 'wpshadow_skiplinks_skip_to_content', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_skiplinks_skip_to_nav', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_skiplinks_skip_to_footer', false ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 1 ? 'good' : 'recommended';
		$label  = $enabled_features >= 1 ?
			__( 'Skip links are active', 'plugin-wpshadow' ) :
			__( 'Skip links should be enabled', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Accessibility', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled skip links */
					__( '%d skip link types are enabled, improving keyboard navigation accessibility.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'skiplinks',
		);
	}

	/**
	 * Get default options.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array(
			'enable_skiplinks' => true,
			'skip_to_content'  => true,
			'skip_to_nav'      => true,
			'content_id'       => '',  // Auto-detect if empty.
			'nav_id'           => '',  // Auto-detect if empty.
			'content_label'    => __( 'Skip to content', 'plugin-wpshadow' ),
			'nav_label'        => __( 'Skip to navigation', 'plugin-wpshadow' ),
			'bg_color'         => '#21759b',
			'text_color'       => '#ffffff',
		);
	}
}
