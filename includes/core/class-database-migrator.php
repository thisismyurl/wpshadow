<?php

/**
 * WPShadow Database Migrator
 *
 * Handles database schema creation, updates, and migrations using WordPress dbDelta().
 * Called during plugin activation to ensure tables are created/updated as needed.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - solid database foundation)
 * Philosophy: Commandment #10 (Beyond Pure - no presumption, proper schema management)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database migration and schema management
 */
class Database_Migrator {


	/**
	 * Current schema version
	 * Increment when database schema changes
	 */
	const SCHEMA_VERSION = 2;

	/**
	 * Database version option key
	 */
	const VERSION_OPTION = 'wpshadow_db_version';

	/**
	 * Run database migrations
	 * Called from activation hook
	 *
	 * @return void
	 */
	public static function migrate() {
		// Get current version
		$current_version = (int) get_option( self::VERSION_OPTION, 0 );

		// Only run if schema needs update
		if ( $current_version >= self::SCHEMA_VERSION ) {
			return;
		}

		// Include wp_upgrade.php for dbDelta
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Version 0 -> 1: Initial schema
		if ( $current_version < 1 ) {
			self::schema_v1();
		}

		// Version 1 -> 2: Exit followup tables
		if ( $current_version < 2 ) {
			self::schema_v2();
		}

		// Update version
		update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
	}

	/**
	 * Schema Version 1: Initial tables
	 *
	 * Creates all base tables needed by WPShadow:
	 * - wpshadow_workflow_logs: Temporary workflow execution logs
	 * - wpshadow_activity_log: Guardian activity tracking (optional, uses options by default)
	 *
	 * @return void
	 */
	private static function schema_v1() {
		global $wpdb;

		// Table prefix
		$table_logs = $wpdb->prefix . 'wpshadow_workflow_logs';

		// SQL statement using WordPress coding standards
		$sql = "CREATE TABLE {$table_logs} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			workflow_id VARCHAR(100) NOT NULL,
			execution_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			execution_time_ms INT UNSIGNED DEFAULT 0,
			handler_name VARCHAR(255) NOT NULL,
			status VARCHAR(50) NOT NULL,
			message TEXT,
			result_data LONGTEXT,
			user_id BIGINT UNSIGNED,
			INDEX (workflow_id),
			INDEX (execution_date),
			INDEX (status),
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// Use dbDelta for safe table creation
		// dbDelta returns array of queries executed, but we just need side effects
		dbDelta( $sql );

		// Optional: Create activity log table for Guardian
		// Currently using wp_options with wpshadow_guardian_activity option
		// This table can be added if performance metrics show need for it
		// For now, keeping it commented to minimize schema complexity
		/*
		$table_activity = $wpdb->prefix . 'wpshadow_activity_log';
		$sql_activity = "CREATE TABLE {$table_activity} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			activity_type VARCHAR(100) NOT NULL,
			activity_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			severity VARCHAR(50),
			details TEXT,
			user_id BIGINT UNSIGNED,
			INDEX (activity_type),
			INDEX (activity_date),
			INDEX (severity),
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		dbDelta($sql_activity);
		*/

		// Exit interview table
		$table_exit_interviews = $wpdb->prefix . 'wpshadow_exit_interviews';
		$sql_exit_interviews   = "CREATE TABLE {$table_exit_interviews} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			reason varchar(50) NOT NULL,
			details text,
			allow_contact tinyint(1) DEFAULT 0,
			contact_email varchar(255),
			site_url varchar(255),
			plugin_version varchar(20),
			wp_version varchar(20),
			php_version varchar(20),
			created_at datetime NOT NULL,
			interview_type varchar(20) DEFAULT 'deactivation',
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY interview_type (interview_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		dbDelta( $sql_exit_interviews );
	}

	/**
	 * Schema Version 2: Exit followup tables
	 *
	 * Creates tables for exit interview followup feature:
	 * - wpshadow_exit_interviews: Exit interview responses with contact permissions
	 * - wpshadow_exit_followups: Scheduled followup contacts and surveys
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	private static function schema_v2() {
		global $wpdb;

		// Exit interviews table - stores deactivation feedback
		$table_interviews = $wpdb->prefix . 'wpshadow_exit_interviews';
		$sql_interviews   = "CREATE TABLE {$table_interviews} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			site_url VARCHAR(255) NOT NULL,
			exit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			exit_reason VARCHAR(100),
			detailed_feedback TEXT,
			competitor_name VARCHAR(100),
			features_needed TEXT,
			contact_allowed TINYINT(1) NOT NULL DEFAULT 0,
			contact_email VARCHAR(255),
			usage_duration_days INT UNSIGNED,
			features_used TEXT,
			site_type VARCHAR(100),
			INDEX (user_id),
			INDEX (exit_date),
			INDEX (contact_allowed),
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// Followup schedules table - manages scheduled followup contacts
		$table_followups = $wpdb->prefix . 'wpshadow_exit_followups';
		$sql_followups   = "CREATE TABLE {$table_followups} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			interview_id BIGINT UNSIGNED NOT NULL,
			followup_type VARCHAR(50) NOT NULL,
			scheduled_date DATETIME NOT NULL,
			completed_date DATETIME,
			status VARCHAR(50) NOT NULL DEFAULT 'pending',
			survey_questions LONGTEXT,
			survey_responses LONGTEXT,
			contact_method VARCHAR(50) NOT NULL DEFAULT 'email',
			notes TEXT,
			INDEX (interview_id),
			INDEX (scheduled_date),
			INDEX (status),
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// Use dbDelta for safe table creation
		dbDelta( $sql_interviews );
		dbDelta( $sql_followups );
	}

	/**
	 * Get database tables created by WPShadow
	 *
	 * @return array List of table names
	 */
	public static function get_tables() {
		global $wpdb;

		return array(
			$wpdb->prefix . 'wpshadow_workflow_logs',
			$wpdb->prefix . 'wpshadow_exit_interviews',
			$wpdb->prefix . 'wpshadow_exit_followups',
		);
	}

	/**
	 * Check if tables exist
	 *
	 * @return bool True if all tables exist
	 */
	public static function tables_exist() {
		global $wpdb;

		foreach ( self::get_tables() as $table ) {
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( ! $exists ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get current database schema version
	 *
	 * @return int Version number
	 */
	public static function get_current_version() {
		return (int) get_option( self::VERSION_OPTION, 0 );
	}

	/**
	 * Get required database schema version
	 *
	 * @return int Latest schema version
	 */
	public static function get_latest_version() {
		return self::SCHEMA_VERSION;
	}

	/**
	 * Check if database schema is up to date
	 *
	 * @return bool True if current == latest version
	 */
	public static function is_up_to_date() {
		return self::get_current_version() >= self::get_latest_version();
	}

	/**
	 * Reset database (for development/testing)
	 *
	 * WARNING: This permanently deletes all WPShadow data
	 * Only call this explicitly with proper capabilities check
	 *
	 * @return bool Success status
	 */
	public static function reset() {
		// Security: Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		global $wpdb;

		foreach ( self::get_tables() as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // @phpstan-ignore-line
		}

		// Reset version
		delete_option( self::VERSION_OPTION );

		return true;
	}
}
