<?php
/**
 * Security API Integrations Settings Page
 *
 * Provides centralized management for all third-party security API keys
 * (WPScan, Have I Been Pwned, AbuseIPDB, Google Safe Browsing, PhishTank)
 *
 * @package    WPShadow
 * @subpackage Admin\Pages
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages\SecurityAPI;

use WPShadow\Core\Settings_Registry;
use WPShadow\Admin\Pages\Settings_Page_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security API Settings Page Class
 *
 * Manages API key configuration, encryption, and connection testing for
 * free security database integrations.
 *
 * @since 1.6093.1200
 */
class Security_API_Settings_Page extends Settings_Page_Base {

	/**
	 * Page slug
	 *
	 * @var string
	 */
	protected $page_slug = 'wpshadow-security-api';

	/**
	 * Menu parent
	 *
	 * @var string
	 */
	protected $menu_parent = 'wpshadow-settings';

	/**
	 * Page title
	 *
	 * @var string
	 */
	protected $page_title = 'Security API Integrations';

	/**
	 * Initialize the page
	 *
	 * @since 1.6093.1200
	 */
	public function __construct() {
		parent::__construct();
		
		// Register AJAX handlers for connection testing
		add_action( 'wp_ajax_wpshadow_test_api_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_wpshadow_save_api_keys', array( $this, 'ajax_save_api_keys' ) );
	}

