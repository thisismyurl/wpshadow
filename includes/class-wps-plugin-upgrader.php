<?php
/**
 * Plugin installer/upgrader for catalog-driven install/update flows.
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.73000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin upgrader for suite modules.
 *
 * Handles ZIP download, extraction, installation, and activation.
 */
class WPS_Plugin_Upgrader {

	/**
	 * Result from upgrader operation.
	 *
	 * @var mixed
	 */
	public $result = false;

	/**
	 * Hook suffix for progress reporting.
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Install plugin from download URL.
	 *
	 * @param string $download_url URL to plugin ZIP.
	 * @param bool   $activate Whether to activate after install.
	 * @param bool   $network Whether to activate network-wide (multisite only).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function install_plugin( string $download_url, bool $activate = true, bool $network = false ) {
		global $wp_filesystem;

		// Validate URL.
		if ( empty( $download_url ) ) {
			return new WP_Error( 'empty_url', __( 'Download URL is required.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Initialize filesystem.
		if ( ! $this->init_filesystem() ) {
			return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Create temporary working directory.
		$working_dir = $this->get_working_directory();
		if ( is_wp_error( $working_dir ) ) {
			return $working_dir;
		}

		// Download ZIP file.
		$download_result = $this->download_package( $download_url, $working_dir );
		if ( is_wp_error( $download_result ) ) {
			$this->cleanup( $working_dir );
			return $download_result;
		}

		$zip_file = $download_result;

		// Extract ZIP.
		$extract_result = $this->unpack_package( $zip_file, $working_dir );
		if ( is_wp_error( $extract_result ) ) {
			$this->cleanup( $working_dir );
			return $extract_result;
		}

		// Find the main plugin file in extracted directory.
		$plugin_file = $this->find_plugin_file( $working_dir, $extract_result );
		if ( is_wp_error( $plugin_file ) ) {
			$this->cleanup( $working_dir );
			return $plugin_file;
		}

		// Determine destination plugin directory.
		$destination = $this->get_destination_path( $plugin_file, $working_dir );
		if ( is_wp_error( $destination ) ) {
			$this->cleanup( $working_dir );
			return $destination;
		}

		// Remove old version if exists.
		if ( $wp_filesystem->exists( $destination ) ) {
			if ( ! $wp_filesystem->delete( $destination, true ) ) {
				$this->cleanup( $working_dir );
				return new WP_Error( 'delete_failed', __( 'Could not remove old plugin version.', 'plugin-wp-support-thisismyurl' ) );
			}
		}

		// Move files to destination.
		$move_result = $wp_filesystem->move( trailingslashit( $working_dir ) . $extract_result, $destination, true );
		if ( ! $move_result ) {
			$this->cleanup( $working_dir );
			return new WP_Error( 'move_failed', __( 'Could not move plugin files to destination.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Clean up working directory.
		$this->cleanup( $working_dir );

		// Determine plugin base (slug/slug.php).
		$plugin_basename = basename( $destination ) . '/' . basename( $destination ) . '.php';

		// Validate plugin file.
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_basename;
		if ( ! file_exists( $plugin_path ) ) {
			return new WP_Error( 'plugin_not_found', __( 'Plugin file not found after extraction.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Activate if requested.
		if ( $activate ) {
			if ( $network && is_multisite() ) {
				$result = activate_plugin( $plugin_basename, '', true );
			} else {
				$result = activate_plugin( $plugin_basename );
			}

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Clear module cache to refresh registry.
		WPS_Module_Registry::refresh_modules();

		$this->result = $plugin_basename;
		return true;
	}

	/**
	 * Update existing plugin from download URL.
	 *
	 * @param string $plugin_file Plugin base name (slug/slug.php).
	 * @param string $download_url URL to plugin ZIP.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function update_plugin( string $plugin_file, string $download_url ) {
		global $wp_filesystem;

		// Validate inputs.
		if ( empty( $plugin_file ) || empty( $download_url ) ) {
			return new WP_Error( 'invalid_params', __( 'Plugin file and download URL are required.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Check plugin exists.
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		if ( ! file_exists( $plugin_path ) ) {
			return new WP_Error( 'plugin_not_found', __( 'Plugin not found.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Initialize filesystem.
		if ( ! $this->init_filesystem() ) {
			return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Create temporary working directory.
		$working_dir = $this->get_working_directory();
		if ( is_wp_error( $working_dir ) ) {
			return $working_dir;
		}

		// Store current activation state.
		$was_active_network = is_multisite() && is_plugin_active_for_network( $plugin_file );
		$was_active_single  = is_plugin_active( $plugin_file );

		// Deactivate plugin during update.
		if ( $was_active_network ) {
			deactivate_plugins( $plugin_file, false, true );
		} elseif ( $was_active_single ) {
			deactivate_plugins( $plugin_file );
		}

		// Download ZIP file.
		$download_result = $this->download_package( $download_url, $working_dir );
		if ( is_wp_error( $download_result ) ) {
			$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );
			$this->cleanup( $working_dir );
			return $download_result;
		}

		$zip_file = $download_result;

		// Extract ZIP.
		$extract_result = $this->unpack_package( $zip_file, $working_dir );
		if ( is_wp_error( $extract_result ) ) {
			$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );
			$this->cleanup( $working_dir );
			return $extract_result;
		}

		// Find the main plugin file.
		$plugin_main_file = $this->find_plugin_file( $working_dir, $extract_result );
		if ( is_wp_error( $plugin_main_file ) ) {
			$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );
			$this->cleanup( $working_dir );
			return $plugin_main_file;
		}

		// Determine destination.
		$destination = dirname( $plugin_path );

		// Backup old version.
		$backup_dir = $destination . '_backup_' . time();
		if ( ! $wp_filesystem->move( $destination, $backup_dir, true ) ) {
			$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );
			$this->cleanup( $working_dir );
			return new WP_Error( 'backup_failed', __( 'Could not backup current plugin version.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Move new version to destination.
		$move_result = $wp_filesystem->move( trailingslashit( $working_dir ) . $extract_result, $destination, true );
		if ( ! $move_result ) {
			// Restore backup on failure.
			$wp_filesystem->move( $backup_dir, $destination, true );
			$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );
			$this->cleanup( $working_dir );
			return new WP_Error( 'move_failed', __( 'Could not move updated plugin files.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Remove backup if successful.
		$wp_filesystem->delete( $backup_dir, true );

		// Clean up working directory.
		$this->cleanup( $working_dir );

		// Reactivate plugin.
		$this->reactivate_plugin( $plugin_file, $was_active_network, $was_active_single );

		// Clear module cache to refresh registry and status.
		WPS_Module_Registry::refresh_modules();

		$this->result = $plugin_file;
		return true;
	}

	/**
	 * Initialize filesystem.
	 *
	 * @return bool True if filesystem available, false otherwise.
	 */
	private function init_filesystem(): bool {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Try direct method first (most common in modern setups).
		if ( ! WP_Filesystem( array(), WP_PLUGIN_DIR ) ) {
			// Fall back to option: prompt for credentials via admin notice.
			return false;
		}

		return isset( $wp_filesystem ) && $wp_filesystem instanceof \WP_Filesystem_Base;
	}

