<?php
/**
 * System Report Generator for comprehensive debug information.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * System Report Generator class.
 * Generates comprehensive system reports for support and debugging.
 */
class WPS_System_Report_Generator {

	/**
	 * Initialize the System Report Generator.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_generate_report', array( __CLASS__, 'ajax_generate_report' ) );
		add_action( 'wp_ajax_wps_create_shareable_link', array( __CLASS__, 'ajax_create_shareable_link' ) );

		// Public report viewing (with token).
		add_action( 'template_redirect', array( __CLASS__, 'handle_shareable_report' ) );

		// Cron for auto-delete expired links.
		add_action( 'wps_cleanup_expired_reports', array( __CLASS__, 'cleanup_expired_reports' ) );

		if ( ! wp_next_scheduled( 'wps_cleanup_expired_reports' ) ) {
			wp_schedule_event( time(), 'daily', 'wps_cleanup_expired_reports' );
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'System Report', 'plugin-wp-support-thisismyurl' ),
			__( 'System Report', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-system-report',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the System Report page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		wp_enqueue_script(
			'wps-system-report',
			wp_support_URL . 'assets/js/system-report.js',
			array( 'jquery' ),
			wp_support_VERSION,
			true
		);

		wp_localize_script(
			'wps-system-report',
			'wpsSystemReport',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wps_system_report' ),
				'strings' => array(
					'generating'   => __( 'Generating report...', 'plugin-wp-support-thisismyurl' ),
					'generated'    => __( 'Report generated successfully', 'plugin-wp-support-thisismyurl' ),
					'copied'       => __( 'Copied to clipboard', 'plugin-wp-support-thisismyurl' ),
					'copyFailed'   => __( 'Failed to copy', 'plugin-wp-support-thisismyurl' ),
					'creatingLink' => __( 'Creating shareable link...', 'plugin-wp-support-thisismyurl' ),
					'linkCreated'  => __( 'Link created successfully', 'plugin-wp-support-thisismyurl' ),
					'error'        => __( 'An error occurred', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);

		wp_enqueue_style(
			'wps-system-report',
			wp_support_URL . 'assets/css/system-report.css',
			array(),
			wp_support_VERSION
		);

		include wp_support_PATH . 'includes/views/system-report.php';
	}

	/**
	 * Collect all system information.
	 *
	 * @return array<string, mixed> System information array.
	 */
	public static function collect_system_info(): array {
		global $wpdb;

		$info = array(
			'generated_at' => current_time( 'mysql' ),
			'site_url'     => get_site_url(),
			'home_url'     => get_home_url(),
		);

		// WordPress/PHP/MySQL versions.
		$info['versions'] = self::get_versions();

		// Active theme.
		$info['theme'] = self::get_theme_info();

		// Plugins.
		$info['plugins'] = self::get_plugins_info();

		// Server config.
		$info['server'] = self::get_server_config();

		// Error log.

		// Database info.
		$info['database'] = self::get_database_info();

		// File permissions.
		$info['file_permissions'] = self::get_file_permissions();

		// Cron status.
		$info['cron'] = self::get_cron_status();

		// Rewrite rules.
		$info['rewrites'] = self::get_rewrite_status();

		// Multisite.
		if ( is_multisite() ) {
			$info['multisite'] = self::get_multisite_config();
		}

		// wp-config constants (sanitized).
		$info['constants'] = self::get_wp_config_constants();

		return $info;
	}

	/**
	 * Get WordPress, PHP, and MySQL versions.
	 *
	 * @return array<string, string>
	 */
	private static function get_versions(): array {
		global $wpdb, $wp_version;

		return array(
			'wordpress' => $wp_version,
			'php'       => PHP_VERSION,
			'mysql'     => $wpdb->db_version(),
			'server'    => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown',
		);
	}

	/**
	 * Get active theme information.
	 *
	 * @return array<string, string>
	 */
	private static function get_theme_info(): array {
		$theme = wp_get_theme();

		return array(
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
			'author'  => $theme->get( 'Author' ),
			'uri'     => $theme->get( 'ThemeURI' ),
		);
	}