	/**
	 * Render the page
	 *
	 * @since 1.6093.1200
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to access this page.', 'wpshadow' ) );
		}

		wp_enqueue_style(
			'wpshadow-security-api-settings-page',
			WPSHADOW_URL . 'assets/css/security-api-settings-page.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-security-api-settings-page',
			WPSHADOW_URL . 'assets/js/security-api-settings-page.js',
			array(),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-security-api-settings-page',
			'wpshadowSecurityApiSettings',
			array(
				'testNonce' => wp_create_nonce( 'wpshadow_test_api_connection' ),
				'saveNonce' => wp_create_nonce( 'wpshadow_save_api_settings' ),
				'strings'   => array(
					'testing'             => __( 'Testing...', 'wpshadow' ),
					'connected_success'   => __( 'Connected successfully', 'wpshadow' ),
					'connection_failed'   => __( 'Connection failed', 'wpshadow' ),
				),
			)
		);
		?>
		<div class="wrap wpshadow-security-api-page">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>

			<div class="wpshadow-api-intro">
				<p>
					<?php
					esc_html_e(
						'Connect to free security databases to protect your site from vulnerabilities, malware, and data breaches. All integrations are optional and can be disabled anytime.',
						'wpshadow'
					);
					?>
				</p>
				<p class="description">
					<?php
					esc_html_e(
						'Think of this like a security toolkit—each service is a free tool that helps protect your site in different ways.',
						'wpshadow'
					);
					?>
				</p>
			</div>

			<form method="post" class="wpshadow-api-form" id="wpshadow-api-settings-form">
				<?php wp_nonce_field( 'wpshadow_save_api_settings', 'wpshadow_api_nonce' ); ?>

				<!-- WPScan Vulnerability Database -->
				<div class="wpshadow-api-service wpshadow-api-wpscan">
					<div class="wpshadow-api-header">
						<h2>
							<span class="wpshadow-api-number">1️⃣</span>
							<?php esc_html_e( 'Plugin Vulnerability Scanning', 'wpshadow' ); ?>
							<span class="wpshadow-badge wpshadow-badge-recommended">
								<?php esc_html_e( 'Recommended', 'wpshadow' ); ?>
							</span>
						</h2>
						<p class="wpshadow-service-description">
							<?php
							esc_html_e(
								'Check plugins for known security issues daily. Like having a security expert review your plugins.',
								'wpshadow'
							);
							?>
						</p>
					</div>

					<div class="wpshadow-api-body">
						<table class="form-table wpshadow-api-form-table">
							<tr>
								<th scope="row">
									<label for="wpshadow_wpscan_enabled">
										<?php esc_html_e( 'Enable WPScan Scanning', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<label class="wpshadow-toggle">
										<input 
											type="checkbox" 
											id="wpshadow_wpscan_enabled" 
											name="wpshadow_wpscan_enabled" 
											value="1"
											<?php checked( get_option( 'wpshadow_wpscan_enabled' ), 1 ); ?>
										/>
										<span class="wpshadow-toggle-slider"></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable to check all plugins for known vulnerabilities', 'wpshadow' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpshadow_wpscan_api_key">
										<?php esc_html_e( 'API Key', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="password" 
										id="wpshadow_wpscan_api_key" 
										name="wpshadow_wpscan_api_key" 
										class="wpshadow-api-key-input"
										placeholder="<?php esc_attr_e( 'Paste your WPScan API key here', 'wpshadow' ); ?>"
									/>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to WPScan registration */
											esc_html__( 'Don\'t have a key? %s', 'wpshadow' ),
											'<a href="https://wpscan.com/register" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get a free WPScan API key →', 'wpshadow' ) . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<button 
										type="button" 
										class="button wpshadow-test-connection" 
										data-service="wpscan"
										<?php disabled( get_option( 'wpshadow_wpscan_enabled' ), 0 ); ?>
									>
										<?php esc_html_e( 'Test Connection', 'wpshadow' ); ?>
									</button>
									<a href="<?php echo esc_url( 'https://wpshadow.com/kb/wpscan-setup' ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'Setup Guide', 'wpshadow' ); ?>
									</a>
									<span class="wpshadow-test-status" id="wpscan-status"></span>
								</td>
							</tr>
						</table>

						<div class="wpshadow-api-details">
							<p>
								<strong><?php esc_html_e( '💰 Cost:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'FREE forever (25 checks/day)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '📊 What\'s sent:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Plugin names only (public info)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '🔒 Privacy:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'No personal data transmitted', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Have I Been Pwned -->
				<div class="wpshadow-api-service wpshadow-api-hibp">
					<div class="wpshadow-api-header">
						<h2>
							<span class="wpshadow-api-number">2️⃣</span>
							<?php esc_html_e( 'Email Breach Detection', 'wpshadow' ); ?>
							<span class="wpshadow-badge wpshadow-badge-privacy-sensitive">
								<?php esc_html_e( 'Privacy-Sensitive', 'wpshadow' ); ?>
							</span>
						</h2>
						<p class="wpshadow-service-description">
							<?php
							esc_html_e(
								'Check if admin emails were exposed in data breaches. Know if your account has been compromised.',
								'wpshadow'
							);
							?>
						</p>
					</div>

					<div class="wpshadow-api-body">
						<div class="wpshadow-privacy-notice">
							<strong>⚠️  <?php esc_html_e( 'Privacy Notice:', 'wpshadow' ); ?></strong>
							<p>
								<?php
								esc_html_e(
									'This sends admin email addresses to HaveIBeenPwned.com to check their breach database. No passwords are sent. This service is disabled by default because it involves sharing email addresses.',
									'wpshadow'
								);
								?>
							</p>
						</div>

						<table class="form-table wpshadow-api-form-table">
							<tr>
								<th scope="row">
									<label for="wpshadow_hibp_enabled">
										<?php esc_html_e( 'Enable Breach Detection', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<label class="wpshadow-toggle">
										<input 
											type="checkbox" 
											id="wpshadow_hibp_enabled" 
											name="wpshadow_hibp_enabled" 
											value="1"
											<?php checked( get_option( 'wpshadow_hibp_enabled' ), 1 ); ?>
										/>
										<span class="wpshadow-toggle-slider"></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable to check admin emails against known data breaches', 'wpshadow' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<button 
										type="button" 
										class="button wpshadow-test-connection" 
										data-service="hibp"
										<?php disabled( get_option( 'wpshadow_hibp_enabled' ), 0 ); ?>
									>
										<?php esc_html_e( 'Test Connection', 'wpshadow' ); ?>
									</button>
									<a href="<?php echo esc_url( 'https://wpshadow.com/kb/hibp-privacy' ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'Understand Privacy Impact', 'wpshadow' ); ?>
									</a>
									<span class="wpshadow-test-status" id="hibp-status"></span>
								</td>
							</tr>
						</table>

						<div class="wpshadow-api-details">
							<p>
								<strong><?php esc_html_e( '💰 Cost:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'FREE forever (rate limited)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '📊 What\'s sent:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Admin email addresses only', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '🔒 Privacy:', 'wpshadow' ); ?></strong>
								<a href="https://haveibeenpwned.com/Privacy" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'View HIBP Privacy Policy →', 'wpshadow' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>

				<!-- AbuseIPDB -->
				<div class="wpshadow-api-service wpshadow-api-abuseipdb">
					<div class="wpshadow-api-header">
						<h2>
							<span class="wpshadow-api-number">3️⃣</span>
							<?php esc_html_e( 'IP Reputation Monitoring', 'wpshadow' ); ?>
						</h2>
						<p class="wpshadow-service-description">
							<?php
							esc_html_e(
								'Check if your server IP is on security blacklists. Helps detect if your site is being used for attacks.',
								'wpshadow'
							);
							?>
						</p>
					</div>

					<div class="wpshadow-api-body">
						<table class="form-table wpshadow-api-form-table">
							<tr>
								<th scope="row">
									<label for="wpshadow_abuseipdb_enabled">
										<?php esc_html_e( 'Enable IP Reputation Checks', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<label class="wpshadow-toggle">
										<input 
											type="checkbox" 
											id="wpshadow_abuseipdb_enabled" 
											name="wpshadow_abuseipdb_enabled" 
											value="1"
											<?php checked( get_option( 'wpshadow_abuseipdb_enabled' ), 1 ); ?>
										/>
										<span class="wpshadow-toggle-slider"></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable to check your server IP against abuse databases', 'wpshadow' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpshadow_abuseipdb_api_key">
										<?php esc_html_e( 'API Key', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="password" 
										id="wpshadow_abuseipdb_api_key" 
										name="wpshadow_abuseipdb_api_key" 
										class="wpshadow-api-key-input"
										placeholder="<?php esc_attr_e( 'Paste your AbuseIPDB API key here', 'wpshadow' ); ?>"
									/>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to AbuseIPDB registration */
											esc_html__( 'Don\'t have a key? %s', 'wpshadow' ),
											'<a href="https://www.abuseipdb.com/register" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get a free AbuseIPDB API key →', 'wpshadow' ) . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<button 
										type="button" 
										class="button wpshadow-test-connection" 
										data-service="abuseipdb"
										<?php disabled( get_option( 'wpshadow_abuseipdb_enabled' ), 0 ); ?>
									>
										<?php esc_html_e( 'Test Connection', 'wpshadow' ); ?>
									</button>
									<a href="<?php echo esc_url( 'https://wpshadow.com/kb/abuseipdb-setup' ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'Setup Guide', 'wpshadow' ); ?>
									</a>
									<span class="wpshadow-test-status" id="abuseipdb-status"></span>
								</td>
							</tr>
						</table>

						<div class="wpshadow-api-details">
							<p>
								<strong><?php esc_html_e( '💰 Cost:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'FREE forever (1,000 checks/day)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '📊 What\'s sent:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Server IP address only', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '🔒 Privacy:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'No personal data transmitted', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Google Safe Browsing -->
				<div class="wpshadow-api-service wpshadow-api-gsb">
					<div class="wpshadow-api-header">
						<h2>
							<span class="wpshadow-api-number">4️⃣</span>
							<?php esc_html_e( 'External Link Safety', 'wpshadow' ); ?>
						</h2>
						<p class="wpshadow-service-description">
							<?php
							esc_html_e(
								'Check external links in your content for phishing and malware. Protects your readers.',
								'wpshadow'
							);
							?>
						</p>
					</div>

					<div class="wpshadow-api-body">
						<table class="form-table wpshadow-api-form-table">
							<tr>
								<th scope="row">
									<label for="wpshadow_gsb_enabled">
										<?php esc_html_e( 'Enable Link Safety Checks', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<label class="wpshadow-toggle">
										<input 
											type="checkbox" 
											id="wpshadow_gsb_enabled" 
											name="wpshadow_gsb_enabled" 
											value="1"
											<?php checked( get_option( 'wpshadow_gsb_enabled' ), 1 ); ?>
										/>
										<span class="wpshadow-toggle-slider"></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable to check external links for phishing and malware', 'wpshadow' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpshadow_gsb_api_key">
										<?php esc_html_e( 'API Key', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="password" 
										id="wpshadow_gsb_api_key" 
										name="wpshadow_gsb_api_key" 
										class="wpshadow-api-key-input"
										placeholder="<?php esc_attr_e( 'Paste your Google Safe Browsing API key here', 'wpshadow' ); ?>"
									/>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to Google Cloud Console */
											esc_html__( 'Don\'t have a key? %s', 'wpshadow' ),
											'<a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get a free Google Safe Browsing API key →', 'wpshadow' ) . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<button 
										type="button" 
										class="button wpshadow-test-connection" 
										data-service="gsb"
										<?php disabled( get_option( 'wpshadow_gsb_enabled' ), 0 ); ?>
									>
										<?php esc_html_e( 'Test Connection', 'wpshadow' ); ?>
									</button>
									<a href="<?php echo esc_url( 'https://wpshadow.com/kb/google-safe-browsing-setup' ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'Setup Guide', 'wpshadow' ); ?>
									</a>
									<span class="wpshadow-test-status" id="gsb-status"></span>
								</td>
							</tr>
						</table>

						<div class="wpshadow-api-details">
							<p>
								<strong><?php esc_html_e( '💰 Cost:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'FREE forever (10,000 checks/day)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '📊 What\'s sent:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'URLs from your content', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '🔒 Privacy:', 'wpshadow' ); ?></strong>
								<a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Google\'s privacy policy applies', 'wpshadow' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>

				<!-- PhishTank -->
				<div class="wpshadow-api-service wpshadow-api-phishtank">
					<div class="wpshadow-api-header">
						<h2>
							<span class="wpshadow-api-number">5️⃣</span>
							<?php esc_html_e( 'Phishing Detection', 'wpshadow' ); ?>
						</h2>
						<p class="wpshadow-service-description">
							<?php
							esc_html_e(
								'Community-verified phishing URL detection. Protects against phishing schemes.',
								'wpshadow'
							);
							?>
						</p>
					</div>

					<div class="wpshadow-api-body">
						<table class="form-table wpshadow-api-form-table">
							<tr>
								<th scope="row">
									<label for="wpshadow_phishtank_enabled">
										<?php esc_html_e( 'Enable PhishTank Checks', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<label class="wpshadow-toggle">
										<input 
											type="checkbox" 
											id="wpshadow_phishtank_enabled" 
											name="wpshadow_phishtank_enabled" 
											value="1"
											<?php checked( get_option( 'wpshadow_phishtank_enabled' ), 1 ); ?>
										/>
										<span class="wpshadow-toggle-slider"></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable to check links against known phishing URLs', 'wpshadow' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpshadow_phishtank_api_key">
										<?php esc_html_e( 'API Key', 'wpshadow' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="password" 
										id="wpshadow_phishtank_api_key" 
										name="wpshadow_phishtank_api_key" 
										class="wpshadow-api-key-input"
										placeholder="<?php esc_attr_e( 'Paste your PhishTank API key here', 'wpshadow' ); ?>"
									/>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to PhishTank registration */
											esc_html__( 'Don\'t have a key? %s', 'wpshadow' ),
											'<a href="https://phishtank.org/register.php" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get a free PhishTank API key →', 'wpshadow' ) . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<button 
										type="button" 
										class="button wpshadow-test-connection" 
										data-service="phishtank"
										<?php disabled( get_option( 'wpshadow_phishtank_enabled' ), 0 ); ?>
									>
										<?php esc_html_e( 'Test Connection', 'wpshadow' ); ?>
									</button>
									<a href="<?php echo esc_url( 'https://wpshadow.com/kb/phishtank-setup' ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'Setup Guide', 'wpshadow' ); ?>
									</a>
									<span class="wpshadow-test-status" id="phishtank-status"></span>
								</td>
							</tr>
						</table>

						<div class="wpshadow-api-details">
							<p>
								<strong><?php esc_html_e( '💰 Cost:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'FREE forever (unlimited)', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '📊 What\'s sent:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'URLs for validation', 'wpshadow' ); ?>
							</p>
							<p>
								<strong><?php esc_html_e( '🔒 Privacy:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'No personal data transmitted', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Form Actions -->
				<div class="wpshadow-api-actions">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save All Settings', 'wpshadow' ); ?>
					</button>
					<button type="reset" class="button">
						<?php esc_html_e( 'Reset to Defaults', 'wpshadow' ); ?>
					</button>
				</div>
			</form>
		</div>

		<?php
	}

	/**
	 * Test API connection
	 *
	 * @since 1.6093.1200
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'wpshadow_test_api_connection', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$service = isset( $_POST['service'] ) ? sanitize_key( $_POST['service'] ) : '';

		if ( empty( $service ) ) {
			wp_send_json_error( array( 'message' => __( 'Service not specified', 'wpshadow' ) ) );
		}

		// Test connection based on service
		$result = $this->test_service_connection( $service );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Save API keys
	 *
	 * @since 1.6093.1200
	 */
	public function ajax_save_api_keys() {
		check_ajax_referer( 'wpshadow_save_api_settings', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		// Process each API key
		$services = array( 'wpscan', 'hibp', 'abuseipdb', 'gsb', 'phishtank' );

		foreach ( $services as $service ) {
			$enabled_key = "wpshadow_{$service}_enabled";
			$api_key_key = "wpshadow_{$service}_api_key";

			// Save enabled status
			if ( isset( $_POST[ $enabled_key ] ) ) {
				update_option( $enabled_key, 1 );
			} else {
				update_option( $enabled_key, 0 );
			}

			// Save API key if provided
			if ( isset( $_POST[ $api_key_key ] ) && ! empty( $_POST[ $api_key_key ] ) ) {
				$api_key = sanitize_text_field( wp_unslash( $_POST[ $api_key_key ] ) );
				$this->save_encrypted_api_key( $service, $api_key );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Settings saved', 'wpshadow' ) ) );
	}

	/**
	 * Test service connection
	 *
	 * @param string $service Service name.
	 * @return array Test result.
	 */
	private function test_service_connection( $service ) {
		if ( ! \WPShadow\Core\External_Request_Guard::is_allowed( 'security_api_test' ) ) {
			return array(
				'success' => false,
				'message' => \WPShadow\Core\External_Request_Guard::get_denied_message( __( 'Security API tests', 'wpshadow' ) ),
			);
		}

		switch ( $service ) {
			case 'wpscan':
				return $this->test_wpscan_connection();
			case 'hibp':
				return $this->test_hibp_connection();
			case 'abuseipdb':
				return $this->test_abuseipdb_connection();
			case 'gsb':
				return $this->test_gsb_connection();
			case 'phishtank':
				return $this->test_phishtank_connection();
			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown service', 'wpshadow' ),
				);
		}
	}

	/**
	 * Test WPScan connection
	 *
	 * @return array Test result.
	 */
	private function test_wpscan_connection() {
		$api_key = get_option( 'wpshadow_wpscan_api_key', '' );

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured', 'wpshadow' ),
			);
		}

		// Test by checking a well-known plugin (Hello Dolly)
		$response = wp_remote_get(
			'https://wpscan.com/api/v3/plugins/hello-dolly?token=' . urlencode( $api_key ),
			array( 'timeout' => 5 )
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Network error - check your internet connection', 'wpshadow' ),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code || 404 === $status_code ) {
			return array(
				'success' => true,
				'message' => __( 'Connected successfully', 'wpshadow' ),
			);
		} elseif ( 403 === $status_code ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API key', 'wpshadow' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API error: %d', 'wpshadow' ), $status_code ),
			);
		}
	}

	/**
	 * Test Have I Been Pwned connection
	 *
	 * @return array Test result.
	 */
	private function test_hibp_connection() {
		// HIBP doesn't require an API key, just verify we can reach their API
		$response = wp_remote_get(
			'https://haveibeenpwned.com/api/v3/breaches',
			array(
				'timeout' => 5,
				'headers' => array(
					'User-Agent' => 'WPShadow',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Network error - check your internet connection', 'wpshadow' ),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code ) {
			return array(
				'success' => true,
				'message' => __( 'Connected successfully', 'wpshadow' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API error: %d', 'wpshadow' ), $status_code ),
			);
		}
	}

	/**
	 * Test AbuseIPDB connection
	 *
	 * @return array Test result.
	 */
	private function test_abuseipdb_connection() {
		$api_key = get_option( 'wpshadow_abuseipdb_api_key', '' );

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured', 'wpshadow' ),
			);
		}

		$response = wp_remote_post(
			'https://api.abuseipdb.com/api/v2/check',
			array(
				'timeout' => 5,
				'headers' => array(
					'Key'        => $api_key,
					'Accept'     => 'application/json',
				),
				'body' => array(
					'ipAddress' => '127.0.0.1',
					'maxAgeInDays' => 90,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Network error - check your internet connection', 'wpshadow' ),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code ) {
			return array(
				'success' => true,
				'message' => __( 'Connected successfully', 'wpshadow' ),
			);
		} elseif ( 401 === $status_code ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API key', 'wpshadow' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API error: %d', 'wpshadow' ), $status_code ),
			);
		}
	}

	/**
	 * Test Google Safe Browsing connection
	 *
	 * @return array Test result.
	 */
	private function test_gsb_connection() {
		$api_key = get_option( 'wpshadow_gsb_api_key', '' );

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured', 'wpshadow' ),
			);
		}

		$response = wp_remote_post(
			'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . urlencode( $api_key ),
			array(
				'timeout' => 5,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( array(
					'client' => array(
						'clientId' => 'wpshadow',
						'clientVersion' => '1.0.0',
					),
					'threatInfo' => array(
						'threatTypes' => array( 'MALWARE' ),
						'platformTypes' => array( 'ANY_PLATFORM' ),
						'threatEntryTypes' => array( 'URL' ),
						'threatEntries' => array(
							array( 'url' => 'http://localhost' ),
						),
					),
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Network error - check your internet connection', 'wpshadow' ),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code ) {
			return array(
				'success' => true,
				'message' => __( 'Connected successfully', 'wpshadow' ),
			);
		} elseif ( 400 === $status_code ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API key', 'wpshadow' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API error: %d', 'wpshadow' ), $status_code ),
			);
		}
	}

	/**
	 * Test PhishTank connection
	 *
	 * @return array Test result.
	 */
	private function test_phishtank_connection() {
		$api_key = get_option( 'wpshadow_phishtank_api_key', '' );

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured', 'wpshadow' ),
			);
		}

		$response = wp_remote_post(
			'https://checkurl.phishtank.com/checkurl/',
			array(
				'timeout' => 5,
				'body' => array(
					'url' => 'http://localhost',
					'app_token' => $api_key,
					'format' => 'json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Network error - check your internet connection', 'wpshadow' ),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code ) {
			return array(
				'success' => true,
				'message' => __( 'Connected successfully', 'wpshadow' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API error: %d', 'wpshadow' ), $status_code ),
			);
		}
	}

	/**
	 * Save encrypted API key
	 *
	 * @param string $service Service name.
	 * @param string $api_key API key.
	 */
	private function save_encrypted_api_key( $service, $api_key ) {
		// Use WordPress salts for encryption
		$encrypted = openssl_encrypt(
			$api_key,
			'AES-256-CBC',
			wp_salt( 'auth' ),
			0,
			substr( wp_salt( 'secure_auth' ), 0, 16 )
		);

		update_option( "wpshadow_{$service}_api_key", $encrypted, false );
	}

	/**
	 * Get decrypted API key
	 *
	 * @param string $service Service name.
	 * @return string Decrypted API key or empty string.
	 */
	public static function get_api_key( $service ) {
		$encrypted = get_option( "wpshadow_{$service}_api_key", '' );

		if ( empty( $encrypted ) ) {
			return '';
		}

		$decrypted = openssl_decrypt(
			$encrypted,
			'AES-256-CBC',
			wp_salt( 'auth' ),
			0,
			substr( wp_salt( 'secure_auth' ), 0, 16 )
		);

		return $decrypted ? $decrypted : '';
	}

	/**
	 * Check if service is enabled
	 *
	 * @param string $service Service name.
	 * @return bool True if enabled.
	 */
	public static function is_service_enabled( $service ) {
		return (bool) get_option( "wpshadow_{$service}_enabled", false );
	}
}
