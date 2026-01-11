<?php
/**
 * Site Audit Generator - Comprehensive performance, security, and optimization analysis.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Audit Manager Class
 */
class WPS_Site_Audit {

	/**
	 * Audit reports option key.
	 */
	private const REPORTS_KEY = 'WPS_site_audit_reports';

	/**
	 * Initialize Site Audit system.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'wp_ajax_WPS_generate_audit', array( __CLASS__, 'handle_audit_generation' ) );
	}

	/**
	 * Generate comprehensive site audit.
	 *
	 * @return array Audit report data.
	 */
	public static function generate_audit(): array {
		$report = array(
			'id'          => wp_generate_uuid4(),
			'timestamp'   => time(),
			'wordpress'   => array(),
			'performance' => array(),
			'security'    => array(),
			'optimization' => array(),
			'plugins'     => array(),
			'theme'       => array(),
		);

		// WordPress analysis.
		$report['wordpress'] = self::audit_wordpress();

		// Performance analysis.
		$report['performance'] = self::audit_performance();

		// Security analysis.
		$report['security'] = self::audit_security();

		// Database optimization opportunities.
		$report['optimization'] = self::audit_optimization();

		// Plugin analysis.
		$report['plugins'] = self::audit_plugins();

		// Theme analysis.
		$report['theme'] = self::audit_theme();

		// Store report.
		$reports = get_option( self::REPORTS_KEY, array() );
		$reports[ $report['id'] ] = $report;

		// Keep last 5 reports.
		if ( count( $reports ) > 5 ) {
			$oldest_id = array_key_first( $reports );
			unset( $reports[ $oldest_id ] );
		}

		update_option( self::REPORTS_KEY, $reports );

		return $report;
	}

	/**
	 * Audit WordPress core and installation.
	 *
	 * @return array Audit data.
	 */
	private static function audit_wordpress(): array {
		global $wp_version;

		return array(
			'version'          => $wp_version,
			'is_multisite'     => is_multisite(),
			'debug_mode'       => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'debug_log'        => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
			'auto_updates'     => (bool) get_option( 'auto_core_update_triggered' ),
			'wp_cron'          => (bool) wp_next_scheduled( 'wp_scheduled_auto_draft_delete' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
			'post_count'       => wp_count_posts()->publish ?? 0,
			'user_count'       => count_users()['total_users'] ?? 0,
		);
	}

	/**
	 * Audit site performance.
	 *
	 * @return array Performance metrics.
	 */
	private static function audit_performance(): array {
		global $wpdb;

		$db_size = 0;
		$tables  = $wpdb->get_results( 'SHOW TABLE STATUS', OBJECT_K );

		if ( $tables ) {
			foreach ( $tables as $table ) {
				$db_size += (int) ( $table->Data_length ?? 0 ) + (int) ( $table->Index_length ?? 0 );
			}
		}

		// Count posts by status.
		$drafts    = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='draft'" );
		$revisions = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='revision'" );
		$spam_comments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam'" );

		return array(
			'database_size'    => size_format( $db_size ),
			'database_size_raw' => $db_size,
			'tables_count'     => count( $tables ),
			'draft_posts'      => intval( $drafts ),
			'post_revisions'   => intval( $revisions ),
			'spam_comments'    => intval( $spam_comments ),
			'transients_count' => count( get_transient( 'WPS_transient_count' ) ?? array() ),
		);
	}

	/**
	 * Audit security issues.
	 *
	 * @return array Security findings.
	 */
	private static function audit_security(): array {
		global $wpdb;

		$issues = array();

		// Check for XML-RPC (security risk if not needed).
		if ( xmlrpc_enabled() ) {
			$issues[] = array(
				'level'        => 'warning',
				'title'        => 'XML-RPC Enabled',
				'description'  => 'XML-RPC can be a security risk if not needed. Consider disabling it.',
				'fix'          => 'Add filter: add_filter("xmlrpc_enabled", "__return_false");',
			);
		}

		// Check default admin user (ID 1).
		$admin = get_user_by( 'id', 1 );
		if ( $admin && 'admin' === $admin->user_login ) {
			$issues[] = array(
				'level'        => 'warning',
				'title'        => 'Default Admin Username',
				'description'  => 'Default "admin" username is a security risk.',
				'fix'          => 'Rename user or create new admin account and delete default.',
			);
		}

		// Check for outdated plugins.
		$outdated = self::get_outdated_plugins();
		if ( ! empty( $outdated ) ) {
			$issues[] = array(
				'level'        => 'critical',
				'title'        => 'Outdated Plugins',
				'description'  => count( $outdated ) . ' plugin(s) have available updates.',
				'plugins'      => $outdated,
				'fix'          => 'Update plugins immediately.',
			);
		}

		// Check SSL.
		$has_ssl = strpos( home_url(), 'https://' ) !== false;
		if ( ! $has_ssl ) {
			$issues[] = array(
				'level'        => 'critical',
				'title'        => 'SSL Not Enabled',
				'description'  => 'Site does not use HTTPS. Enable SSL for security.',
				'fix'          => 'Enable SSL in hosting control panel and update WordPress Address.',
			);
		}

		return array(
			'issues'         => $issues,
			'issues_count'   => count( $issues ),
			'has_ssl'        => $has_ssl,
			'xmlrpc_enabled' => xmlrpc_enabled(),
		);
	}

	/**
	 * Audit optimization opportunities.
	 *
	 * @return array Optimization suggestions.
	 */
	private static function audit_optimization(): array {
		$suggestions = array();

		// Check for unused revisions.
		global $wpdb;
		$revision_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='revision'" );
		if ( $revision_count > 100 ) {
			$suggestions[] = array(
				'title'        => 'Cleanup Old Post Revisions',
				'description'  => "You have {$revision_count} post revisions. Clean old ones to reduce database size.",
				'action'       => 'WPS_cleanup_revisions',
			);
		}

		// Check for spam comments.
		$spam_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam'" );
		if ( $spam_count > 50 ) {
			$suggestions[] = array(
				'title'        => 'Delete Spam Comments',
				'description'  => "You have {$spam_count} spam comments. Delete to improve performance.",
				'action'       => 'WPS_delete_spam_comments',
			);
		}

		// Check for auto draft cleanup.
		$auto_draft = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='auto-draft' AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)" );
		if ( $auto_draft > 0 ) {
			$suggestions[] = array(
				'title'        => 'Delete Old Auto Drafts',
				'description'  => "You have {$auto_draft} old auto-draft posts. Clean to save space.",
				'action'       => 'WPS_cleanup_auto_drafts',
			);
		}

		// Check for unused plugins.
		$unused = self::find_unused_plugins();
		if ( ! empty( $unused ) ) {
			$suggestions[] = array(
				'title'        => 'Deactivate Unused Plugins',
				'description'  => 'Found ' . count( $unused ) . ' inactive plugin(s). Deactivate unused plugins to improve security.',
				'plugins'      => $unused,
				'action'       => 'WPS_deactivate_plugins',
			);
		}

		return array(
			'suggestions'      => $suggestions,
			'suggestions_count' => count( $suggestions ),
		);
	}