	/**
	 * Get working directory for temporary files.
	 *
	 * @return string|WP_Error Path to working directory or error.
	 */
	private function get_working_directory() {
		$upload_dir = wp_upload_dir();

		if ( is_wp_error( $upload_dir ) || empty( $upload_dir['basedir'] ) ) {
			return new WP_Error( 'upload_dir_error', __( 'Could not determine upload directory.', 'plugin-wp-support-thisismyurl' ) );
		}

		$working_dir = $upload_dir['basedir'] . '/wps-temp-' . time();

		if ( ! wp_mkdir_p( $working_dir ) ) {
			return new WP_Error( 'mkdir_failed', __( 'Could not create temporary directory.', 'plugin-wp-support-thisismyurl' ) );
		}

		return $working_dir;
	}

	/**
	 * Download package from URL.
	 *
	 * @param string $url Download URL.
	 * @param string $working_dir Working directory path.
	 * @return string|WP_Error Path to downloaded ZIP or error.
	 */
	private function download_package( string $url, string $working_dir ) {
		// Validate URL.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid download URL.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Download with timeout.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'  => 30,
				'stream'   => true,
				'filename' => $working_dir . '/plugin.zip',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $http_code ) {
			return new WP_Error( 'http_error', sprintf( __( 'HTTP error: %s', 'plugin-wp-support-thisismyurl' ), intval( $http_code ) ) );
		}

		$zip_file = $working_dir . '/plugin.zip';
		if ( ! file_exists( $zip_file ) ) {
			return new WP_Error( 'download_failed', __( 'Could not download plugin ZIP.', 'plugin-wp-support-thisismyurl' ) );
		}

