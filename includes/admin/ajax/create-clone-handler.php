<?php
/**
 * AJAX: Create Site Clone
 *
 * @since   1.6030.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Clone Handler
 */
class AJAX_Create_Clone extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6030.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_site_cloner', 'manage_options' );

		$clone_type = self::get_post_param( 'clone_type', 'text', 'subdomain', true );
		$clone_name = self::get_post_param( 'clone_name', 'text', '', true );
		$options    = self::get_post_param( 'options', 'array', array() );

		// Validate clone type
		if ( ! in_array( $clone_type, array( 'subdomain', 'subdirectory' ), true ) ) {
			self::send_error( __( 'Invalid clone type', 'wpshadow' ) );
			return;
		}

		// Sanitize clone name
		$clone_name = sanitize_key( $clone_name );
		if ( empty( $clone_name ) ) {
			self::send_error( __( 'Clone name is required', 'wpshadow' ) );
			return;
		}

		// Check free tier limit
		$existing_clones = get_option( 'wpshadow_site_clones', array() );
		if ( ! is_array( $existing_clones ) ) {
			$existing_clones = array();
		}

		$is_pro = apply_filters( 'wpshadow_is_pro', false );
		if ( ! $is_pro && count( $existing_clones ) >= 2 ) {
			self::send_error( __( 'Free tier limit reached. Upgrade to Pro for unlimited clones.', 'wpshadow' ) );
			return;
		}

		// Check if clone name already exists
		if ( isset( $existing_clones[ $clone_name ] ) ) {
			self::send_error( __( 'A clone with this name already exists', 'wpshadow' ) );
			return;
		}

		try {
			// Build clone URL
			$site_url = get_site_url();
			$parsed   = wp_parse_url( $site_url );

			if ( 'subdomain' === $clone_type ) {
				$clone_url = $parsed['scheme'] . '://' . $clone_name . '.' . $parsed['host'];
			} else {
				$clone_url = trailingslashit( $site_url ) . $clone_name;
			}

			// Create Vault Light snapshot
			$snapshot_result = self::create_vault_snapshot();
			if ( ! $snapshot_result['success'] ) {
				throw new \Exception( $snapshot_result['message'] );
			}

			// Clone the site
			$clone_result = self::clone_site( $clone_name, $clone_type, $clone_url, $options, $snapshot_result['snapshot_id'] );

			if ( ! $clone_result['success'] ) {
				throw new \Exception( $clone_result['message'] );
			}

			// Save clone info
			$existing_clones[ $clone_name ] = array(
				'type'       => $clone_type,
				'url'        => $clone_url,
				'path'       => $clone_result['path'],
				'created_at' => time(),
				'options'    => $options,
			);
			update_option( 'wpshadow_site_clones', $existing_clones );

			// Log activity
			Activity_Logger::log(
				'site_clone_created',
				array(
					'clone_name' => $clone_name,
					'clone_type' => $clone_type,
					'clone_url'  => $clone_url,
				)
			);

			self::send_success(
				array(
					'message'    => __( 'Clone created successfully', 'wpshadow' ),
					'clone_name' => $clone_name,
					'clone_url'  => $clone_url,
				)
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Create Vault Light snapshot.
	 *
	 * @since  1.6030.2200
	 * @return array Result array.
	 */
	private static function create_vault_snapshot() {
		// Check if Vault Light is available
		if ( ! class_exists( 'WPShadow\\Backup\\Vault_Light' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Vault Light is not available', 'wpshadow' ),
			);
		}

		// Create snapshot
		try {
			$snapshot_id = \WPShadow\Backup\Vault_Light::create_snapshot(
				array(
					'description' => __( 'Clone source snapshot', 'wpshadow' ),
				)
			);

			return array(
				'success'     => true,
				'snapshot_id' => $snapshot_id,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Clone the site.
	 *
	 * @since  1.6030.2200
	 * @param  string $clone_name Clone identifier.
	 * @param  string $clone_type Clone type (subdomain/subdirectory).
	 * @param  string $clone_url  Clone URL.
	 * @param  array  $options    Clone options.
	 * @param  string $snapshot_id Vault snapshot ID.
	 * @return array Result array.
	 */
	private static function clone_site( $clone_name, $clone_type, $clone_url, $options, $snapshot_id ) {
		// Determine clone path
		if ( 'subdirectory' === $clone_type ) {
			$clone_path = ABSPATH . $clone_name;
		} else {
			// For subdomain, use same parent directory as main site
			$clone_path = dirname( ABSPATH ) . '/' . $clone_name;
		}

		// Create clone directory
		if ( ! wp_mkdir_p( $clone_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Could not create clone directory', 'wpshadow' ),
			);
		}

		// Copy files based on options
		$files_to_copy = array();

		if ( in_array( 'themes', $options, true ) ) {
			$files_to_copy[] = WP_CONTENT_DIR . '/themes';
		}

		if ( in_array( 'plugins', $options, true ) ) {
			$files_to_copy[] = WP_CONTENT_DIR . '/plugins';
		}

		if ( in_array( 'uploads', $options, true ) ) {
			$files_to_copy[] = WP_CONTENT_DIR . '/uploads';
		}

		// Copy WordPress core files
		$files_to_copy[] = ABSPATH . 'wp-admin';
		$files_to_copy[] = ABSPATH . 'wp-includes';

		// Copy files using WordPress filesystem API
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		foreach ( $files_to_copy as $source ) {
			if ( is_dir( $source ) ) {
				$dest = str_replace( ABSPATH, $clone_path . '/', $source );
				$wp_filesystem->mkdir( $dest );
				copy_dir( $source, $dest );
			}
		}

		return array(
			'success' => true,
			'path'    => $clone_path,
		);
	}

	/**
	 * Clone the database.
	 *
	 * @since  1.6030.2200
	 * @param  string $clone_name Clone identifier.
	 * @param  string $clone_url  Clone URL.
	 * @return array Result array.
	 */
	private static function clone_database( $clone_name, $clone_url ) {
		return array(
			'success' => false,
			'message' => __( 'Database cloning is currently unavailable in this build.', 'wpshadow' ),
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_create_clone', array( '\WPShadow\\Admin\\AJAX_Create_Clone', 'handle' ) );
