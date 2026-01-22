<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Backup Manager
 *
 * Manages automated backups before auto-fixes.
 * Creates snapshots of critical options before applying treatments.
 * Stores backups in transients with 4-week expiration for rollback.
 *
 * Data Storage:
 * - wpshadow_backup_{id}: Backup snapshot (transient, 28 days)
 * - wpshadow_backup_manifest: List of backups (option)
 */
class Backup_Manager {

	/**
	 * Create automated backup
	 *
	 * Stores snapshot of critical options before auto-fix.
	 * Backup auto-expires after 4 weeks (28 days).
	 *
	 * @param string $reason Reason for backup (e.g., 'auto_fix_ssl')
	 *
	 * @return string Backup ID
	 */
	public static function create_automated_backup( string $reason = 'auto_fix' ): string {
		$backup_id = 'backup_' . time() . '_' . wp_generate_password( 8, false );

		// Capture critical options
		$backup = array(
			'id'        => $backup_id,
			'timestamp' => current_time( 'mysql' ),
			'reason'    => sanitize_text_field( $reason ),
			'options'   => self::get_critical_options(),
		);

		// Store in transient (4 weeks expiration)
		set_transient(
			"wpshadow_{$backup_id}",
			$backup,
			WEEK_IN_SECONDS * 4
		);

		// Add to manifest
		self::add_to_manifest( $backup_id, $reason );

		do_action( 'wpshadow_backup_created', $backup_id, $reason );

		return $backup_id;
	}

	/**
	 * Restore from backup
	 *
	 * Restores all options to state when backup was created.
	 *
	 * @param string $backup_id Backup ID to restore
	 *
	 * @return bool Success
	 */
	public static function restore_backup( string $backup_id ): bool {
		$backup = get_transient( "wpshadow_{$backup_id}" );

		if ( ! $backup ) {
			return false; // Backup expired or not found
		}

		// Restore all options
		foreach ( $backup['options'] as $option_name => $option_value ) {
			update_option( $option_name, $option_value );
		}

		do_action( 'wpshadow_backup_restored', $backup_id );

		return true;
	}

	/**
	 * Get list of available backups
	 *
	 * @return array Array of backup info
	 */
	public static function get_available_backups(): array {
		$manifest = get_option( 'wpshadow_backup_manifest', array() );

		// Verify each backup still exists in transients
		$available = array();
		foreach ( $manifest as $backup_info ) {
			$backup_id = $backup_info['id'];
			if ( get_transient( "wpshadow_{$backup_id}" ) ) {
				$available[] = $backup_info;
			}
		}

		return $available;
	}

	/**
	 * Delete backup
	 *
	 * @param string $backup_id Backup to delete
	 *
	 * @return bool Success
	 */
	public static function delete_backup( string $backup_id ): bool {
		delete_transient( "wpshadow_{$backup_id}" );

		// Remove from manifest
		$manifest = get_option( 'wpshadow_backup_manifest', array() );
		$manifest = array_filter(
			$manifest,
			fn( $b ) => $b['id'] !== $backup_id
		);
		update_option( 'wpshadow_backup_manifest', $manifest );

		do_action( 'wpshadow_backup_deleted', $backup_id );

		return true;
	}

	/**
	 * Get critical options for backup
	 *
	 * These are the options most likely to be changed by treatments.
	 *
	 * @return array Option name => value pairs
	 */
	private static function get_critical_options(): array {
		$critical_options = array(
			'siteurl',
			'home',
			'permalink_structure',
			'blogname',
			'blogdescription',
			'blog_charset',
			'active_plugins',
			'default_theme',
			'template',
			'stylesheet',
			'wpmu_json_before_db_error_message',
			'posts_per_page',
			'posts_per_rss',
			'rss_use_excerpt',
			'timezone_string',
			'date_format',
			'time_format',
			'use_gravatar',
			'comment_moderation',
			'comment_max_links',
			'moderation_keys',
			'disallowed_keys',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'WPSEO_titles', // Yoast SEO example
			'wpshadow_settings', // Any WPShadow settings
		);

		$backup = array();
		foreach ( $critical_options as $option ) {
			$value = get_option( $option );
			if ( $value !== false ) { // Only include if option exists
				$backup[ $option ] = $value;
			}
		}

		return $backup;
	}

	/**
	 * Add backup to manifest
	 *
	 * @param string $backup_id Backup ID
	 * @param string $reason Reason for backup
	 */
	private static function add_to_manifest( string $backup_id, string $reason ): void {
		$manifest = get_option( 'wpshadow_backup_manifest', array() );

		$manifest[] = array(
			'id'        => $backup_id,
			'timestamp' => current_time( 'mysql' ),
			'reason'    => $reason,
			'expires'   => wp_date( 'Y-m-d H:i:s', strtotime( '+4 weeks' ) ),
		);

		// Keep only last 50 in manifest (oldest auto-deleted from transients)
		$manifest = array_slice( $manifest, -50 );

		update_option( 'wpshadow_backup_manifest', $manifest );
	}
}
