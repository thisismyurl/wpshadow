<?php
/**
 * Accessibility Settings Page
 *
 * Provides settings for keyboard navigation, screen readers, contrast,
 * motion preferences, and other accessibility features.
 *
 * Philosophy Alignment:
 * - Pillar 🌍: Accessibility First (WCAG AA compliance)
 * - Commandment #1: Helpful Neighbor (works for everyone)
 * - Commandment #8: Inspire Confidence (clear settings)
 *
 * @package    WPShadow
 * @subpackage Admin\Settings
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Settings;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Settings Class
 *
 * Manages accessibility preference settings for the admin interface.
 *
 * @since 1.6093.1200
 */
class Accessibility_Settings {

	/**
	 * Register hooks
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings_section' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Add settings page to WordPress admin menu
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_settings_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Accessibility Settings', 'wpshadow' ),
			__( 'Accessibility', 'wpshadow' ),
			'manage_options',
			'wpshadow-accessibility',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register settings section and fields
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_settings_section(): void {
		add_settings_section(
			'wpshadow_accessibility_section',
			__( 'Accessibility Preferences', 'wpshadow' ),
			array( __CLASS__, 'render_section_description' ),
			'wpshadow_accessibility_settings'
		);

		// Keyboard Navigation
		add_settings_field(
			'wpshadow_keyboard_nav_hints',
			__( 'Keyboard Navigation Hints', 'wpshadow' ),
			array( __CLASS__, 'render_keyboard_hints_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// Screen Reader Optimization
		add_settings_field(
			'wpshadow_screen_reader_optimization',
			__( 'Screen Reader Optimization', 'wpshadow' ),
			array( __CLASS__, 'render_screen_reader_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// High Contrast Mode
		add_settings_field(
			'wpshadow_high_contrast_mode',
			__( 'High Contrast Mode', 'wpshadow' ),
			array( __CLASS__, 'render_high_contrast_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// Reduce Motion
		add_settings_field(
			'wpshadow_reduce_motion',
			__( 'Reduce Motion', 'wpshadow' ),
			array( __CLASS__, 'render_reduce_motion_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// Font Size
		add_settings_field(
			'wpshadow_font_size_multiplier',
			__( 'Text Size', 'wpshadow' ),
			array( __CLASS__, 'render_font_size_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// Simplified UI
		add_settings_field(
			'wpshadow_simplified_ui',
			__( 'Simplified Interface', 'wpshadow' ),
			array( __CLASS__, 'render_simplified_ui_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);

		// Focus Indicators
		add_settings_field(
			'wpshadow_focus_indicators',
			__( 'Focus Indicator Visibility', 'wpshadow' ),
			array( __CLASS__, 'render_focus_indicators_field' ),
			'wpshadow_accessibility_settings',
			'wpshadow_accessibility_section'
		);
	}

	/**
	 * Render section description
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_section_description(): void {
		?>
		<p><?php esc_html_e( 'These settings help make WPShadow work better for everyone, including people with disabilities.', 'wpshadow' ); ?></p>
		<p><?php esc_html_e( 'You can enable features like keyboard shortcuts, screen reader support, and visual adjustments.', 'wpshadow' ); ?></p>
		<?php
	}

	/**
	 * Render keyboard hints field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_keyboard_hints_field(): void {
		$value = Settings_Registry::is_keyboard_hints_enabled();
		?>
		<label>
			<input type="checkbox" name="wpshadow_keyboard_nav_hints" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Show keyboard shortcuts and navigation hints', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Helpful for people who navigate with keyboard instead of mouse (essential for many disabled users).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render screen reader field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_screen_reader_field(): void {
		$value = Settings_Registry::is_screen_reader_optimized();
		?>
		<label>
			<input type="checkbox" name="wpshadow_screen_reader_optimization" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Optimize for screen readers', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Adds enhanced labels for blind and low-vision users who use screen readers (like JAWS or NVDA).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render high contrast field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_high_contrast_field(): void {
		$value = Settings_Registry::is_high_contrast();
		?>
		<label>
			<input type="checkbox" name="wpshadow_high_contrast_mode" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Use high contrast colors', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Makes text and buttons easier to see (WCAG AAA standard for visibility).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render reduce motion field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_reduce_motion_field(): void {
		$value = Settings_Registry::is_motion_reduced();
		?>
		<label>
			<input type="checkbox" name="wpshadow_reduce_motion" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Disable animations', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Removes moving elements that can cause motion sensitivity or distraction.', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render font size field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_font_size_field(): void {
		$value = Settings_Registry::get_font_multiplier();
		?>
		<input 
			type="range" 
			name="wpshadow_font_size_multiplier" 
			min="0.8" 
			max="2.0" 
			step="0.1" 
			value="<?php echo esc_attr( (string) $value ); ?>"
			id="wpshadow_font_size_multiplier"
		/>
		<span id="wpshadow_font_size_display"><?php echo esc_html( $value ); ?>×</span>
		<p class="description">
			<?php esc_html_e( 'Adjust text size (0.8 = smaller,1.0 = normal, 2.0 = twice as large).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render simplified UI field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_simplified_ui_field(): void {
		$value = Settings_Registry::is_simplified_ui();
		?>
		<label>
			<input type="checkbox" name="wpshadow_simplified_ui" value="1" <?php checked( $value ); ?> />
			<?php esc_html_e( 'Use simplified interface', 'wpshadow' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Shows fewer options at once to reduce cognitive load (helps with focus and decision-making).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render focus indicators field
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_focus_indicators_field(): void {
		$value = Settings_Registry::get_focus_style();
		?>
		<select name="wpshadow_focus_indicators" id="wpshadow_focus_indicators">
			<option value="standard" <?php selected( $value, 'standard' ); ?>>
				<?php esc_html_e( 'Standard (subtle outline)', 'wpshadow' ); ?>
			</option>
			<option value="enhanced" <?php selected( $value, 'enhanced' ); ?>>
				<?php esc_html_e( 'Enhanced (clear visible outline)', 'wpshadow' ); ?>
			</option>
			<option value="maximum" <?php selected( $value, 'maximum' ); ?>>
				<?php esc_html_e( 'Maximum (bold high-contrast outline)', 'wpshadow' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'How visible the focus outline is when navigating with keyboard (helps you know where you are).', 'wpshadow' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Accessibility Settings', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			
			<div class="wpshadow-settings-intro">
				<h2><?php esc_html_e( '🌍 Making WPShadow Work for Everyone', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'These settings help people with different abilities use WPShadow comfortably:', 'wpshadow' ); ?></p>
				<ul>
					<li><?php esc_html_e( '♿ People who can\'t use a mouse (keyboard navigation)', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( '👁️ People who are blind or have low vision (screen readers)', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( '🎨 People who need different colors (high contrast)', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( '🎭 People sensitive to motion (reduced animations)', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( '🧠 People who need simpler interfaces (cognitive accessibility)', 'wpshadow' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'About 1 in 4 adults has some type of disability. These features help everyone.', 'wpshadow' ); ?></strong></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_accessibility_settings' );
				do_settings_sections( 'wpshadow_accessibility_settings' );
				submit_button( __( 'Save Accessibility Settings', 'wpshadow' ) );
				?>
			</form>

			<div class="wpshadow-settings-footer">
				<p>
					<?php
					printf(
						/* translators: %s: link to accessibility documentation */
						esc_html__( 'Learn more about accessibility features in our %s.', 'wpshadow' ),
						'<a href="https://wpshadow.com/kb/accessibility" target="_blank">' . esc_html__( 'Knowledge Base', 'wpshadow' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue assets for settings page
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-accessibility' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-accessibility-settings',
			WPSHADOW_URL . 'assets/js/accessibility-settings.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_enqueue_style(
			'wpshadow-accessibility-settings',
			WPSHADOW_URL . 'assets/css/accessibility-settings.css',
			array(),
			WPSHADOW_VERSION
		);
	}
}
