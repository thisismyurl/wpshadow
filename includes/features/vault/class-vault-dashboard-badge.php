<?php
/**
 * WPShadow Vault Dashboard Badge
 *
 * Displays Vault status badge in Core dashboard.
 * Shows backup count, tier, and quick actions.
 *
 * @package    WPShadow
 * @subpackage Vault
 * @since      1.6030.1840
 */

declare(strict_types=1);

namespace WPShadow\Vault;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault_Dashboard_Badge Class
 *
 * Integrates Vault status into WPShadow Core dashboard.
 *
 * @since 1.6030.1840
 */
class Vault_Dashboard_Badge extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_dashboard_widgets' => 'add_dashboard_widget',
			'admin_enqueue_scripts'      => 'enqueue_assets',
		);
	}

	/**
	 * Initialize dashboard integration (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Vault_Dashboard_Badge::subscribe() instead
	 * @since      1.6030.1840
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Add Vault dashboard widget
	 *
	 * @since  1.6030.1840
	 * @return void
	 */
	public static function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'wpshadow_vault_status',
			__( 'WPShadow Vault - Backups', 'wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget
	 *
	 * @since  1.6030.1840
	 * @return void
	 */
	public static function render_dashboard_widget() {
		$vault_manager = Vault_Manager::get_instance();
		$status        = $vault_manager->get_status();

		?>
		<div class="wpshadow-vault-dashboard-widget">
			<?php if ( $status['registered'] ) : ?>
				<div class="wpshadow-vault-stats">
					<div class="wpshadow-vault-stat">
						<span class="wpshadow-vault-stat-value"><?php echo esc_html( $status['backup_count'] ); ?></span>
						<span class="wpshadow-vault-stat-label">
							<?php
							printf(
								/* translators: %s: max backups */
								esc_html__( 'of %s backups', 'wpshadow' ),
								esc_html( $status['max_backups'] )
							);
							?>
						</span>
					</div>
					<div class="wpshadow-vault-stat">
						<span class="wpshadow-vault-stat-value wpshadow-vault-tier-<?php echo esc_attr( $status['tier'] ); ?>">
							<?php echo esc_html( ucfirst( $status['tier'] ) ); ?>
						</span>
						<span class="wpshadow-vault-stat-label"><?php esc_html_e( 'Plan', 'wpshadow' ); ?></span>
					</div>
				</div>

				<?php if ( $status['latest_backup'] ) : ?>
					<div class="wpshadow-vault-latest-backup">
						<strong><?php esc_html_e( 'Latest Backup:', 'wpshadow' ); ?></strong>
						<span><?php echo esc_html( $status['latest_backup']['label'] ); ?></span>
						<span class="wpshadow-vault-backup-date">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: relative time */
									__( '%s ago', 'wpshadow' ),
									human_time_diff( strtotime( $status['latest_backup']['created_at'] ) )
								)
							);
							?>
						</span>
					</div>
				<?php else : ?>
					<div class="wpshadow-vault-no-backups">
						<p><?php esc_html_e( 'No backups yet. Create your first backup!', 'wpshadow' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="wpshadow-vault-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-vault' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Create Backup', 'wpshadow' ); ?>
					</a>
					<?php if ( $status['latest_backup'] ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-vault' ) ); ?>" class="button">
							<?php esc_html_e( 'View All', 'wpshadow' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( 'free' === $status['tier'] && $status['backup_count'] >= 2 ) : ?>
					<div class="wpshadow-vault-upgrade-prompt">
						<p>
							<?php
							printf(
								/* translators: %d: remaining backups */
								esc_html__( '⚠️ You have %d free backup slots left.', 'wpshadow' ),
								$status['max_backups'] - $status['backup_count']
							);
							?>
							<a href="https://wpshadow.com/vault/pricing/" target="_blank">
								<?php esc_html_e( 'Upgrade for more', 'wpshadow' ); ?>
							</a>
						</p>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="wpshadow-vault-register-prompt">
					<?php echo Vault_Registration::get_registration_prompt(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Enqueue dashboard assets
	 *
	 * @since  1.6030.1840
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook && false === strpos( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-vault-dashboard',
			WPSHADOW_URL . 'assets/css/vault-dashboard.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Get Vault status badge HTML
	 *
	 * For display in Core dashboard header.
	 *
	 * @since  1.6030.1840
	 * @return string Badge HTML.
	 */
	public static function get_status_badge() {
		$vault_manager = Vault_Manager::get_instance();
		$status        = $vault_manager->get_status();

		if ( ! $status['registered'] ) {
			return self::get_unregistered_badge();
		}

		$badge_class = 'wpshadow-vault-badge wpshadow-vault-badge-' . esc_attr( $status['tier'] );

		if ( 'free' === $status['tier'] && $status['backup_count'] >= $status['max_backups'] ) {
			$badge_class .= ' wpshadow-vault-badge-full';
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $badge_class ); ?>">
			<span class="wpshadow-vault-badge-icon">💾</span>
			<div class="wpshadow-vault-badge-content">
				<span class="wpshadow-vault-badge-count">
					<?php echo esc_html( $status['backup_count'] ); ?>/<?php echo esc_html( $status['max_backups'] ); ?>
				</span>
				<span class="wpshadow-vault-badge-label"><?php esc_html_e( 'Backups', 'wpshadow' ); ?></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get unregistered badge HTML
	 *
	 * @since  1.6030.1840
	 * @return string Badge HTML.
	 */
	private static function get_unregistered_badge() {
		ob_start();
		?>
		<div class="wpshadow-vault-badge wpshadow-vault-badge-unregistered">
			<span class="wpshadow-vault-badge-icon">🔒</span>
			<div class="wpshadow-vault-badge-content">
				<span class="wpshadow-vault-badge-cta">
					<?php esc_html_e( 'Get 3 Free Backups', 'wpshadow' ); ?>
				</span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Show backup prompt before treatment
	 *
	 * Displays in treatment confirmation modal.
	 *
	 * @since  1.6030.1840
	 * @param  string $finding_id Finding ID being treated.
	 * @return string Prompt HTML.
	 */
	public static function get_treatment_backup_prompt( $finding_id ) {
		$vault_manager   = Vault_Manager::get_instance();
		$auto_backup_enabled = \WPShadow\Core\Settings_Registry::get( 'vault_auto_backup', true );

		ob_start();
		?>
		<div class="wpshadow-vault-treatment-prompt">
			<label>
				<input
					type="checkbox"
					name="vault_backup_before_treatment"
					<?php checked( $auto_backup_enabled ); ?>
				/>
				<?php esc_html_e( 'Create backup before applying fix', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php
				if ( $vault_manager->is_registered() ) {
					esc_html_e( 'Recommended for critical changes. You can restore if anything goes wrong.', 'wpshadow' );
				} else {
					printf(
						/* translators: %s: link to register */
						esc_html__(
							'%s to enable automatic backups (3 free).',
							'wpshadow'
						),
						'<a href="#" class="wpshadow-vault-register-link">' . esc_html__( 'Register for Vault', 'wpshadow' ) . '</a>'
					);
				}
				?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
