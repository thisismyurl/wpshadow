<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Baseline Manager
 *
 * Maintains site baseline snapshot for anomaly detection.
 * Detects unusual changes in site configuration.
 *
 * Tracks:
 * - Plugin count & list
 * - Active plugins
 * - Theme
 * - PHP version
 * - WordPress version
 *
 * Used by WPShadow Guardian to alert on unexpected changes.
 *
 * Data Storage:
 * - wpshadow_site_baseline: Initial snapshot
 * - wpshadow_baseline_history: Historical snapshots (last 30)
 */
class Baseline_Manager {

	/**
	 * Create initial baseline (first run)
	 *
	 * Called when WPShadow Guardian first enabled.
	 * Captures current site state as baseline.
	 */
	public static function create_baseline(): void {
		$baseline = array(
			'created_at'           => current_time( 'mysql' ),
			'updated_at'           => current_time( 'mysql' ),
			'security_findings'    => 0,
			'performance_findings' => 0,
			'plugin_count'         => count( get_plugins() ),
			'active_plugins'       => count( get_option( 'active_plugins', array() ) ),
			'active_plugins_list'  => get_option( 'active_plugins', array() ),
			'theme'                => wp_get_theme()->get( 'Name' ),
			'php_version'          => phpversion(),
			'wp_version'           => get_bloginfo( 'version' ),
			'memory_limit'         => WP_MEMORY_LIMIT,
			'timezone'             => get_option( 'timezone_string' ),
			'blog_public'          => get_option( 'blog_public' ),
		);

		update_option( 'wpshadow_site_baseline', $baseline );

		// Store in history
		self::add_to_history( $baseline );
	}

	/**
	 * Get current baseline
	 *
	 * @return array Current baseline or empty if not created
	 */
	public static function get_baseline(): array {
		return get_option( 'wpshadow_site_baseline', array() );
	}

	/**
	 * Update baseline with latest site state
	 *
	 * Called daily. Captures current findings for anomaly detection.
	 *
	 * @param array $findings Current findings from health check
	 */
	public static function update_baseline( array $findings ): void {
		$baseline = self::get_baseline();

		if ( empty( $baseline ) ) {
			self::create_baseline();
			return;
		}

		// Count findings by severity
		$critical_count    = 0;
		$performance_count = 0;

		foreach ( $findings as $finding ) {
			$severity = $finding['severity'] ?? 'medium';

			if ( $severity === 'critical' ) {
				++$critical_count;
			} elseif ( $severity === 'warning' ) {
				++$performance_count;
			}
		}

		// Update finding counts
		$baseline['last_check_at']        = current_time( 'mysql' );
		$baseline['security_findings']    = $critical_count;
		$baseline['performance_findings'] = $performance_count;
		$baseline['findings_total']       = count( $findings );

		// Check for plugin changes
		$baseline['plugin_count_current']   = count( get_plugins() );
		$baseline['active_plugins_current'] = count( get_option( 'active_plugins', array() ) );

		update_option( 'wpshadow_site_baseline', $baseline );
	}

