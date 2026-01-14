<?php
/**
 * Site Snapshot Manager - Versioning and rollback capability.
 *
 * Captures site state (plugins, theme, database metadata) for rollback.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Snapshot Manager Class
 */
class WPS_Snapshot_Manager {

	/**
	 * Maximum snapshots to keep (default 10).
	 */
	private const MAX_SNAPSHOTS = 10;

	/**
	 * Snapshot option key.
	 */
	private const SNAPSHOTS_KEY = 'WPS_site_snapshots';

	/**
	 * Initialize snapshot manager.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Auto-snapshot before plugin updates.
		add_action( 'upgrader_process_complete', array( __CLASS__, 'maybe_create_snapshot' ), 10, 2 );
		add_action( 'switch_theme', array( __CLASS__, 'create_snapshot_before_theme_switch' ) );

		// Register admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

	/**
	 * Create a snapshot manually or automatically.
	 *
	 * @param string $description Optional snapshot description.
	 * @return string|false Snapshot ID or false on failure.
	 */
	public static function create_snapshot( string $description = '' ): string|false {
		$snapshot = array(
			'id'          => wp_generate_uuid4(),
			'timestamp'   => time(),
			'description' => sanitize_text_field( $description ),
			'wordpress'   => get_bloginfo( 'version' ),
			'php'         => phpversion(),
			'mysql'       => self::get_mysql_version(),
			'plugins'     => self::capture_plugin_state(),
			'theme'       => self::capture_theme_state(),
			'metrics'     => self::capture_site_metrics(),
		);

		$snapshots                    = get_option( self::SNAPSHOTS_KEY, array() );
		$snapshots[ $snapshot['id'] ] = $snapshot;

		// Keep only last N snapshots.
		if ( count( $snapshots ) > self::MAX_SNAPSHOTS ) {
			// Remove oldest.
			$oldest_id = array_key_first( $snapshots );
			unset( $snapshots[ $oldest_id ] );
		}

		update_option( self::SNAPSHOTS_KEY, $snapshots );

		// Log action.
		self::log_snapshot_action( 'created', $snapshot['id'], $description );

		return $snapshot['id'];
	}

	/**
	 * Get all snapshots.
	 *
	 * @return array Snapshots array.
	 */
	public static function get_snapshots(): array {
		return (array) get_option( self::SNAPSHOTS_KEY, array() );
	}

	/**
	 * Get single snapshot.
	 *
	 * @param string $snapshot_id Snapshot ID.
	 * @return array|null Snapshot data or null.
	 */
	public static function get_snapshot( string $snapshot_id ): ?array {
		$snapshots = self::get_snapshots();
		return $snapshots[ $snapshot_id ] ?? null;
	}

	/**
	 * Restore a snapshot.
	 *
	 * @param string $snapshot_id Snapshot ID to restore.
	 * @return bool True on success.
	 */
	public static function restore_snapshot( string $snapshot_id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$snapshot = self::get_snapshot( $snapshot_id );
		if ( ! $snapshot ) {
			return false;
		}

		// Create backup of current state before restoring.
		self::create_snapshot( 'Backup before restore to ' . wp_date( 'Y-m-d H:i:s', $snapshot['timestamp'] ) );

		// Deactivate plugins not in snapshot.
		$current_plugins  = get_option( 'active_plugins', array() );
		$snapshot_plugins = array_keys( $snapshot['plugins']['active'] );

		foreach ( $current_plugins as $plugin ) {
			if ( ! in_array( $plugin, $snapshot_plugins, true ) ) {
				deactivate_plugins( $plugin );
			}
		}

		// Activate plugins from snapshot.
		foreach ( $snapshot_plugins as $plugin ) {
			if ( ! in_array( $plugin, $current_plugins, true ) && is_plugin_available( $plugin ) ) {
				activate_plugin( $plugin );
			}
		}

		// Switch theme if different.
		if ( $snapshot['theme']['stylesheet'] !== get_stylesheet() ) {
			switch_theme( $snapshot['theme']['stylesheet'] );
		}

		self::log_snapshot_action( 'restored', $snapshot_id, 'Restored to ' . wp_date( 'Y-m-d H:i:s', $snapshot['timestamp'] ) );

		return true;
	}

