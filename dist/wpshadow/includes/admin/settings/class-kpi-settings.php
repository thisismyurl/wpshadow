<?php
/**
 * KPI Tracking Settings Page
 *
 * Provides UI for KPI and impact tracking preferences
 * (Commandment #9: Everything Has a KPI)
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
 * KPI Settings Class
 *
 * Manages KPI tracking and value demonstration settings to show
 * users the measurable impact WPShadow has on their site.
 *
 * @since 1.6093.1200
 */
class KPI_Settings {

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
	 * Enqueue KPI settings assets.
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'settings_page_wpshadow-kpi' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-kpi-settings',
			WPSHADOW_URL . 'assets/css/kpi-settings.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Register the settings page in WordPress admin
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_menu_page(): void {
		add_options_page(
			__( 'KPI & Impact Tracking', 'wpshadow' ),
			__( 'KPI Tracking', 'wpshadow' ),
			'manage_options',
			'wpshadow-kpi',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the KPI settings page
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
		$track_usage = Settings_Registry::track_feature_usage();
		$show_metrics = Settings_Registry::show_impact_metrics();
		$track_value = Settings_Registry::enable_value_tracking();

		?>
		<div class="wrap wpshadow-settings-page">
			<h1>📊 <?php esc_html_e( 'KPI & Impact Tracking', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			
			<div class="wpshadow-settings-intro">
				<h2><?php esc_html_e( 'See the Value WPShadow Delivers', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'These settings help you measure and demonstrate the impact WPShadow has on your site—like time saved, performance gains, and issues prevented. Great for showing your boss or clients why this plugin matters.', 'wpshadow' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_kpi_settings' );
				do_settings_sections( 'wpshadow_kpi_settings' );
				?>

				<table class="form-table" role="presentation">
					<!-- Feature Usage Tracking -->
					<tr>
						<th scope="row">
							<label for="wpshadow_track_feature_usage">
								<?php esc_html_e( 'Feature Usage Tracking', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_track_feature_usage" 
									id="wpshadow_track_feature_usage" 
									value="1" 
									<?php checked( $track_usage, true ); ?>
								/>
								<?php esc_html_e( 'Track which features help you most', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'We\'ll track which diagnostics you run, which treatments you apply, and what reports you generate. This data is anonymous and helps us improve WPShadow by focusing on features people actually use.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-privacy-box">
								<strong>🔒 <?php esc_html_e( 'Privacy Promise:', 'wpshadow' ); ?></strong><br>
								<?php esc_html_e( 'We ONLY track feature names (like "Security Diagnostic Run"), never your actual site data. No personal information, no content, no visitors tracked.', 'wpshadow' ); ?>
							</div>
						</td>
					</tr>

					<!-- Show Impact Metrics -->
					<tr>
						<th scope="row">
							<label for="wpshadow_show_impact_metrics">
								<?php esc_html_e( 'Show Impact Metrics', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_show_impact_metrics" 
									id="wpshadow_show_impact_metrics" 
									value="1" 
									<?php checked( $show_metrics, true ); ?>
								/>
								<?php esc_html_e( 'Display time saved and performance gains', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'Shows metrics like "This diagnostic saved you 2 hours of manual checking" or "Your site loads 30% faster after these fixes." Great for demonstrating ROI to clients or management.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-example-box">
								<strong><?php esc_html_e( 'Example Metrics:', 'wpshadow' ); ?></strong><br>
								<ul class="wpshadow-example-list">
									<li>⏱️ <?php esc_html_e( 'Time saved: 12.5 hours this month', 'wpshadow' ); ?></li>
									<li>🚀 <?php esc_html_e( 'Performance: 28% faster page loads', 'wpshadow' ); ?></li>
									<li>🛡️ <?php esc_html_e( 'Security: 8 vulnerabilities prevented', 'wpshadow' ); ?></li>
									<li>💰 <?php esc_html_e( 'Estimated savings: $450 (avoided developer costs)', 'wpshadow' ); ?></li>
								</ul>
							</div>
						</td>
					</tr>

					<!-- Value Tracking -->
					<tr>
						<th scope="row">
							<label for="wpshadow_enable_value_tracking">
								<?php esc_html_e( 'Value Tracking', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_enable_value_tracking" 
									id="wpshadow_enable_value_tracking" 
									value="1" 
									<?php checked( $track_value, true ); ?>
								/>
								<?php esc_html_e( 'Calculate money saved and issues prevented', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'Estimates the monetary value of fixes (like "prevented security breach worth $5,000" or "avoided 3 hours of developer time at $100/hr"). Useful for justifying the plugin to stakeholders.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-value-formula">
								<strong><?php esc_html_e( 'How We Calculate Value:', 'wpshadow' ); ?></strong>
								<ul class="wpshadow-value-formula-list">
									<li><?php esc_html_e( 'Time saved × your hourly rate (you set this in settings)', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Issues prevented × industry average cost of that issue', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Performance gains × estimated revenue impact (based on research)', 'wpshadow' ); ?></li>
								</ul>
								<p class="wpshadow-value-formula-tip">
									<?php esc_html_e( '💡 Tip: These are conservative estimates based on industry research. Your actual value may be higher!', 'wpshadow' ); ?>
								</p>
							</div>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save KPI Settings', 'wpshadow' ) ); ?>
			</form>

			<div class="wpshadow-settings-footer">
				<h3><?php esc_html_e( 'Why This Matters', 'wpshadow' ); ?></h3>
				<p>
					<?php
					esc_html_e(
						'Most WordPress plugins don\'t track their impact, so you never know if they\'re actually helping. WPShadow measures everything—time saved, performance gains, security improvements—so you can prove the value to your boss, clients, or yourself.',
						'wpshadow'
					);
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Philosophy Alignment:', 'wpshadow' ); ?></strong>
					<?php esc_html_e( 'Commandment #9: Everything Has a KPI', 'wpshadow' ); ?>
				</p>
				<p>
					<a href="https://wpshadow.com/kb/kpi-tracking" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Learn more about how KPI tracking works', 'wpshadow' ); ?>
					</a>
				</p>
			</div>
		</div>

		<?php
	}
}
