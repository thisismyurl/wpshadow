<?php
/**
 * Site Audit Generator - Comprehensive performance, security, and optimization analysis.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Audit Manager Class
 */
class WPSHADOW_Site_Audit {

	/**
	 * Audit reports option key.
	 */
	private const REPORTS_KEY = 'wpshadow_site_audit_reports';

	/**
	 * Initialize Site Audit system.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'wp_ajax_WPSHADOW_generate_audit', array( __CLASS__, 'handle_audit_generation' ) );
		add_action( 'wp_ajax_WPSHADOW_toggle_autoload', array( __CLASS__, 'handle_toggle_autoload' ) );
	}

	/**
	 * Generate comprehensive site audit.
	 *
	 * @return array Audit report data.
	 */
	public static function generate_audit(): array {
		$report = array(
			'id'           => wp_generate_uuid4(),
			'timestamp'    => time(),
			'wordpress'    => array(),
			'performance'  => array(),
			'security'     => array(),
			'optimization' => array(),
			'plugins'      => array(),
			'theme'        => array(),
			'broken_links' => array(),
			'missing_alt'  => array(),
			'php_compat'   => array(),
			'autoload'     => array(),
		);

		// WordPress analysis.
		$report['wordpress'] = self::audit_wordpress();

		// Performance analysis.
		$report['performance'] = self::audit_performance();

		// Security analysis.
		$report['security'] = self::audit_security();

		// Database optimization opportunities.
		$report['optimization'] = self::audit_optimization();

		// Autoload analysis (wp_options).
		$report['autoload'] = self::audit_autoload();

		// Plugin analysis.
		$report['plugins'] = self::audit_plugins();

		// Theme analysis.
		$report['theme'] = self::audit_theme();

		// Broken links analysis.
		$report['broken_links'] = self::audit_broken_links();

		// Missing alt tags analysis.
		$report['missing_alt'] = self::audit_missing_alt_tags();

		// PHP compatibility analysis.
		$report['php_compat'] = self::audit_php_compatibility();

		// Store report.
		$reports                  = get_option( self::REPORTS_KEY, array() );
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
			'version'             => $wp_version,
			'is_multisite'        => is_multisite(),
			'debug_mode'          => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'debug_log'           => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
			'auto_updates'        => (bool) get_option( 'auto_core_update_triggered' ),
			'wp_cron'             => (bool) wp_next_scheduled( 'wp_scheduled_auto_draft_delete' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
			'post_count'          => wp_count_posts()->publish ?? 0,
			'user_count'          => count_users()['total_users'] ?? 0,
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
		$drafts        = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='draft'" );
		$revisions     = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='revision'" );
		$spam_comments = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam'" );

		return array(
			'database_size'     => size_format( $db_size ),
			'database_size_raw' => $db_size,
			'tables_count'      => count( $tables ),
			'draft_posts'       => intval( $drafts ),
			'post_revisions'    => intval( $revisions ),
			'spam_comments'     => intval( $spam_comments ),
			'transients_count'  => count( get_transient( 'wpshadow_transient_count' ) ?? array() ),
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
				'level'       => 'warning',
				'title'       => 'XML-RPC Enabled',
				'description' => 'XML-RPC can be a security risk if not needed. Consider disabling it.',
				'fix'         => 'Add filter: add_filter("xmlrpc_enabled", "__return_false");',
			);
		}

		// Check default admin user (ID 1).
		$admin = get_user_by( 'id', 1 );
		if ( $admin && 'admin' === $admin->user_login ) {
			$issues[] = array(
				'level'       => 'warning',
				'title'       => 'Default Admin Username',
				'description' => 'Default "admin" username is a security risk.',
				'fix'         => 'Rename user or create new admin account and delete default.',
			);
		}

		// Check for outdated plugins (enhanced).
		$outdated_plugins = self::get_outdated_plugins();
		if ( ! empty( $outdated_plugins ) ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'Outdated Plugins',
				'description' => count( $outdated_plugins ) . ' plugin(s) have available updates.',
				'plugins'     => $outdated_plugins,
				'fix'         => 'Update plugins immediately to ensure security and compatibility.',
			);
		}

