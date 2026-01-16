<?php
/**
 * Feature: Mobile-Friendliness Test
 *
 * Analyzes if your content layout is device-adjustable, ensuring buttons are
 * large enough for touch targets and text remains readable on small screens.
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
 * WPSHADOW_Feature_Mobile_Friendliness
 *
 * Provides comprehensive mobile-friendliness testing and recommendations.
 */
final class WPSHADOW_Feature_Mobile_Friendliness extends WPSHADOW_Abstract_Feature {

	/**
	 * Minimum touch target size in pixels (W3C/WCAG recommendation)
	 */
	private const MIN_TOUCH_TARGET_SIZE = 44;

	/**
	 * Minimum font size for readability on mobile devices
	 */
	private const MIN_FONT_SIZE = 12;

	/**
	 * Maximum file size for header.php (1MB)
	 */
	private const MAX_HEADER_FILE_SIZE = 1000000;

	/**
	 * Maximum bytes to read from header.php (10KB)
	 */
	private const MAX_HEADER_READ_SIZE = 10240;

	/**
	 * Maximum file size for main stylesheet (500KB)
	 */
	private const MAX_STYLESHEET_SIZE = 512000;

	/**
	 * Maximum file size for responsive CSS (200KB)
	 */
	private const MAX_RESPONSIVE_CSS_SIZE = 204800;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'mobile-friendliness',
				'name'               => __( 'Mobile-Friendliness Test', 'plugin-wpshadow' ),
				'description'        => __( 'Analyzes if your content layout is device-adjustable, ensuring buttons are large enough for touch targets and text remains readable on small screens.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'widget_label'       => __( 'UX & Accessibility', 'plugin-wpshadow' ),
				'widget_description' => __( 'Improve user experience and accessibility standards', 'plugin-wpshadow' ),
				'icon'               => 'dashicons-smartphone',
				'category'           => 'diagnostics',
			)
		);
	}

	/**
	 * Enable details page for this feature.
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

		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// AJAX handlers for running tests.
		add_action( 'wp_ajax_wpshadow_run_mobile_test', array( $this, 'ajax_run_mobile_test' ) );
		add_action( 'wp_ajax_wpshadow_get_mobile_report', array( $this, 'ajax_get_mobile_report' ) );

		// Add to dashboard widget if available.
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

		// Register Site Health test.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Mobile Friendliness Test initialized', 'info' );
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'wpshadow_mobile_test_options_group',
			'wpshadow_mobile_test_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => array(
					'check_viewport'       => true,
					'check_touch_targets'  => true,
					'check_font_sizes'     => true,
					'check_tap_spacing'    => true,
					'min_touch_size'       => self::MIN_TOUCH_TARGET_SIZE,
					'min_font_size'        => self::MIN_FONT_SIZE,
					'auto_check_on_save'   => false,
				),
			)
		);
	}

	/**
	 * Sanitize plugin options.
	 *
	 * @param array<string, mixed> $input Input options with keys:
	 *                                    - check_viewport (bool): Enable viewport checks
	 *                                    - check_touch_targets (bool): Enable touch target checks
	 *                                    - check_font_sizes (bool): Enable font size checks
	 *                                    - check_tap_spacing (bool): Enable tap spacing checks
	 *                                    - min_touch_size (int): Minimum touch target size in pixels
	 *                                    - min_font_size (int): Minimum font size in pixels
	 *                                    - auto_check_on_save (bool): Auto-run on post save
	 * @return array<string, mixed> Sanitized options.
	 */
	public function sanitize_options( array $input ): array {
		$sanitized = array();

		$sanitized['check_viewport']      = ! empty( $input['check_viewport'] );
		$sanitized['check_touch_targets'] = ! empty( $input['check_touch_targets'] );
		$sanitized['check_font_sizes']    = ! empty( $input['check_font_sizes'] );
		$sanitized['check_tap_spacing']   = ! empty( $input['check_tap_spacing'] );
		$sanitized['min_touch_size']      = max( 32, min( 64, (int) ( $input['min_touch_size'] ?? self::MIN_TOUCH_TARGET_SIZE ) ) );
		$sanitized['min_font_size']       = max( 10, min( 16, (int) ( $input['min_font_size'] ?? self::MIN_FONT_SIZE ) ) );
		$sanitized['auto_check_on_save']  = ! empty( $input['auto_check_on_save'] );

		return $sanitized;
	}

	/**
	 * Render the mobile-friendliness test page.
	 *
	 * @return void
	 */
	public function render_test_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'wpshadow-mobile-test',
			plugin_dir_url( WPSHADOW_FILE ) . 'assets/css/mobile-test.css',
			array(),
			WPSHADOW_VERSION
		);

		// Enqueue admin scripts.
		wp_enqueue_script(
			'wpshadow-mobile-test',
			plugin_dir_url( WPSHADOW_FILE ) . 'assets/js/mobile-test.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-mobile-test',
			'wpshadowMobileTest',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_mobile_test' ),
				'strings' => array(
					'testing'    => __( 'Running tests...', 'plugin-wpshadow' ),
					'complete'   => __( 'Tests complete!', 'plugin-wpshadow' ),
					'error'      => __( 'An error occurred while running tests.', 'plugin-wpshadow' ),
					'homeUrl'    => home_url(),
				),
			)
		);

		$options = get_option( 'wpshadow_mobile_test_options', array() );
		$options = array_merge( $this->sanitize_options( array() ), $options );

		require_once WPSHADOW_PATH . 'includes/views/mobile-test.php';
	}

	/**
	 * Add dashboard widget.
	 *
	 * @return void
	 */
	public function add_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow_mobile_friendliness',
			__( 'Mobile-Friendliness Status', 'plugin-wpshadow' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public function render_dashboard_widget(): void {
		$last_test = get_option( 'wpshadow_last_mobile_test', array() );
		
		if ( empty( $last_test ) ) {
			echo '<p>' . esc_html__( 'No mobile-friendliness test has been run yet.', 'plugin-wpshadow' ) . '</p>';
			echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-mobile-test' ) ) . '" class="button button-primary">' . esc_html__( 'Run Test Now', 'plugin-wpshadow' ) . '</a></p>';
			return;
		}

		$score = $last_test['score'] ?? 0;
		$status_class = $score >= 80 ? 'good' : ( $score >= 50 ? 'warning' : 'critical' );
		$status_text = $score >= 80 ? __( 'Good', 'plugin-wpshadow' ) : ( $score >= 50 ? __( 'Needs Improvement', 'plugin-wpshadow' ) : __( 'Poor', 'plugin-wpshadow' ) );

		echo '<div class="wpshadow-mobile-score">';
		echo '<div class="score ' . esc_attr( $status_class ) . '">';
		echo '<span class="score-value">' . esc_html( $score ) . '</span>';
		echo '<span class="score-label">/' . esc_html__( '100', 'plugin-wpshadow' ) . '</span>';
		echo '</div>';
		echo '<p class="status-text">' . esc_html( $status_text ) . '</p>';
		echo '</div>';

		if ( ! empty( $last_test['issues'] ) ) {
			echo '<ul class="wpshadow-mobile-issues">';
			$count = 0;
			foreach ( $last_test['issues'] as $issue ) {
				if ( $count++ >= 3 ) {
					break;
				}
				echo '<li>' . esc_html( $issue ) . '</li>';
			}
			if ( count( $last_test['issues'] ) > 3 ) {
				echo '<li><em>' . sprintf( esc_html__( 'And %d more issues...', 'plugin-wpshadow' ), count( $last_test['issues'] ) - 3 ) . '</em></li>';
			}
			echo '</ul>';
		}

		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-mobile-test' ) ) . '">' . esc_html__( 'View Full Report', 'plugin-wpshadow' ) . '</a></p>';
	}

	/**
	 * AJAX handler to run mobile-friendliness test.
	 *
	 * @return void
	 */
	public function ajax_run_mobile_test(): void {
		check_ajax_referer( 'wpshadow_mobile_test', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : home_url();
		
		if ( empty( $url ) ) {
			$url = home_url();
		}

		$results = $this->run_mobile_test( $url );

		// Store results.
		update_option( 'wpshadow_last_mobile_test', $results );

		wp_send_json_success( $results );
	}

	/**
	 * AJAX handler to get mobile test report.
	 *
	 * @return void
	 */
	public function ajax_get_mobile_report(): void {
		check_ajax_referer( 'wpshadow_mobile_test', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$last_test = get_option( 'wpshadow_last_mobile_test', array() );

		if ( empty( $last_test ) ) {
			wp_send_json_error( array( 'message' => __( 'No test results available.', 'plugin-wpshadow' ) ) );
		}

		wp_send_json_success( $last_test );
	}

	/**
	 * Run mobile-friendliness test on a URL.
	 *
	 * @param string $url URL to test.
	 * @return array<string, mixed> Test results.
	 */
	private function run_mobile_test( string $url ): array {
		$options = $this->get_options();

		$results = array(
			'url'       => $url,
			'timestamp' => time(),
			'score'     => 100,
			'issues'    => array(),
			'warnings'  => array(),
			'passes'    => array(),
			'recommendations' => array(),
		);

		// Check viewport meta tag.
		if ( $options['check_viewport'] ) {
			$viewport_result = $this->check_viewport( $url );
			if ( ! $viewport_result['pass'] ) {
				$results['issues'][] = $viewport_result['message'];
				$results['score'] -= 25;
				if ( ! empty( $viewport_result['recommendation'] ) ) {
					$results['recommendations'][] = $viewport_result['recommendation'];
				}
			} else {
				$results['passes'][] = $viewport_result['message'];
			}
		}

		// Check touch target sizes.
		if ( $options['check_touch_targets'] ) {
			$touch_result = $this->check_touch_targets( $url, $options['min_touch_size'] );
			if ( ! empty( $touch_result['issues'] ) ) {
				$results['issues'] = array_merge( $results['issues'], $touch_result['issues'] );
				$results['score'] -= min( 25, count( $touch_result['issues'] ) * 5 );
			}
			if ( ! empty( $touch_result['warnings'] ) ) {
				$results['warnings'] = array_merge( $results['warnings'], $touch_result['warnings'] );
				$results['score'] -= min( 10, count( $touch_result['warnings'] ) * 2 );
			}
			if ( ! empty( $touch_result['passes'] ) ) {
				$results['passes'] = array_merge( $results['passes'], $touch_result['passes'] );
			}
			if ( ! empty( $touch_result['recommendations'] ) ) {
				$results['recommendations'] = array_merge( $results['recommendations'], $touch_result['recommendations'] );
			}
		}

		// Check font sizes.
		if ( $options['check_font_sizes'] ) {
			$font_result = $this->check_font_sizes( $url, $options['min_font_size'] );
			if ( ! empty( $font_result['issues'] ) ) {
				$results['issues'] = array_merge( $results['issues'], $font_result['issues'] );
				$results['score'] -= min( 20, count( $font_result['issues'] ) * 5 );
			}
			if ( ! empty( $font_result['passes'] ) ) {
				$results['passes'] = array_merge( $results['passes'], $font_result['passes'] );
			}
			if ( ! empty( $font_result['recommendations'] ) ) {
				$results['recommendations'] = array_merge( $results['recommendations'], $font_result['recommendations'] );
			}
		}

		// Check tap spacing.
		if ( $options['check_tap_spacing'] ) {
			$spacing_result = $this->check_tap_spacing( $url );
			if ( ! empty( $spacing_result['warnings'] ) ) {
				$results['warnings'] = array_merge( $results['warnings'], $spacing_result['warnings'] );
				$results['score'] -= min( 10, count( $spacing_result['warnings'] ) * 2 );
			}
			if ( ! empty( $spacing_result['passes'] ) ) {
				$results['passes'] = array_merge( $results['passes'], $spacing_result['passes'] );
			}
			if ( ! empty( $spacing_result['recommendations'] ) ) {
				$results['recommendations'] = array_merge( $results['recommendations'], $spacing_result['recommendations'] );
			}
		}

		// Ensure score doesn't go below 0.
		$results['score'] = max( 0, $results['score'] );

		return $results;
	}

	/**
	 * Check if viewport meta tag is properly configured.
	 *
	 * @param string $url URL to check.
	 * @return array<string, mixed> Check result.
	 */
	private function check_viewport( string $url ): array {
		// Check cache first.
		$cache_key = 'wpshadow_viewport_check_' . md5( get_template() );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Check if viewport meta tag is present in the theme.
		$theme_functions = get_template_directory() . '/functions.php';
		$theme_header = get_template_directory() . '/header.php';
		
		$has_viewport = false;
		
		// Check header.php for viewport meta tag (limit to first 10KB for performance).
		if ( file_exists( $theme_header ) && filesize( $theme_header ) < self::MAX_HEADER_FILE_SIZE ) {
			$header_content = file_get_contents( $theme_header, false, null, 0, self::MAX_HEADER_READ_SIZE );
			if ( $header_content && preg_match( '/viewport.*width=device-width/i', $header_content ) ) {
				$has_viewport = true;
			}
		}

		// Check if wp_head() is called (which may include viewport from theme support).
		if ( current_theme_supports( 'html5' ) ) {
			$has_viewport = true;
		}

		$result = array();
		if ( ! $has_viewport ) {
			$result = array(
				'pass'    => false,
				'message' => __( 'Viewport meta tag is missing or not properly configured.', 'plugin-wpshadow' ),
				'recommendation' => __( 'Add <meta name="viewport" content="width=device-width, initial-scale=1.0"> to your theme\'s header.php or enable HTML5 theme support.', 'plugin-wpshadow' ),
			);
		} else {
			$result = array(
				'pass'    => true,
				'message' => __( 'Viewport meta tag is properly configured.', 'plugin-wpshadow' ),
			);
		}

		// Cache for 1 hour.
		set_transient( $cache_key, $result, HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check touch target sizes.
	 *
	 * @param string $url URL to check.
	 * @param int    $min_size Minimum touch target size in pixels.
	 * @return array<string, mixed> Check result.
	 */
	private function check_touch_targets( string $url, int $min_size ): array {
		$result = array(
			'issues'   => array(),
			'warnings' => array(),
			'passes'   => array(),
			'recommendations' => array(),
		);

		// Check common button/link classes in the theme.
		$theme_css = $this->get_theme_css();
		
		// Simplified button selectors for basic checking.
		$button_patterns = array(
			'/button\s*\{[^}]*(?:min-height|height)\s*:\s*(\d+)px/i',
			'/\.button\s*\{[^}]*(?:min-height|height)\s*:\s*(\d+)px/i',
			'/\.btn\s*\{[^}]*(?:min-height|height)\s*:\s*(\d+)px/i',
			'/input\[type=["\']?submit["\']?\]\s*\{[^}]*(?:min-height|height)\s*:\s*(\d+)px/i',
		);
		
		$small_buttons = 0;
		
		foreach ( $button_patterns as $pattern ) {
			if ( preg_match( $pattern, $theme_css, $matches ) ) {
				$height = (int) $matches[1];
				if ( $height < $min_size ) {
					$small_buttons++;
				}
			}
		}

		if ( $small_buttons > 0 ) {
			$result['issues'][] = sprintf(
				__( 'Found %d button styles with height less than %dpx (recommended minimum for touch targets).', 'plugin-wpshadow' ),
				$small_buttons,
				$min_size
			);
			$result['recommendations'][] = sprintf(
				__( 'Ensure all clickable elements (buttons, links) have a minimum size of %dx%dpx for easy tapping on mobile devices.', 'plugin-wpshadow' ),
				$min_size,
				$min_size
			);
		} else {
			$result['passes'][] = __( 'Touch target sizes appear to be adequate.', 'plugin-wpshadow' );
		}

		return $result;
	}

	/**
	 * Check font sizes for mobile readability.
	 *
	 * @param string $url URL to check.
	 * @param int    $min_size Minimum font size in pixels.
	 * @return array<string, mixed> Check result.
	 */
	private function check_font_sizes( string $url, int $min_size ): array {
		$result = array(
			'issues'   => array(),
			'passes'   => array(),
			'recommendations' => array(),
		);

		$theme_css = $this->get_theme_css();

		// Check body font size.
		if ( preg_match( '/body\s*\{[^}]*font-size\s*:\s*(\d+)px/i', $theme_css, $matches ) ) {
			$font_size = (int) $matches[1];
			if ( $font_size < $min_size ) {
				$result['issues'][] = sprintf(
					__( 'Base font size (%dpx) is smaller than recommended minimum (%dpx) for mobile readability.', 'plugin-wpshadow' ),
					$font_size,
					$min_size
				);
				$result['recommendations'][] = sprintf(
					__( 'Increase base font size to at least %dpx for better readability on small screens.', 'plugin-wpshadow' ),
					$min_size
				);
			} else {
				$result['passes'][] = __( 'Base font size is appropriate for mobile devices.', 'plugin-wpshadow' );
			}
		} else {
			$result['passes'][] = __( 'Using browser default font size (typically 16px).', 'plugin-wpshadow' );
		}

		return $result;
	}

	/**
	 * Check tap spacing between interactive elements.
	 *
	 * @param string $url URL to check.
	 * @return array<string, mixed> Check result.
	 */
	private function check_tap_spacing( string $url ): array {
		$result = array(
			'warnings' => array(),
			'passes'   => array(),
			'recommendations' => array(),
		);

		$theme_css = $this->get_theme_css();

		// Check for padding/margin on navigation items.
		if ( preg_match( '/\.menu\s+li\s*\{[^}]*(?:padding|margin)\s*:\s*(\d+)px/i', $theme_css, $matches ) ) {
			$spacing = (int) $matches[1];
			if ( $spacing < 8 ) {
				$result['warnings'][] = __( 'Navigation menu items may be too close together for comfortable tapping on mobile devices.', 'plugin-wpshadow' );
				$result['recommendations'][] = __( 'Add at least 8px padding or margin between interactive elements in navigation menus.', 'plugin-wpshadow' );
			} else {
				$result['passes'][] = __( 'Navigation spacing appears adequate for mobile interaction.', 'plugin-wpshadow' );
			}
		}

		return $result;
	}

	/**
	 * Get theme CSS content for analysis.
	 *
	 * @return string CSS content.
	 */
	private function get_theme_css(): string {
		// Check cache first.
		$cache_key = 'wpshadow_theme_css_' . md5( get_template() . get_stylesheet() );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$css = '';
		
		// Get main theme stylesheet (limit to 500KB for performance).
		$stylesheet = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet ) ) {
			$filesize = filesize( $stylesheet );
			if ( $filesize > 0 && $filesize < self::MAX_STYLESHEET_SIZE ) {
				$content = file_get_contents( $stylesheet );
				if ( $content !== false ) {
					$css .= $content;
				}
			}
		}

		// Get additional CSS files if they exist (limit to 200KB each).
		$responsive_css = get_stylesheet_directory() . '/responsive.css';
		if ( file_exists( $responsive_css ) ) {
			$filesize = filesize( $responsive_css );
			if ( $filesize > 0 && $filesize < self::MAX_RESPONSIVE_CSS_SIZE ) {
				$content = file_get_contents( $responsive_css );
				if ( $content !== false ) {
					$css .= $content;
				}
			}
		}

		// Cache for 1 hour.
		set_transient( $cache_key, $css, HOUR_IN_SECONDS );

		return $css;
	}

	/**
	 * Get feature-specific options.
	 *
	 * @return array<string, mixed> Feature options.
	 */
	private function get_options(): array {
		$options = get_option( 'wpshadow_mobile_test_options', array() );
		return array_merge( $this->sanitize_options( array() ), $options );
	}
}
