<?php
/**
 * Feature: Database Cleanup
 *
 * Automated cleanup of database overhead including post revisions, transients,
 * spam comments, and orphaned metadata for improved database performance.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Database_Cleanup
 *
 * Comprehensive database cleanup and optimization.
 */
final class WPSHADOW_Feature_Database_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'database-cleanup',
				'name'               => __( 'Database Cleanup & Optimization', 'plugin-wpshadow' ),
				'description'        => __( 'Give your database a spring cleaning - we remove old revisions, expired data, and clutter so your site runs faster and uses less space.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'advanced',
				'license_level'      => 3,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-database',
				'category'           => 'maintenance',
				'priority'           => 70,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'cleanup_revisions'     => __( 'Clean Post Revisions', 'plugin-wpshadow' ),
					'cleanup_transients'    => __( 'Remove Expired Transients', 'plugin-wpshadow' ),
					'cleanup_spam'          => __( 'Delete Spam Comments', 'plugin-wpshadow' ),
					'cleanup_orphaned_meta' => __( 'Remove Orphaned Metadata', 'plugin-wpshadow' ),
					'cleanup_auto_drafts'   => __( 'Delete Auto-Drafts', 'plugin-wpshadow' ),
					'optimize_tables'       => __( 'Optimize Database Tables', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'cleanup_revisions'     => true,
						'cleanup_transients'    => true,
						'cleanup_spam'          => true,
						'cleanup_orphaned_meta' => true,
						'cleanup_auto_drafts'   => true,
						'optimize_tables'       => false,
					)
				);
			}
		}
		
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
		
		$this->log_activity( 'feature_initialized', 'Database Cleanup feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Schedule cleanup events only if at least one cleanup option is enabled.
		$has_any_enabled = get_option( 'wpshadow_database-cleanup_cleanup_revisions', true )
			|| get_option( 'wpshadow_database-cleanup_cleanup_transients', true )
			|| get_option( 'wpshadow_database-cleanup_cleanup_spam', true )
			|| get_option( 'wpshadow_database-cleanup_cleanup_orphaned_meta', true )
			|| get_option( 'wpshadow_database-cleanup_cleanup_auto_drafts', true )
			|| get_option( 'wpshadow_database-cleanup_optimize_tables', false );
		
		if ( $has_any_enabled ) {
			add_action( 'init', array( $this, 'schedule_cleanup' ) );
		}

		// Register cleanup hooks.
		add_action( 'wpshadow_database_cleanup', array( $this, 'run_cleanup' ) );

		// Admin notice removed - cleanup is automated via WP-Cron schedule.
		// Users can manage settings in Dashboard Settings tab.

		// Show success notice after manual cleanup.
		add_action( 'admin_notices', array( $this, 'show_cleanup_success_notice' ) );

		// Handle manual cleanup action.
		add_action( 'admin_post_WPSHADOW_run_database_cleanup', array( $this, 'handle_manual_cleanup' ) );
	}

	/**
	 * Schedule automated database cleanup.
	 *
	 * @return void
	 */
	public function schedule_cleanup(): void {
		if ( ! wp_next_scheduled( 'wpshadow_database_cleanup' ) ) {
			// Schedule weekly cleanup by default.
			$frequency = $this->get_setting( 'cleanup_frequency', 'weekly' );
			wp_schedule_event( time(), $frequency, 'wpshadow_database_cleanup' );
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
		if ( ( $options['cleanup_revisions'] ?? true ) && get_option( 'wpshadow_database-cleanup_cleanup_revisions', true ) ) {
			$stats['revisions_deleted'] = $this->cleanup_revisions( $options['keep_revisions'] ?? 5 );
			if ( $stats['revisions_deleted'] > 0 ) {
				$this->log_activity(
					'cleanup_revisions',
					sprintf( __( 'Deleted %d post revisions', 'plugin-wpshadow' ), $stats['revisions_deleted'] ),
					'success'
				);
			}
		}

		// Clean up expired transients.
		if ( ( $options['cleanup_transients'] ?? true ) && get_option( 'wpshadow_database-cleanup_cleanup_transients', true ) ) {
			$stats['transients_deleted'] = $this->cleanup_transients();
			if ( $stats['transients_deleted'] > 0 ) {
				$this->log_activity(
					'cleanup_transients',
					sprintf( __( 'Removed %d expired transients', 'plugin-wpshadow' ), $stats['transients_deleted'] ),
					'success'
				);
			}
		}

		// Clean up spam comments.
		if ( ( $options['cleanup_spam'] ?? true ) && get_option( 'wpshadow_database-cleanup_cleanup_spam', true ) ) {
			$stats['spam_comments_deleted'] = $this->cleanup_spam_comments();
			if ( $stats['spam_comments_deleted'] > 0 ) {
				$this->log_activity(
					'cleanup_spam',
					sprintf( __( 'Deleted %d spam comments', 'plugin-wpshadow' ), $stats['spam_comments_deleted'] ),
					'success'
				);
			}
		}

		// Clean up orphaned metadata.
		if ( ( $options['cleanup_orphaned_meta'] ?? true ) && get_option( 'wpshadow_database-cleanup_cleanup_orphaned_meta', true ) ) {
			$stats['orphaned_meta_deleted'] = $this->cleanup_orphaned_meta();
			if ( $stats['orphaned_meta_deleted'] > 0 ) {
				$this->log_activity(
					'cleanup_orphaned_meta',
					sprintf( __( 'Removed %d orphaned metadata entries', 'plugin-wpshadow' ), $stats['orphaned_meta_deleted'] ),
					'success'
				);
			}
		}

		// Clean up auto-drafts.
		if ( ( $options['cleanup_auto_drafts'] ?? true ) && get_option( 'wpshadow_database-cleanup_cleanup_auto_drafts', true ) ) {
			$stats['auto_drafts_deleted'] = $this->cleanup_auto_drafts();
			if ( $stats['auto_drafts_deleted'] > 0 ) {
				$this->log_activity(
					'cleanup_auto_drafts',
					sprintf( __( 'Deleted %d auto-drafts', 'plugin-wpshadow' ), $stats['auto_drafts_deleted'] ),
					'success'
				);
			}
		}

		// Optimize tables if enabled.
		if ( ( $options['optimize_tables'] ?? false ) && get_option( 'wpshadow_database-cleanup_optimize_tables', false ) ) {
			$this->optimize_database_tables();
			$this->log_activity(
				'optimize_tables',
				__( 'Optimized database tables', 'plugin-wpshadow' ),
				'success'
			);
		}

		// Store last cleanup timestamp.
		update_option( 'wpshadow_last_database_cleanup', time() );

		// Log cleanup activity.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			\WPShadow\WPSHADOW_Activity_Logger::log(
				'database_cleanup',
				__( 'Database cleanup completed', 'plugin-wpshadow' ),
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
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		// Check if cleanup was just run.
		if ( isset( $_GET['wpshadow_cleanup'] ) && 'success' === sanitize_text_field( wp_unslash( $_GET['wpshadow_cleanup'] ) ) ) {
			// Mark as dismissed to prevent re-display on refresh.
			WPSHADOW_Notice_Manager::dismiss_notice( 'success_database_cleanup_completed' );
			// Use notice manager for persistent dismissal.
			WPSHADOW_Notice_Manager::render_notice(
				'success_database_cleanup_completed',
				esc_html__( 'Database cleanup completed successfully.', 'plugin-wpshadow' ),
				'success'
			);
			// Redirect to remove query param after showing once.
			wp_safe_remote_post(
				add_query_arg( 'wpshadow_cleanup', null ),
				array( 'blocking' => false )
			);
			return;
		}

		// Show manual cleanup option.
		$cleanup_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=WPSHADOW_run_database_cleanup' ),
			'wpshadow_run_database_cleanup'
		);

		echo '<div class="notice notice-info"><p>' .
			esc_html__( 'Database Cleanup is enabled. ', 'plugin-wpshadow' ) .
			'<a href="' . esc_url( $cleanup_url ) . '" class="button button-small">' .
			esc_html__( 'Run Cleanup Now', 'plugin-wpshadow' ) .
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
		if ( ! isset( $_GET['wpshadow_cleanup'] ) || 'success' !== sanitize_text_field( wp_unslash( $_GET['wpshadow_cleanup'] ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Database cleanup completed successfully.', 'plugin-wpshadow' );
		echo '</p></div>';
	}

	/**
	 * Handle manual cleanup request.
	 *
	 * @return void
	 */
	public function handle_manual_cleanup(): void {
		// Verify nonce.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpshadow_run_database_cleanup' ) ) {
			wp_die( esc_html__( 'Your session expired. Please refresh and try again.', 'plugin-wpshadow' ) );
		}

		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'plugin-wpshadow' ) );
		}

		// Run cleanup.
		$this->run_cleanup();

		// Redirect back with success message.
		// Prefer referer (settings tab), fallback to settings, then dashboard.
		$referer = wp_get_referer();
		if ( $referer && strpos( $referer, 'page=wpshadow' ) !== false ) {
			$redirect_url = add_query_arg( array( 'wpshadow_cleanup' => 'success' ), $referer );
		} else {
			$redirect_url = add_query_arg(
				array(
					'page'        => 'wpshadow',
					'wpshadow_tab'     => 'settings',
					'wpshadow_cleanup' => 'success',
				),
				admin_url( 'admin.php' )
			);
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Add Site Health tests.
	 *
	 * @return void
	 */
	public function add_site_health_tests(): void {
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Register Database Cleanup Site Health test.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_database_cleanup'] = array(
			'label' => __( 'Database Cleanup', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_database_cleanup' ),
		);
		return $tests;
	}

	/**
	 * Test Database Cleanup configuration.
	 *
	 * @return array Test result.
	 */
	public function test_database_cleanup(): array {
		$is_enabled = $this->is_enabled();
		$last_cleanup = get_option( 'wpshadow_last_database_cleanup', 0 );
		$enabled_count = 0;
		
		if ( get_option( 'wpshadow_database-cleanup_cleanup_revisions', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_database-cleanup_cleanup_transients', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_database-cleanup_cleanup_spam', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_database-cleanup_cleanup_orphaned_meta', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_database-cleanup_cleanup_auto_drafts', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_database-cleanup_optimize_tables', false ) ) {
			++$enabled_count;
		}

		if ( $is_enabled && $enabled_count > 0 ) {
			$days_since_cleanup = $last_cleanup > 0 ? floor( ( time() - $last_cleanup ) / DAY_IN_SECONDS ) : 999;
			$status_label = $days_since_cleanup < 7 ? 'good' : 'recommended';
			
			return array(
				'label'       => __( 'Database cleanup is active', 'plugin-wpshadow' ),
				'status'      => $status_label,
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					wp_kses_post(
						sprintf(
							/* translators: 1: Number of enabled cleanup types, 2: Days since last cleanup */
							__( 'Database cleanup is enabled with %1$d cleanup types active. Last cleanup was %2$d days ago.', 'plugin-wpshadow' ),
							$enabled_count,
							$days_since_cleanup
						)
					)
				),
				'actions'     => sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url( 'admin.php?page=wpshadow-feature-details&feature=database-cleanup' ) ),
					esc_html__( 'View Database Cleanup Settings', 'plugin-wpshadow' )
				),
				'test'        => 'wpshadow_database_cleanup',
			);
		}

		return array(
			'label'       => __( 'Database cleanup is not configured', 'plugin-wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'orange',
			),
			'description' => '<p>' . __( 'Enabling database cleanup removes unnecessary data and improves query performance.', 'plugin-wpshadow' ) . '</p>',
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=wpshadow-feature-details&feature=database-cleanup' ) ),
				esc_html__( 'Configure Database Cleanup', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_database_cleanup',
		);
	}
}