		// Check for outdated theme.
		$outdated_themes = self::get_outdated_themes();
		if ( ! empty( $outdated_themes ) ) {
			$issues[] = array(
				'level'       => 'warning',
				'title'       => 'Outdated Theme',
				'description' => count( $outdated_themes ) . ' theme(s) have available updates.',
				'themes'      => $outdated_themes,
				'fix'         => 'Update themes to ensure security and compatibility.',
			);
		}

		// Check SSL/HTTPS.
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );
		$has_ssl = strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0;
		
		if ( ! $has_ssl ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'HTTPS Not Enabled',
				'description' => 'Site does not use HTTPS. Modern browsers may flag non-HTTPS sites as "Not Secure".',
				'fix'         => 'Obtain an SSL certificate from your hosting provider, update WordPress Address and Site Address URLs to use https:// in Settings → General, and enable HTTPS enforcement in WPS Security Hardening.',
			);
		}

		// Check for WordPress core updates.
		$core_updates = get_core_updates();
		if ( ! empty( $core_updates ) && isset( $core_updates[0]->response ) && 'upgrade' === $core_updates[0]->response ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'WordPress Core Update Available',
				'description' => 'WordPress ' . $core_updates[0]->current . ' is available. Current version: ' . get_bloginfo( 'version' ),
				'fix'         => 'Update WordPress core immediately.',
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
				'title'       => 'Cleanup Old Post Revisions',
				'description' => "You have {$revision_count} post revisions. Clean old ones to reduce database size.",
				'action'      => 'wpshadow_cleanup_revisions',
			);
		}

		// Check for spam comments.
		$spam_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam'" );
		if ( $spam_count > 50 ) {
			$suggestions[] = array(
				'title'       => 'Delete Spam Comments',
				'description' => "You have {$spam_count} spam comments. Delete to improve performance.",
				'action'      => 'wpshadow_delete_spam_comments',
			);
		}

		// Check for auto draft cleanup.
		$auto_draft = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='auto-draft' AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)" );
		if ( $auto_draft > 0 ) {
			$suggestions[] = array(
				'title'       => 'Delete Old Auto Drafts',
				'description' => "You have {$auto_draft} old auto-draft posts. Clean to save space.",
				'action'      => 'wpshadow_cleanup_auto_drafts',
			);
		}

		// Check for unused plugins.
		$unused = self::find_unused_plugins();
		if ( ! empty( $unused ) ) {
			$suggestions[] = array(
				'title'       => 'Deactivate Unused Plugins',
				'description' => 'Found ' . count( $unused ) . ' inactive plugin(s). Deactivate unused plugins to improve security.',
				'plugins'     => $unused,
				'action'      => 'wpshadow_deactivate_plugins',
			);
		}

		return array(
			'suggestions'       => $suggestions,
			'suggestions_count' => count( $suggestions ),
		);
	}

	/**
	 * Audit wp_options autoload data (the biggest silent killer).
	 *
	 * Every row where autoload = 'yes' is loaded into memory on every single request.
	 * Uninstalled plugins often leave behind megabytes of data here.
	 *
	 * @return array Autoload analysis with offenders.
	 */
	private static function audit_autoload(): array {
		global $wpdb;

		// Query to find largest autoloaded options (from issue description).
		$query = $wpdb->prepare(
			"SELECT option_name, LENGTH(option_value) AS size 
			FROM {$wpdb->options} 
			WHERE autoload = %s 
			ORDER BY size DESC 
			LIMIT 20",
			'yes'
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		$offenders       = array();
		$total_autoload  = 0;
		$offenders_500kb = 0;
		$offenders_100kb = 0;

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$size_bytes      = (int) $row['size'];
				$size_kb         = round( $size_bytes / 1024, 2 );
				$option_name     = (string) $row['option_name'];
				$total_autoload += $size_bytes;

				$severity = 'info';
				if ( $size_kb >= 500 ) {
					$severity = 'critical';
					++$offenders_500kb;
				} elseif ( $size_kb >= 100 ) {
					$severity = 'warning';
					++$offenders_100kb;
				}

				$offenders[] = array(
					'option_name' => $option_name,
					'size_bytes'  => $size_bytes,
					'size_kb'     => $size_kb,
					'size_human'  => size_format( $size_bytes ),
					'severity'    => $severity,
				);
			}
		}

		return array(
			'offenders'       => $offenders,
			'total_autoload'  => $total_autoload,
			'total_human'     => size_format( $total_autoload ),
			'offenders_500kb' => $offenders_500kb,
			'offenders_100kb' => $offenders_100kb,
			'count'           => count( $offenders ),
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
			'active_count'   => count( $active ),
			'inactive_count' => count( $plugins ) - count( $active ),
			'total_count'    => count( $plugins ),
			'active'         => array(),
			'inactive'       => array(),
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
		$outdated  = array();
		$plugins   = get_plugins();
		$transient = get_site_transient( 'update_plugins' );

		if ( ! is_object( $transient ) || empty( $transient->response ) ) {
			return $outdated;
		}

		foreach ( $transient->response as $plugin_path => $update_data ) {
			if ( isset( $plugins[ $plugin_path ] ) ) {
				$outdated[] = array(
					'name'      => $plugins[ $plugin_path ]['Name'] ?? '',
					'current'   => $plugins[ $plugin_path ]['Version'] ?? '0',
					'available' => $update_data->new_version ?? 'unknown',
				);
			}
		}

		return $outdated;
	}

	/**
	 * Get outdated themes.
	 *
	 * @return array Outdated themes list.
	 */
	private static function get_outdated_themes(): array {
		$outdated  = array();
		$themes    = wp_get_themes();
		$transient = get_site_transient( 'update_themes' );

		if ( ! is_object( $transient ) || empty( $transient->response ) ) {
			return $outdated;
		}

		foreach ( $transient->response as $theme_slug => $update_data ) {
			if ( isset( $themes[ $theme_slug ] ) ) {
				$theme      = $themes[ $theme_slug ];
				$outdated[] = array(
					'name'      => $theme->get( 'Name' ),
					'current'   => $theme->get( 'Version' ),
					'available' => $update_data['new_version'] ?? 'unknown',
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
		$unused  = array();
		$active  = get_option( 'active_plugins', array() );
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
	 * Audit for broken links in posts and pages.
	 *
	 * @return array Broken links report.
	 */
	private static function audit_broken_links(): array {
		global $wpdb;

		$broken_links = array();
		$posts        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_content FROM {$wpdb->posts} 
				WHERE post_status = %s 
				AND post_type IN ('post', 'page') 
				LIMIT 100",
				'publish'
			)
		);

		if ( empty( $posts ) ) {
			return array(
				'total_checked' => 0,
				'broken_count'  => 0,
				'links'         => array(),
			);
		}

		$total_links_checked = 0;

		foreach ( $posts as $post ) {
			// Extract all links from post content.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $url ) {
				// Skip anchors, mailto, and tel links.
				if ( strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
					continue;
				}

				// Convert relative URLs to absolute.
				$parsed = wp_parse_url( $url );
				if ( empty( $parsed['scheme'] ) && empty( $parsed['host'] ) ) {
					$url = home_url( $url );
				}

				++$total_links_checked;

				// Check if URL is accessible (basic check).
				$response = wp_safe_remote_head(
					$url,
					array(
						'timeout'     => 5,
						'redirection' => 3,
						'sslverify'   => true,
					)
				);

				if ( is_wp_error( $response ) ) {
					$broken_links[] = array(
						'post_id'    => $post->ID,
						'post_title' => $post->post_title,
						'url'        => $url,
						'error'      => $response->get_error_message(),
					);
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					if ( $status_code >= 400 ) {
						$broken_links[] = array(
							'post_id'     => $post->ID,
							'post_title'  => $post->post_title,
							'url'         => $url,
							'status_code' => $status_code,
						);
					}
				}

				// Limit to prevent timeout (check max 50 links).
				if ( $total_links_checked >= 50 ) {
					break 2;
				}
			}
		}

		return array(
			'total_checked' => $total_links_checked,
			'broken_count'  => count( $broken_links ),
			'links'         => $broken_links,
		);
	}

	/**
	 * Audit for missing alt tags in images.
	 *
	 * @return array Missing alt tags report.
	 */
	private static function audit_missing_alt_tags(): array {
		global $wpdb;

		$missing_alt = array();
		$posts       = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_content FROM {$wpdb->posts} 
				WHERE post_status = %s 
				AND post_type IN ('post', 'page') 
				LIMIT 100",
				'publish'
			)
		);

		if ( empty( $posts ) ) {
			return array(
				'total_images'  => 0,
				'missing_count' => 0,
				'images'        => array(),
			);
		}

		$total_images = 0;

		foreach ( $posts as $post ) {
			// Extract all img tags from post content.
			preg_match_all( '/<img[^>]+>/i', $post->post_content, $matches );

			if ( empty( $matches[0] ) ) {
				continue;
			}

			foreach ( $matches[0] as $img_tag ) {
				++$total_images;

				// Check if alt attribute exists and is not empty.
				if ( ! preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match ) || empty( trim( $alt_match[1] ) ) ) {
					// Extract src for reference.
					preg_match( '/src=["\']([^"\']+)["\']/', $img_tag, $src_match );
					$src = $src_match[1] ?? 'unknown';

					$missing_alt[] = array(
						'post_id'    => $post->ID,
						'post_title' => $post->post_title,
						'src'        => $src,
					);
				}
			}
		}

		return array(
			'total_images'  => $total_images,
			'missing_count' => count( $missing_alt ),
			'images'        => $missing_alt,
		);
	}

	/**
	 * Audit PHP version compatibility.
	 *
	 * @return array PHP compatibility report.
	 */
	private static function audit_php_compatibility(): array {
		$current_php     = PHP_VERSION;
		$recommended_php = '8.1'; // WordPress recommended minimum as of WP 6.4.
		$min_wp_php      = '7.4'; // WordPress absolute minimum as of WP 6.4.

		/**
		 * Filter the recommended PHP version for compatibility checks.
		 *
		 * @param string $recommended_php Recommended PHP version.
		 */
		$recommended_php = apply_filters( 'wpshadow_audit_recommended_php_version', $recommended_php );

		/**
		 * Filter the minimum WordPress PHP version for compatibility checks.
		 *
		 * @param string $min_wp_php Minimum WordPress PHP version.
		 */
		$min_wp_php = apply_filters( 'wpshadow_audit_minimum_php_version', $min_wp_php );

		$issues = array();

		// Check if PHP version is below recommended.
		if ( version_compare( $current_php, $recommended_php, '<' ) ) {
			$issues[] = array(
				'level'       => 'warning',
				'title'       => 'PHP Version Below Recommended',
				'description' => sprintf(
					'Current PHP version %s is below the recommended %s. Consider upgrading for better performance and security.',
					$current_php,
					$recommended_php
				),
			);
		}

		// Check if PHP version is below WordPress minimum.
		if ( version_compare( $current_php, $min_wp_php, '<' ) ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'PHP Version Below WordPress Minimum',
				'description' => sprintf(
					'Current PHP version %s is below WordPress minimum %s. Upgrade immediately.',
					$current_php,
					$min_wp_php
				),
			);
		}

		// Check plugins for PHP requirements.
		$plugins              = get_plugins();
		$incompatible_plugins = array();

		foreach ( $plugins as $plugin_path => $plugin_data ) {
			if ( isset( $plugin_data['RequiresPHP'] ) && ! empty( $plugin_data['RequiresPHP'] ) ) {
				$required_php = $plugin_data['RequiresPHP'];
				if ( version_compare( $current_php, $required_php, '<' ) ) {
					$incompatible_plugins[] = array(
						'name'         => $plugin_data['Name'] ?? 'Unknown',
						'requires_php' => $required_php,
					);
				}
			}
		}

		if ( ! empty( $incompatible_plugins ) ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'Plugins Require Higher PHP Version',
				'description' => sprintf(
					'%d plugin(s) require a higher PHP version than currently installed.',
					count( $incompatible_plugins )
				),
				'plugins'     => $incompatible_plugins,
			);
		}

		// Check theme PHP requirements.
		$theme        = wp_get_theme();
		$theme_php    = $theme->get( 'RequiresPHP' );
		$theme_issues = array();

		if ( ! empty( $theme_php ) && version_compare( $current_php, $theme_php, '<' ) ) {
			$theme_issues[] = array(
				'name'         => $theme->get( 'Name' ),
				'requires_php' => $theme_php,
			);
		}

		if ( ! empty( $theme_issues ) ) {
			$issues[] = array(
				'level'       => 'critical',
				'title'       => 'Theme Requires Higher PHP Version',
				'description' => sprintf(
					'Active theme requires PHP %s but current version is %s.',
					$theme_php,
					$current_php
				),
				'themes'      => $theme_issues,
			);
		}

		return array(
			'current_version'     => $current_php,
			'recommended_version' => $recommended_php,
			'issues_count'        => count( $issues ),
			'issues'              => $issues,
			'is_compatible'       => empty( $issues ),
		);
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
			'wpshadow',
			__( 'Site Audit', 'plugin-wpshadow' ),
			__( 'Audit', 'plugin-wpshadow' ),
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
		check_ajax_referer( 'wpshadow_audit_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wpshadow' ) );
		}

		$report = self::generate_audit();
		wp_send_json_success( $report );
	}

	/**
	 * Handle AJAX toggle autoload request.
	 *
	 * @return void
	 */
	public static function handle_toggle_autoload(): void {
		check_ajax_referer( 'wpshadow_audit_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'plugin-wpshadow' ) );
		}

		$option_name = \WPShadow\WPSHADOW_get_post_text( 'option_name' );

		if ( empty( $option_name ) ) {
			wp_send_json_error( __( 'Invalid option name', 'plugin-wpshadow' ) );
		}

		global $wpdb;

		// Update the autoload flag to 'no'.
		$result = $wpdb->update(
			$wpdb->options,
			array( 'autoload' => 'no' ),
			array( 'option_name' => $option_name ),
			array( '%s' ),
			array( '%s' )
		);

		if ( false === $result ) {
			wp_send_json_error( __( 'Failed to update option', 'plugin-wpshadow' ) );
		}

		// Clear cache to ensure the change is reflected.
		wp_cache_delete( $option_name, 'options' );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: option name */
					__( 'Successfully disabled autoload for %s', 'plugin-wpshadow' ),
					$option_name
				),
			)
		);
	}

	/**
	 * Render audit page.
	 *
	 * @return void
	 */
	public static function render_audit_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wpshadow' ) );
		}

		$reports = self::get_reports();
		$latest  = ! empty( $reports ) ? end( $reports ) : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Audit', 'plugin-wpshadow' ); ?></h1>
			<p><?php esc_html_e( 'Comprehensive analysis of site performance, security, and optimization opportunities.', 'plugin-wpshadow' ); ?></p>

			<button id="wps-audit-generate" class="button button-primary">
				<?php esc_html_e( '📋 Generate New Audit', 'plugin-wpshadow' ); ?>
			</button>

			<?php if ( ! $latest ) : ?>
				<p style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #ffb900;">
					<?php esc_html_e( 'No audits generated yet. Create one to see recommendations.', 'plugin-wpshadow' ); ?>
				</p>
			<?php else : ?>
				<div style="margin-top: 30px;">
					<h2><?php esc_html_e( 'Latest Audit Report', 'plugin-wpshadow' ); ?></h2>
					<p style="color: #666; font-size: 13px;">
						<?php echo esc_html( wp_date( 'F j, Y \a\t g:i a', $latest['timestamp'] ) ); ?>
					</p>

					<!-- Security Issues -->
					<?php if ( ! empty( $latest['security']['issues'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #fee; border-left: 4px solid #c00;">
							<h3><?php esc_html_e( '🚨 Security Issues Found', 'plugin-wpshadow' ); ?> (<?php echo intval( count( $latest['security']['issues'] ) ); ?>)</h3>
							<ul style="margin: 10px 0; padding-left: 20px;">
								<?php foreach ( $latest['security']['issues'] as $issue ) : ?>
									<li>
										<strong><?php echo esc_html( $issue['title'] ); ?></strong>
										<p style="margin: 5px 0; font-size: 13px; color: #666;">
											<?php echo esc_html( $issue['description'] ); ?>
										</p>
										<?php if ( ! empty( $issue['fix'] ) ) : ?>
											<p style="margin: 5px 0; font-size: 12px; color: #333;"><em><?php echo esc_html( $issue['fix'] ); ?></em></p>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- PHP Compatibility Issues -->
					<?php if ( ! empty( $latest['php_compat']['issues'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #fee; border-left: 4px solid #ff6600;">
							<h3><?php esc_html_e( '⚠️ PHP Compatibility Issues', 'plugin-wpshadow' ); ?> (<?php echo intval( count( $latest['php_compat']['issues'] ) ); ?>)</h3>
							<p style="margin: 10px 0; font-size: 13px;">
								<?php
								printf(
									esc_html__( 'Current PHP Version: %1$s | Recommended: %2$s', 'plugin-wpshadow' ),
									esc_html( $latest['php_compat']['current_version'] ),
									esc_html( $latest['php_compat']['recommended_version'] )
								);
								?>
							</p>
							<ul style="margin: 10px 0; padding-left: 20px;">
								<?php foreach ( $latest['php_compat']['issues'] as $issue ) : ?>
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

					<!-- Broken Links -->
					<?php if ( ! empty( $latest['broken_links']['broken_count'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #ffe; border-left: 4px solid #ff6600;">
							<h3><?php esc_html_e( '🔗 Broken Links Found', 'plugin-wpshadow' ); ?> (<?php echo intval( $latest['broken_links']['broken_count'] ); ?>)</h3>
							<p style="margin: 10px 0; font-size: 13px; color: #666;">
								<?php
								printf(
									esc_html__( 'Checked %1$d links and found %2$d broken.', 'plugin-wpshadow' ),
									intval( $latest['broken_links']['total_checked'] ),
									intval( $latest['broken_links']['broken_count'] )
								);
								?>
							</p>
							<ul style="margin: 10px 0; padding-left: 20px; max-height: 300px; overflow-y: auto;">
								<?php foreach ( array_slice( $latest['broken_links']['links'], 0, 10 ) as $link ) : ?>
									<li style="margin-bottom: 10px;">
										<strong><?php echo esc_html( $link['post_title'] ); ?></strong>
										<br/>
										<code style="font-size: 11px; background: #f5f5f5; padding: 2px 5px;"><?php echo esc_html( $link['url'] ); ?></code>
										<?php if ( ! empty( $link['status_code'] ) ) : ?>
											<span style="color: #c00; font-size: 12px;"> - HTTP <?php echo intval( $link['status_code'] ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
								<?php if ( count( $latest['broken_links']['links'] ) > 10 ) : ?>
									<li style="font-style: italic; color: #666;"><?php printf( esc_html__( 'And %d more...', 'plugin-wpshadow' ), count( $latest['broken_links']['links'] ) - 10 ); ?></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Missing Alt Tags -->
					<?php if ( ! empty( $latest['missing_alt']['missing_count'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #ffe; border-left: 4px solid #ffb900;">
							<h3><?php esc_html_e( '🖼️ Missing Alt Tags', 'plugin-wpshadow' ); ?> (<?php echo intval( $latest['missing_alt']['missing_count'] ); ?>)</h3>
							<p style="margin: 10px 0; font-size: 13px; color: #666;">
								<?php
								printf(
									esc_html__( 'Found %1$d images without alt tags out of %2$d total images checked.', 'plugin-wpshadow' ),
									intval( $latest['missing_alt']['missing_count'] ),
									intval( $latest['missing_alt']['total_images'] )
								);
								?>
							</p>
							<ul style="margin: 10px 0; padding-left: 20px; max-height: 300px; overflow-y: auto;">
								<?php foreach ( array_slice( $latest['missing_alt']['images'], 0, 10 ) as $image ) : ?>
									<li style="margin-bottom: 10px;">
										<strong><?php echo esc_html( $image['post_title'] ); ?></strong>
										<br/>
										<code style="font-size: 11px; background: #f5f5f5; padding: 2px 5px;"><?php echo esc_html( basename( $image['src'] ) ); ?></code>
									</li>
								<?php endforeach; ?>
								<?php if ( count( $latest['missing_alt']['images'] ) > 10 ) : ?>
									<li style="font-style: italic; color: #666;"><?php printf( esc_html__( 'And %d more...', 'plugin-wpshadow' ), count( $latest['missing_alt']['images'] ) - 10 ); ?></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Optimization Suggestions -->
					<?php if ( ! empty( $latest['optimization']['suggestions'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: #eff; border-left: 4px solid #0073aa;">
							<h3><?php esc_html_e( '⚡ Optimization Opportunities', 'plugin-wpshadow' ); ?> (<?php echo intval( count( $latest['optimization']['suggestions'] ) ); ?>)</h3>
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

					<!-- Autoload Audit (wp_options) -->
					<?php if ( ! empty( $latest['autoload']['offenders'] ) ) : ?>
						<div style="margin: 20px 0; padding: 15px; background: <?php echo ( $latest['autoload']['offenders_500kb'] > 0 ) ? '#fee' : '#ffe'; ?>; border-left: 4px solid <?php echo ( $latest['autoload']['offenders_500kb'] > 0 ) ? '#c00' : '#ffb900'; ?>;">
							<h3>
								<?php
								if ( $latest['autoload']['offenders_500kb'] > 0 ) {
									esc_html_e( '⚠️ Autoload Audit (wp_options) - Critical Offenders Found', 'plugin-wpshadow' );
								} else {
									esc_html_e( '⚡ Autoload Audit (wp_options)', 'plugin-wpshadow' );
								}
								?>
							</h3>
							<p style="margin: 10px 0; font-size: 13px; color: #666;">
								<?php
								printf(
									/* translators: %s: total autoload size */
									esc_html__( 'Total autoloaded data: %s. Every row with autoload=yes is loaded into memory on every request.', 'plugin-wpshadow' ),
									esc_html( $latest['autoload']['total_human'] )
								);
								?>
							</p>
							<?php if ( $latest['autoload']['offenders_500kb'] > 0 ) : ?>
								<p style="margin: 10px 0; font-size: 13px; color: #c00; font-weight: bold;">
									<?php
									printf(
										/* translators: %d: number of critical offenders */
										esc_html__( '⚠️ %d option(s) over 500KB found! These should be disabled from autoload.', 'plugin-wpshadow' ),
										intval( $latest['autoload']['offenders_500kb'] )
									);
									?>
								</p>
							<?php endif; ?>
							<table style="width: 100%; font-size: 13px; margin-top: 10px; border-collapse: collapse;">
								<thead>
									<tr style="background: rgba(0,0,0,0.05); border-bottom: 2px solid rgba(0,0,0,0.1);">
										<th style="padding: 8px; text-align: left;"><?php esc_html_e( 'Option Name', 'plugin-wpshadow' ); ?></th>
										<th style="padding: 8px; text-align: right;"><?php esc_html_e( 'Size', 'plugin-wpshadow' ); ?></th>
										<th style="padding: 8px; text-align: center;"><?php esc_html_e( 'Severity', 'plugin-wpshadow' ); ?></th>
										<th style="padding: 8px; text-align: center;"><?php esc_html_e( 'Action', 'plugin-wpshadow' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $latest['autoload']['offenders'] as $offender ) : ?>
										<tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
											<td style="padding: 8px; font-family: monospace; font-size: 12px;"><?php echo esc_html( $offender['option_name'] ); ?></td>
											<td style="padding: 8px; text-align: right; font-weight: bold;">
												<?php echo esc_html( $offender['size_human'] ); ?>
												<span style="color: #999; font-size: 11px;">(<?php echo esc_html( number_format( $offender['size_kb'], 2 ) ); ?> KB)</span>
											</td>
											<td style="padding: 8px; text-align: center;">
												<?php
												$badge_color = '#999';
												$badge_text  = __( 'Info', 'plugin-wpshadow' );
												if ( 'critical' === $offender['severity'] ) {
													$badge_color = '#c00';
													$badge_text  = __( 'Critical', 'plugin-wpshadow' );
												} elseif ( 'warning' === $offender['severity'] ) {
													$badge_color = '#ffb900';
													$badge_text  = __( 'Warning', 'plugin-wpshadow' );
												}
												?>
												<span style="display: inline-block; padding: 2px 8px; background: <?php echo esc_attr( $badge_color ); ?>; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">
													<?php echo esc_html( $badge_text ); ?>
												</span>
											</td>
											<td style="padding: 8px; text-align: center;">
												<button 
													class="button button-small wps-toggle-autoload" 
													data-option="<?php echo esc_attr( $offender['option_name'] ); ?>"
													data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_audit_nonce' ) ); ?>"
													style="font-size: 11px;">
													<?php esc_html_e( 'Disable Autoload', 'plugin-wpshadow' ); ?>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p style="margin-top: 15px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 3px; font-size: 12px; color: #666;">
								<strong><?php esc_html_e( 'Tip:', 'plugin-wpshadow' ); ?></strong>
								<?php esc_html_e( 'If you find a plugin\'s settings taking up 500kb+ but only used on one admin page, update it to autoload = no. WordPress will still fetch it when requested, but it won\'t clog every frontend page load.', 'plugin-wpshadow' ); ?>
							</p>
						</div>
					<?php endif; ?>

					<!-- Database Stats -->
					<div style="margin: 20px 0; padding: 15px; background: #eee;">
						<h3><?php esc_html_e( '📊 Database', 'plugin-wpshadow' ); ?></h3>
						<table style="width: 100%; font-size: 13px;">
							<tr>
								<td><strong><?php esc_html_e( 'Database Size:', 'plugin-wpshadow' ); ?></strong></td>
								<td><?php echo esc_html( $latest['performance']['database_size'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Draft Posts:', 'plugin-wpshadow' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['draft_posts'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Post Revisions:', 'plugin-wpshadow' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['post_revisions'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Spam Comments:', 'plugin-wpshadow' ); ?></strong></td>
								<td><?php echo intval( $latest['performance']['spam_comments'] ); ?></td>
							</tr>
						</table>
					</div>

					<!-- Plugin Stats -->
					<div style="margin: 20px 0; padding: 15px; background: #eee;">
						<h3><?php esc_html_e( '🔌 Plugins', 'plugin-wpshadow' ); ?></h3>
						<p><?php printf( esc_html__( 'Active: %1$d | Inactive: %2$d | Total: %3$d', 'plugin-wpshadow' ), intval( $latest['plugins']['active_count'] ), intval( $latest['plugins']['inactive_count'] ), intval( $latest['plugins']['total_count'] ) ); ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<script>
		document.getElementById('wps-audit-generate')?.addEventListener('click', function() {
			this.disabled = true;
			this.textContent = '<?php esc_html_e( 'Generating Audit...', 'plugin-wpshadow' ); ?>';
			fetch(ajaxurl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: 'action=WPSHADOW_generate_audit&nonce=<?php echo esc_js( wp_create_nonce( 'wpshadow_audit_nonce' ) ); ?>'
			})
			.then(r => r.json())
			.then(d => {
				if (d.success) { location.reload(); }
				else { alert('Error: ' + d.data); this.disabled = false; this.textContent = '<?php esc_html_e( '📋 Generate New Audit', 'plugin-wpshadow' ); ?>'; }
			})
			.catch(e => { alert('Error: ' + e); this.disabled = false; this.textContent = '<?php esc_html_e( '📋 Generate New Audit', 'plugin-wpshadow' ); ?>'; });
		});

		// Handle toggle autoload buttons
		document.querySelectorAll('.wps-toggle-autoload').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to disable autoload for this option? WordPress will still fetch it when needed, but it won\'t be loaded on every request.', 'plugin-wpshadow' ) ); ?>')) {
					return;
				}
				
				const option = this.getAttribute('data-option');
				const nonce = this.getAttribute('data-nonce');
				const button = this;
				
				button.disabled = true;
				button.textContent = '<?php echo esc_js( __( 'Processing...', 'plugin-wpshadow' ) ); ?>';
				
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: 'action=WPSHADOW_toggle_autoload&nonce=' + encodeURIComponent(nonce) + '&option_name=' + encodeURIComponent(option)
				})
				.then(r => r.json())
				.then(d => {
					if (d.success) {
						button.textContent = '<?php echo esc_js( __( 'Disabled ✓', 'plugin-wpshadow' ) ); ?>';
						button.style.background = '#46b450';
						button.style.color = 'white';
						button.style.borderColor = '#46b450';
						setTimeout(function() { location.reload(); }, 1500);
					} else {
						alert('<?php echo esc_js( __( 'Error:', 'plugin-wpshadow' ) ); ?> ' + d.data);
						button.disabled = false;
						button.textContent = '<?php echo esc_js( __( 'Disable Autoload', 'plugin-wpshadow' ) ); ?>';
					}
				})
				.catch(e => {
					alert('<?php echo esc_js( __( 'Error:', 'plugin-wpshadow' ) ); ?> ' + e);
					button.disabled = false;
					button.textContent = '<?php echo esc_js( __( 'Disable Autoload', 'plugin-wpshadow' ) ); ?>';
				});
			});
		});
		</script>
		<?php
	}
}