	/**
	 * Get all plugins information.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function get_plugins_info(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_active = get_site_option( 'active_sitewide_plugins', array() );
			$network_active = array_keys( $network_active );
		} else {
			$network_active = array();
		}

		$plugins = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$is_active         = in_array( $plugin_path, $active_plugins, true );
			$is_network_active = in_array( $plugin_path, $network_active, true );

			$plugins[ $plugin_path ] = array(
				'name'           => $plugin_data['Name'],
				'version'        => $plugin_data['Version'],
				'author'         => $plugin_data['Author'],
				'active'         => $is_active || $is_network_active,
				'network_active' => $is_network_active,
			);
		}

		return $plugins;
	}

	/**
	 * Get server configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_server_config(): array {
		$memory_limit   = ini_get( 'memory_limit' );
		$max_execution  = ini_get( 'max_execution_time' );
		$upload_max     = ini_get( 'upload_max_filesize' );
		$post_max       = ini_get( 'post_max_size' );
		$max_input_vars = ini_get( 'max_input_vars' );

		return array(
			'memory_limit'       => $memory_limit,
			'max_execution_time' => $max_execution,
			'upload_max_size'    => $upload_max,
			'post_max_size'      => $post_max,
			'max_input_vars'     => $max_input_vars,
			'wp_memory_limit'    => WP_MEMORY_LIMIT,
			'wp_max_memory'      => WP_MAX_MEMORY_LIMIT,
		);
	}

	/**
	 * Get last 100 lines of error log.
	 *
	 * @return array<int, string>
	 */
	private static function get_error_log_lines(): array {
		$error_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( empty( $error_log_path ) || ! file_exists( $error_log_path ) || ! is_readable( $error_log_path ) ) {
			return array( 'Error log not accessible or not configured' );
		}

		$lines = array();
		$file  = new \SplFileObject( $error_log_path, 'r' );
		$file->seek( PHP_INT_MAX );
		$last_line = $file->key();

		$start = max( 0, $last_line - 99 );

		$file->seek( $start );

		while ( ! $file->eof() ) {
			$line = $file->fgets();
			if ( $line ) {
				$lines[] = self::sanitize_log_line( $line );
			}
		}

		return array_slice( $lines, -100 );
	}

