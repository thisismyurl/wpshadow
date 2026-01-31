<?php
/**
 * Guardian Scan Interface
 *
 * UI for initiating and managing Guardian AI scans.
 * Phase 7: Guardian Launch - Scan Management
 *
 * @package    WPShadow
 * @subpackage Guardian
 * @since      1.2604.0300
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian Scan Interface Class
 *
 * Provides UI and AJAX handlers for Guardian scanning.
 *
 * @since 1.2604.0300
 */
class Guardian_Scan_Interface extends AJAX_Handler_Base {

	/**
	 * Initialize the interface.
	 *
	 * @since 1.2604.0300
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_guardian_scan', array( __CLASS__, 'handle_scan_request' ) );
		add_action( 'wp_ajax_wpshadow_guardian_check_scan', array( __CLASS__, 'handle_check_scan' ) );
	}

	/**
	 * Add Guardian menu page.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	public static function add_menu_page() {
		add_submenu_page(
			'wpshadow',
			__( 'WPShadow Guardian', 'wpshadow' ),
			'<span class="dashicons dashicons-cloud" style="font-size: 17px; vertical-align: middle;"></span> ' . __( 'Guardian', 'wpshadow' ),
			'manage_options',
			'wpshadow-guardian',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue page assets.
	 *
	 * @since  1.2604.0300
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-guardian' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-guardian-scan',
			WPSHADOW_URL . 'assets/js/guardian-scan-interface.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-guardian-scan',
			'wpShadowGuardian',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_guardian' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'strings' => array(
					'scanning'       => __( 'Scanning...', 'wpshadow' ),
					'complete'       => __( 'Scan Complete!', 'wpshadow' ),
					'error'          => __( 'Scan Failed', 'wpshadow' ),
					'checkingStatus' => __( 'Checking scan status...', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render Guardian page.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	public static function render_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';

		?>
		<div class="wrap wpshadow-guardian-page">
			<h1>
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'WPShadow Guardian', 'wpshadow' ); ?>
			</h1>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=dashboard' ) ); ?>"
				   class="nav-tab <?php echo 'dashboard' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Dashboard', 'wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=scan' ) ); ?>"
				   class="nav-tab <?php echo 'scan' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Run Scan', 'wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=history' ) ); ?>"
				   class="nav-tab <?php echo 'history' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'History', 'wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=pricing' ) ); ?>"
				   class="nav-tab <?php echo 'pricing' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Pricing', 'wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=account' ) ); ?>"
				   class="nav-tab <?php echo 'account' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Account', 'wpshadow' ); ?>
				</a>
			</nav>

			<div class="wpshadow-guardian-content">
				<?php
				switch ( $tab ) {
					case 'scan':
						self::render_scan_tab();
						break;
					case 'history':
						self::render_history_tab();
						break;
					case 'pricing':
						self::render_pricing_tab();
						break;
					case 'account':
						self::render_account_tab();
						break;
					case 'dashboard':
					default:
						self::render_dashboard_tab();
						break;
				}
				?>
			</div>
		</div>
		<?php

		self::output_styles();
	}

	/**
	 * Render dashboard tab.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_dashboard_tab() {
		if ( ! Guardian_API_Client::is_connected() ) {
			self::render_connect_prompt();
			return;
		}

		$balance = Guardian_API_Client::get_token_balance();
		$account = Guardian_API_Client::get_account_info();
		$scans   = Guardian_API_Client::get_recent_scans( 5 );

		?>
		<div class="guardian-dashboard">
			<div class="dashboard-cards">
				<div class="dashboard-card balance-card">
					<h3><?php esc_html_e( 'Token Balance', 'wpshadow' ); ?></h3>
					<div class="card-value">
						<?php
						if ( is_wp_error( $balance ) ) {
							echo '<span class="error">' . esc_html( $balance->get_error_message() ) . '</span>';
						} elseif ( 'unlimited' === $balance || ( is_array( $account ) && isset( $account['subscription'] ) && 'pro' === $account['subscription'] ) ) {
							echo '<span class="unlimited">' . esc_html__( 'Unlimited', 'wpshadow' ) . '</span>';
						} else {
							echo '<span class="number">' . number_format_i18n( $balance ) . '</span>';
						}
						?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=pricing' ) ); ?>" class="button">
						<?php esc_html_e( 'Get More Tokens', 'wpshadow' ); ?>
					</a>
				</div>

				<div class="dashboard-card scans-card">
					<h3><?php esc_html_e( 'Total Scans', 'wpshadow' ); ?></h3>
					<div class="card-value">
						<span class="number"><?php echo count( Guardian_API_Client::get_recent_scans( 999 ) ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=scan' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Run New Scan', 'wpshadow' ); ?>
					</a>
				</div>

				<div class="dashboard-card account-card">
					<h3><?php esc_html_e( 'Account Status', 'wpshadow' ); ?></h3>
					<div class="card-value">
						<?php if ( is_array( $account ) && isset( $account['subscription'] ) && 'pro' === $account['subscription'] ) : ?>
							<span class="status-badge pro"><?php esc_html_e( 'Guardian Pro', 'wpshadow' ); ?></span>
						<?php else : ?>
							<span class="status-badge free"><?php esc_html_e( 'Free Tier', 'wpshadow' ); ?></span>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=account' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage Account', 'wpshadow' ); ?>
					</a>
				</div>
			</div>

			<?php if ( ! empty( $scans ) ) : ?>
				<div class="recent-scans">
					<h2><?php esc_html_e( 'Recent Scans', 'wpshadow' ); ?></h2>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Type', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $scans as $scan ) : ?>
								<tr>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $scan['requested'] ) ) ); ?></td>
									<td><?php echo esc_html( ucfirst( $scan['scan_type'] ) ); ?></td>
									<td><span class="status-badge status-<?php echo esc_attr( $scan['status'] ); ?>"><?php echo esc_html( ucfirst( $scan['status'] ) ); ?></span></td>
									<td>
										<?php if ( 'complete' === $scan['status'] ) : ?>
											<a href="#" class="view-results" data-scan-id="<?php echo esc_attr( $scan['scan_id'] ); ?>">
												<?php esc_html_e( 'View Results', 'wpshadow' ); ?>
											</a>
										<?php elseif ( 'pending' === $scan['status'] ) : ?>
											<button class="button-link check-status" data-scan-id="<?php echo esc_attr( $scan['scan_id'] ); ?>">
												<?php esc_html_e( 'Check Status', 'wpshadow' ); ?>
											</button>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render scan tab.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_scan_tab() {
		if ( ! Guardian_API_Client::is_connected() ) {
			self::render_connect_prompt();
			return;
		}

		?>
		<div class="guardian-scan-interface">
			<h2><?php esc_html_e( 'AI-Powered Site Scanning', 'wpshadow' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Guardian uses advanced AI to analyze your site for security issues, performance problems, and optimization opportunities.', 'wpshadow' ); ?>
			</p>

			<div class="scan-types">
				<div class="scan-type-card" data-scan-type="security">
					<div class="scan-type-icon">
						<span class="dashicons dashicons-shield-alt"></span>
					</div>
					<h3><?php esc_html_e( 'Security Scan', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Detect vulnerabilities, malware, and security misconfigurations.', 'wpshadow' ); ?></p>
					<ul class="scan-features">
						<li><?php esc_html_e( 'Vulnerability detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Malware scanning', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Configuration audit', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'File integrity check', 'wpshadow' ); ?></li>
					</ul>
					<button class="button button-primary run-scan" data-scan-type="security">
						<?php esc_html_e( 'Run Security Scan', 'wpshadow' ); ?>
					</button>
					<span class="token-cost">1 token</span>
				</div>

				<div class="scan-type-card" data-scan-type="performance">
					<div class="scan-type-icon">
						<span class="dashicons dashicons-performance"></span>
					</div>
					<h3><?php esc_html_e( 'Performance Scan', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Analyze speed, efficiency, and resource usage.', 'wpshadow' ); ?></p>
					<ul class="scan-features">
						<li><?php esc_html_e( 'Page speed analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Database optimization', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Caching recommendations', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Resource bottlenecks', 'wpshadow' ); ?></li>
					</ul>
					<button class="button button-primary run-scan" data-scan-type="performance">
						<?php esc_html_e( 'Run Performance Scan', 'wpshadow' ); ?>
					</button>
					<span class="token-cost">1 token</span>
				</div>

				<div class="scan-type-card" data-scan-type="seo">
					<div class="scan-type-icon">
						<span class="dashicons dashicons-search"></span>
					</div>
					<h3><?php esc_html_e( 'SEO Scan', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Optimize your site for search engines.', 'wpshadow' ); ?></p>
					<ul class="scan-features">
						<li><?php esc_html_e( 'Meta tags analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Content optimization', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Structured data check', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Mobile-friendliness', 'wpshadow' ); ?></li>
					</ul>
					<button class="button button-primary run-scan" data-scan-type="seo">
						<?php esc_html_e( 'Run SEO Scan', 'wpshadow' ); ?>
					</button>
					<span class="token-cost">1 token</span>
				</div>

				<div class="scan-type-card featured" data-scan-type="full">
					<div class="scan-type-badge"><?php esc_html_e( 'Recommended', 'wpshadow' ); ?></div>
					<div class="scan-type-icon">
						<span class="dashicons dashicons-admin-multisite"></span>
					</div>
					<h3><?php esc_html_e( 'Full Site Scan', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Comprehensive analysis of security, performance, and SEO.', 'wpshadow' ); ?></p>
					<ul class="scan-features">
						<li><?php esc_html_e( 'All security checks', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'All performance checks', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'All SEO checks', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Priority recommendations', 'wpshadow' ); ?></li>
					</ul>
					<button class="button button-primary run-scan" data-scan-type="full">
						<?php esc_html_e( 'Run Full Scan', 'wpshadow' ); ?>
					</button>
					<span class="token-cost">3 tokens</span>
				</div>
			</div>

			<div id="scan-progress" class="scan-progress" style="display: none;">
				<div class="progress-header">
					<h3><?php esc_html_e( 'Scanning in Progress...', 'wpshadow' ); ?></h3>
					<span class="spinner is-active"></span>
				</div>
				<div class="progress-bar">
					<div class="progress-fill"></div>
				</div>
				<p class="progress-message"><?php esc_html_e( 'Analyzing your site...', 'wpshadow' ); ?></p>
			</div>

			<div id="scan-results" class="scan-results" style="display: none;">
				<!-- Results populated by JavaScript -->
			</div>
		</div>
		<?php
	}

	/**
	 * Render history tab.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_history_tab() {
		$scans = Guardian_API_Client::get_recent_scans( 50 );

		?>
		<div class="guardian-history">
			<h2><?php esc_html_e( 'Scan History', 'wpshadow' ); ?></h2>

			<?php if ( empty( $scans ) ) : ?>
				<div class="no-scans">
					<p><?php esc_html_e( 'No scans yet. Run your first scan to get started!', 'wpshadow' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=scan' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Run Your First Scan', 'wpshadow' ); ?>
					</a>
				</div>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Type', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $scans as $scan ) : ?>
							<tr>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $scan['requested'] ) ) ); ?></td>
								<td><?php echo esc_html( ucfirst( $scan['scan_type'] ) ); ?></td>
								<td><span class="status-badge status-<?php echo esc_attr( $scan['status'] ); ?>"><?php echo esc_html( ucfirst( $scan['status'] ) ); ?></span></td>
								<td><?php echo isset( $scan['issues_found'] ) ? absint( $scan['issues_found'] ) : '—'; ?></td>
								<td>
									<?php if ( 'complete' === $scan['status'] ) : ?>
										<a href="#" class="view-results" data-scan-id="<?php echo esc_attr( $scan['scan_id'] ); ?>">
											<?php esc_html_e( 'View Results', 'wpshadow' ); ?>
										</a>
									<?php elseif ( 'pending' === $scan['status'] ) : ?>
										<button class="button-link check-status" data-scan-id="<?php echo esc_attr( $scan['scan_id'] ); ?>">
											<?php esc_html_e( 'Check Status', 'wpshadow' ); ?>
										</button>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render pricing tab.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_pricing_tab() {
		$pricing = Guardian_API_Client::get_pricing();
		$balance = Guardian_API_Client::is_connected() ? Guardian_API_Client::get_token_balance() : 0;

		?>
		<div class="guardian-pricing">
			<h2><?php esc_html_e( 'Guardian Pricing', 'wpshadow' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Choose the plan that works best for you. Tokens never expire.', 'wpshadow' ); ?>
			</p>

			<div class="pricing-cards">
				<?php foreach ( $pricing as $tier_id => $tier ) : ?>
					<div class="pricing-card <?php echo esc_attr( $tier_id ); ?>">
						<?php if ( 'unlimited' === $tier_id ) : ?>
							<div class="pricing-badge"><?php esc_html_e( 'Most Popular', 'wpshadow' ); ?></div>
						<?php endif; ?>
						<h3><?php echo esc_html( $tier['name'] ); ?></h3>
						<div class="pricing-amount">
							<span class="currency">$</span>
							<span class="price"><?php echo esc_html( $tier['price'] ); ?></span>
							<span class="period">/<?php echo esc_html( $tier['period'] ); ?></span>
						</div>
						<div class="pricing-tokens">
							<?php if ( 'unlimited' === $tier['tokens'] ) : ?>
								<?php esc_html_e( 'Unlimited Scans', 'wpshadow' ); ?>
							<?php else : ?>
								<?php echo number_format_i18n( $tier['tokens'] ) . ' ' . esc_html__( 'tokens', 'wpshadow' ); ?>
							<?php endif; ?>
						</div>
						<a href="https://guardian.wpshadow.com/purchase/<?php echo esc_attr( $tier_id ); ?>"
						   class="button <?php echo 'unlimited' === $tier_id ? 'button-primary' : ''; ?>"
						   target="_blank">
							<?php echo 0 === $tier['price'] ? esc_html__( 'Get Started', 'wpshadow' ) : esc_html__( 'Purchase', 'wpshadow' ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render account tab.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_account_tab() {
		if ( ! Guardian_API_Client::is_connected() ) {
			self::render_connect_prompt();
			return;
		}

		$account = Guardian_API_Client::get_account_info();

		?>
		<div class="guardian-account">
			<h2><?php esc_html_e( 'Account Information', 'wpshadow' ); ?></h2>

			<?php if ( is_wp_error( $account ) ) : ?>
				<div class="notice notice-error">
					<p><?php echo esc_html( $account->get_error_message() ); ?></p>
				</div>
			<?php else : ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Email', 'wpshadow' ); ?></th>
						<td><?php echo isset( $account['email'] ) ? esc_html( $account['email'] ) : '—'; ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Account Type', 'wpshadow' ); ?></th>
						<td>
							<?php
							if ( isset( $account['subscription'] ) && 'pro' === $account['subscription'] ) {
								echo '<span class="status-badge pro">' . esc_html__( 'Guardian Pro', 'wpshadow' ) . '</span>';
							} else {
								echo '<span class="status-badge free">' . esc_html__( 'Free Tier', 'wpshadow' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Member Since', 'wpshadow' ); ?></th>
						<td><?php echo isset( $account['created'] ) ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $account['created'] ) ) ) : '—'; ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Total Scans', 'wpshadow' ); ?></th>
						<td><?php echo isset( $account['total_scans'] ) ? number_format_i18n( $account['total_scans'] ) : '—'; ?></td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Account Actions', 'wpshadow' ); ?></h3>
				<p>
					<a href="https://guardian.wpshadow.com/account" class="button" target="_blank">
						<?php esc_html_e( 'Manage Account Online', 'wpshadow' ); ?>
					</a>
					<button class="button" id="disconnect-guardian">
						<?php esc_html_e( 'Disconnect Guardian', 'wpshadow' ); ?>
					</button>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render connect prompt.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function render_connect_prompt() {
		?>
		<div class="guardian-connect-prompt">
			<div class="connect-hero">
				<span class="dashicons dashicons-cloud"></span>
				<h2><?php esc_html_e( 'AI-Powered Site Protection', 'wpshadow' ); ?></h2>
				<p class="lead">
					<?php esc_html_e( 'Guardian uses advanced artificial intelligence to scan your site for security vulnerabilities, performance issues, and optimization opportunities.', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="connect-features-grid">
				<div class="feature">
					<span class="dashicons dashicons-shield-alt"></span>
					<h3><?php esc_html_e( 'Security Scanning', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Detect vulnerabilities, malware, and security misconfigurations.', 'wpshadow' ); ?></p>
				</div>
				<div class="feature">
					<span class="dashicons dashicons-performance"></span>
					<h3><?php esc_html_e( 'Performance Analysis', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Identify bottlenecks and get actionable optimization recommendations.', 'wpshadow' ); ?></p>
				</div>
				<div class="feature">
					<span class="dashicons dashicons-search"></span>
					<h3><?php esc_html_e( 'SEO Optimization', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'Improve search rankings with data-driven SEO insights.', 'wpshadow' ); ?></p>
				</div>
			</div>

			<div class="connect-cta">
				<h3><?php esc_html_e( 'Get Started with Guardian', 'wpshadow' ); ?></h3>
				<p><?php esc_html_e( '100 free scans every month. No credit card required.', 'wpshadow' ); ?></p>
				<div class="connect-buttons">
					<a href="https://guardian.wpshadow.com/register" class="button button-primary button-hero" target="_blank">
						<?php esc_html_e( 'Create Free Account', 'wpshadow' ); ?>
					</a>
					<button class="button button-hero" id="connect-existing">
						<?php esc_html_e( 'Connect Existing Account', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<div class="privacy-note">
				<span class="dashicons dashicons-privacy"></span>
				<p>
					<?php
					printf(
						/* translators: %s: Privacy Policy link */
						esc_html__( 'Guardian requires sending anonymized site data to our cloud service for analysis. Review our %s to learn exactly what we collect.', 'wpshadow' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ) . '">' . esc_html__( 'Privacy Policy', 'wpshadow' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>

		<div id="connect-modal" class="guardian-modal" style="display: none;">
			<div class="modal-content">
				<span class="modal-close">&times;</span>
				<h2><?php esc_html_e( 'Connect Guardian Account', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Enter your Guardian API key to connect your account.', 'wpshadow' ); ?></p>
				<form id="connect-form">
					<input type="text"
					       id="guardian-api-key"
					       placeholder="<?php esc_attr_e( 'Enter API Key', 'wpshadow' ); ?>"
					       style="width: 100%; padding: 10px; margin: 20px 0;" />
					<p class="description">
						<?php
						printf(
							/* translators: %s: Guardian account URL */
							esc_html__( 'Find your API key in your %s.', 'wpshadow' ),
							'<a href="https://guardian.wpshadow.com/account/api" target="_blank">' . esc_html__( 'Guardian account dashboard', 'wpshadow' ) . '</a>'
						);
						?>
					</p>
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Connect Account', 'wpshadow' ); ?>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle scan request AJAX.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	public static function handle_scan_request() {
		self::verify_request( 'wpshadow_guardian', 'manage_options' );

		$scan_type = self::get_post_param( 'scan_type', 'text', '', true );

		$result = Guardian_API_Client::request_scan( $scan_type );

		if ( is_wp_error( $result ) ) {
			self::send_error( $result->get_error_message() );
		}

		self::send_success( $result );
	}

	/**
	 * Handle check scan status AJAX.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	public static function handle_check_scan() {
		self::verify_request( 'wpshadow_guardian', 'manage_options' );

		$scan_id = self::get_post_param( 'scan_id', 'text', '', true );

		$result = Guardian_API_Client::get_scan_results( $scan_id );

		if ( is_wp_error( $result ) ) {
			self::send_error( $result->get_error_message() );
		}

		self::send_success( $result );
	}

	/**
	 * Output page styles.
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function output_styles() {
		?>
		<style>
			.wpshadow-guardian-page h1 {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.wpshadow-guardian-page h1 .dashicons {
				font-size: 32px;
				width: 32px;
				height: 32px;
				color: #667eea;
			}
			.wpshadow-guardian-content {
				background: #fff;
				padding: 20px;
				margin-top: 20px;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,0.04);
			}
			.dashboard-cards {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 20px;
				margin-bottom: 30px;
			}
			.dashboard-card {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: #fff;
				padding: 30px;
				border-radius: 8px;
				text-align: center;
			}
			.dashboard-card h3 {
				margin: 0 0 20px 0;
				font-size: 16px;
				opacity: 0.9;
			}
			.dashboard-card .card-value {
				margin-bottom: 20px;
			}
			.dashboard-card .card-value .number {
				font-size: 48px;
				font-weight: 700;
				display: block;
				line-height: 1;
			}
			.dashboard-card .card-value .unlimited {
				font-size: 36px;
				font-weight: 700;
			}
			.dashboard-card .button {
				background: rgba(255,255,255,0.2);
				color: #fff;
				border: none;
			}
			.dashboard-card .button-primary {
				background: #fff;
				color: #667eea;
			}
			.scan-types {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
				gap: 20px;
				margin-top: 30px;
			}
			.scan-type-card {
				border: 2px solid #ddd;
				padding: 30px;
				border-radius: 8px;
				text-align: center;
				position: relative;
			}
			.scan-type-card.featured {
				border-color: #667eea;
				background: #f8f9ff;
			}
			.scan-type-badge {
				position: absolute;
				top: 10px;
				right: 10px;
				background: #667eea;
				color: #fff;
				padding: 5px 12px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
			}
			.scan-type-icon .dashicons {
				font-size: 64px;
				width: 64px;
				height: 64px;
				color: #667eea;
			}
			.scan-type-card h3 {
				margin: 20px 0 10px 0;
			}
			.scan-features {
				list-style: none;
				padding: 0;
				margin: 20px 0;
				text-align: left;
			}
			.scan-features li {
				padding: 5px 0;
				padding-left: 20px;
				position: relative;
			}
			.scan-features li:before {
				content: "✓";
				position: absolute;
				left: 0;
				color: #00a32a;
				font-weight: 600;
			}
			.token-cost {
				display: block;
				margin-top: 10px;
				font-size: 12px;
				color: #757575;
			}
			.status-badge {
				display: inline-block;
				padding: 4px 12px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
			}
			.status-badge.status-pending {
				background: #f0f0f0;
				color: #757575;
			}
			.status-badge.status-complete {
				background: #d4edda;
				color: #155724;
			}
			.status-badge.status-error {
				background: #f8d7da;
				color: #721c24;
			}
			.status-badge.pro {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: #fff;
			}
			.status-badge.free {
				background: #f0f0f0;
				color: #757575;
			}
			.guardian-connect-prompt {
				text-align: center;
				max-width: 900px;
				margin: 40px auto;
			}
			.connect-hero .dashicons {
				font-size: 128px;
				width: 128px;
				height: 128px;
				color: #667eea;
			}
			.connect-hero h2 {
				font-size: 32px;
				margin: 20px 0;
			}
			.connect-hero .lead {
				font-size: 18px;
				color: #757575;
				max-width: 600px;
				margin: 0 auto 40px auto;
			}
			.connect-features-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 30px;
				margin: 40px 0;
			}
			.connect-features-grid .feature {
				padding: 30px;
			}
			.connect-features-grid .dashicons {
				font-size: 48px;
				width: 48px;
				height: 48px;
				color: #667eea;
			}
			.connect-features-grid h3 {
				margin: 20px 0 10px 0;
			}
			.connect-cta {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: #fff;
				padding: 40px;
				border-radius: 8px;
				margin: 40px 0;
			}
			.connect-cta h3 {
				margin: 0 0 10px 0;
				font-size: 24px;
			}
			.connect-cta p {
				margin: 0 0 30px 0;
				font-size: 16px;
				opacity: 0.9;
			}
			.connect-buttons {
				display: flex;
				gap: 15px;
				justify-content: center;
			}
			.connect-buttons .button {
				background: rgba(255,255,255,0.2);
				color: #fff;
				border: none;
			}
			.connect-buttons .button-primary {
				background: #fff;
				color: #667eea;
			}
			.privacy-note {
				margin-top: 30px;
				padding: 20px;
				background: #f0f0f0;
				border-radius: 8px;
				display: flex;
				align-items: flex-start;
				gap: 10px;
				text-align: left;
			}
			.privacy-note .dashicons {
				color: #667eea;
				flex-shrink: 0;
			}
			.privacy-note p {
				margin: 0;
			}
			.pricing-cards {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 20px;
				margin-top: 30px;
			}
			.pricing-card {
				border: 2px solid #ddd;
				padding: 30px;
				border-radius: 8px;
				text-align: center;
				position: relative;
			}
			.pricing-card.unlimited {
				border-color: #667eea;
				background: #f8f9ff;
			}
			.pricing-badge {
				position: absolute;
				top: 10px;
				right: 10px;
				background: #667eea;
				color: #fff;
				padding: 5px 12px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
			}
			.pricing-amount {
				margin: 20px 0;
				display: flex;
				align-items: baseline;
				justify-content: center;
			}
			.pricing-amount .price {
				font-size: 48px;
				font-weight: 700;
			}
			.pricing-amount .currency {
				font-size: 24px;
				margin-right: 5px;
			}
			.pricing-amount .period {
				font-size: 16px;
				color: #757575;
				margin-left: 5px;
			}
			.pricing-tokens {
				font-size: 16px;
				color: #757575;
				margin-bottom: 20px;
			}
			.guardian-modal {
				position: fixed;
				z-index: 100000;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				background: rgba(0,0,0,0.5);
			}
			.modal-content {
				background: #fff;
				margin: 10% auto;
				padding: 40px;
				border-radius: 8px;
				max-width: 600px;
				position: relative;
			}
			.modal-close {
				position: absolute;
				top: 15px;
				right: 20px;
				font-size: 28px;
				font-weight: bold;
				cursor: pointer;
			}
		</style>
		<?php
	}
}
