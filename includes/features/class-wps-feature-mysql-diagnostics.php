<?php
/**
 * Feature: MySQL Diagnostics
 *
 * Displays MySQL/MariaDB database information:
 * - Database server version and configuration
 * - Database size and table statistics
 * - Query performance metrics
 * - InnoDB/MyISAM statistics
 * - Connection status
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_MySQL_Diagnostics
 *
 * MySQL database diagnostics and information viewer.
 */
final class WPSHADOW_Feature_MySQL_Diagnostics extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'mysql-diagnostics',
				'name'               => __( 'MySQL Diagnostics', 'plugin-wpshadow' ),
				'description'        => __( 'Views MySQL or MariaDB server information, table statistics, query performance metrics, and database configuration in a read-only interface. Shows version numbers, storage engine details, table sizes, index usage, and slow query patterns to help you understand database health, identify optimization opportunities, and troubleshoot performance issues without running risky commands or needing command-line access to the database.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'server-diagnostics',
				'widget_label'       => __( 'Server Diagnostics', 'plugin-wpshadow' ),
				'widget_description' => __( 'Server environment and configuration tools', 'plugin-wpshadow' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-database',
				'category'           => 'debugging',
				'priority'           => 25,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 25,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'MySQL Diagnostics', 'plugin-wpshadow' ),
			__( 'MySQL Info', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-mysql-diagnostics',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get database server information.
	 *
	 * @return array Database information.
	 */
	private function get_database_info(): array {
		global $wpdb;

		$info = array(
			'server_version' => '',
			'client_version' => '',
			'database_name'  => DB_NAME,
			'host'           => DB_HOST,
			'charset'        => $wpdb->charset,
			'collate'        => $wpdb->collate,
			'prefix'         => $wpdb->prefix,
			'server_type'    => 'MySQL',
		);

		// Get server version.
		$server_info = $wpdb->get_var( 'SELECT VERSION()' );
		if ( $server_info ) {
			$info['server_version'] = $server_info;
			
			// Detect MariaDB.
			if ( stripos( $server_info, 'mariadb' ) !== false ) {
				$info['server_type'] = 'MariaDB';
			}
		}

		// Get client version.
		if ( function_exists( 'mysqli_get_client_info' ) ) {
			$info['client_version'] = mysqli_get_client_info();
		}

		return $info;
	}

	/**
	 * Get database size statistics.
	 *
	 * @return array Database size information.
	 */
	private function get_database_size(): array {
		global $wpdb;

		$size_data = array(
			'total_size'  => 0,
			'data_size'   => 0,
			'index_size'  => 0,
			'table_count' => 0,
		);

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					SUM(data_length + index_length) as total_size,
					SUM(data_length) as data_size,
					SUM(index_length) as index_size,
					COUNT(*) as table_count
				FROM information_schema.TABLES
				WHERE table_schema = %s",
				DB_NAME
			),
			ARRAY_A
		);

		if ( ! empty( $results ) && is_array( $results[0] ) ) {
			$result = $results[0];
			$size_data['total_size']  = isset( $result['total_size'] ) ? (int) $result['total_size'] : 0;
			$size_data['data_size']   = isset( $result['data_size'] ) ? (int) $result['data_size'] : 0;
			$size_data['index_size']  = isset( $result['index_size'] ) ? (int) $result['index_size'] : 0;
			$size_data['table_count'] = isset( $result['table_count'] ) ? (int) $result['table_count'] : 0;
		}

		return $size_data;
	}

	/**
	 * Get table information.
	 *
	 * @return array Table statistics.
	 */
	private function get_table_info(): array {
		global $wpdb;

		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					table_name as name,
					engine,
					table_rows as rows,
					data_length as data_size,
					index_length as index_size,
					(data_length + index_length) as total_size
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name LIKE %s
				ORDER BY (data_length + index_length) DESC
				LIMIT 20",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_A
		);

		return is_array( $tables ) ? $tables : array();
	}

	/**
	 * Get database variables.
	 *
	 * @return array Important MySQL variables.
	 */
	private function get_database_variables(): array {
		global $wpdb;

		$important_vars = array(
			'max_allowed_packet',
			'max_connections',
			'wait_timeout',
			'innodb_buffer_pool_size',
			'innodb_log_file_size',
			'query_cache_size',
			'tmp_table_size',
			'max_heap_table_size',
		);

		$variables = array();
		foreach ( $important_vars as $var ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SHOW VARIABLES LIKE %s", $var ) );
			if ( $value !== null ) {
				$variables[ $var ] = $wpdb->get_var( $wpdb->prepare( "SHOW VARIABLES WHERE Variable_name = %s", $var ) );
			}
		}

		// Get actual values.
		$results = $wpdb->get_results( "SHOW VARIABLES", ARRAY_A );
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( isset( $row['Variable_name'], $row['Value'] ) && in_array( $row['Variable_name'], $important_vars, true ) ) {
					$variables[ $row['Variable_name'] ] = $row['Value'];
				}
			}
		}

		return $variables;
	}

	/**
	 * Get database status variables.
	 *
	 * @return array Status information.
	 */
	private function get_database_status(): array {
		global $wpdb;

		$important_status = array(
			'Uptime',
			'Threads_connected',
			'Questions',
			'Slow_queries',
			'Opens',
			'Flush_commands',
			'Open_tables',
			'Queries_per_second_avg',
		);

		$status = array();
		$results = $wpdb->get_results( "SHOW GLOBAL STATUS", ARRAY_A );
		
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( isset( $row['Variable_name'], $row['Value'] ) && in_array( $row['Variable_name'], $important_status, true ) ) {
					$status[ $row['Variable_name'] ] = $row['Value'];
				}
			}
		}

		return $status;
	}

	/**
	 * Format bytes to human readable size.
	 *
	 * @param int $bytes Size in bytes.
	 * @return string Formatted size.
	 */
	private function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		
		for ( $i = 0; $bytes > 1024 && $i < count( $units ) - 1; $i++ ) {
			$bytes /= 1024;
		}
		
		return round( $bytes, 2 ) . ' ' . $units[ $i ];
	}

	/**
	 * Render MySQL diagnostics page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$db_info   = $this->get_database_info();
		$db_size   = $this->get_database_size();
		$tables    = $this->get_table_info();
		$variables = $this->get_database_variables();
		$status    = $this->get_database_status();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'MySQL Diagnostics', 'plugin-wpshadow' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'Database Server Information', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'Server Type', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['server_type'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Server Version', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['server_version'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Client Version', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['client_version'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Database Name', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['database_name'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Database Host', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['host'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Table Prefix', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['prefix'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Character Set', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['charset'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Collation', 'plugin-wpshadow' ); ?></th>
							<td><code><?php echo esc_html( $db_info['collate'] ); ?></code></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Database Size', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'Total Size', 'plugin-wpshadow' ); ?></th>
							<td><strong><?php echo esc_html( $this->format_bytes( $db_size['total_size'] ) ); ?></strong></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Data Size', 'plugin-wpshadow' ); ?></th>
							<td><?php echo esc_html( $this->format_bytes( $db_size['data_size'] ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Index Size', 'plugin-wpshadow' ); ?></th>
							<td><?php echo esc_html( $this->format_bytes( $db_size['index_size'] ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Number of Tables', 'plugin-wpshadow' ); ?></th>
							<td><?php echo esc_html( number_format_i18n( $db_size['table_count'] ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Largest Tables (Top 20)', 'plugin-wpshadow' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 35%;"><?php esc_html_e( 'Table Name', 'plugin-wpshadow' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Engine', 'plugin-wpshadow' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Rows', 'plugin-wpshadow' ); ?></th>
							<th style="width: 15%;"><?php esc_html_e( 'Data Size', 'plugin-wpshadow' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Total Size', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $tables ) ) : ?>
							<?php foreach ( $tables as $table ) : ?>
								<tr>
									<td><code><?php echo esc_html( $table['name'] ); ?></code></td>
									<td><?php echo esc_html( $table['engine'] ); ?></td>
									<td><?php echo esc_html( number_format_i18n( (int) $table['rows'] ) ); ?></td>
									<td><?php echo esc_html( $this->format_bytes( (int) $table['data_size'] ) ); ?></td>
									<td><strong><?php echo esc_html( $this->format_bytes( (int) $table['total_size'] ) ); ?></strong></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5"><?php esc_html_e( 'No table information available.', 'plugin-wpshadow' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Important MySQL Variables', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<?php if ( ! empty( $variables ) ) : ?>
							<?php foreach ( $variables as $var_name => $var_value ) : ?>
								<tr>
									<th style="width: 40%;"><code><?php echo esc_html( $var_name ); ?></code></th>
									<td><code><?php echo esc_html( $var_value ); ?></code></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="2"><?php esc_html_e( 'Unable to retrieve MySQL variables.', 'plugin-wpshadow' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Database Status', 'plugin-wpshadow' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<?php if ( ! empty( $status ) ) : ?>
							<?php foreach ( $status as $status_name => $status_value ) : ?>
								<tr>
									<th style="width: 40%;"><code><?php echo esc_html( $status_name ); ?></code></th>
									<td>
										<code><?php echo esc_html( $status_value ); ?></code>
										<?php if ( 'Uptime' === $status_name ) : ?>
											<small>(<?php echo esc_html( human_time_diff( time() - (int) $status_value, time() ) ); ?>)</small>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="2"><?php esc_html_e( 'Unable to retrieve database status.', 'plugin-wpshadow' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