	/**
	 * Get database size and largest tables.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_database_info(): array {
		global $wpdb;

		$db_name = DB_NAME;

		// Get total database size.
		$size_query = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT SUM(data_length + index_length) / 1024 / 1024 
				FROM information_schema.TABLES 
				WHERE table_schema = %s',
				$db_name
			)
		);

		$total_size = $size_query ? round( (float) $size_query, 2 ) : 0;

		// Get largest tables.
		$tables_query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT table_name AS 'table', 
				ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
				FROM information_schema.TABLES 
				WHERE table_schema = %s 
				ORDER BY (data_length + index_length) DESC 
				LIMIT 10",
				$db_name
			),
			ARRAY_A
		);

		return array(
			'total_size_mb'  => $total_size,
			'largest_tables' => $tables_query ? $tables_query : array(),
		);
	}

	/**
	 * Check file permissions for critical directories.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_file_permissions(): array {
		$upload_dir  = wp_upload_dir();
		$upload_path = ! empty( $upload_dir['basedir'] ) && empty( $upload_dir['error'] ) ? $upload_dir['basedir'] : WP_CONTENT_DIR . '/uploads';

		$paths = array(
			'wp-content'         => WP_CONTENT_DIR,
			'wp-content/uploads' => $upload_path,
			'wp-content/plugins' => WP_PLUGIN_DIR,
			'wp-content/themes'  => get_theme_root(),
		);

		$permissions = array();

		foreach ( $paths as $name => $path ) {
			if ( file_exists( $path ) ) {
				$perms                = fileperms( $path );
				$permissions[ $name ] = array(
					'path'       => $path,
					'permission' => substr( sprintf( '%o', $perms ), -4 ),
					'writable'   => is_writable( $path ),
				);
			} else {
				$permissions[ $name ] = array(
					'path'       => $path,
					'permission' => 'N/A',
					'writable'   => false,
					'exists'     => false,
				);
			}
		}

		return $permissions;
	}

	/**
	 * Get cron status.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_cron_status(): array {
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

		$cron_events = _get_cron_array();
		$event_count = 0;

		if ( is_array( $cron_events ) ) {
			foreach ( $cron_events as $timestamp => $events ) {
				$event_count += count( $events );
			}
		}

		return array(
			'disabled'    => $cron_disabled,
			'event_count' => $event_count,
			'next_run'    => $cron_disabled ? 'N/A' : wp_next_scheduled( 'wp_scheduled_auto_draft_delete' ),
		);
	}

	/**
	 * Get rewrite rules status.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_rewrite_status(): array {
		global $wp_rewrite;

		$permalink_structure = get_option( 'permalink_structure' );

		return array(
			'permalink_structure' => $permalink_structure ? $permalink_structure : 'Default',
			'using_permalinks'    => (bool) $permalink_structure,
			'rules_count'         => is_array( $wp_rewrite->rules ) ? count( $wp_rewrite->rules ) : 0,
		);
	}

	/**
	 * Get multisite configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_multisite_config(): array {
		if ( ! is_multisite() ) {
			return array();
		}

		return array(
			'network_id'   => get_current_network_id(),
			'site_count'   => get_blog_count(),
			'subdomain'    => is_subdomain_install(),
			'main_site_id' => get_main_site_id(),
		);
	}

	/**
	 * Get wp-config.php constants (sanitized).
	 *
	 * @return array<string, mixed>
	 */
	private static function get_wp_config_constants(): array {
		$constants = array(
			'WP_DEBUG',
			'WP_DEBUG_LOG',
			'WP_DEBUG_DISPLAY',
			'SCRIPT_DEBUG',
			'SAVEQUERIES',
			'WP_CACHE',
			'CONCATENATE_SCRIPTS',
			'COMPRESS_SCRIPTS',
			'COMPRESS_CSS',
			'WP_POST_REVISIONS',
			'AUTOSAVE_INTERVAL',
			'WP_CRON_LOCK_TIMEOUT',
			'EMPTY_TRASH_DAYS',
			'WP_ALLOW_MULTISITE',
			'MULTISITE',
			'SUBDOMAIN_INSTALL',
			'DOMAIN_CURRENT_SITE',
			'PATH_CURRENT_SITE',
			'FS_METHOD',
		);

		$values = array();

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$value = constant( $constant );

				// Convert boolean to string.
				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				}

