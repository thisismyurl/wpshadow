<?php
/**
 * WPShadow Vault UI
 *
 * Admin interface for backup and restore operations.
 *
 * @package    WPShadow
 * @subpackage Vault
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Vault;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault_UI Class
 *
 * Manages Vault admin pages and AJAX handlers.
 *
 * @since 1.6093.1200
 */
class Vault_UI extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_menu'                           => 'register_menu_pages',
			'admin_enqueue_scripts'                => 'enqueue_assets',
			'wp_ajax_wpshadow_vault_create_backup' => 'handle_create_backup',
			'wp_ajax_wpshadow_vault_delete_backup' => 'handle_delete_backup',
			'wp_ajax_wpshadow_vault_restore_backup' => 'handle_restore_backup',
		);
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since 1.6093.1200
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6364';
	}

	/**
	 * Initialize UI hooks (deprecated)
	 *
	 * @deprecated1.0 Use Vault_UI::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Register admin menu pages
	 *
	 * Vault menu is disabled in core - this is a pro feature.
	 * Vault Light functionality remains available programmatically.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_menu_pages() {
		// Vault submenu removed - pro feature handled by wpshadow-pro-vault
		// Vault Light backup functionality still available via API
		/* Commented out - Pro feature
		add_submenu_page(
			'wpshadow',
			__( 'WPShadow Vault - Backups', 'wpshadow' ),
			__( 'Vault', 'wpshadow' ),
			'manage_options',
			'wpshadow-vault',
			array( __CLASS__, 'render_vault_page' )
		);
		*/
	}

	/**
	 * Render main Vault page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_vault_page() {
		$vault_manager = Vault_Manager::get_instance();
		$status        = $vault_manager->get_status();
		$backups       = $vault_manager->get_backups();

		?>
		<div class="wrap wpshadow-vault-page">
			<h1><?php esc_html_e( 'WPShadow Vault - Backups', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>

			<?php if ( ! $status['registered'] ) : ?>
				<?php self::render_registration_prompt(); ?>
			<?php else : ?>
				<?php self::render_vault_header( $status ); ?>
				<?php self::render_backup_list( $backups, $status ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render registration prompt
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function render_registration_prompt() {
		?>
		<div class="wpshadow-vault-register-card">
			<?php echo Vault_Registration::get_registration_prompt(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<div class="wpshadow-vault-register-form" style="display:none;">
				<h3><?php esc_html_e( 'Create Your Free Vault Account', 'wpshadow' ); ?></h3>
				<form id="wpshadow-vault-register-form">
					<p>
						<label for="vault-email"><?php esc_html_e( 'Email Address', 'wpshadow' ); ?></label>
						<input
							type="email"
							id="vault-email"
							name="email"
							class="regular-text"
							required
						/>
					</p>
					<p>
						<label for="vault-password"><?php esc_html_e( 'Password', 'wpshadow' ); ?></label>
						<input
							type="password"
							id="vault-password"
							name="password"
							class="regular-text"
							required
							minlength="8"
						/>
						<span class="description"><?php esc_html_e( 'At least 8 characters', 'wpshadow' ); ?></span>
					</p>
					<p>
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Register Free', 'wpshadow' ); ?>
						</button>
					</p>
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpshadow_vault_register' ) ); ?>" />
				</form>

				<p class="wpshadow-vault-or"><?php esc_html_e( 'Already have an account?', 'wpshadow' ); ?></p>

				<form id="wpshadow-vault-connect-form">
					<p>
						<label for="vault-api-key"><?php esc_html_e( 'API Key', 'wpshadow' ); ?></label>
						<input
							type="text"
							id="vault-api-key"
							name="api_key"
							class="regular-text"
							required
						/>
					</p>
					<p>
						<button type="submit" class="button">
							<?php esc_html_e( 'Connect', 'wpshadow' ); ?>
						</button>
					</p>
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpshadow_vault_connect' ) ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Vault header with stats
	 *
	 * @since 1.6093.1200
	 * @param  array $status Vault status.
	 * @return void
	 */
	private static function render_vault_header( $status ) {
		?>
		<div class="wpshadow-vault-header">
			<div class="wpshadow-vault-stats-cards">
				<div class="wpshadow-vault-stat-card">
					<span class="wpshadow-vault-stat-value"><?php echo esc_html( $status['backup_count'] ); ?></span>
					<span class="wpshadow-vault-stat-label"><?php esc_html_e( 'Backups Created', 'wpshadow' ); ?></span>
				</div>
				<div class="wpshadow-vault-stat-card">
					<span class="wpshadow-vault-stat-value wpshadow-vault-tier-<?php echo esc_attr( $status['tier'] ); ?>">
						<?php echo esc_html( ucfirst( $status['tier'] ) ); ?>
					</span>
					<span class="wpshadow-vault-stat-label"><?php esc_html_e( 'Current Plan', 'wpshadow' ); ?></span>
				</div>
				<div class="wpshadow-vault-stat-card">
					<span class="wpshadow-vault-stat-value">
						<?php echo esc_html( $status['max_backups'] ); ?>
					</span>
					<span class="wpshadow-vault-stat-label"><?php esc_html_e( 'Max Backups', 'wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wpshadow-vault-actions-header">
				<button
					type="button"
					class="button button-primary button-hero"
					id="wpshadow-vault-create-backup-btn"
					<?php disabled( $status['backup_count'] >= $status['max_backups'] && 'unlimited' !== $status['max_backups'] ); ?>
				>
					<?php esc_html_e( 'Create Backup Now', 'wpshadow' ); ?>
				</button>

				<?php if ( 'free' === $status['tier'] ) : ?>
					<a href="https://wpshadow.com/vault/pricing/" target="_blank" class="button button-secondary">
						<?php esc_html_e( 'Upgrade Plan', 'wpshadow' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( 'unlimited' !== $status['max_backups'] && $status['backup_count'] >= $status['max_backups'] ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php
					printf(
						/* translators: %d: max backups */
						esc_html__(
							'You\'ve reached your backup limit (%d backups). Delete old backups or upgrade your plan.',
							'wpshadow'
						),
						$status['max_backups']
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render backup list table
	 *
	 * @since 1.6093.1200
	 * @param  array $backups List of backups.
	 * @param  array $status Vault status.
	 * @return void
	 */
	private static function render_backup_list( $backups, $status ) {
		?>
		<div class="wpshadow-vault-backups">
			<h2><?php esc_html_e( 'Your Backups', 'wpshadow' ); ?></h2>

			<?php if ( empty( $backups ) ) : ?>
				<div class="wpshadow-vault-no-backups">
					<p><?php esc_html_e( 'No backups yet. Create your first backup above!', 'wpshadow' ); ?></p>
				</div>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Label', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Created', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Size', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $backups as $backup ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $backup['label'] ); ?></strong>
								</td>
								<td>
									<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $backup['created_at'] ) ) ); ?>
									<br/>
									<span class="description">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: relative time */
												__( '%s ago', 'wpshadow' ),
												human_time_diff( strtotime( $backup['created_at'] ) )
											)
										);
										?>
									</span>
								</td>
								<td>
									<?php
									if ( isset( $backup['size_bytes'] ) ) {
										echo esc_html( size_format( $backup['size_bytes'] ) );
									} else {
										echo '—';
									}
									?>
								</td>
								<td>
									<?php
									$status_class = 'wpshadow-vault-status-' . esc_attr( $backup['status'] );
									$status_label = ucfirst( $backup['status'] );
									?>
									<span class="wpshadow-vault-status <?php echo esc_attr( $status_class ); ?>">
										<?php echo esc_html( $status_label ); ?>
									</span>
								</td>
								<td>
									<?php if ( 'completed' === $backup['status'] ) : ?>
										<button
											type="button"
											class="button wpshadow-vault-restore-btn"
											data-backup-id="<?php echo esc_attr( $backup['id'] ); ?>"
										>
											<?php esc_html_e( 'Restore', 'wpshadow' ); ?>
										</button>
									<?php endif; ?>
									<button
										type="button"
										class="button wpshadow-vault-delete-btn"
										data-backup-id="<?php echo esc_attr( $backup['id'] ); ?>"
									>
										<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
									</button>
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
	 * Enqueue UI assets
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'wpshadow-vault' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-vault-ui',
			WPSHADOW_URL . 'assets/css/vault-ui.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-vault-ui',
			WPSHADOW_URL . 'assets/js/vault-ui.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-vault-ui',
			'wpShadowVault',
			'wpshadow_vault_create_backup',
			array(
				'nonces'   => array(
					'create_backup'  => wp_create_nonce( 'wpshadow_vault_create_backup' ),
					'delete_backup'  => wp_create_nonce( 'wpshadow_vault_delete_backup' ),
					'restore_backup' => wp_create_nonce( 'wpshadow_vault_restore_backup' ),
					'register'       => wp_create_nonce( 'wpshadow_vault_register' ),
					'connect'        => wp_create_nonce( 'wpshadow_vault_connect' ),
				),
				'strings'  => array(
					'creating'        => __( 'Creating backup...', 'wpshadow' ),
					'deleting'        => __( 'Deleting backup...', 'wpshadow' ),
					'restoring'       => __( 'Restoring backup...', 'wpshadow' ),
					'confirm_delete'  => __( 'Are you sure you want to delete this backup? This cannot be undone.', 'wpshadow' ),
					'confirm_restore' => __( 'Are you sure you want to restore this backup? Your current site will be backed up first.', 'wpshadow' ),
				),
			),
			'nonce',
			'ajax_url'
		);
	}

	/**
	 * Handle create backup AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_create_backup() {
		self::verify_request( 'wpshadow_vault_create_backup', 'manage_options' );

		$label = self::get_post_param( 'label', 'text', __( 'Manual Backup', 'wpshadow' ) );

		$vault_manager = Vault_Manager::get_instance();
		$result        = $vault_manager->create_backup( $label );

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle delete backup AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_delete_backup() {
		self::verify_request( 'wpshadow_vault_delete_backup', 'manage_options' );

		$backup_id = self::get_post_param( 'backup_id', 'text', '', true );

		$vault_manager = Vault_Manager::get_instance();
		$result        = $vault_manager->delete_backup( $backup_id );

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle restore backup AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_restore_backup() {
		self::verify_request( 'wpshadow_vault_restore_backup', 'manage_options' );

		$backup_id = self::get_post_param( 'backup_id', 'text', '', true );

		$vault_manager = Vault_Manager::get_instance();
		$result        = $vault_manager->restore_backup( $backup_id );

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}
}