		return $zip_file;
	}

	/**
	 * Unpack/extract ZIP file.
	 *
	 * @param string $zip_file Path to ZIP file.
	 * @param string $working_dir Working directory path.
	 * @return string|WP_Error Extracted directory name or error.
	 */
	private function unpack_package( string $zip_file, string $working_dir ) {
		// Load extraction class.
		if ( ! class_exists( '\ZipArchive' ) ) {
			return new WP_Error( 'no_zip_ext', __( 'ZIP extension not available.', 'plugin-wp-support-thisismyurl' ) );
		}

		$zip = new \ZipArchive();
		$res = $zip->open( $zip_file );

		if ( true !== $res ) {
			return new WP_Error( 'bad_zip', __( 'Could not open ZIP file.', 'plugin-wp-support-thisismyurl' ) );
		}

		if ( ! $zip->extractTo( $working_dir ) ) {
			$zip->close();
			return new WP_Error( 'extract_failed', __( 'Could not extract ZIP file.', 'plugin-wp-support-thisismyurl' ) );
		}

		$zip->close();

		// Find extracted directory (usually first folder in ZIP).
		$files = scandir( $working_dir );
		if ( false === $files ) {
			return new WP_Error( 'scan_failed', __( 'Could not scan extracted files.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Find first non-. directory.
		foreach ( $files as $file ) {
			if ( '.' !== $file && '..' !== $file && is_dir( $working_dir . '/' . $file ) ) {
				return $file;
			}
		}

		return new WP_Error( 'no_dir_found', __( 'Could not find extracted plugin directory.', 'plugin-wp-support-thisismyurl' ) );
	}

	/**
	 * Find main plugin file in extracted directory.
	 *
	 * @param string $working_dir Working directory path.
	 * @param string $extracted_dir Extracted directory name.
	 * @return string|WP_Error Main plugin file path or error.
	 */
	private function find_plugin_file( string $working_dir, string $extracted_dir ) {
		$plugin_dir = $working_dir . '/' . $extracted_dir;
		$files      = scandir( $plugin_dir );

		if ( false === $files ) {
			return new WP_Error( 'scan_failed', __( 'Could not scan plugin directory.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Look for PHP file matching directory name.
		$main_file = $extracted_dir . '.php';
		foreach ( $files as $file ) {
			if ( $main_file === $file ) {
				return $plugin_dir . '/' . $file;
			}
		}

		// Fallback: first .php file.
		foreach ( $files as $file ) {
			if ( '.php' === substr( $file, -4 ) ) {
				return $plugin_dir . '/' . $file;
			}
		}

		return new WP_Error( 'no_plugin_file', __( 'Could not find plugin file in extracted directory.', 'plugin-wp-support-thisismyurl' ) );
	}

	/**
	 * Determine destination plugin directory.
	 *
	 * @param string $plugin_file Full path to plugin file.
	 * @param string $working_dir Working directory.
	 * @return string|WP_Error Destination directory or error.
	 */
	private function get_destination_path( string $plugin_file, string $working_dir ) {
		// Extract directory name from plugin file path.
		$plugin_dir = dirname( $plugin_file );
		$dir_name   = basename( $plugin_dir );

		if ( empty( $dir_name ) ) {
			return new WP_Error( 'invalid_dir_name', __( 'Could not determine plugin directory name.', 'plugin-wp-support-thisismyurl' ) );
		}

		return WP_PLUGIN_DIR . '/' . $dir_name;
	}

	/**
	 * Reactivate a plugin after update.
	 *
	 * @param string $plugin_file Plugin base name (slug/slug.php).
	 * @param bool   $network Whether it was network active.
	 * @param bool   $single Whether it was single site active.
	 * @return bool|WP_Error Result of activation.
	 */
	private function reactivate_plugin( string $plugin_file, bool $network, bool $single ) {
		if ( $network && is_multisite() ) {
			return activate_plugin( $plugin_file, '', true );
		} elseif ( $single ) {
			return activate_plugin( $plugin_file );
		}

		return true;
	}

	/**
	 * Clean up temporary working directory.
	 *
	 * @param string $working_dir Path to clean.
	 * @return void
	 */
	private function cleanup( string $working_dir ): void {
		if ( is_dir( $working_dir ) ) {
			array_map( 'unlink', glob( $working_dir . '/*', GLOB_NOSORT ) );
			rmdir( $working_dir );
		}
	}
}

/* @changelog WPS_Plugin_Upgrader class created for install/update/activate flows. */