				$values[ $constant ] = $value;
			}
		}

		return $values;
	}

	/**
	 * Sanitize sensitive data from log lines.
	 *
	 * @param string $line Log line.
	 * @return string Sanitized line.
	 */
	private static function sanitize_log_line( string $line ): string {
		// Remove potential passwords, tokens, keys.
		$patterns = array(
			'/password["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
			'/token["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
			'/api[_-]?key["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
			'/secret["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
			'/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/i',
		);

		foreach ( $patterns as $pattern ) {
			$result = preg_replace( $pattern, '[REDACTED]', $line );
			if ( null !== $result ) {
				$line = $result;
			}
		}

		return $line;
	}

	/**
	 * Sanitize report data to remove sensitive information.
	 *
	 * @param array<string, mixed> $data Report data.
	 * @return array<string, mixed> Sanitized data.
	 */
	public static function sanitize_report_data( array $data ): array {
		// Already sanitized in collection methods.
		// Additional sanitization can be added here if needed.
		return $data;
	}

	/**
	 * Export report as JSON.
	 *
	 * @param array<string, mixed> $data Report data.
	 * @return string JSON string.
	 */
	public static function export_json( array $data ): string {
		return wp_json_encode( $data, JSON_PRETTY_PRINT );
	}

	/**
	 * Export report as human-readable text.
	 *
	 * @param array<string, mixed> $data Report data.
	 * @return string Text report.
	 */
	public static function export_txt( array $data ): string {
		$output  = "=== SYSTEM REPORT ===\n";
		$output .= 'Generated: ' . $data['generated_at'] . "\n";
		$output .= 'Site URL: ' . $data['site_url'] . "\n\n";

		// Versions.
		$output .= "=== VERSIONS ===\n";
		foreach ( $data['versions'] as $key => $value ) {
			$output .= ucfirst( $key ) . ': ' . $value . "\n";
		}
		$output .= "\n";

		// Theme.
		$output .= "=== ACTIVE THEME ===\n";
		$output .= 'Name: ' . $data['theme']['name'] . "\n";
		$output .= 'Version: ' . $data['theme']['version'] . "\n";
		$output .= 'Author: ' . $data['theme']['author'] . "\n\n";

		// Plugins.
		$output        .= "=== PLUGINS ===\n";
		$active_count   = 0;
		$inactive_count = 0;
		foreach ( $data['plugins'] as $path => $plugin ) {
			$status = $plugin['active'] ? '[Active]' : '[Inactive]';
			if ( $plugin['active'] ) {
				++$active_count;
			} else {
				++$inactive_count;
			}
			$output .= $status . ' ' . $plugin['name'] . ' - v' . $plugin['version'] . "\n";
		}
		$output .= "\nTotal Active: " . $active_count . ', Inactive: ' . $inactive_count . "\n\n";

		// Server config.
		$output .= "=== SERVER CONFIGURATION ===\n";
		foreach ( $data['server'] as $key => $value ) {
			$output .= ucfirst( str_replace( '_', ' ', $key ) ) . ': ' . $value . "\n";
		}
		$output .= "\n";

		// Database.
		$output .= "=== DATABASE ===\n";
		$output .= 'Total Size: ' . $data['database']['total_size_mb'] . " MB\n";
		$output .= "Largest Tables:\n";
		foreach ( $data['database']['largest_tables'] as $table ) {
			$output .= '  - ' . $table['table'] . ': ' . $table['size_mb'] . " MB\n";
		}
		$output .= "\n";

		// File permissions.
		$output .= "=== FILE PERMISSIONS ===\n";
		foreach ( $data['file_permissions'] as $name => $info ) {
			$writable = $info['writable'] ? 'Writable' : 'Not Writable';
			$output  .= $name . ': ' . $info['permission'] . ' (' . $writable . ")\n";
		}
		$output .= "\n";

		// Cron.
		$output .= "=== CRON STATUS ===\n";
		$output .= 'Disabled: ' . ( $data['cron']['disabled'] ? 'Yes' : 'No' ) . "\n";
		$output .= 'Scheduled Events: ' . $data['cron']['event_count'] . "\n\n";

		// Rewrite rules.
		$output .= "=== REWRITE RULES ===\n";
		$output .= 'Permalink Structure: ' . $data['rewrites']['permalink_structure'] . "\n";
		$output .= 'Rules Count: ' . $data['rewrites']['rules_count'] . "\n\n";

		// Multisite.
		if ( ! empty( $data['multisite'] ) ) {
			$output .= "=== MULTISITE ===\n";
			$output .= 'Site Count: ' . $data['multisite']['site_count'] . "\n";
			$output .= 'Subdomain Install: ' . ( $data['multisite']['subdomain'] ? 'Yes' : 'No' ) . "\n\n";
		}

		// Constants.
		$output .= "=== WP-CONFIG CONSTANTS ===\n";
		foreach ( $data['constants'] as $constant => $value ) {
			$output .= $constant . ': ' . $value . "\n";
		}
		$output .= "\n";

		// Error log.
		$output .= "=== ERROR LOG (Last 100 Lines) ===\n";
		foreach ( $data['error_log'] as $line ) {
			// Ensure each line ends with a newline.
			$output .= rtrim( $line ) . "\n";
		}

		return $output;
	}

	/**
	 * Export report as PDF.
	 * Note: Basic implementation - can be enhanced with proper PDF library.
	 *
	 * @param array<string, mixed> $data Report data.
	 * @return string PDF content or error message.
	 */
	public static function export_pdf( array $data ): string {
		// For now, return text format with PDF header.
		// In production, use a library like TCPDF or FPDF.
		return self::export_txt( $data );
	}

	/**
	 * Create a shareable link for the report.
	 *
	 * @param array<string, mixed> $data     Report data.
	 * @param string               $password Optional password.
	 * @return array<string, mixed> Link info with token and expiry.
	 */
	public static function create_shareable_link( array $data, string $password = '' ): array {
		$token  = wp_generate_password( 32, false );
		$expiry = time() + ( 7 * DAY_IN_SECONDS );

		$link_data = array(
			'token'      => $token,
			'data'       => $data,
			'password'   => $password ? wp_hash_password( $password ) : '',
			'expires_at' => $expiry,
			'created_at' => time(),
		);

		// Store in transient (expires automatically).
		set_transient( 'wps_report_' . $token, $link_data, 7 * DAY_IN_SECONDS );

		$url = add_query_arg(
			array(
				'wps_report' => $token,
			),
			home_url( '/' )
		);

		return array(
			'token'      => $token,
			'url'        => $url,
			'expires_at' => date( 'Y-m-d H:i:s', $expiry ),
		);
	}

	/**
	 * Handle shareable report viewing.
	 * Runs on template_redirect to intercept report viewing requests.
	 *
	 * @return void
	 */
	public static function handle_shareable_report(): void {
		if ( ! isset( $_GET['wps_report'] ) ) {
			return;
		}

		$token     = sanitize_text_field( wp_unslash( $_GET['wps_report'] ) );
		$link_data = get_transient( 'wps_report_' . $token );

		if ( ! $link_data || ! is_array( $link_data ) ) {
			wp_die( esc_html__( 'This report link is invalid or has expired.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Check if password required.
		if ( ! empty( $link_data['password'] ) ) {
			$provided_password = isset( $_POST['report_password'] ) ? sanitize_text_field( wp_unslash( $_POST['report_password'] ) ) : '';

			if ( empty( $provided_password ) ) {
				// Show password form.
				self::render_password_form( $token );
				exit;
			}

			if ( ! wp_check_password( $provided_password, $link_data['password'] ) ) {
				wp_die( esc_html__( 'Incorrect password.', 'plugin-wp-support-thisismyurl' ) );
			}
		}

		// Display the report.
		self::render_shareable_report( $link_data['data'], $link_data['expires_at'] );
		exit;
	}

	/**
	 * Render password form for protected reports.
	 *
	 * @param string $token Report token.
	 * @return void
	 */
	private static function render_password_form( string $token ): void {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Protected Report', 'plugin-wp-support-thisismyurl' ); ?></title>
			<style>
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					background: #f0f0f1;
					padding: 50px 20px;
				}
				.password-form-container {
					max-width: 400px;
					margin: 0 auto;
					background: #fff;
					padding: 40px;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.1);
				}
				h1 {
					margin: 0 0 20px;
					font-size: 24px;
					color: #1d2327;
				}
				label {
					display: block;
					margin-bottom: 8px;
					font-weight: 600;
					color: #1d2327;
				}
				input[type="password"] {
					width: 100%;
					padding: 10px;
					border: 1px solid #ddd;
					border-radius: 4px;
					font-size: 14px;
					box-sizing: border-box;
				}
				button {
					width: 100%;
					padding: 12px;
					background: #2271b1;
					color: #fff;
					border: none;
					border-radius: 4px;
					font-size: 14px;
					font-weight: 600;
					cursor: pointer;
					margin-top: 15px;
				}
				button:hover {
					background: #135e96;
				}
			</style>
		</head>
		<body>
			<div class="password-form-container">
				<h1><?php esc_html_e( 'Protected Report', 'plugin-wp-support-thisismyurl' ); ?></h1>
				<p><?php esc_html_e( 'This report is password protected. Please enter the password to view.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<form method="post">
					<label for="report_password"><?php esc_html_e( 'Password:', 'plugin-wp-support-thisismyurl' ); ?></label>
					<input type="password" id="report_password" name="report_password" required />
					<button type="submit"><?php esc_html_e( 'View Report', 'plugin-wp-support-thisismyurl' ); ?></button>
				</form>
			</div>
		</body>
		</html>
		<?php
	}

	/**
	 * Render the shareable report.
	 *
	 * @param array<string, mixed> $data       Report data.
	 * @param int                  $expires_at Expiry timestamp.
	 * @return void
	 */
	private static function render_shareable_report( array $data, int $expires_at ): void {
		$report_text = self::export_txt( $data );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'System Report', 'plugin-wp-support-thisismyurl' ); ?></title>
			<style>
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					background: #f0f0f1;
					padding: 20px;
					margin: 0;
				}
				.report-container {
					max-width: 1200px;
					margin: 0 auto;
					background: #fff;
					padding: 30px;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0,0,0,0.1);
				}
				h1 {
					margin: 0 0 10px;
					font-size: 28px;
					color: #1d2327;
				}
				.expiry-notice {
					background: #fff3cd;
					border: 1px solid #ffecb5;
					padding: 12px;
					border-radius: 4px;
					margin-bottom: 20px;
					color: #856404;
				}
				pre {
					background: #f9f9f9;
					border: 1px solid #ddd;
					padding: 20px;
					border-radius: 4px;
					overflow-x: auto;
					white-space: pre-wrap;
					word-wrap: break-word;
					font-family: 'Courier New', Courier, monospace;
					font-size: 13px;
					line-height: 1.6;
				}
				.actions {
					margin-bottom: 20px;
				}
				button {
					padding: 10px 20px;
					background: #2271b1;
					color: #fff;
					border: none;
					border-radius: 4px;
					font-size: 14px;
					cursor: pointer;
					margin-right: 10px;
				}
				button:hover {
					background: #135e96;
				}
			</style>
		</head>
		<body>
			<div class="report-container">
				<h1><?php esc_html_e( 'System Report', 'plugin-wp-support-thisismyurl' ); ?></h1>
				<div class="expiry-notice">
					<?php
					printf(
						/* translators: %s: Expiry date */
						esc_html__( 'This report link expires on: %s', 'plugin-wp-support-thisismyurl' ),
						'<strong>' . esc_html( date( 'Y-m-d H:i:s', $expires_at ) ) . '</strong>'
					);
					?>
				</div>
				<div class="actions">
					<button onclick="copyToClipboard()"><?php esc_html_e( 'Copy to Clipboard', 'plugin-wp-support-thisismyurl' ); ?></button>
					<button onclick="downloadReport()"><?php esc_html_e( 'Download', 'plugin-wp-support-thisismyurl' ); ?></button>
				</div>
				<pre id="report-content"><?php echo esc_html( $report_text ); ?></pre>
			</div>
			<script>
				function copyToClipboard() {
					const content = document.getElementById('report-content').textContent;
					navigator.clipboard.writeText(content).then(function() {
						alert('<?php esc_html_e( 'Report copied to clipboard', 'plugin-wp-support-thisismyurl' ); ?>');
					}).catch(function() {
						alert('<?php esc_html_e( 'Failed to copy', 'plugin-wp-support-thisismyurl' ); ?>');
					});
				}

				function downloadReport() {
					const content = document.getElementById('report-content').textContent;
					const blob = new Blob([content], { type: 'text/plain' });
					const url = window.URL.createObjectURL(blob);
					const a = document.createElement('a');
					a.href = url;
					a.download = 'system-report-' + Date.now() + '.txt';
					document.body.appendChild(a);
					a.click();
					window.URL.revokeObjectURL(url);
					document.body.removeChild(a);
				}
			</script>
		</body>
		</html>
		<?php
	}

	/**
	 * Cleanup expired report links.
	 * Called by cron.
	 *
	 * @return void
	 */
	public static function cleanup_expired_reports(): void {
		global $wpdb;

		// Transients auto-expire, but we can force cleanup.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				'_transient_timeout_wps_report_%',
				time()
			)
		);
	}

	/**
	 * AJAX handler for report generation.
	 *
	 * @return void
	 */
	public static function ajax_generate_report(): void {
		check_ajax_referer( 'wps_system_report', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$format = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'json';

		$data = self::collect_system_info();
		$data = self::sanitize_report_data( $data );

		$output = '';

		switch ( $format ) {
			case 'txt':
				$output = self::export_txt( $data );
				break;
			case 'pdf':
				$output = self::export_pdf( $data );
				break;
			case 'json':
			default:
				$output = self::export_json( $data );
				break;
		}

		wp_send_json_success(
			array(
				'report' => $output,
				'format' => $format,
			)
		);
	}

	/**
	 * AJAX handler for creating shareable link.
	 *
	 * @return void
	 */
	public static function ajax_create_shareable_link(): void {
		check_ajax_referer( 'wps_system_report', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';

		$data = self::collect_system_info();
		$data = self::sanitize_report_data( $data );

		$link_info = self::create_shareable_link( $data, $password );

		wp_send_json_success( $link_info );
	}
}
