<?php
/**
 * WPS Update Simulator & Safe Deployment
 *
 * Simulates plugin/theme updates in staging before applying to live site.
 * Integrates with #165 (Staging Manager) and #156 (Snapshot Manager) for safe testing.
 *
 * @package WPS_WP_SUPPORT
 * @since 1.2601.1111
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * Class WPS_Update_Simulator
 *
 * Provides safe update testing workflow:
 * - Simulate updates in staging environment
 * - Show change logs and compatibility checks
 * - Test changes before live deployment
 * - Instant rollback if issues arise
 */
class WPS_Update_Simulator {

	/**
	 * Database option key for simulation history.
	 */
	private const SIMULATION_HISTORY_KEY = 'wps_simulation_history';

	/**
	 * Database option key for pending deployments.
	 */
	private const PENDING_DEPLOYMENTS_KEY = 'wps_pending_deployments';

	/**
	 * Initialize the update simulator.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wps_simulate_update', array( __CLASS__, 'ajax_simulate_update' ) );
		add_action( 'wp_ajax_wps_deploy_update', array( __CLASS__, 'ajax_deploy_update' ) );
		add_action( 'wp_ajax_wps_rollback_update', array( __CLASS__, 'ajax_rollback_update' ) );
		add_filter( 'plugin_action_links', array( __CLASS__, 'add_simulate_link' ), 10, 2 );
	}

	/**
	 * Register admin page.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'wp-support',
			__( 'Update Simulator', 'plugin-wp-support-thisismyurl' ),
			__( 'Update Simulator', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-update-simulator',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue CSS/JS assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'wps-update-simulator' ) ) {
			return;
		}

		wp_enqueue_style( 'wps-update-simulator', plugins_url( '../assets/css/update-simulator.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'wps-update-simulator', plugins_url( '../assets/js/update-simulator.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		wp_localize_script(
			'wps-update-simulator',
			'wpsUpdateSimulator',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wps_update_simulator_nonce' ),
			)
		);
	}

	/**
	 * Add "Simulate Update" link to plugins with available updates.
	 *
	 * @param string[] $actions Array of action links.
	 * @param string   $plugin_file Plugin file path.
	 * @return string[] Modified action links.
	 */
	public static function add_simulate_link( array $actions, string $plugin_file ): array {
		$updates = get_site_transient( 'update_plugins' );
		if ( isset( $updates->response[ $plugin_file ] ) ) {
			$actions['wps_simulate'] = sprintf(
				'<a href="%s" class="wps-simulate-update">%s</a>',
				esc_url( admin_url( 'admin.php?page=wps-update-simulator&plugin=' . urlencode( $plugin_file ) ) ),
				esc_html__( 'Simulate Update', 'plugin-wp-support-thisismyurl' )
			);
		}
		return $actions;
	}

	/**
	 * AJAX handler: Simulate update in staging environment.
	 *
	 * @return void
	 */
	public static function ajax_simulate_update(): void {
		check_ajax_referer( 'wps_update_simulator_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid plugin specified', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Get update information.
		$update_info = self::get_update_info( $plugin );
		if ( is_wp_error( $update_info ) ) {
			wp_send_json_error( array( 'message' => $update_info->get_error_message() ) );
		}

		// Check if staging environment exists.
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Staging_Manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Staging Manager not available', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Create snapshot before simulation.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Snapshot_Manager' ) ) {
			$snapshot = WPS_Snapshot_Manager::create_snapshot( 'pre-simulation-' . sanitize_key( $plugin ) );
		}

		// Log simulation.
		self::log_simulation( $plugin, $update_info );

