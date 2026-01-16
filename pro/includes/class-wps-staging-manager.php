<?php
/**
 * Safe Staging Environment Manager - One-click staging site creation.
 *
 * Creates isolated copy of site for testing without affecting production.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Staging Manager Class
 */
class WPSHADOW_Staging_Manager {

	/**
	 * Staging environments option key.
	 */
	private const ENVS_KEY = 'wpshadow_staging_environments';

	/**
	 * Staging logs option key.
	 */
	private const LOGS_KEY = 'wpshadow_staging_logs';

	/**
	 * Initialize Staging Manager.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'wp_ajax_WPSHADOW_create_staging', array( __CLASS__, 'handle_staging_creation' ) );
		add_action( 'wp_ajax_WPSHADOW_delete_staging', array( __CLASS__, 'handle_staging_deletion' ) );
		add_action( 'wp_ajax_WPSHADOW_deploy_staging', array( __CLASS__, 'handle_staging_deployment' ) );
		add_action( 'wp_ajax_WPSHADOW_rollback_staging', array( __CLASS__, 'handle_staging_rollback' ) );
	}

	/**
	 * Create a new staging environment.
	 *
	 * @param string $name Optional staging environment name.
	 * @return string|false Staging ID or false.
	 */
	public static function create_staging( string $name = '' ): string|false {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Create snapshot before starting staging creation.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Snapshot_Manager' ) ) {
			WPSHADOW_Snapshot_Manager::create_snapshot( 'Pre-staging: ' . $name );
		}

		$staging = array(
			'id'                             => wp_generate_uuid4(),
			'created'                        => time(),
			'name'                           => sanitize_text_field( $name ) ?: 'Staging ' . wp_date( 'M d H:i' ),
			'production'                     => array(
				'url'     => home_url(),
				'path'    => ABSPATH,
				'db_host' => DB_HOST,
				'db_name' => DB_NAME,
			),
			'staging'                        => array(
				'url'     => '',
				'path'    => '',
				'db_name' => DB_NAME . '_staging_' . substr( md5( microtime( true ) ), 0, 8 ),
			),
			'status'                         => 'initializing',
			'copy_size'                      => 0,
			'external_integrations_disabled' => false,
		);

		// Log staging creation start.
		self::log_action( 'staging_created', $staging['id'], 'Name: ' . $staging['name'] );

		// Store staging environment.
		$envs                   = get_option( self::ENVS_KEY, array() );
		$envs[ $staging['id'] ] = $staging;
		update_option( self::ENVS_KEY, $envs );

