<?php
/**
 * Learning Settings Page
 *
 * Provides UI for learning preferences to support different learning
 * styles and neurodiversity needs (Pillar 🎓: Learning Inclusive)
 *
 * @package    WPShadow
 * @subpackage Admin\Settings
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Learning Settings Class
 *
 * Manages learning style and neurodiversity accommodation settings
 * to make WPShadow work for all types of learners.
 *
 * @since 1.6093.1200
 */
class Learning_Settings {

	/**
	 * Initialize the settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the settings page in WordPress admin
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_menu_page(): void {
		add_options_page(
			__( 'Learning Settings', 'wpshadow' ),
			__( 'Learning', 'wpshadow' ),
			'manage_options',
			'wpshadow-learning',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue CSS for the settings page
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'settings_page_wpshadow-learning' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-learning-settings',
			WPSHADOW_URL . 'assets/css/learning-settings.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render the learning settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_page(): void {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		// Get current settings
		$learning_style = Settings_Registry::get_learning_style();
		$step_by_step = Settings_Registry::is_step_by_step_mode();
		$show_examples = Settings_Registry::show_examples();
		$adhd_mode = Settings_Registry::is_adhd_friendly_mode();
		$dyslexia_font = Settings_Registry::use_dyslexia_font();

		if ( $dyslexia_font ) {
			add_filter(
				'admin_body_class',
				static function( string $classes ): string {
					return trim( $classes . ' wpshadow-dyslexic-font' );
				}
			);
		}

		?>
		<div class="wrap wpshadow-settings-page">
			<h1>🎓 <?php esc_html_e( 'Learning Settings', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			
			<div class="wpshadow-settings-intro">
				<h2><?php esc_html_e( 'Everyone Learns Differently', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Tell us how you learn best, and we\'ll adapt WPShadow to match your style. Whether you prefer reading text, watching videos, or hands-on practice, we support all learning approaches.', 'wpshadow' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_learning_settings' );
				do_settings_sections( 'wpshadow_learning_settings' );
				?>

				<table class="form-table" role="presentation">
					<!-- Learning Style Preference -->
					<tr>
						<th scope="row">
							<label for="wpshadow_preferred_learning_style">
								<?php esc_html_e( 'Preferred Learning Format', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select name="wpshadow_preferred_learning_style" id="wpshadow_preferred_learning_style">
								<option value="mixed" <?php selected( $learning_style, 'mixed' ); ?>>
									<?php esc_html_e( 'Show Me Everything (text, videos, examples)', 'wpshadow' ); ?>
								</option>
								<option value="text" <?php selected( $learning_style, 'text' ); ?>>
									<?php esc_html_e( 'Text & Written Guides (I prefer reading)', 'wpshadow' ); ?>
								</option>
								<option value="video" <?php selected( $learning_style, 'video' ); ?>>
									<?php esc_html_e( 'Videos & Visual Learning (I learn by watching)', 'wpshadow' ); ?>
								</option>
								<option value="interactive" <?php selected( $learning_style, 'interactive' ); ?>>
									<?php esc_html_e( 'Hands-On Practice (I learn by doing)', 'wpshadow' ); ?>
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'We\'ll prioritize this format when showing help content, documentation, and tutorials', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<!-- Step-by-Step Mode -->
					<tr>
						<th scope="row">
							<label for="wpshadow_step_by_step_mode">
								<?php esc_html_e( 'Step-by-Step Guidance', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_step_by_step_mode" 
									id="wpshadow_step_by_step_mode" 
									value="1" 
									<?php checked( $step_by_step, true ); ?>
								/>
								<?php esc_html_e( 'Break complex tasks into smaller steps', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'When enabled, we\'ll guide you through complicated operations one step at a time, showing progress and letting you pause or go back.',
									'wpshadow'
								);
								?>
							</p>
						</td>
					</tr>

					<!-- Show Examples -->
					<tr>
						<th scope="row">
							<label for="wpshadow_show_examples">
								<?php esc_html_e( 'Real-World Examples', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_show_examples" 
									id="wpshadow_show_examples" 
									value="1" 
									<?php checked( $show_examples, true ); ?>
								/>
								<?php esc_html_e( 'Show examples with every explanation', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'We\'ll include practical examples showing how features work in real sites (like "This diagnostic helped a restaurant site load 3x faster")',
									'wpshadow'
								);
								?>
							</p>
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<hr class="wpshadow-learning-settings-separator">
							<h3><?php esc_html_e( 'Neurodiversity Support', 'wpshadow' ); ?></h3>
							<p><?php esc_html_e( 'These settings help people with ADHD, dyslexia, and other neurodivergent conditions use WPShadow more comfortably.', 'wpshadow' ); ?></p>
						</td>
					</tr>

					<!-- ADHD-Friendly Mode -->
					<tr>
						<th scope="row">
							<label for="wpshadow_adhd_friendly_mode">
								<?php esc_html_e( 'ADHD-Friendly Mode', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_adhd_friendly_mode" 
									id="wpshadow_adhd_friendly_mode" 
									value="1" 
									<?php checked( $adhd_mode, true ); ?>
								/>
								<?php esc_html_e( 'Reduce distractions and show clear priorities', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'Highlights the most important actions, reduces visual clutter, shows progress bars, and auto-saves your work frequently.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-feature-list">
								<strong><?php esc_html_e( 'What This Changes:', 'wpshadow' ); ?></strong>
								<ul>
									<li>✅ <?php esc_html_e( 'Clear "Do This Next" indicators', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Progress bars show completion', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Auto-save every 30 seconds', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Reduced animations', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Simplified navigation', 'wpshadow' ); ?></li>
								</ul>
							</div>
						</td>
					</tr>

					<!-- Dyslexia-Friendly Font -->
					<tr>
						<th scope="row">
							<label for="wpshadow_dyslexia_friendly_font">
								<?php esc_html_e( 'Dyslexia-Friendly Font', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_dyslexia_friendly_font" 
									id="wpshadow_dyslexia_friendly_font" 
									value="1" 
									<?php checked( $dyslexia_font, true ); ?>
								/>
								<?php esc_html_e( 'Use dyslexia-friendly font stack', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'Switches to a dyslexia-friendly local font stack with clearer letterforms and generous spacing to improve readability.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-font-preview">
								<p class="wpshadow-font-preview-text">
									<?php
									esc_html_e(
										'This is how text looks with the dyslexia-friendly font stack: clearer letter shapes and wider spacing can make reading more comfortable.',
										'wpshadow'
									);
									?>
								</p>
							</div>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Learning Settings', 'wpshadow' ) ); ?>
			</form>

			<div class="wpshadow-settings-footer">
				<h3><?php esc_html_e( 'Why This Matters', 'wpshadow' ); ?></h3>
				<p>
					<?php
					esc_html_e(
						'Research shows that people learn in different ways. Some need to read, some need to watch, some need to practice. About 15-20% of people have dyslexia or ADHD. These settings ensure WPShadow works for everyone.',
						'wpshadow'
					);
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Philosophy Alignment:', 'wpshadow' ); ?></strong>
					<?php esc_html_e( '🎓 Learning Inclusive (CANON Pillar)', 'wpshadow' ); ?>
				</p>
				<p>
					<a href="https://wpshadow.com/kb/learning-styles" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Learn more about learning styles and neurodiversity', 'wpshadow' ); ?>
					</a>
				</p>
			</div>
		</div>

		<?php
	}
}