	/**
	 * Compare two snapshots.
	 *
	 * @param string $snapshot_id_1 First snapshot ID.
	 * @param string $snapshot_id_2 Second snapshot ID.
	 * @return array Comparison data.
	 */
	public static function compare_snapshots( string $snapshot_id_1, string $snapshot_id_2 ): array {
		$snap1 = self::get_snapshot( $snapshot_id_1 );
		$snap2 = self::get_snapshot( $snapshot_id_2 );

		if ( ! $snap1 || ! $snap2 ) {
			return array();
		}

		$comparison = array(
			'date1'       => wp_date( 'Y-m-d H:i:s', $snap1['timestamp'] ),
			'date2'       => wp_date( 'Y-m-d H:i:s', $snap2['timestamp'] ),
			'versions'    => array(
				'wordpress' => array(
					'before'  => $snap1['wordpress'],
					'after'   => $snap2['wordpress'],
					'changed' => $snap1['wordpress'] !== $snap2['wordpress'],
				),
				'php'       => array(
					'before'  => $snap1['php'],
					'after'   => $snap2['php'],
					'changed' => $snap1['php'] !== $snap2['php'],
				),
			),
			'plugins'     => self::compare_plugin_changes( $snap1['plugins'], $snap2['plugins'] ),
			'theme'       => array(
				'changed' => $snap1['theme']['stylesheet'] !== $snap2['theme']['stylesheet'],
				'before'  => $snap1['theme']['name'] ?? $snap1['theme']['stylesheet'],
				'after'   => $snap2['theme']['name'] ?? $snap2['theme']['stylesheet'],
			),
			'performance' => array(
				'load_time_before' => $snap1['metrics']['estimated_load_time'] ?? 'N/A',
				'load_time_after'  => $snap2['metrics']['estimated_load_time'] ?? 'N/A',
				'database_before'  => $snap1['metrics']['database_size'] ?? 'N/A',
				'database_after'   => $snap2['metrics']['database_size'] ?? 'N/A',
			),
		);

		return $comparison;
	}

	/**
	 * Delete a snapshot.
	 *
	 * @param string $snapshot_id Snapshot ID to delete.
	 * @return bool True on success.
	 */
	public static function delete_snapshot( string $snapshot_id ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$snapshots = self::get_snapshots();
		if ( ! isset( $snapshots[ $snapshot_id ] ) ) {
			return false;
		}

		unset( $snapshots[ $snapshot_id ] );
		update_option( self::SNAPSHOTS_KEY, $snapshots );

		self::log_snapshot_action( 'deleted', $snapshot_id );

		return true;
	}

	/**
	 * Capture plugin state.
	 *
	 * @return array Plugin state data.
	 */
	private static function capture_plugin_state(): array {
		$active  = get_option( 'active_plugins', array() );
		$plugins = get_plugins();
		$state   = array(
			'active'   => array(),
			'inactive' => array(),
			'all'      => array(),
		);

		foreach ( $plugins as $plugin_path => $plugin_data ) {
			$state['all'][ $plugin_path ] = array(
				'name'    => $plugin_data['Name'] ?? '',
				'version' => $plugin_data['Version'] ?? '0',
				'active'  => in_array( $plugin_path, $active, true ),
			);

			if ( in_array( $plugin_path, $active, true ) ) {
				$state['active'][ $plugin_path ] = array(
					'name'    => $plugin_data['Name'] ?? '',
					'version' => $plugin_data['Version'] ?? '0',
				);
			} else {
				$state['inactive'][ $plugin_path ] = array(
					'name'    => $plugin_data['Name'] ?? '',
					'version' => $plugin_data['Version'] ?? '0',
				);
			}
		}

		return $state;
	}

	/**
	 * Capture theme state.
	 *
	 * @return array Theme state data.
	 */
	private static function capture_theme_state(): array {
		$current_theme = wp_get_theme();

		return array(
			'stylesheet' => get_stylesheet(),
			'name'       => $current_theme->get( 'Name' ),
			'version'    => $current_theme->get( 'Version' ),
		);
	}

	/**
	 * Capture site metrics.
	 *
	 * @return array Site metrics.
	 */
	private static function capture_site_metrics(): array {
		global $wpdb;

		$db_size = 0;
		$tables  = $wpdb->get_results( 'SHOW TABLE STATUS', OBJECT_K );

		if ( $tables ) {
			foreach ( $tables as $table ) {
				$db_size += (int) ( $table->Data_length ?? 0 ) + (int) ( $table->Index_length ?? 0 );
			}
		}

		return array(
			'database_size'       => size_format( $db_size ),
			'plugin_count'        => count( get_option( 'active_plugins', array() ) ),
			'post_count'          => wp_count_posts()->publish ?? 0,
			'estimated_load_time' => 'pending',
		);
	}