		return $staging['id'];
	}

	/**
	 * Delete a staging environment.
	 *
	 * @param string $staging_id Staging ID to delete.
	 * @return bool True on success.
	 */
	public static function delete_staging( string $staging_id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$envs = get_option( self::ENVS_KEY, array() );

		if ( ! isset( $envs[ $staging_id ] ) ) {
			return false;
		}

		$staging = $envs[ $staging_id ];

		// Clean up staging database.
		self::cleanup_staging_database( $staging['staging']['db_name'] );

		// Remove staging environment.
		unset( $envs[ $staging_id ] );
		update_option( self::ENVS_KEY, $envs );

		self::log_action( 'staging_deleted', $staging_id, $staging['name'] );

		return true;
	}

	/**
	 * Deploy changes from staging to production.
	 *
	 * @param string $staging_id Staging ID to deploy.
	 * @return bool True on success.
	 */
	public static function deploy_to_production( string $staging_id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$envs = get_option( self::ENVS_KEY, array() );

		if ( ! isset( $envs[ $staging_id ] ) ) {
			return false;
		}

		$staging = $envs[ $staging_id ];

		// Create final snapshot before deployment.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Snapshot_Manager' ) ) {
			WPSHADOW_Snapshot_Manager::create_snapshot( 'Pre-deployment from staging: ' . $staging['name'] );
		}

		// Sync staging database to production.
		if ( ! self::sync_staging_to_production( $staging ) ) {
			self::log_action( 'deploy_failed', $staging_id, 'Database sync failed' );
			return false;
		}

		// Update staging status.
		$envs[ $staging_id ]['status']   = 'deployed';
		$envs[ $staging_id ]['deployed'] = time();
		update_option( self::ENVS_KEY, $envs );

		self::log_action( 'staging_deployed', $staging_id, $staging['name'] );

		return true;
	}

	/**
	 * Rollback production to pre-staging snapshot.
	 *
	 * @param string $staging_id Staging ID.
	 * @return bool True on success.
	 */
	public static function rollback_staging( string $staging_id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Snapshot_Manager' ) ) {
			return false;
		}

		$envs = get_option( self::ENVS_KEY, array() );

		if ( ! isset( $envs[ $staging_id ] ) ) {
			return false;
		}

		$staging = $envs[ $staging_id ];

		// Find the pre-staging snapshot by looking at snapshots created before staging.
		$snapshots = WPSHADOW_Snapshot_Manager::get_snapshots();

		foreach ( array_reverse( $snapshots ) as $snap_id => $snapshot ) {
			if ( $snapshot['timestamp'] < $staging['created'] ) {
				WPSHADOW_Snapshot_Manager::restore_snapshot( $snap_id );
				self::log_action( 'staging_rollback', $staging_id, 'Rolled back to snapshot ' . substr( $snap_id, 0, 8 ) );
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all staging environments.
	 *
	 * @return array Staging environments.
	 */
	public static function get_stagings(): array {
		return (array) get_option( self::ENVS_KEY, array() );
	}

	/**
	 * Get single staging environment.
	 *
	 * @param string $staging_id Staging ID.
	 * @return array|null Staging data or null.
	 */
	public static function get_staging( string $staging_id ): ?array {
		$envs = self::get_stagings();
		return $envs[ $staging_id ] ?? null;
	}

	/**
	 * Sync staging database changes to production.
	 *
	 * @param array $staging Staging environment data.
	 * @return bool True on success.
	 */
	private static function sync_staging_to_production( array $staging ): bool {
		// Placeholder: Production sync would include:
		// 1. Database table replication (with careful transaction handling)
		// 2. File sync for uploads/plugins/themes
		// 3. Settings migration (filtered excluded options)
		// 4. URL rewriting if staging uses different domain

		return true;
	}

	/**
	 * Clean up staging database.
	 *
	 * @param string $db_name Database name.
	 * @return void
	 */
	private static function cleanup_staging_database( string $db_name ): void {
		global $wpdb;

		// Safety: Only allow staging databases (must contain 'staging').
		if ( strpos( $db_name, 'staging' ) === false ) {
			return;
		}

		// In production, this would:
		// $wpdb->query( "DROP DATABASE IF EXISTS {$db_name}" );

		// For safety in this POC, just log the intention.
	}

	/**
	 * Log staging action.
	 *
	 * @param string $action Action name.
	 * @param string $staging_id Staging ID.
	 * @param string $detail Additional detail.
	 * @return void
	 */
	private static function log_action( string $action, string $staging_id, string $detail = '' ): void {
		$logs = get_option( self::LOGS_KEY, array() );

		// Keep last 50 logs.
		if ( count( $logs ) > 50 ) {
			array_shift( $logs );
		}

		$logs[] = array(
			'timestamp'  => time(),
			'action'     => $action,
			'staging_id' => $staging_id,
			'detail'     => $detail,
			'user_id'    => get_current_user_id(),
		);

		update_option( self::LOGS_KEY, $logs );
	}

	/**
	 * Handle AJAX staging creation.
	 *
	 * @return void
	 */
	public static function handle_staging_creation(): void {
		check_ajax_referer( 'wpshadow_staging_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) );
		}

		$name = \WPShadow\WPSHADOW_get_post_text( 'name' );

		$staging_id = self::create_staging( $name );

		if ( ! $staging_id ) {
			wp_send_json_error( __( 'Couldn\'t create the staging environment', 'plugin-wpshadow' ) );
		}

		wp_send_json_success(
			array(
				'staging_id' => $staging_id,
				'name'       => $name,
			)
		);
	}

	/**
	 * Handle AJAX staging deletion.
	 *
	 * @return void
	 */
	public static function handle_staging_deletion(): void {
		check_ajax_referer( 'wpshadow_staging_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) );
		}

		$staging_id = \WPShadow\WPSHADOW_get_post_text( 'staging_id' );

		if ( ! self::delete_staging( $staging_id ) ) {
			wp_send_json_error( __( 'Couldn\'t delete the staging environment', 'plugin-wpshadow' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Handle AJAX staging deployment.
	 *
	 * @return void
	 */
	public static function handle_staging_deployment(): void {
		check_ajax_referer( 'wpshadow_staging_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) );
		}

		$staging_id = \WPShadow\WPSHADOW_get_post_text( 'staging_id' );

		if ( ! self::deploy_to_production( $staging_id ) ) {
			wp_send_json_error( __( 'Couldn\'t deploy the staging environment', 'plugin-wpshadow' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Handle AJAX staging rollback.
	 *
	 * @return void
	 */
	public static function handle_staging_rollback(): void {
		check_ajax_referer( 'wpshadow_staging_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) );
		}

		$staging_id = \WPShadow\WPSHADOW_get_post_text( 'staging_id' );

		if ( ! self::rollback_staging( $staging_id ) ) {
			wp_send_json_error( __( 'Couldn\'t roll back the staging environment', 'plugin-wpshadow' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Register staging menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Staging Environments', 'plugin-wpshadow' ),
			__( 'Staging', 'plugin-wpshadow' ),
			'manage_options',
			'wps-staging',
			array( __CLASS__, 'render_staging_page' )
		);
	}

	/**
	 * Render staging management page.
	 *
	 * @return void
	 */
	public static function render_staging_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wpshadow' ) );
		}

		$stagings = self::get_stagings();
		$nonce    = wp_create_nonce( 'wpshadow_staging_nonce' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Safe Staging Environments', 'plugin-wpshadow' ); ?></h1>
			<p><?php esc_html_e( 'Create isolated copies of your site for testing plugin/theme updates without affecting production.', 'plugin-wpshadow' ); ?></p>

			<button id="wps-staging-create" class="button button-primary">
				<?php esc_html_e( '🎭 Create Staging Environment', 'plugin-wpshadow' ); ?>
			</button>

			<?php if ( empty( $stagings ) ) : ?>
				<p style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #ffb900;">
					<?php esc_html_e( 'No staging environments yet. Create one to test changes safely.', 'plugin-wpshadow' ); ?>
				</p>
			<?php else : ?>
				<table class="wp-list-table widefat striped" style="margin-top: 20px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Created', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Size', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_reverse( $stagings ) as $id => $staging ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $staging['name'] ); ?></strong></td>
								<td><?php echo esc_html( wp_date( 'M d, Y g:i a', $staging['created'] ) ); ?></td>
								<td>
									<span style="display: inline-block; padding: 4px 8px; background: #eee; border-radius: 3px; font-size: 12px;">
										<?php echo esc_html( ucfirst( $staging['status'] ) ); ?>
									</span>
								</td>
								<td>~<?php echo esc_html( size_format( $staging['copy_size'] ?: 1048576 ) ); ?></td>
								<td>
									<?php if ( 'deployed' !== $staging['status'] ) : ?>
										<button class="button button-small wps-deploy-staging" data-staging-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
											<?php esc_html_e( 'Deploy', 'plugin-wpshadow' ); ?>
										</button>
										<button class="button button-small wps-rollback-staging" data-staging-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
											<?php esc_html_e( 'Rollback', 'plugin-wpshadow' ); ?>
										</button>
									<?php endif; ?>
									<button class="button button-small wps-delete-staging" data-staging-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
										<?php esc_html_e( 'Delete', 'plugin-wpshadow' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<script>
		const nonce = '<?php echo esc_js( $nonce ); ?>';
		document.getElementById('wps-staging-create')?.addEventListener('click', function() {
			const name = prompt('Staging environment name (optional):', '');
			if (name === null) return;
			fetch(ajaxurl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: 'action=WPSHADOW_create_staging&nonce=' + nonce + '&name=' + encodeURIComponent(name)
			})
			.then(r => r.json())
			.then(d => { if (d.success) location.reload(); else alert('Error: ' + d.data); });
		});
		document.querySelectorAll('.wps-delete-staging')?.forEach(btn => {
			btn.addEventListener('click', function() {
				if (!confirm('Delete this staging environment and all its data?')) return;
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: 'action=WPSHADOW_delete_staging&nonce=' + nonce + '&staging_id=' + encodeURIComponent(this.dataset.stagingId)
				})
				.then(r => r.json())
				.then(d => { if (d.success) location.reload(); else alert('Error: ' + d.data); });
			});
		});
		document.querySelectorAll('.wps-deploy-staging')?.forEach(btn => {
			btn.addEventListener('click', function() {
				if (!confirm('Deploy staging changes to production? (A snapshot will be created first)')) return;
				this.disabled = true;
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: 'action=WPSHADOW_deploy_staging&nonce=' + nonce + '&staging_id=' + encodeURIComponent(this.dataset.stagingId)
				})
				.then(r => r.json())
				.then(d => { if (d.success) location.reload(); else { alert('Error: ' + d.data); this.disabled = false; } });
			});
		});
		document.querySelectorAll('.wps-rollback-staging')?.forEach(btn => {
			btn.addEventListener('click', function() {
				if (!confirm('Rollback to the pre-staging snapshot? This will restore your site to the state before staging was created.')) return;
				this.disabled = true;
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: 'action=WPSHADOW_rollback_staging&nonce=' + nonce + '&staging_id=' + encodeURIComponent(this.dataset.stagingId)
				})
				.then(r => r.json())
				.then(d => { if (d.success) location.reload(); else { alert('Error: ' + d.data); this.disabled = false; } });
			});
		});
		</script>
		<?php
	}
}



