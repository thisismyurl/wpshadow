<?php
/**
 * Developer Settings Page
 *
 * Provides settings for extension development, hook visibility,
 * API documentation, and sandbox testing.
 *
 * Philosophy Alignment:
 * - Commandment #12: Expandable (developers can extend for free)
 * - Commandment #6: Drive to Training (inline documentation)
 * - Commandment #1: Helpful Neighbor (make extension easy)
 *
 * @package    WPShadow
 * @subpackage Admin\Settings
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Settings;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Developer Settings Class
 *
 * Manages developer mode and extension development features.
 *
 * @since 0.6093.1200
 */
class Developer_Settings {

	/**
	 * Register hooks
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings_section' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_developer_mode_notice' ) );
	}

	/**
	 * Add settings page to WordPress admin menu
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function add_settings_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Developer Settings', 'wpshadow' ),
			__( 'Developer', 'wpshadow' ),
			'manage_options',
			'wpshadow-developer',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register settings section and fields
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_settings_section(): void {
		add_settings_section(
			'wpshadow_developer_section',
			__( 'Developer Mode Options', 'wpshadow' ),
			array( __CLASS__, 'render_section_description' ),
			'wpshadow_developer_settings'
		);

		// Developer Mode
		add_settings_field(
			'wpshadow_developer_mode',
			__( 'Developer Mode', 'wpshadow' ),
			array( __CLASS__, 'render_developer_mode_field' ),
			'wpshadow_developer_settings',
			'wpshadow_developer_section'
		);

		// Show Hooks
		add_settings_field(
			'wpshadow_show_hooks',
			__( 'Display Hooks & Filters', 'wpshadow' ),
			array( __CLASS__, 'render_show_hooks_field' ),
			'wpshadow_developer_settings',
			'wpshadow_developer_section'
		);

		// Inline API Documentation
		add_settings_field(
			'wpshadow_api_documentation_inline',
			__( 'Inline API Documentation', 'wpshadow' ),
			array( __CLASS__, 'render_inline_docs_field' ),
			'wpshadow_developer_settings',
			'wpshadow_developer_section'
		);

		// Extension Sandbox
		add_settings_field(
			'wpshadow_extension_sandbox',
			__( 'Extension Testing Sandbox', 'wpshadow' ),
			array( __CLASS__, 'render_sandbox_field' ),
			'wpshadow_developer_settings',
			'wpshadow_developer_section'
		);
	}

	/**
	 * Render section description
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_section_description(): void {
		?>
		<p><?php esc_html_e( 'These tools help developers build extensions and integrations with WPShadow.', 'wpshadow' ); ?></p>
		<p><?php esc_html_e( 'All extension features are free - we want developers to extend WPShadow!', 'wpshadow' ); ?></p>
		<?php
	}

	/**
	 * Render developer mode field
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_developer_mode_field(): void {
		$value = Settings_Registry::is_developer_mode();
		?>
		<label>
			<input type="checkbox" name="wpshadow_developer_mode" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Enable developer mode', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Shows extension points, available hooks, and technical information throughout the admin.', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render show hooks field
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_show_hooks_field(): void {
		$value          = Settings_Registry::should_show_hooks();
		$developer_mode = Settings_Registry::is_developer_mode();
		?>
		<label>
			<input
				type="checkbox"
				name="wpshadow_show_hooks"
				value="1"
				<?php checked( $value ); ?>
				<?php disabled( ! $developer_mode ); ?>
			/>
			<?php esc_html_e( 'Display available hooks and filters', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php
			if ( ! $developer_mode ) {
				esc_html_e( 'Enable Developer Mode first to use this feature.', 'wpshadow' );
			} else {
				esc_html_e( 'Shows which hooks and filters you can use to extend WPShadow functionality.', 'wpshadow' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Render inline docs field
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_inline_docs_field(): void {
		$value          = Settings_Registry::is_inline_docs_enabled();
		$developer_mode = Settings_Registry::is_developer_mode();
		?>
		<label>
			<input
				type="checkbox"
				name="wpshadow_api_documentation_inline"
				value="1"
				<?php checked( $value ); ?>
				<?php disabled( ! $developer_mode ); ?>
			/>
			<?php esc_html_e( 'Show inline API documentation', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php
			if ( ! $developer_mode ) {
				esc_html_e( 'Enable Developer Mode first to use this feature.', 'wpshadow' );
			} else {
				esc_html_e( 'Displays code examples and usage instructions directly in the admin interface.', 'wpshadow' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Render sandbox field
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_sandbox_field(): void {
		$value          = Settings_Registry::is_sandbox_enabled();
		$developer_mode = Settings_Registry::is_developer_mode();
		?>
		<label>
			<input
				type="checkbox"
				name="wpshadow_extension_sandbox"
				value="1"
				<?php checked( $value ); ?>
				<?php disabled( ! $developer_mode ); ?>
			/>
			<?php esc_html_e( 'Enable extension testing sandbox', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php
			if ( ! $developer_mode ) {
				esc_html_e( 'Enable Developer Mode first to use this feature.', 'wpshadow' );
			} else {
				esc_html_e( 'Test your extensions in a safe environment without affecting your live site.', 'wpshadow' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Render the settings page
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Developer Settings', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>

			<div class="wpshadow-developer-intro">
				<h2><?php esc_html_e( '🛠️ Build Extensions for Free', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'WPShadow is designed to be extended by developers. All extension features are 100% free.', 'wpshadow' ); ?></p>

				<h3><?php esc_html_e( 'What You Can Build:', 'wpshadow' ); ?></h3>
				<ul>
					<li><strong><?php esc_html_e( 'Custom Diagnostics:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Add your own health checks', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Custom Treatments:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Create automatic fixes for issues', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Custom Workflows:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Automate WordPress tasks', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Custom Integrations:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Connect external services', 'wpshadow' ); ?></li>
					<li><strong><?php esc_html_e( 'Dashboard Widgets:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Add custom reporting', 'wpshadow' ); ?></li>
				</ul>

				<h3><?php esc_html_e( 'Extension Points Available:', 'wpshadow' ); ?></h3>
				<ul>
					<li><code>Diagnostic_Base</code> - <?php esc_html_e( 'Extend to create diagnostics', 'wpshadow' ); ?></li>
					<li><code>Treatment_Base</code> - <?php esc_html_e( 'Extend to create treatments', 'wpshadow' ); ?></li>
					<li><code>AJAX_Handler_Base</code> - <?php esc_html_e( 'Secure AJAX endpoints', 'wpshadow' ); ?></li>
					<li><code>Settings_Registry</code> - <?php esc_html_e( 'Register custom settings', 'wpshadow' ); ?></li>
					<li><code>Activity_Logger</code> - <?php esc_html_e( 'Track custom events', 'wpshadow' ); ?></li>
				</ul>

				<div class="wpshadow-developer-resources">
					<h3><?php esc_html_e( '📚 Developer Resources:', 'wpshadow' ); ?></h3>
					<ul>
						<li><a href="https://wpshadow.com/developers" target="_blank"><?php esc_html_e( 'Developer Documentation', 'wpshadow' ); ?></a></li>
						<li><a href="https://wpshadow.com/developers/api" target="_blank"><?php esc_html_e( 'API Reference', 'wpshadow' ); ?></a></li>
						<li><a href="https://wpshadow.com/developers/examples" target="_blank"><?php esc_html_e( 'Code Examples', 'wpshadow' ); ?></a></li>
						<li><a href="https://github.com/thisismyurl/wpshadow" target="_blank"><?php esc_html_e( 'GitHub Repository', 'wpshadow' ); ?></a></li>
					</ul>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_developer_settings' );
				do_settings_sections( 'wpshadow_developer_settings' );
				submit_button( __( 'Save Developer Settings', 'wpshadow' ) );
				?>
			</form>

			<?php if ( Settings_Registry::is_developer_mode() ) : ?>
				<div class="wpshadow-developer-active">
					<h3><?php esc_html_e( '✅ Developer Mode Active', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'You should now see extension points, hooks, and developer information throughout WPShadow.', 'wpshadow' ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Go to Dashboard', 'wpshadow' ); ?>
					</a></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Show admin notice when developer mode is active
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function show_developer_mode_notice(): void {
		if ( ! Settings_Registry::is_developer_mode() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( '🛠️ Developer Mode Active', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'Extension points and hooks are visible. Disable in Settings > Developer.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}
}
