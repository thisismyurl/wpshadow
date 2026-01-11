<?php
/**
 * Uninstall script for WordPress Support (thisismyurl).
 *
 * Fired when the plugin is uninstalled.
 *
 * @package TIMU_WORDPRESS_SUPPORT
 */

declare(strict_types=1);

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin options.
 *
 * @return void
 */
function wp_support_delete_options(): void {
	// Delete single site options.
	delete_option( 'wp_support_settings' );
	delete_option( 'wp_support_version' );

	// Delete multisite options if applicable.
	if ( is_multisite() ) {
		delete_site_option( 'wp_support_network_settings' );
		delete_site_option( 'wp_support_network_version' );
	}
}

/**
 * Clean up vault directory (optional - commented out for safety).
 *
 * WARNING: This will permanently delete all vault contents.
 * Only uncomment if you want to remove originals on uninstall.
 *
 * @return void
 */
function wp_support_cleanup_vault(): void {
	/*
	$upload_dir = wp_upload_dir();
	$vault_path = $upload_dir['basedir'] . '/vault';

	if ( file_exists( $vault_path ) ) {
		// Recursive delete function
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $vault_path, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			$todo = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
			$todo( $fileinfo->getRealPath() );
		}

		rmdir( $vault_path );
	}
	*/
}

// Execute cleanup.
wp_support_delete_options();

// Optional: Uncomment to remove vault on uninstall.
// wp_support_cleanup_vault();

/* @changelog
 * [1.2601.71701] - 2026-01-07 17:17
 * - Created uninstall script with options cleanup
 * - Added optional vault cleanup (commented for safety)
 * - Implemented multisite option cleanup
 */
