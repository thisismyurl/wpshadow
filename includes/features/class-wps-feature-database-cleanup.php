<?php
/**
 * Feature: Database Cleanup
 *
 * Automated cleanup of database overhead including post revisions, transients,
 * spam comments, and orphaned metadata for improved database performance.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPS_Feature_Database_Cleanup
 *
 * Comprehensive database cleanup and optimization.
 */
final class WPS_Feature_Database_Cleanup extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'database-cleanup',
				'name'               => __( 'Database Cleanup & Optimization', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Clean up post revisions, expired transients, and optimize database tables', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => true,
			)
		);
		
		$this->register_default_settings(
			array(
				'cleanup_frequency' => 'weekly',
				'cleanup_options'   => array(
					'cleanup_revisions'     => true,
					'cleanup_transients'    => true,
					'cleanup_spam'          => true,
					'cleanup_orphaned_meta' => true,
					'cleanup_auto_drafts'   => true,
					'optimize_tables'       => false,
					'keep_revisions'        => 5,
				),
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

		// Schedule cleanup events.
		add_action( 'init', array( $this, 'schedule_cleanup' ) );

		// Register cleanup hooks.
		add_action( 'wps_database_cleanup', array( $this, 'run_cleanup' ) );

		// Admin notice removed - cleanup is automated via WP-Cron schedule.
		// Users can manage settings in Dashboard Settings tab.

		// Show success notice after manual cleanup.
		add_action( 'admin_notices', array( $this, 'show_cleanup_success_notice' ) );

		// Handle manual cleanup action.
		add_action( 'admin_post_wps_run_database_cleanup', array( $this, 'handle_manual_cleanup' ) );
	}

	/**
	 * Schedule automated database cleanup.
	 *
	 * @return void
	 */
	public function schedule_cleanup(): void {
		if ( ! wp_next_scheduled( 'wps_database_cleanup' ) ) {
			// Schedule weekly cleanup by default.
			$frequency = $this->get_setting( 'cleanup_frequency', 'weekly' );
			wp_schedule_event( time(), $frequency, 'wps_database_cleanup' );
		}
	}

	/**
	 * Run database cleanup tasks.
	 *
	 * @return array Cleanup statistics.
	 */
	public function run_cleanup(): array {
		global $wpdb;

		$stats = array(
			'revisions_deleted'     => 0,
			'transients_deleted'    => 0,
			'spam_comments_deleted' => 0,
			'orphaned_meta_deleted' => 0,
			'auto_drafts_deleted'   => 0,
		);

		// Get cleanup options.
		$options = $this->get_cleanup_options();

		// Clean up post revisions.
		if ( $options['cleanup_revisions'] ?? true ) {
			$stats['revisions_deleted'] = $this->cleanup_revisions( $options['keep_revisions'] ?? 5 );
		}

		// Clean up expired transients.
		if ( $options['cleanup_transients'] ?? true ) {
			$stats['transients_deleted'] = $this->cleanup_transients();
		}

		// Clean up spam comments.
		if ( $options['cleanup_spam'] ?? true ) {
			$stats['spam_comments_deleted'] = $this->cleanup_spam_comments();
		}

		// Clean up orphaned metadata.
		if ( $options['cleanup_orphaned_meta'] ?? true ) {
			$stats['orphaned_meta_deleted'] = $this->cleanup_orphaned_meta();
		}

		// Clean up auto-drafts.
		if ( $options['cleanup_auto_drafts'] ?? true ) {
			$stats['auto_drafts_deleted'] = $this->cleanup_auto_drafts();
		}

		// Optimize tables if enabled.
		if ( $options['optimize_tables'] ?? false ) {
			$this->optimize_database_tables();
		}

		// Store last cleanup timestamp.
		update_option( 'wps_last_database_cleanup', time() );

		// Log cleanup activity.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			\WPS\CoreSupport\WPS_Activity_Logger::log(
				'database_cleanup',
				__( 'Database cleanup completed', 'plugin-wp-support-thisismyurl' ),
				$stats
			);
		}

		return $stats;
	}

	/**
	 * Clean up old post revisions.
	 *
	 * @param int $keep_count Number of revisions to keep per post.
	 * @return int Number of revisions deleted.
	 */
	private function cleanup_revisions( int $keep_count = 5 ): int {
		global $wpdb;

		$deleted = 0;

		// Get all posts with revisions.
		$posts_with_revisions = $wpdb->get_results(
			"SELECT post_parent, COUNT(*) as revision_count 
			FROM {$wpdb->posts} 
			WHERE post_type = 'revision' 
			GROUP BY post_parent 
			HAVING revision_count > " . absint( $keep_count )
		);

		foreach ( $posts_with_revisions as $post ) {
			// Get revisions for this post, ordered by date.
			$revisions = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} 
					WHERE post_parent = %d 
					AND post_type = 'revision' 
					ORDER BY post_date DESC",
					$post->post_parent
				)
			);

			// Skip the number we want to keep and delete the rest.
			$revisions_to_delete = array_slice( $revisions, $keep_count );

			foreach ( $revisions_to_delete as $revision ) {
				if ( wp_delete_post_revision( $revision->ID ) ) {
					++$deleted;
				}
			}
		}

		return $deleted;
	}

	/**
	 * Clean up expired transients.
	 *
	 * @return int Number of transients deleted.
	 */
	private function cleanup_transients(): int {
		global $wpdb;

		// Delete expired transients.
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Clean up orphaned transient options.
		$wpdb->query(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_%' 
			AND option_name NOT LIKE '_transient_timeout_%' 
			AND option_name NOT IN (
				SELECT REPLACE(option_name, '_timeout', '') 
				FROM {$wpdb->options} 
				WHERE option_name LIKE '_transient_timeout_%'
			)"
		);

		return (int) $deleted;
	}

	/**
	 * Clean up spam and trashed comments.
	 *
	 * @return int Number of comments deleted.
	 */
	private function cleanup_spam_comments(): int {
		global $wpdb;

		$deleted = $wpdb->query(
			"DELETE FROM {$wpdb->comments} 
			WHERE comment_approved = 'spam' 
			OR comment_approved = 'trash'"
		);

		return (int) $deleted;
	}

	/**
	 * Clean up orphaned post metadata.
	 *
	 * @return int Number of meta rows deleted.
	 */
	private function cleanup_orphaned_meta(): int {
		global $wpdb;

		$deleted = $wpdb->query(
			"DELETE FROM {$wpdb->postmeta} 
			WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
		);

		return (int) $deleted;
	}

	/**
	 * Clean up old auto-drafts.
	 *
	 * @return int Number of auto-drafts deleted.
	 */
	private function cleanup_auto_drafts(): int {
		global $wpdb;

		// Delete auto-drafts older than 7 days.
		$deleted     = 0;
		$auto_drafts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} 
				WHERE post_status = 'auto-draft' 
				AND post_modified < %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		foreach ( $auto_drafts as $draft ) {
			if ( wp_delete_post( $draft->ID, true ) ) {
				++$deleted;
			}
		}

		return $deleted;
	}

	/**
	 * Optimize database tables.
	 *
	 * @return void
	 */
	private function optimize_database_tables(): void {
		global $wpdb;

		// Get all WordPress tables.
		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );

		foreach ( $tables as $table ) {
			$table_name = $table[0];
			// Only optimize WordPress tables with the correct prefix.
			if ( strpos( $table_name, $wpdb->prefix ) === 0 ) {
				// Validate table name contains only alphanumeric characters and underscores.
				if ( preg_match( '/^[a-zA-Z0-9_]+$/', $table_name ) ) {
					$wpdb->query( "OPTIMIZE TABLE `{$table_name}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				}
			}
		}
	}

	/**
	 * Get cleanup options with defaults.
	 *
	 * @return array Cleanup options.
	 */
	private function get_cleanup_options(): array {
		$defaults = array(
			'cleanup_revisions'     => true,
			'keep_revisions'        => 5,
			'cleanup_transients'    => true,
			'cleanup_spam'          => true,
			'cleanup_orphaned_meta' => true,
			'cleanup_auto_drafts'   => true,
			'optimize_tables'       => false,
		);

		return array_merge( $defaults, (array) $this->get_setting( 'cleanup_options', array() ) );
	}

	/**
	 * Display admin notice for manual cleanup.
	 *
	 * @return void
	 */
	public function admin_cleanup_notice(): void {
		// Only show to administrators.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only show on specific admin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wp-support' ) === false ) {
			return;
		}

		// Check if cleanup was just run.
		if ( isset( $_GET['wps_cleanup'] ) && 'success' === sanitize_text_field( wp_unslash( $_GET['wps_cleanup'] ) ) ) {
			// Mark as dismissed to prevent re-display on refresh.
			WPS_Notice_Manager::dismiss_notice( 'success_database_cleanup_completed' );
			// Use notice manager for persistent dismissal.
			WPS_Notice_Manager::render_notice(
				'success_database_cleanup_completed',
				esc_html__( 'Database cleanup completed successfully.', 'plugin-wp-support-thisismyurl' ),
				'success'
			);
			// Redirect to remove query param after showing once.
			wp_safe_remote_post(
				add_query_arg( 'wps_cleanup', null ),
				array( 'blocking' => false )
			);
			return;
		}

		// Show manual cleanup option.
		$cleanup_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wps_run_database_cleanup' ),
			'wps_run_database_cleanup'
		);

		echo '<div class="notice notice-info"><p>' .
			esc_html__( 'Database Cleanup is enabled. ', 'plugin-wp-support-thisismyurl' ) .
			'<a href="' . esc_url( $cleanup_url ) . '" class="button button-small">' .
			esc_html__( 'Run Cleanup Now', 'plugin-wp-support-thisismyurl' ) .
			'</a></p></div>';
	}

	/**
	 * Show success notice after manual cleanup.
	 *
	 * @return void
	 */
	public function show_cleanup_success_notice(): void {
		// Only show to administrators on settings page.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if we're on the settings page and cleanup was successful.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['wps_cleanup'] ) || 'success' !== sanitize_text_field( wp_unslash( $_GET['wps_cleanup'] ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wp-support' ) === false ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Database cleanup completed successfully.', 'plugin-wp-support-thisismyurl' );
		echo '</p></div>';
	}

	/**
	 * Handle manual cleanup request.
	 *
	 * @return void
	 */
	public function handle_manual_cleanup(): void {
		// Verify nonce.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wps_run_database_cleanup' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Run cleanup.
		$this->run_cleanup();

		// Redirect back with success message.
		// Prefer referer (settings tab), fallback to settings, then dashboard.
		$referer = wp_get_referer();
		if ( $referer && strpos( $referer, 'page=wp-support' ) !== false ) {
			$redirect_url = add_query_arg( array( 'wps_cleanup' => 'success' ), $referer );
		} else {
			$redirect_url = add_query_arg(
				array(
					'page'        => 'wp-support',
					'WPS_tab'     => 'settings',
					'wps_cleanup' => 'success',
				),
				admin_url( 'admin.php' )
			);
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
