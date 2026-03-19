<?php
/**
 * Guardian Token Balance Widget
 *
 * Displays user's Guardian token balance in admin area.
 * Phase 7: Guardian Launch - Token Economy UI
 *
 * @package    WPShadow
 * @subpackage Guardian
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Token Balance Widget Class
 *
 * Shows Guardian token balance and provides quick access to purchase more.
 *
 * @since 1.6093.1200
 */
class Token_Balance_Widget extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_bar_menu'        => array( 'add_admin_bar_item', 999 ),
			'admin_enqueue_scripts' => array( 'enqueue_styles' ),
			'admin_enqueue_scripts' => array( 'enqueue_scripts' ),
			'wp_dashboard_setup'    => 'add_dashboard_widget',
		);
	}

	/**
	 * Initialize the widget (deprecated).
	 *
	 * @deprecated1.0 Use Token_Balance_Widget::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Add token balance to admin bar.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public static function add_admin_bar_item( $wp_admin_bar ) {
		// Admin bar widget disabled
		return;

		// Check if Guardian is enabled
		$is_enabled = Guardian_Manager::is_enabled();
		$status_dot = $is_enabled
			? '<span class="wpshadow-status-dot wpshadow-status-active" title="' . esc_attr__( 'Guardian Active', 'wpshadow' ) . '"></span>'
			: '<span class="wpshadow-status-dot wpshadow-status-inactive" title="' . esc_attr__( 'Guardian Inactive', 'wpshadow' ) . '"></span>';

		if ( ! Guardian_API_Client::is_connected() ) {
			// Show connect button with red dot (inactive)
			$wp_admin_bar->add_node(
				array(
					'id'    => 'wpshadow-guardian',
					'title' => '<span class="wpshadow-guardian-connect">' .
								$status_dot .
								'<span class="dashicons dashicons-cloud" style="font-size: 20px; vertical-align: middle;"></span> ' .
								__( 'Connect Guardian', 'wpshadow' ) .
								'</span>',
					'href'  => '#',
					'meta'  => array(
						'class' => 'wpshadow-guardian-adminbar wpshadow-guardian-main-toggle',
						'title' => __( 'Click to toggle Guardian', 'wpshadow' ),
					),
				)
			);
			return;
		}

		// Get token balance
		$balance = Guardian_API_Client::get_token_balance();

		if ( is_wp_error( $balance ) ) {
			$balance = '?';
			$title   = __( 'Unable to load balance', 'wpshadow' );
		} else {
			$title = sprintf(
				/* translators: %s: token count */
				__( 'You have %s Guardian tokens', 'wpshadow' ),
				number_format_i18n( $balance )
			);
		}

		// Show token balance with status indicator (main item is now toggleable)
		$wp_admin_bar->add_node(
			array(
				'id'    => 'wpshadow-guardian',
				'title' => self::get_admin_bar_html( $balance, $status_dot ),
				'href'  => '#',
				'meta'  => array(
					'class' => 'wpshadow-guardian-adminbar wpshadow-guardian-main-toggle',
					'title' => __( 'Click to toggle Guardian', 'wpshadow' ),
				),
			)
		);

		// Add submenu items
		// Toggle Guardian on/off
		$toggle_text = $is_enabled
			? '<span class="wpshadow-guardian-toggle-active">' . __( '✓ Guardian Running', 'wpshadow' ) . '</span>'
			: '<span class="wpshadow-guardian-toggle-inactive">' . __( '○ Guardian Stopped', 'wpshadow' ) . '</span>';

		$wp_admin_bar->add_node(
			array(
				'parent' => 'wpshadow-guardian',
				'id'     => 'wpshadow-guardian-toggle',
				'title'  => $toggle_text,
				'href'   => '#',
				'meta'   => array(
					'onclick' => 'return false;',
					'class'   => 'wpshadow-guardian-toggle-link',
				),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'wpshadow-guardian',
				'id'     => 'wpshadow-guardian-run-scan',
				'title'  => __( 'Run AI Scan', 'wpshadow' ),
				'href'   => admin_url( 'admin.php?page=wpshadow-guardian&action=scan' ),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'wpshadow-guardian',
				'id'     => 'wpshadow-guardian-history',
				'title'  => __( 'Scan History', 'wpshadow' ),
				'href'   => admin_url( 'admin.php?page=wpshadow-guardian&tab=history' ),
			)
		);

		if ( ! is_numeric( $balance ) || $balance < 10 ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'wpshadow-guardian',
					'id'     => 'wpshadow-guardian-buy-tokens',
					'title'  => '<span style="color: #00a32a;">' . __( 'Get More Tokens', 'wpshadow' ) . '</span>',
					'href'   => admin_url( 'admin.php?page=wpshadow-guardian&tab=pricing' ),
				)
			);
		}
	}

	/**
	 * Get HTML for admin bar item.
	 *
	 * @since 1.6093.1200
	 * @param  int|string $balance    Token balance.
	 * @param  string     $status_dot Status indicator HTML.
	 * @return string HTML.
	 */
	private static function get_admin_bar_html( $balance, $status_dot = '' ) {
		$low_balance = is_numeric( $balance ) && $balance < 10;
		$class       = $low_balance ? 'wpshadow-tokens-low' : 'wpshadow-tokens-ok';

		return sprintf(
			'<span class="wpshadow-guardian-balance %s">' .
			'%s' .
			'<span class="dashicons dashicons-cloud" style="font-size: 20px; vertical-align: middle;"></span> ' .
			'<span class="wpshadow-token-count">%s</span>' .
			'</span>',
			esc_attr( $class ),
			$status_dot,
			esc_html( is_numeric( $balance ) ? number_format_i18n( $balance ) : $balance )
		);
	}

	/**
	 * Enqueue widget styles.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function enqueue_styles() {
		?>
		<style>
			.wpshadow-guardian-adminbar .wpshadow-guardian-balance {
				display: inline-flex;
				align-items: center;
				gap: 5px;
			}
			.wpshadow-guardian-adminbar .wpshadow-token-count {
				font-weight: 600;
				padding: 2px 8px;
				border-radius: 12px;
				background: rgba(0, 163, 42, 0.2);
				font-size: 12px;
			}
			.wpshadow-guardian-adminbar .wpshadow-tokens-low .wpshadow-token-count {
				background: rgba(220, 50, 50, 0.2);
				color: #dc3232;
			}
			.wpshadow-guardian-connect {
				display: inline-flex;
				align-items: center;
				gap: 6px;
			}
			.wpshadow-status-dot {
				display: inline-block;
				width: 10px;
				height: 10px;
				border-radius: 50%;
				flex-shrink: 0;
				transition: opacity 0.2s ease;
			}
			.wpshadow-status-active {
				background-color: #00a32a;
				box-shadow: 0 0 4px rgba(0, 163, 42, 0.8);
			}
			.wpshadow-status-inactive {
				background-color: #dc3232;
				box-shadow: 0 0 4px rgba(220, 50, 50, 0.8);
			}
			#wpshadow-guardian-buy-tokens {
				border-top: 1px solid rgba(255,255,255,0.1);
				margin-top: 5px;
				padding-top: 5px;
			}
			.wpshadow-guardian-toggle-active {
				color: #00a32a !important;
				font-weight: 600;
			}
			.wpshadow-guardian-toggle-inactive {
				color: #dc3232 !important;
			}
			.wpshadow-guardian-main-toggle {
				cursor: pointer;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue widget scripts.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function enqueue_scripts() {
		// Only load on admin pages
		if ( ! is_admin() ) {
			return;
		}

		$nonce = wp_create_nonce( 'wpshadow_toggle_guardian' );

		// Enqueue jQuery (it should already be enqueued, but be explicit)
		wp_enqueue_script( 'jquery' );

		// Build inline script with proper dependencies
		$inline_script = '
		jQuery(document).ready(function($) {
			// Toggle function for both main item and submenu
			function toggleGuardian(e) {
				e.preventDefault();

				var $link = $(this);
				var $statusDot = $link.find(".wpshadow-status-dot");
				var isActive = $statusDot.hasClass("wpshadow-status-active");
				var newState = !isActive;

				// Show loading state on status dot
				$statusDot.css("opacity", "0.5");

				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "wpshadow_toggle_guardian",
						nonce: "' . esc_js( $nonce ) . '",
						enabled: newState ? "1" : "0"
					},
					success: function(response) {
						if (response.success) {
							// Update all status dots in main toolbar item
							var $mainStatusDot = $("#wp-admin-bar-wpshadow-guardian > a .wpshadow-status-dot");
							if (newState) {
								$mainStatusDot.removeClass("wpshadow-status-inactive")
									.addClass("wpshadow-status-active")
									.attr("title", "' . esc_js( __( 'Guardian Active', 'wpshadow' ) ) . '");
							} else {
								$mainStatusDot.removeClass("wpshadow-status-active")
									.addClass("wpshadow-status-inactive")
									.attr("title", "' . esc_js( __( 'Guardian Inactive', 'wpshadow' ) ) . '");
							}

							// Update toggle submenu text
							var $toggleText = $("#wp-admin-bar-wpshadow-guardian-toggle a span");
							if (newState) {
								$toggleText.removeClass("wpshadow-guardian-toggle-inactive")
									.addClass("wpshadow-guardian-toggle-active")
									.text("' . esc_js( __( '✓ Guardian Running', 'wpshadow' ) ) . '");
							} else {
								$toggleText.removeClass("wpshadow-guardian-toggle-active")
									.addClass("wpshadow-guardian-toggle-inactive")
									.text("' . esc_js( __( '○ Guardian Stopped', 'wpshadow' ) ) . '");
							}

							$statusDot.css("opacity", "1");
						} else {
							$statusDot.css("opacity", "1");
							alert(response.data && response.data.message ? response.data.message : "' . esc_js( __( 'Failed to toggle Guardian', 'wpshadow' ) ) . '");
						}
					},
					error: function() {
						$statusDot.css("opacity", "1");
						alert("' . esc_js( __( 'Failed to toggle Guardian', 'wpshadow' ) ) . '");
					}
				});
			}

			// Bind toggle to main admin bar item and submenu toggle
			$("#wp-admin-bar-wpshadow-guardian > a.wpshadow-guardian-main-toggle").on("click", toggleGuardian);
			$("#wp-admin-bar-wpshadow-guardian-toggle a").on("click", toggleGuardian);
		});
		';

		// Add inline script with jQuery as dependency
		wp_add_inline_script( 'jquery', $inline_script );
	}

	/**
	 * Add dashboard widget.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_guardian_widget',
			__( 'WPShadow Guardian', 'wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_dashboard_widget() {
		if ( ! Guardian_API_Client::is_connected() ) {
			self::render_connect_prompt();
			return;
		}

		$balance = Guardian_API_Client::get_token_balance();
		$account = Guardian_API_Client::get_account_info();
		$scans   = Guardian_API_Client::get_recent_scans( 3 );

		?>
		<div class="wpshadow-guardian-widget">
			<div class="guardian-balance-card">
				<div class="balance-header">
					<span class="dashicons dashicons-cloud"></span>
					<h3><?php esc_html_e( 'Token Balance', 'wpshadow' ); ?></h3>
				</div>
				<div class="balance-amount">
					<?php
					if ( is_wp_error( $balance ) ) {
						echo '<span class="error">' . esc_html__( 'Unable to load', 'wpshadow' ) . '</span>';
					} elseif ( 'unlimited' === $balance || ( is_array( $account ) && isset( $account['subscription'] ) && 'pro' === $account['subscription'] ) ) {
						echo '<span class="unlimited">' . esc_html__( 'Unlimited', 'wpshadow' ) . '</span>';
						echo '<span class="subscription-badge">' . esc_html__( 'Guardian Pro', 'wpshadow' ) . '</span>';
					} else {
						echo '<span class="token-count">' . number_format_i18n( $balance ) . '</span>';
						echo '<span class="token-label">' . esc_html__( 'tokens', 'wpshadow' ) . '</span>';
					}
					?>
				</div>
				<div class="balance-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&action=scan' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Run AI Scan', 'wpshadow' ); ?>
					</a>
					<?php if ( is_numeric( $balance ) && $balance < 10 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=pricing' ) ); ?>" class="button">
							<?php esc_html_e( 'Get More Tokens', 'wpshadow' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( ! empty( $scans ) ) : ?>
				<div class="guardian-recent-scans">
					<h4><?php esc_html_e( 'Recent Scans', 'wpshadow' ); ?></h4>
					<ul>
						<?php foreach ( $scans as $scan ) : ?>
							<li>
								<span class="scan-type"><?php echo esc_html( ucfirst( $scan['scan_type'] ) ); ?></span>
								<span class="scan-date"><?php echo esc_html( human_time_diff( strtotime( $scan['requested'] ) ) . ' ' . __( 'ago', 'wpshadow' ) ); ?></span>
								<span class="scan-status status-<?php echo esc_attr( $scan['status'] ); ?>">
									<?php echo esc_html( ucfirst( $scan['status'] ) ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=history' ) ); ?>" class="view-all">
						<?php esc_html_e( 'View All Scans →', 'wpshadow' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<style>
			.wpshadow-guardian-widget {
				padding: 0;
			}
			.guardian-balance-card {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: #fff;
				padding: 20px;
				border-radius: 8px;
				margin-bottom: 20px;
			}
			.balance-header {
				display: flex;
				align-items: center;
				gap: 8px;
				margin-bottom: 15px;
			}
			.balance-header .dashicons {
				font-size: 24px;
				width: 24px;
				height: 24px;
			}
			.balance-header h3 {
				margin: 0;
				font-size: 16px;
				font-weight: 600;
			}
			.balance-amount {
				margin-bottom: 15px;
			}
			.balance-amount .token-count {
				font-size: 48px;
				font-weight: 700;
				line-height: 1;
				display: block;
			}
			.balance-amount .token-label {
				font-size: 14px;
				opacity: 0.9;
			}
			.balance-amount .unlimited {
				font-size: 36px;
				font-weight: 700;
				display: block;
			}
			.balance-amount .subscription-badge {
				display: inline-block;
				background: rgba(255,255,255,0.2);
				padding: 4px 12px;
				border-radius: 12px;
				font-size: 12px;
				margin-top: 8px;
			}
			.balance-actions {
				display: flex;
				gap: 10px;
			}
			.balance-actions .button {
				border: none;
				text-shadow: none;
			}
			.balance-actions .button-primary {
				background: #fff;
				color: #667eea;
			}
			.balance-actions .button-primary:hover {
				background: #f0f0f0;
			}
			.guardian-recent-scans {
				padding: 15px 0;
			}
			.guardian-recent-scans h4 {
				margin: 0 0 10px 0;
				font-size: 14px;
				color: #1d2327;
			}
			.guardian-recent-scans ul {
				margin: 0;
				padding: 0;
				list-style: none;
			}
			.guardian-recent-scans li {
				display: flex;
				align-items: center;
				gap: 10px;
				padding: 8px 0;
				border-bottom: 1px solid #f0f0f0;
			}
			.guardian-recent-scans li:last-child {
				border-bottom: none;
			}
			.scan-type {
				font-weight: 600;
				flex: 1;
			}
			.scan-date {
				font-size: 12px;
				color: #757575;
			}
			.scan-status {
				font-size: 11px;
				padding: 2px 8px;
				border-radius: 10px;
				text-transform: uppercase;
				font-weight: 600;
			}
			.scan-status.status-pending {
				background: #f0f0f0;
				color: #757575;
			}
			.scan-status.status-complete {
				background: #d4edda;
				color: #155724;
			}
			.scan-status.status-error {
				background: #f8d7da;
				color: #721c24;
			}
			.guardian-recent-scans .view-all {
				display: inline-block;
				margin-top: 10px;
				text-decoration: none;
				color: #2271b1;
			}
			.guardian-recent-scans .view-all:hover {
				color: #135e96;
			}
		</style>
		<?php
	}

	/**
	 * Render connect prompt.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function render_connect_prompt() {
		?>
		<div class="wpshadow-guardian-connect-prompt">
			<div class="connect-icon">
				<span class="dashicons dashicons-cloud" style="font-size: 64px; color: #667eea;"></span>
			</div>
			<h3><?php esc_html_e( 'AI-Powered Site Scanning', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Guardian uses advanced AI to scan your site for security issues, performance problems, and optimization opportunities.', 'wpshadow' ); ?></p>
			<div class="connect-features">
				<ul>
					<li>✓ <?php esc_html_e( 'Security vulnerability detection', 'wpshadow' ); ?></li>
					<li>✓ <?php esc_html_e( 'Performance analysis', 'wpshadow' ); ?></li>
					<li>✓ <?php esc_html_e( 'SEO recommendations', 'wpshadow' ); ?></li>
					<li>✓ <?php esc_html_e( '100 free scans per month', 'wpshadow' ); ?></li>
				</ul>
			</div>
			<div class="connect-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Connect Guardian', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/guardian/" target="_blank" class="button">
					<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
				</a>
			</div>
		</div>

		<style>
			.wpshadow-guardian-connect-prompt {
				text-align: center;
				padding: 20px;
			}
			.connect-icon {
				margin-bottom: 20px;
			}
			.wpshadow-guardian-connect-prompt h3 {
				margin: 0 0 10px 0;
				font-size: 18px;
			}
			.wpshadow-guardian-connect-prompt p {
				margin: 0 0 20px 0;
				color: #757575;
			}
			.connect-features ul {
				list-style: none;
				padding: 0;
				margin: 0 0 20px 0;
				text-align: left;
				display: inline-block;
			}
			.connect-features li {
				padding: 5px 0;
				color: #1d2327;
			}
			.connect-actions {
				display: flex;
				gap: 10px;
				justify-content: center;
			}
		</style>
		<?php
	}

	/**
	 * Get token status summary.
	 *
	 * @since 1.6093.1200
	 * @return array Status information.
	 */
	public static function get_status() {
		if ( ! Guardian_API_Client::is_connected() ) {
			return array(
				'connected' => false,
				'message'   => __( 'Not connected to Guardian', 'wpshadow' ),
			);
		}

		$balance = Guardian_API_Client::get_token_balance();
		$account = Guardian_API_Client::get_account_info();

		if ( is_wp_error( $balance ) ) {
			return array(
				'connected' => true,
				'error'     => true,
				'message'   => $balance->get_error_message(),
			);
		}

		$is_unlimited = 'unlimited' === $balance || ( is_array( $account ) && isset( $account['subscription'] ) && 'pro' === $account['subscription'] );

		return array(
			'connected'    => true,
			'balance'      => $balance,
			'unlimited'    => $is_unlimited,
			'low_balance'  => ! $is_unlimited && is_numeric( $balance ) && $balance < 10,
			'subscription' => is_array( $account ) && isset( $account['subscription'] ) ? $account['subscription'] : null,
		);
	}
}