	/**
	 * Audit active plugins.
	 *
	 * @return array Plugin analysis.
	 */
	private static function audit_plugins(): array {
		$active   = get_option( 'active_plugins', array() );
		$plugins  = get_plugins();
		$analysis = array(
			'active_count'    => count( $active ),
			'inactive_count'  => count( $plugins ) - count( $active ),
			'total_count'     => count( $plugins ),
			'active'          => array(),
			'inactive'        => array(),
		);

		foreach ( $plugins as $path => $data ) {
			$plugin_info = array(
				'name'    => $data['Name'] ?? '',
				'version' => $data['Version'] ?? '0',
				'author'  => $data['Author'] ?? 'Unknown',
			);

			if ( in_array( $path, $active, true ) ) {
				$analysis['active'][ $path ] = $plugin_info;
			} else {
				$analysis['inactive'][ $path ] = $plugin_info;
			}
		}

		return $analysis;
	}

	/**
	 * Audit theme.
	 *
	 * @return array Theme analysis.
	 */
	private static function audit_theme(): array {
		$current = wp_get_theme();
		$parent  = $current->parent();

		return array(
			'name'        => $current->get( 'Name' ),
			'version'     => $current->get( 'Version' ),
			'stylesheet'  => get_stylesheet(),
			'has_parent'  => ! empty( $parent->get_stylesheet() ),
			'parent_name' => ! empty( $parent->get_stylesheet() ) ? $parent->get( 'Name' ) : null,
		);
	}

	/**
	 * Get outdated plugins.
	 *
	 * @return array Outdated plugins list.
	 */
	private static function get_outdated_plugins(): array {
		$outdated = array();
		$plugins  = get_plugins();
		$transient = get_transients( 'update_plugins' );

		if ( ! is_object( $transient ) || empty( $transient->response ) ) {
			return $outdated;
		}

		foreach ( $transient->response as $plugin_path => $update_data ) {
			if ( isset( $plugins[ $plugin_path ] ) ) {
				$outdated[] = array(
					'name'     => $plugins[ $plugin_path ]['Name'] ?? '',
					'current'  => $plugins[ $plugin_path ]['Version'] ?? '0',
					'available' => $update_data->new_version ?? 'unknown',
				);
			}
		}

		return $outdated;
	}

	/**
	 * Find inactive plugins.
	 *
	 * @return array Inactive plugins.
	 */
	private static function find_unused_plugins(): array {
		$unused = array();
		$active = get_option( 'active_plugins', array() );
		$plugins = get_plugins();

		foreach ( $plugins as $path => $data ) {
			if ( ! in_array( $path, $active, true ) ) {
				$unused[] = array(
					'name'    => $data['Name'] ?? '',
					'version' => $data['Version'] ?? '0',
					'path'    => $path,
				);
			}
		}

		return $unused;
	}

