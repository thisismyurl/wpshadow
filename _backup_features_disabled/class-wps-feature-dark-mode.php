<?php
/**
 * Dark Mode Feature
 *
 * Provides dark mode support with automatic detection from WordPress color scheme,
 * manual toggle, and system preference detection.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.77000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dark Mode Feature Class
 */
final class WPSHADOW_Feature_Dark_Mode extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'dark-mode',
				'name'               => __( 'Dark Mode Support', 'wpshadow' ),
				'description'        => __( 'Enable dark mode for WPShadow admin pages with automatic WordPress color scheme detection and manual toggle.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'icon'               => 'dashicons-admin-appearance',
				'category'           => 'interface',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'priority'           => 30,
				'sub_features'       => array(
					'respect_system_preference' => __( 'Respect System Preferences', 'wpshadow' ),
					'user_override'             => __( 'Allow User to Override Default', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dark_mode_assets' ) );
		add_action( 'wp_ajax_wpshadow_set_dark_mode', array( $this, 'ajax_set_dark_mode' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_toggle' ), 100 );
		
		// Add dashboard widget
		add_action( 'admin_init', array( $this, 'maybe_add_dashboard_widget' ) );
	}
	
	/**
	 * Add dashboard widget if on WPShadow dashboard page.
	 *
	 * @return void
	 */
	public function maybe_add_dashboard_widget(): void {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}
		
		// Register widget for dashboard
		add_action( 'wpshadow_dashboard_widgets', array( $this, 'render_dashboard_widget' ) );
	}
	
	/**
	 * Render the dark mode dashboard widget.
	 *
	 * @return void
	 */
	public function render_dashboard_widget(): void {
		$current_mode = $this->get_current_mode();
		$user_preference = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );
		if ( empty( $user_preference ) ) {
			$user_preference = 'auto';
		}
		
		?>
		<div class="postbox wpshadow-dashboard-widget">
			<div class="postbox-header">
				<h2 class="hndle">🌓 <?php esc_html_e( 'Dark Mode', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<p>
					<?php esc_html_e( 'Control the appearance of WPShadow admin pages.', 'wpshadow' ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Current Mode:', 'wpshadow' ); ?></strong>
					<span id="wpshadow-current-mode-display">
						<?php echo esc_html( ucfirst( $current_mode ) ); ?>
					</span>
				</p>
				<p>
					<button type="button" class="button button-primary wpshadow-dark-mode-toggle-btn" data-current-preference="<?php echo esc_attr( $user_preference ); ?>">
						<span class="dashicons dashicons-admin-appearance"></span>
						<?php esc_html_e( 'Toggle Mode:', 'wpshadow' ); ?>
						<strong id="wpshadow-mode-label"><?php echo esc_html( ucfirst( $user_preference ) ); ?></strong>
					</button>
				</p>
				<p class="description">
					<?php esc_html_e( 'Auto mode detects your WordPress color scheme or system preferences.', 'wpshadow' ); ?>
				</p>
				<?php if ( 'auto' === $user_preference ) : ?>
					<p class="description">
						<em>
							<?php
							$wp_scheme = $this->get_wp_color_scheme();
							$dark_schemes = array( 'midnight', 'ectoplasm', 'coffee' );
							if ( in_array( $wp_scheme, $dark_schemes, true ) ) {
								/* translators: %s: WordPress color scheme name */
								printf( esc_html__( 'Detected WordPress "%s" color scheme → Dark mode', 'wpshadow' ), esc_html( $wp_scheme ) );
							} else {
								/* translators: %s: WordPress color scheme name */
								printf( esc_html__( 'Detected WordPress "%s" color scheme → Light mode', 'wpshadow' ), esc_html( $wp_scheme ) );
							}
							?>
						</em>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue dark mode assets.
	 *
	 * @return void
	 */
	public function enqueue_dark_mode_assets(): void {
		// Only on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Enqueue dark mode CSS
		wp_enqueue_style(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/css/dark-mode.css',
			array(),
			WPSHADOW_VERSION
		);

		// Enqueue dark mode JS
		wp_enqueue_script(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/js/dark-mode.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'wpshadow-dark-mode',
			'wpshadowDarkMode',
			array(
				'nonce'           => wp_create_nonce( 'wpshadow_dark_mode' ),
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'currentMode'     => $this->get_current_mode(),
				'wpColorScheme'   => $this->get_wp_color_scheme(),
				'userPreference'  => get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true ),
			)
		);
	}

	/**
	 * Get current dark mode setting.
	 *
	 * @return string 'auto', 'light', or 'dark'
	 */
	private function get_current_mode(): string {
		$preference = get_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', true );
		
		if ( empty( $preference ) || 'auto' === $preference ) {
			// Auto mode - check WordPress color scheme
			$wp_scheme = $this->get_wp_color_scheme();
			return in_array( $wp_scheme, array( 'midnight', 'ectoplasm', 'coffee' ), true ) ? 'dark' : 'light';
		}

		return $preference;
	}

	/**
	 * Get WordPress user color scheme.
	 *
	 * @return string
	 */
	private function get_wp_color_scheme(): string {
		$user_id = get_current_user_id();
		return get_user_meta( $user_id, 'admin_color', true ) ?: 'fresh';
	}

	/**
	 * AJAX handler to set dark mode preference.
	 *
	 * @return void
	 */
	public function ajax_set_dark_mode(): void {
		check_ajax_referer( 'wpshadow_dark_mode', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'auto';

		if ( ! in_array( $mode, array( 'auto', 'light', 'dark' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mode.', 'wpshadow' ) ) );
		}

		update_user_meta( get_current_user_id(), 'wpshadow_dark_mode_preference', $mode );

		wp_send_json_success( array(
			'mode'    => $mode,
			'message' => __( 'Dark mode preference saved.', 'wpshadow' ),
		) );
	}

	/**
	 * Add dark mode toggle to admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_toggle( $wp_admin_bar ): void {
		// Only on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		$current_mode = $this->get_current_mode();
		$icon = 'dark' === $current_mode ? '🌙' : '☀️';

		$wp_admin_bar->add_node( array(
			'id'     => 'wpshadow-dark-mode-toggle',
			'title'  => $icon . ' ' . __( 'Dark Mode', 'wpshadow' ),
			'href'   => '#',
			'meta'   => array(
				'class' => 'wpshadow-dark-mode-toggle',
			),
		) );
	}

	/**
	 * Get default enabled state.
	 *
	 * @return bool
	 */
	public function get_default_enabled(): bool {
		return false; // Opt-in feature
	}
}