	/**
	 * Compare plugin changes between snapshots.
	 *
	 * @param array $plugins1 First snapshot plugins.
	 * @param array $plugins2 Second snapshot plugins.
	 * @return array Changes.
	 */
	private static function compare_plugin_changes( array $plugins1, array $plugins2 ): array {
		$changes = array(
			'added'       => array(),
			'removed'     => array(),
			'updated'     => array(),
			'activated'   => array(),
			'deactivated' => array(),
		);

		// Find added/updated/removed plugins.
		foreach ( $plugins2['all'] as $path => $data ) {
			if ( ! isset( $plugins1['all'][ $path ] ) ) {
				$changes['added'][ $path ] = $data;
			} elseif ( $plugins1['all'][ $path ]['version'] !== $data['version'] ) {
				$changes['updated'][ $path ] = array(
					'from' => $plugins1['all'][ $path ]['version'],
					'to'   => $data['version'],
				);
			}
		}

		// Find removed plugins.
		foreach ( $plugins1['all'] as $path => $data ) {
			if ( ! isset( $plugins2['all'][ $path ] ) ) {
				$changes['removed'][ $path ] = $data;
			}
		}

		// Find activation/deactivation changes.
		foreach ( $plugins1['active'] as $path => $data ) {
			if ( ! isset( $plugins2['active'][ $path ] ) ) {
				$changes['deactivated'][ $path ] = $data;
			}
		}

		foreach ( $plugins2['active'] as $path => $data ) {
			if ( ! isset( $plugins1['active'][ $path ] ) ) {
				$changes['activated'][ $path ] = $data;
			}
		}

		return $changes;
	}

	/**
	 * Maybe auto-create snapshot before upgrader process.
	 *
	 * @param object $upgrader Upgrader instance.
	 * @param array  $options  Upgrader options.
	 * @return void
	 */
	public static function maybe_create_snapshot( object $upgrader, array $options ): void {
		if ( ! isset( $options['action'] ) || 'update' !== $options['action'] ) {
			return;
		}

		if ( ! isset( $options['type'] ) ) {
			return;
		}

		$type = $options['type'];

		if ( 'plugin' === $type || 'theme' === $type ) {
			$description = ucfirst( $type ) . ' update: ' . ( $options['bulk'] ? 'bulk' : 'single' );
			self::create_snapshot( $description );
		}
	}

	/**
	 * Create snapshot before theme switch.
	 *
	 * @return void
	 */
	public static function create_snapshot_before_theme_switch(): void {
		self::create_snapshot( 'Theme switch' );
	}

	/**
	 * Register snapshots menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Site Snapshots', 'plugin-wp-support-thisismyurl' ),
			__( 'Snapshots', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-snapshots',
			array( __CLASS__, 'render_snapshots_page' )
		);
	}

	/**
	 * Render snapshots management page.
	 *
	 * @return void
	 */
	public static function render_snapshots_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$snapshots = self::get_snapshots();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Snapshots', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p><?php esc_html_e( 'Snapshots capture your site state for rollback capability.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<button id="wps-snapshot-create" class="button button-primary">
				<?php esc_html_e( '📸 Create Snapshot Now', 'plugin-wp-support-thisismyurl' ); ?>
			</button>

			<?php if ( empty( $snapshots ) ) : ?>
				<p style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #ffb900;">
					<?php esc_html_e( 'No snapshots yet. Create one to enable rollback capability.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			<?php else : ?>
				<table class="wp-list-table widefat striped" style="margin-top: 20px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Description', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Plugins', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Theme', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Database', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wp-support-thisismyurl' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_reverse( $snapshots ) as $id => $snapshot ) : ?>
							<tr>
								<td><?php echo esc_html( wp_date( 'M d, Y g:i a', $snapshot['timestamp'] ) ); ?></td>
								<td><?php echo esc_html( $snapshot['description'] ); ?></td>
								<td><?php echo intval( count( $snapshot['plugins']['active'] ) ); ?> active</td>
								<td><?php echo esc_html( $snapshot['theme']['name'] ?? 'Unknown' ); ?></td>
								<td><?php echo esc_html( $snapshot['metrics']['database_size'] ); ?></td>
								<td>
									<button class="button button-small wps-restore-snapshot" data-snapshot-id="<?php echo esc_attr( $id ); ?>">
										<?php esc_html_e( 'Restore', 'plugin-wp-support-thisismyurl' ); ?>
									</button>
									<button class="button button-small wps-delete-snapshot" data-snapshot-id="<?php echo esc_attr( $id ); ?>">
										<?php esc_html_e( 'Delete', 'plugin-wp-support-thisismyurl' ); ?>
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
	 * Log snapshot action.
	 *
	 * @param string $action Action name.
	 * @param string $snapshot_id Snapshot ID.
	 * @param string $description Optional description.
	 * @return void
	 */
	private static function log_snapshot_action( string $action, string $snapshot_id, string $description = '' ): void {
		$log_message = sprintf(
			'[SNAPSHOT] Action: %s | Snapshot ID: %s | User: %s | Description: %s',
			$action,
			$snapshot_id,
			get_current_user_id(),
			$description
		);
	}

	/**
	 * Get MySQL version.
	 *
	 * @return string MySQL version.
	 */
	private static function get_mysql_version(): string {
		global $wpdb;
		$version = $wpdb->db_version();
		return is_string( $version ) ? $version : '5.7';
	}
}