	/**
	 * Get all audit reports.
	 *
	 * @return array Reports.
	 */
	public static function get_reports(): array {
		return (array) get_option( self::REPORTS_KEY, array() );
	}

	/**
	 * Get single report.
	 *
	 * @param string $report_id Report ID.
	 * @return array|null Report data or null.
	 */
	public static function get_report( string $report_id ): ?array {
		$reports = self::get_reports();
		return $reports[ $report_id ] ?? null;
	}

	/**
	 * Register audit menu.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Site Audit', 'plugin-wp-support-thisismyurl' ),
			__( 'Audit', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-audit',
			array( __CLASS__, 'render_audit_page' )
		);
	}

	/**
	 * Handle AJAX audit generation.
	 *
	 * @return void
	 */
	public static function handle_audit_generation(): void {
		check_ajax_referer( 'WPS_audit_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) );
		}

		$report = self::generate_audit();
		wp_send_json_success( $report );
	}

	/**
	 * Render audit page.
	 *
	 * @return void
	 */
	public static function render_audit_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$reports = self::get_reports();
		$latest  = ! empty( $reports ) ? end( $reports ) : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Audit', 'plugin-wp-support-thisismyurl' ); ?></h1>
			<p><?php esc_html_e( 'Comprehensive analysis of site performance, security, and optimization opportunities.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<button id="wps-audit-generate" class="button button-primary">
				<?php esc_html_e( '📋 Generate New Audit', 'plugin-wp-support-thisismyurl' ); ?>
			</button>

			<?php if ( ! $latest ) : ?>
				<p style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #ffb900;">
					<?php esc_html_e( 'No audits generated yet. Create one to see recommendations.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			<?php else : ?>
				<div style="margin-top: 30px;">
					<h2><?php esc_html_e( 'Latest Audit Report', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<p style="color: #666; font-size: 13px;">
						<?php echo esc_html( wp_date( 'F j, Y \a\t g:i a', $latest['timestamp'] ) ); ?>
					</p>

					<!-- Security Issues -->
					<?php if ( ! empty( $latest['security']['issues'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #fee; border-left: 4px solid #c00;">
							<h3><?php esc_html_e( '🚨 Security Issues Found', 'plugin-wp-support-thisismyurl' ); ?> (<?php echo intval( count( $latest['security']['issues'] ) ); ?>)</h3>
							<ul style="margin: 10px 0; padding-left: 20px;">
								<?php foreach ( $latest['security']['issues'] as $issue ) : ?>
									<li>
										<strong><?php echo esc_html( $issue['title'] ); ?></strong>
										<p style="margin: 5px 0; font-size: 13px; color: #666;">
											<?php echo esc_html( $issue['description'] ); ?>
										</p>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Optimization Suggestions -->
					<?php if ( ! empty( $latest['optimization']['suggestions'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #ffe; border-left: 4px solid #ffb900;">
							<h3><?php esc_html_e( '⚡ Optimization Opportunities', 'plugin-wp-support-thisismyurl' ); ?> (<?php echo intval( count( $latest['optimization']['suggestions'] ) ); ?>)</h3>
							<ul style="margin: 10px 0; padding-left: 20px;">
								<?php foreach ( $latest['optimization']['suggestions'] as $suggestion ) : ?>
									<li>
										<strong><?php echo esc_html( $suggestion['title'] ); ?></strong>
										<p style="margin: 5px 0; font-size: 13px; color: #666;">
											<?php echo esc_html( $suggestion['description'] ); ?>
										</p>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Database Stats -->
					<div style="margin: 20px 0; padding: 15px; background: #eee;">
						<h3><?php esc_html_e( '📊 Database', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<table style="width: 100%; font-size: 13px;">
							<tr>
								<td><strong><?php esc_html_e( 'Database Size:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
								<td><?php echo esc_html( $latest['performance']['database_size'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Draft Posts:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['draft_posts'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Post Revisions:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['post_revisions'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Spam Comments:', 'plugin-wp-support-thisismyurl' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['spam_comments'] ); ?></td>
							</tr>
						</table>
					</div>

					<!-- Plugin Stats -->
					<div style="margin: 20px 0; padding: 15px; background: #eee;">
						<h3><?php esc_html_e( '🔌 Plugins', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<p><?php printf( esc_html__( 'Active: %d | Inactive: %d | Total: %d', 'plugin-wp-support-thisismyurl' ), intval( $latest['plugins']['active_count'] ), intval( $latest['plugins']['inactive_count'] ), intval( $latest['plugins']['total_count'] ) ); ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<script>
		document.getElementById('wps-audit-generate')?.addEventListener('click', function() {
			this.disabled = true;
			fetch(ajaxurl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: 'action=WPS_generate_audit&nonce=<?php echo esc_js( wp_create_nonce( 'WPS_audit_nonce' ) ); ?>'
			})
			.then(r => r.json())
			.then(d => {
				if (d.success) { location.reload(); }
				else { alert('Error: ' + d.data); this.disabled = false; }
			})
			.catch(e => { alert('Error: ' + e); this.disabled = false; });
		});
		</script>
		<?php
	}
}