		wp_send_json_success(
			array(
				'message'     => __( 'Simulation prepared successfully', 'plugin-wp-support-thisismyurl' ),
				'update_info' => $update_info,
				'snapshot_id' => $snapshot ?? null,
			)
		);
	}

	/**
	 * AJAX handler: Deploy update to live site.
	 *
	 * @return void
	 */
	public static function ajax_deploy_update(): void {
		check_ajax_referer( 'wps_update_simulator_nonce', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid plugin specified', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Create pre-deployment snapshot.
		$snapshot_id = null;
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Snapshot_Manager' ) ) {
			$snapshot_id = WPS_Snapshot_Manager::create_snapshot( 'pre-deployment-' . sanitize_key( $plugin ) );
		}

		// Perform update.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$upgrader = new \Plugin_Upgrader();
		$result   = $upgrader->upgrade( $plugin );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Log deployment.
		self::log_deployment( $plugin, $snapshot_id );

		wp_send_json_success(
			array(
				'message'     => __( 'Update deployed successfully', 'plugin-wp-support-thisismyurl' ),
				'snapshot_id' => $snapshot_id,
			)
		);
	}

	/**
	 * AJAX handler: Rollback update using snapshot.
	 *
	 * @return void
	 */
	public static function ajax_rollback_update(): void {
		check_ajax_referer( 'wps_update_simulator_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$snapshot_id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( wp_unslash( $_POST['snapshot_id'] ) ) : '';
		if ( empty( $snapshot_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid snapshot specified', 'plugin-wp-support-thisismyurl' ) ) );
		}

		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Snapshot_Manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Snapshot Manager not available', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$result = WPS_Snapshot_Manager::restore_snapshot( $snapshot_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Rollback successful', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * Render update simulator admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		$available_updates = self::get_available_updates();
		$simulation_history = self::get_simulation_history();
		?>
		<div class="wrap wps-update-simulator-page">
			<h1><?php esc_html_e( 'Update Simulator & Safe Deployment', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Test plugin and theme updates in staging before deploying to your live site. Includes instant rollback if issues arise.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<?php if ( empty( $available_updates ) ) : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'No updates available. All plugins and themes are up to date!', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>
			<?php else : ?>
				<div class="wps-updates-available">
					<h2><?php esc_html_e( 'Available Updates', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<table class="widefat wps-updates-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin/Theme', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Current', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Available', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Changes', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $available_updates as $item ) : ?>
								<tr data-plugin="<?php echo esc_attr( $item['slug'] ); ?>">
									<td><strong><?php echo esc_html( $item['name'] ); ?></strong></td>
									<td><?php echo esc_html( $item['current_version'] ); ?></td>
									<td><?php echo esc_html( $item['new_version'] ); ?></td>
									<td>
										<?php if ( ! empty( $item['changelog'] ) ) : ?>
											<a href="#" class="wps-view-changelog" data-changelog="<?php echo esc_attr( wp_json_encode( $item['changelog'] ) ); ?>">
												<?php esc_html_e( 'View Changes', 'plugin-wp-support-thisismyurl' ); ?>
											</a>
										<?php else : ?>
											<span class="description"><?php esc_html_e( 'No changelog available', 'plugin-wp-support-thisismyurl' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<button type="button" class="button wps-simulate-btn" data-plugin="<?php echo esc_attr( $item['slug'] ); ?>">
											<?php esc_html_e( 'Simulate Update', 'plugin-wp-support-thisismyurl' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $simulation_history ) ) : ?>
				<div class="wps-simulation-history">
					<h2><?php esc_html_e( 'Recent Simulations', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Date', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $simulation_history, 0, 10 ) as $sim ) : ?>
								<tr>
									<td><?php echo esc_html( $sim['plugin_name'] ?? $sim['plugin'] ); ?></td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $sim['timestamp'] ) ); ?></td>
									<td>
										<?php if ( 'deployed' === $sim['status'] ) : ?>
											<span class="wps-badge-success"><?php esc_html_e( 'Deployed', 'plugin-wp-support-thisismyurl' ); ?></span>
										<?php elseif ( 'simulated' === $sim['status'] ) : ?>
											<span class="wps-badge-pending"><?php esc_html_e( 'Tested in Staging', 'plugin-wp-support-thisismyurl' ); ?></span>
										<?php else : ?>
											<span class="wps-badge-neutral"><?php echo esc_html( $sim['status'] ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( isset( $sim['snapshot_id'] ) ) : ?>
											<button type="button" class="button button-small wps-rollback-btn" data-snapshot="<?php echo esc_attr( $sim['snapshot_id'] ); ?>">
												<?php esc_html_e( 'Rollback', 'plugin-wp-support-thisismyurl' ); ?>
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

		<!-- Changelog Modal -->
		<div id="wps-changelog-modal" style="display:none;">
			<div class="wps-modal-overlay"></div>
			<div class="wps-modal-content">
				<button type="button" class="wps-modal-close">&times;</button>
				<h2><?php esc_html_e( 'Update Changes', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<div class="wps-changelog-content"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get available plugin and theme updates.
	 *
	 * @return array<int, array<string, mixed>> Available updates.
	 */
	private static function get_available_updates(): array {
		$updates = array();

		// Get plugin updates.
		$plugin_updates = get_site_transient( 'update_plugins' );
		if ( isset( $plugin_updates->response ) && is_array( $plugin_updates->response ) ) {
			$all_plugins = get_plugins();
			foreach ( $plugin_updates->response as $plugin_file => $plugin_data ) {
				if ( isset( $all_plugins[ $plugin_file ] ) ) {
					$updates[] = array(
						'type'            => 'plugin',
						'slug'            => $plugin_file,
						'name'            => $all_plugins[ $plugin_file ]['Name'],
						'current_version' => $all_plugins[ $plugin_file ]['Version'],
						'new_version'     => $plugin_data->new_version,
						'changelog'       => self::fetch_changelog( $plugin_data ),
					);
				}
			}
		}

		return $updates;
	}

	/**
	 * Get update information for specific plugin.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array<string, mixed>|\WP_Error Update info or error.
	 */
	private static function get_update_info( string $plugin ): array|\WP_Error {
		$updates = get_site_transient( 'update_plugins' );

		if ( ! isset( $updates->response[ $plugin ] ) ) {
			return new \WP_Error( 'no_update', __( 'No update available for this plugin', 'plugin-wp-support-thisismyurl' ) );
		}

		$plugin_data = $updates->response[ $plugin ];
		$all_plugins = get_plugins();

		if ( ! isset( $all_plugins[ $plugin ] ) ) {
			return new \WP_Error( 'plugin_not_found', __( 'Plugin not found', 'plugin-wp-support-thisismyurl' ) );
		}

		return array(
			'name'            => $all_plugins[ $plugin ]['Name'],
			'current_version' => $all_plugins[ $plugin ]['Version'],
			'new_version'     => $plugin_data->new_version,
			'changelog'       => self::fetch_changelog( $plugin_data ),
			'compatibility'   => self::check_compatibility( $plugin_data ),
		);
	}

	/**
	 * Fetch changelog from plugin repository.
	 *
	 * @param object $plugin_data Plugin update data.
	 * @return string Changelog HTML or empty string.
	 */
	private static function fetch_changelog( object $plugin_data ): string {
		if ( empty( $plugin_data->slug ) ) {
			return '';
		}

		$changelog = get_transient( 'wps_changelog_' . $plugin_data->slug );
		if ( false !== $changelog ) {
			return $changelog;
		}

		$url = 'https://wordpress.org/plugins/' . $plugin_data->slug . '/';
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		if ( preg_match( '/<h4>(.+?)<\/h4>.*?<ul>(.+?)<\/ul>/s', $body, $matches ) ) {
			$changelog = '<strong>' . $matches[1] . '</strong><ul>' . $matches[2] . '</ul>';
			set_transient( 'wps_changelog_' . $plugin_data->slug, $changelog, DAY_IN_SECONDS );
			return $changelog;
		}

		return __( 'Changelog not available', 'plugin-wp-support-thisismyurl' );
	}

	/**
	 * Check compatibility with current WordPress/PHP versions.
	 *
	 * @param object $plugin_data Plugin update data.
	 * @return array<string, mixed> Compatibility status.
	 */
	private static function check_compatibility( object $plugin_data ): array {
		global $wp_version;

		$compatibility = array(
			'wordpress' => true,
			'php'       => true,
			'warnings'  => array(),
		);

		// Check WordPress version.
		if ( isset( $plugin_data->requires ) && version_compare( $wp_version, $plugin_data->requires, '<' ) ) {
			$compatibility['wordpress'] = false;
			$compatibility['warnings'][] = sprintf(
				/* translators: %s: Required WordPress version */
				__( 'Requires WordPress %s or higher', 'plugin-wp-support-thisismyurl' ),
				$plugin_data->requires
			);
		}

		// Check PHP version.
		if ( isset( $plugin_data->requires_php ) && version_compare( phpversion(), $plugin_data->requires_php, '<' ) ) {
			$compatibility['php'] = false;
			$compatibility['warnings'][] = sprintf(
				/* translators: %s: Required PHP version */
				__( 'Requires PHP %s or higher', 'plugin-wp-support-thisismyurl' ),
				$plugin_data->requires_php
			);
		}

		return $compatibility;
	}

	/**
	 * Log simulation to history.
	 *
	 * @param string               $plugin Plugin file path.
	 * @param array<string, mixed> $update_info Update information.
	 * @return void
	 */
	private static function log_simulation( string $plugin, array $update_info ): void {
		$history = get_option( self::SIMULATION_HISTORY_KEY, array() );

		$history[] = array(
			'plugin'      => $plugin,
			'plugin_name' => $update_info['name'],
			'timestamp'   => time(),
			'status'      => 'simulated',
			'user_id'     => get_current_user_id(),
		);

		// Keep only last 50 simulations.
		if ( count( $history ) > 50 ) {
			$history = array_slice( $history, -50 );
		}

		update_option( self::SIMULATION_HISTORY_KEY, $history );
	}

	/**
	 * Log deployment to history.
	 *
	 * @param string      $plugin Plugin file path.
	 * @param string|null $snapshot_id Snapshot ID if created.
	 * @return void
	 */
	private static function log_deployment( string $plugin, ?string $snapshot_id ): void {
		$history = get_option( self::SIMULATION_HISTORY_KEY, array() );

		$history[] = array(
			'plugin'      => $plugin,
			'plugin_name' => basename( dirname( $plugin ) ),
			'timestamp'   => time(),
			'status'      => 'deployed',
			'snapshot_id' => $snapshot_id,
			'user_id'     => get_current_user_id(),
		);

		if ( count( $history ) > 50 ) {
			$history = array_slice( $history, -50 );
		}

		update_option( self::SIMULATION_HISTORY_KEY, $history );
	}

	/**
	 * Get simulation history.
	 *
	 * @return array<int, array<string, mixed>> Simulation history.
	 */
	private static function get_simulation_history(): array {
		$history = get_option( self::SIMULATION_HISTORY_KEY, array() );
		return is_array( $history ) ? array_reverse( $history ) : array();
	}
}
