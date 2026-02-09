<?php
/**
 * WPShadow Account Registration Page
 *
 * Unified registration interface for Guardian, Vault, and Cloud Services.
 *
 * Philosophy: "Register, Don't Pay" (Commandment #3)
 * - Registration is FREE
 * - Creates ONE account for all services
 * - Generous free tiers for everything
 * - Clear upgrade paths without pressure
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.6032.0000
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\WPShadow_Account_API;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account Registration Page Class
 *
 * Manages the unified account registration UI.
 *
 * @since 1.6032.0000
 */
class Account_Registration_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_enqueue_scripts' => 'enqueue_assets',
		);
	}

	/**
	 * Initialize the registration page (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Account_Registration_Page::subscribe() instead
	 * @since      1.6032.0000
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Register menu page.
	 *
	 * @since  1.6032.0000
	 * @return void
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'WPShadow Account', 'wpshadow' ),
			__( 'WPShadow Account', 'wpshadow' ),
			'manage_options',
			'wpshadow-account',
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-users',
			2
		);
	}

	/**
	 * Enqueue page assets.
	 *
	 * @since  1.6032.0000
	 * @param  string $hook Current page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wpshadow-account' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-account',
			WPSHADOW_URL . 'assets/css/account.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-account',
			WPSHADOW_URL . 'assets/js/account.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-account',
			'wpShadowAccount',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'is_registered'  => WPShadow_Account_API::is_registered(),
				'nonces'         => array(
					'register'   => wp_create_nonce( 'wpshadow_account_register' ),
					'connect'    => wp_create_nonce( 'wpshadow_account_connect' ),
					'disconnect' => wp_create_nonce( 'wpshadow_account_disconnect' ),
					'status'     => wp_create_nonce( 'wpshadow_account_status' ),
					'sync'       => wp_create_nonce( 'wpshadow_account_sync' ),
				),
				'i18n'           => array(
					'registering'    => __( 'Registering...', 'wpshadow' ),
					'connecting'     => __( 'Connecting...', 'wpshadow' ),
					'disconnecting'  => __( 'Disconnecting...', 'wpshadow' ),
					'syncing'        => __( 'Syncing services...', 'wpshadow' ),
					'confirm_disconnect' => __( 'Are you sure you want to disconnect your account? Your local data will be safe.', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render the registration page.
	 *
	 * @since  1.6032.0000
	 * @return void
	 */
	public static function render_page() {
		$is_registered = WPShadow_Account_API::is_registered();
		$account_info  = $is_registered ? WPShadow_Account_API::get_account_info() : null;
		$services      = $is_registered ? WPShadow_Account_API::get_services_status() : WPShadow_Account_API::get_default_service_limits();

		?>
		<div class="wrap wpshadow-account-page wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'WPShadow Account', 'wpshadow' ),
				__( 'Manage your WPShadow account and connected services in one place.', 'wpshadow' ),
				'dashicons-admin-users'
			);
			?>

			<div id="wpshadow-account-notices"></div>

			<?php if ( ! $is_registered ) : ?>
				<?php self::render_registration_view( $services ); ?>
			<?php else : ?>
				<?php self::render_dashboard_view( $account_info, $services ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render registration view.
	 *
	 * @since  1.6032.0000
	 * @param  array $services Service information.
	 * @return void
	 */
	private static function render_registration_view( $services ) {
		?>
		<div class="wpshadow-account-registration">
			<div class="wpshadow-hero">
				<h2><?php esc_html_e( 'One Account. All WPShadow Services.', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Register once and get access to Guardian AI scanning, Vault backups, and Cloud Services. All with generous free tiers.', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="wpshadow-two-column">
				<!-- Registration Form -->
				<div class="wpshadow-column">
					<div class="wpshadow-card">
						<h3><?php esc_html_e( 'Create Free Account', 'wpshadow' ); ?></h3>
						<form id="wpshadow-account-register-form">
							<p>
								<label for="register-email">
									<?php esc_html_e( 'Email Address', 'wpshadow' ); ?>
								</label>
								<input
									type="email"
									id="register-email"
									name="email"
									class="regular-text"
									required
									autocomplete="email"
								/>
							</p>
							<p>
								<label for="register-password">
									<?php esc_html_e( 'Password', 'wpshadow' ); ?>
								</label>
								<input
									type="password"
									id="register-password"
									name="password"
									class="regular-text"
									required
									minlength="8"
									autocomplete="new-password"
								/>
								<span class="description">
									<?php esc_html_e( 'At least 8 characters', 'wpshadow' ); ?>
								</span>
							</p>
							<p>
								<button type="submit" class="button button-primary button-hero">
									<?php esc_html_e( 'Create Free Account', 'wpshadow' ); ?>
								</button>
							</p>
						</form>

						<p class="wpshadow-divider">
							<span><?php esc_html_e( 'Already have an account?', 'wpshadow' ); ?></span>
						</p>

						<form id="wpshadow-account-connect-form">
							<p>
								<label for="connect-api-key">
									<?php esc_html_e( 'API Key', 'wpshadow' ); ?>
								</label>
								<input
									type="text"
									id="connect-api-key"
									name="api_key"
									class="regular-text"
									required
									placeholder="wps_xxxxxxxxxxxxxxxx"
								/>
								<span class="description">
									<?php
									printf(
										/* translators: %s: account dashboard URL */
										__( 'Get your API key from %s', 'wpshadow' ),
										'<a href="https://account.wpshadow.com/api-keys" target="_blank">' . esc_html__( 'account.wpshadow.com', 'wpshadow' ) . '</a>'
									);
									?>
								</span>
							</p>
							<p>
								<button type="submit" class="button">
									<?php esc_html_e( 'Connect Account', 'wpshadow' ); ?>
								</button>
							</p>
						</form>
					</div>
				</div>

				<!-- Benefits Column -->
				<div class="wpshadow-column">
					<?php self::render_service_benefits( $services ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render dashboard view.
	 *
	 * @since  1.6032.0000
	 * @param  array|\WP_Error $account_info Account information.
	 * @param  array           $services Service information.
	 * @return void
	 */
	private static function render_dashboard_view( $account_info, $services ) {
		?>
		<div class="wpshadow-account-dashboard">
			<div class="wpshadow-account-header">
				<div class="account-info">
					<h2>
						<?php
						if ( ! is_wp_error( $account_info ) && ! empty( $account_info['email'] ) ) {
							echo esc_html( $account_info['email'] );
						} else {
							esc_html_e( 'Connected', 'wpshadow' );
						}
						?>
					</h2>
					<?php if ( ! is_wp_error( $account_info ) && ! empty( $account_info['member_since'] ) ) : ?>
						<p class="description">
							<?php
							printf(
								/* translators: %s: date */
								esc_html__( 'Member since %s', 'wpshadow' ),
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $account_info['member_since'] ) ) )
							);
							?>
						</p>
					<?php endif; ?>
				</div>
				<div class="account-actions">
					<button type="button" id="wpshadow-sync-services" class="button">
						<?php esc_html_e( 'Sync Services', 'wpshadow' ); ?>
					</button>
					<a href="https://account.wpshadow.com/dashboard" class="button" target="_blank">
						<?php esc_html_e( 'Manage Online', 'wpshadow' ); ?>
					</a>
					<button type="button" id="wpshadow-disconnect-account" class="button">
						<?php esc_html_e( 'Disconnect', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<div class="wpshadow-services-grid">
				<?php self::render_service_card( 'guardian', $services['guardian'] ?? array() ); ?>
				<?php self::render_service_card( 'vault', $services['vault'] ?? array() ); ?>
				<?php self::render_service_card( 'cloud', $services['cloud'] ?? array() ); ?>
			</div>

			<?php self::render_upgrade_cta( $services ); ?>
		</div>
		<?php
	}

	/**
	 * Render service benefits.
	 *
	 * @since  1.6032.0000
	 * @param  array $services Service information.
	 * @return void
	 */
	private static function render_service_benefits( $services ) {
		$guardian = $services['guardian'] ?? array();
		$vault    = $services['vault'] ?? array();
		$cloud    = $services['cloud'] ?? array();
		?>
		<div class="wpshadow-card wpshadow-benefits">
			<h3><?php esc_html_e( 'What You Get (Free)', 'wpshadow' ); ?></h3>

			<div class="service-benefit">
				<div class="service-icon">🛡️</div>
				<div class="service-details">
					<h4><?php esc_html_e( 'Guardian AI Scanning', 'wpshadow' ); ?></h4>
					<ul>
						<li><?php echo esc_html( sprintf( __( '%d AI scans per month', 'wpshadow' ), $guardian['tokens_per_month'] ?? 100 ) ); ?></li>
						<li><?php esc_html_e( 'Security vulnerability detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Performance profiling', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'SEO technical audit', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="service-benefit">
				<div class="service-icon">💾</div>
				<div class="service-details">
					<h4><?php esc_html_e( 'Vault Cloud Backups', 'wpshadow' ); ?></h4>
					<ul>
						<li><?php echo esc_html( sprintf( __( '%d free backups', 'wpshadow' ), $vault['max_backups'] ?? 3 ) ); ?></li>
						<li><?php echo esc_html( sprintf( __( '%d-day retention', 'wpshadow' ), $vault['retention_days'] ?? 7 ) ); ?></li>
						<li><?php echo esc_html( sprintf( __( '%d GB storage', 'wpshadow' ), $vault['storage_limit'] ?? 1 ) ); ?></li>
						<li><?php esc_html_e( 'One-click restore', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="service-benefit">
				<div class="service-icon">☁️</div>
				<div class="service-details">
					<h4><?php esc_html_e( 'Cloud Services', 'wpshadow' ); ?></h4>
					<ul>
						<li><?php echo esc_html( sprintf( __( '%d uptime checks/month', 'wpshadow' ), $cloud['uptime_checks'] ?? 100 ) ); ?></li>
						<li><?php esc_html_e( 'SSL certificate monitoring', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Domain expiration alerts', 'wpshadow' ); ?></li>
						<li><?php echo esc_html( sprintf( __( '%d AI content analyses/month', 'wpshadow' ), $cloud['ai_scans_per_month'] ?? 50 ) ); ?></li>
					</ul>
				</div>
			</div>

			<p class="wpshadow-highlight">
				<?php esc_html_e( '🎉 All free forever. Upgrade only when you need more.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render service card.
	 *
	 * @since  1.6032.0000
	 * @param  string $service_id Service identifier.
	 * @param  array  $service_data Service data.
	 * @return void
	 */
	private static function render_service_card( $service_id, $service_data ) {
		$service_meta = self::get_service_meta( $service_id );
		$tier         = $service_data['tier'] ?? 'free';
		?>
		<div class="wpshadow-service-card <?php echo esc_attr( $service_id ); ?>">
			<div class="service-header">
				<div class="service-icon"><?php echo esc_html( $service_meta['icon'] ); ?></div>
				<h3><?php echo esc_html( $service_meta['name'] ); ?></h3>
				<span class="service-tier tier-<?php echo esc_attr( $tier ); ?>">
					<?php echo esc_html( ucfirst( $tier ) ); ?>
				</span>
			</div>
			<div class="service-usage">
				<?php
				switch ( $service_id ) {
					case 'guardian':
						$current = $service_data['tokens_current'] ?? 0;
						$max     = $service_data['tokens_per_month'] ?? 100;
						printf(
							'<div class="usage-bar"><div class="usage-fill" style="width: %d%%"></div></div>',
							min( 100, ( $current / max( $max, 1 ) ) * 100 )
						);
						printf(
							'<p class="usage-text">%s</p>',
							sprintf(
								/* translators: 1: current tokens, 2: max tokens */
								esc_html__( '%1$d / %2$d tokens remaining', 'wpshadow' ),
								$current,
								$max
							)
						);
						break;

					case 'vault':
						$used = $service_data['storage_used'] ?? 0;
						$max  = $service_data['storage_limit'] ?? 1;
						printf(
							'<div class="usage-bar"><div class="usage-fill" style="width: %d%%"></div></div>',
							min( 100, ( $used / max( $max, 1 ) ) * 100 )
						);
						printf(
							'<p class="usage-text">%s</p>',
							sprintf(
								/* translators: 1: used storage, 2: max storage */
								esc_html__( '%1$s GB / %2$s GB used', 'wpshadow' ),
								number_format( $used, 2 ),
								number_format( $max, 2 )
							)
						);
						break;

					case 'cloud':
						$checks = $service_data['uptime_checks'] ?? 100;
						printf(
							'<p class="usage-text">%s</p>',
							sprintf(
								/* translators: %d: number of checks */
								esc_html__( '%d checks remaining this month', 'wpshadow' ),
								$checks
							)
						);
						break;
				}
				?>
			</div>
			<div class="service-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-' . $service_id ) ); ?>" class="button">
					<?php esc_html_e( 'Open Dashboard', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render upgrade CTA.
	 *
	 * @since  1.6032.0000
	 * @param  array $services Service information.
	 * @return void
	 */
	private static function render_upgrade_cta( $services ) {
		// Only show if all services are on free tier.
		$all_free = true;
		foreach ( $services as $service ) {
			if ( isset( $service['tier'] ) && 'free' !== $service['tier'] ) {
				$all_free = false;
				break;
			}
		}

		if ( ! $all_free ) {
			return;
		}
		?>
		<div class="wpshadow-upgrade-cta">
			<h3><?php esc_html_e( 'Need More? Upgrade When Ready', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'No pressure. Your free tier is generous. But if you need more, we\'re here.', 'wpshadow' ); ?></p>
			<div class="upgrade-options">
				<div class="upgrade-option">
					<h4><?php esc_html_e( 'Guardian Pro', 'wpshadow' ); ?></h4>
					<p class="price">$19/month</p>
					<ul>
						<li><?php esc_html_e( 'Unlimited AI scans', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Priority support', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Email notifications', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div class="upgrade-option">
					<h4><?php esc_html_e( 'Vault Pro', 'wpshadow' ); ?></h4>
					<p class="price">$9/month</p>
					<ul>
						<li><?php esc_html_e( 'Unlimited backups', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( '30-day retention', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( '10 GB storage', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div class="upgrade-option">
					<h4><?php esc_html_e( 'Cloud Pro', 'wpshadow' ); ?></h4>
					<p class="price">$14/month</p>
					<ul>
						<li><?php esc_html_e( 'Unlimited monitoring', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Advanced AI services', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Email alerts', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>
			<p class="upgrade-cta-footer">
				<a href="https://account.wpshadow.com/upgrade" class="button button-primary" target="_blank">
					<?php esc_html_e( 'Compare Plans', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Get service metadata.
	 *
	 * @since  1.6032.0000
	 * @param  string $service_id Service identifier.
	 * @return array Service metadata.
	 */
	private static function get_service_meta( $service_id ) {
		$services = array(
			'guardian' => array(
				'name' => __( 'Guardian', 'wpshadow' ),
				'icon' => '🛡️',
			),
			'vault'    => array(
				'name' => __( 'Vault', 'wpshadow' ),
				'icon' => '💾',
			),
			'cloud'    => array(
				'name' => __( 'Cloud Services', 'wpshadow' ),
				'icon' => '☁️',
			),
		);

		return $services[ $service_id ] ?? array(
			'name' => ucfirst( $service_id ),
			'icon' => '📦',
		);
	}
}

// Initialize page.
Account_Registration_Page::init();