	/**
	 * Detect anomalies in site configuration
	 *
	 * Compares current state with baseline to detect unexpected changes.
	 * Returns array of detected anomalies.
	 *
	 * @return array Anomalies detected
	 */
	public static function detect_anomalies(): array {
		$baseline = self::get_baseline();

		if ( empty( $baseline ) ) {
			return array(); // No baseline to compare against
		}

		$anomalies = array();

		// Check plugin count change
		$current_plugin_count  = count( get_plugins() );
		$baseline_plugin_count = $baseline['plugin_count'] ?? $current_plugin_count;

		if ( abs( $current_plugin_count - $baseline_plugin_count ) >= 3 ) {
			$anomalies[] = array(
				'type'     => 'plugin_count_change',
				'severity' => 'warning',
				'message'  => sprintf(
					__( 'Plugin count changed from %1$d to %2$d', 'wpshadow' ),
					$baseline_plugin_count,
					$current_plugin_count
				),
				'expected' => $baseline_plugin_count,
				'actual'   => $current_plugin_count,
			);
		}

		// Check active plugins count change (significant drop = possible deactivation)
		$current_active  = count( get_option( 'active_plugins', array() ) );
		$baseline_active = $baseline['active_plugins'] ?? $current_active;

		if ( $current_active < $baseline_active - 2 ) {
			$anomalies[] = array(
				'type'              => 'plugins_deactivated',
				'severity'          => 'info',
				'message'           => sprintf(
					__( '%d plugin(s) were deactivated', 'wpshadow' ),
					$baseline_active - $current_active
				),
				'deactivated_count' => $baseline_active - $current_active,
			);
		}

		// Check theme change
		$current_theme  = wp_get_theme()->get( 'Name' );
		$baseline_theme = $baseline['theme'] ?? $current_theme;

		if ( $current_theme !== $baseline_theme ) {
			$anomalies[] = array(
				'type'           => 'theme_changed',
				'severity'       => 'info',
				'message'        => sprintf(
					__( 'Theme changed from "%1$s" to "%2$s"', 'wpshadow' ),
					$baseline_theme,
					$current_theme
				),
				'previous_theme' => $baseline_theme,
				'current_theme'  => $current_theme,
			);
		}

		// Check PHP version change (warning - might affect compatibility)
		$current_php  = phpversion();
		$baseline_php = $baseline['php_version'] ?? $current_php;

		if ( version_compare( $current_php, $baseline_php, '!=' ) ) {
			$anomalies[] = array(
				'type'             => 'php_version_change',
				'severity'         => 'warning',
				'message'          => sprintf(
					__( 'PHP version changed from %1$s to %2$s', 'wpshadow' ),
					$baseline_php,
					$current_php
				),
				'previous_version' => $baseline_php,
				'current_version'  => $current_php,
			);
		}

		// Check WordPress version change
		$current_wp  = get_bloginfo( 'version' );
		$baseline_wp = $baseline['wp_version'] ?? $current_wp;

		if ( version_compare( $current_wp, $baseline_wp, '!=' ) ) {
			$anomalies[] = array(
				'type'             => 'wordpress_version_change',
				'severity'         => 'info',
				'message'          => sprintf(
					__( 'WordPress updated from %1$s to %2$s', 'wpshadow' ),
					$baseline_wp,
					$current_wp
				),
				'previous_version' => $baseline_wp,
				'current_version'  => $current_wp,
			);
		}

		// Check blog public setting change (privacy concern)
		$current_public  = get_option( 'blog_public' );
		$baseline_public = $baseline['blog_public'] ?? $current_public;

		if ( $current_public !== $baseline_public ) {
			$anomalies[] = array(
				'type'      => 'blog_public_changed',
				'severity'  => 'warning',
				'message'   => $current_public
					? __( 'Site is now publicly visible in search engines', 'wpshadow' )
					: __( 'Site is now hidden from search engines', 'wpshadow' ),
				'is_public' => (bool) $current_public,
			);
		}

		return $anomalies;
	}

	/**
	 * Get baseline history (snapshots over time)
	 *
	 * @param int $limit Number of recent snapshots to return
	 *
	 * @return array Historical baselines
	 */
	public static function get_history( int $limit = 30 ): array {
		$history = get_option( 'wpshadow_baseline_history', array() );
		return array_slice( $history, -$limit );
	}

	/**
	 * Add baseline to history
	 *
	 * Keeps last 30 snapshots for trend analysis.
	 *
	 * @param array $baseline Baseline snapshot to add
	 */
	private static function add_to_history( array $baseline ): void {
		$history = get_option( 'wpshadow_baseline_history', array() );

		$history[] = $baseline;

		// Keep only last 30
		$history = array_slice( $history, -30 );

		update_option( 'wpshadow_baseline_history', $history );
	}

	/**
	 * Get trend data for dashboard
	 *
	 * Shows security & performance findings trend over time.
	 *
	 * @param int $days Number of days to include
	 *
	 * @return array Trend data
	 */
	public static function get_trend( int $days = 7 ): array {
		$history = self::get_history( $days );

		$trend = array();

		foreach ( $history as $snapshot ) {
			$trend[] = array(
				'date'                 => $snapshot['last_check_at'] ?? $snapshot['created_at'],
				'security_findings'    => $snapshot['security_findings'] ?? 0,
				'performance_findings' => $snapshot['performance_findings'] ?? 0,
				'findings_total'       => $snapshot['findings_total'] ?? 0,
			);
		}

		return $trend;
	}

	/**
	 * Reset baseline
	 *
	 * Called if user wants to "forget" current state and start fresh.
	 */
	public static function reset(): void {
		delete_option( 'wpshadow_site_baseline' );
		delete_option( 'wpshadow_baseline_history' );

		do_action( 'wpshadow_baseline_reset' );
	}
}
