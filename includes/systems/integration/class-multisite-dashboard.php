<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

/**
 * Cloud Multisite Dashboard
 *
 * Centralized dashboard for managing multiple WordPress sites.
 * Shows aggregate health and metrics across all registered sites.
 * Pro feature for unlimited sites (free: max 3 sites).
 *
 * Features:
 * - Site list retrieval
 * - Current site status
 * - Network health aggregation
 * - Site comparison
 * - Performance trending
 *
 * Philosophy: Free tier includes multi-site tracking up to limit.
 * Pro tier removes site limit. All data via cloud dashboard.
 */
class Multisite_Dashboard {

	/**
	 * Get all registered sites from cloud service
	 *
	 * Returns list of sites the current cloud user manages.
	 * Cached for 1 hour to reduce API calls.
	 *
	 * @return array List of registered sites
	 */
	public static function get_registered_sites(): array {
		// Check cache first (1 hour TTL)
		$cached = \WPShadow\Core\Cache_Manager::get( 'registered_sites_list', 'wpshadow_cloud' );
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Fetch from cloud API
		$response = Cloud_Client::request( 'GET', '/sites' );

		if ( isset( $response['error'] ) ) {
			return array();
		}

		$sites = $response['sites'] ?? array();

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set( 'registered_sites_list', $sites, 3600  , 'wpshadow_cloud');

		return $sites;
	}

	/**
	 * Get current site status from cloud perspective
	 *
	 * Retrieves this site's data as seen by cloud dashboard.
	 *
	 * @return array Current site status
	 */
	public static function get_current_site_status(): array {
		$site_id = get_option( 'wpshadow_site_id' );
		if ( ! $site_id ) {
			return array( 'error' => 'Site not registered' );
		}

		// Try cache first (5 minute TTL)
		$cached = \WPShadow\Core\Cache_Manager::get( 'site_status_{$site_id}', 'wpshadow_cloud' );
		if ( $cached ) {
			return $cached;
		}

		// Fetch from API
		$response = Cloud_Client::request(
			'GET',
			'/sites/' . sanitize_key( $site_id )
		);

		if ( isset( $response['error'] ) ) {
			return $response;
		}

		// Cache for 5 minutes
		\WPShadow\Core\Cache_Manager::set( 'site_status_{$site_id}', $response, 300  , 'wpshadow_cloud');

		return $response;
	}

	/**
	 * Get aggregate health across all sites
		 *
	 * Available in pro tier. Free tier returns current site only.
	 * Calculates total risk and average health metrics.
	 *
	 * @return array Network health aggregation
	 */
	public static function get_network_health(): array {
		$status = Registration_Manager::get_registration_status();
		$tier   = $status['tier'] ?? 'free';

		// Free tier: current site only
		if ( $tier === 'free' ) {
			$site = self::get_current_site_status();
			if ( isset( $site['error'] ) ) {
				return array(
					'error'             => $site['error'],
					'total_sites'       => 0,
					'critical_findings' => 0,
				);
			}

			return array(
				'total_sites'       => 1,
				'current_site'      => $site['site_url'] ?? '',
				'health_score'      => $site['health_score'] ?? 0,
				'critical_findings' => $site['critical_count'] ?? 0,
				'warning_findings'  => $site['warning_count'] ?? 0,
			);
		}

		// Pro tier: aggregate across all sites
		$sites = self::get_registered_sites();

		if ( empty( $sites ) ) {
			return array(
				'total_sites'       => 0,
				'critical_findings' => 0,
				'average_health'    => 0,
			);
		}

		$health_scores  = array();
		$critical_total = 0;
		$warning_total  = 0;

		foreach ( $sites as $site ) {
			$health_scores[] = $site['health_score'] ?? 0;
			$critical_total += (int) ( $site['critical_count'] ?? 0 );
			$warning_total  += (int) ( $site['warning_count'] ?? 0 );
		}

		$average_health = ! empty( $health_scores )
			? (int) ( array_sum( $health_scores ) / count( $health_scores ) )
			: 0;

		return array(
			'total_sites'       => count( $sites ),
			'average_health'    => $average_health,
			'critical_findings' => $critical_total,
			'warning_findings'  => $warning_total,
			'sites_at_risk'     => count(
				array_filter(
					$sites,
					fn( $s ) => ( $s['health_score'] ?? 100 ) < 50
				)
			),
		);
	}

	/**
	 * Get site comparison data
	 *
	 * Compare this site against average of all sites.
	 * Shows how site ranks in network.
	 * Pro tier only.
	 *
	 * @return array Comparison metrics
	 */
	public static function get_site_comparison(): array {
		$status = Registration_Manager::get_registration_status();
		if ( $status['tier'] !== 'pro' ) {
			return array( 'error' => 'Pro tier only' );
		}

		$current = self::get_current_site_status();
		$network = self::get_network_health();

		if ( isset( $current['error'] ) || isset( $network['error'] ) ) {
			return array( 'error' => 'Unable to retrieve comparison data' );
		}

		return array(
			'current_health' => $current['health_score'] ?? 0,
			'average_health' => $network['average_health'] ?? 0,
			'health_rank'    => $current['health_rank'] ?? 0,
			'total_sites'    => $network['total_sites'] ?? 0,
			'percentile'     => $this->calculate_percentile( $current['health_score'] ?? 0, $network ),
		);
	}

	/**
	 * Get performance trends for site
	 *
	 * Historical health score and findings trend.
	 * Returns data points for chart visualization.
	 *
	 * @param string $period 'week'|'month'|'quarter'
	 *
	 * @return array Trend data points
	 */
	public static function get_performance_trends( string $period = 'month' ): array {
		$period = sanitize_key( $period );

		// Check cache (1 hour TTL)
		$cached = \WPShadow\Core\Cache_Manager::get( 'trends_{$period}', 'wpshadow_cloud' );
		if ( $cached ) {
			return $cached;
		}

		// Fetch from API
		$response = Cloud_Client::request(
			'GET',
			'/sites/' . get_option( 'wpshadow_site_id' ) . '/trends?period=' . $period
		);

		if ( isset( $response['error'] ) ) {
			return array();
		}

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set( 'trends_{$period}', $response, 3600  , 'wpshadow_cloud');

		return $response;
	}

	/**
	 * Get site alerts and issues
	 *
	 * Critical and warning findings across all registered sites.
	 * Prioritized by severity.
	 *
	 * @param int $limit Number of alerts to return
	 *
	 * @return array Aggregated alerts
	 */
	public static function get_network_alerts( int $limit = 10 ): array {
		$limit = max( 1, min( 100, $limit ) );

		// Check cache (5 minute TTL)
		$cached = \WPShadow\Core\Cache_Manager::get( 'network_alerts', 'wpshadow_cloud' );
		if ( $cached ) {
			return array_slice( $cached, 0, $limit );
		}

		// Fetch from API
		$response = Cloud_Client::request( 'GET', '/alerts?limit=' . $limit );

		if ( isset( $response['error'] ) ) {
			return array();
		}

		$alerts = $response['alerts'] ?? array();

		// Cache for 5 minutes
		\WPShadow\Core\Cache_Manager::set( 'network_alerts', $alerts, 300  , 'wpshadow_cloud');

		return array_slice( $alerts, 0, $limit );
	}

	/**
	 * Get dashboard URL for cloud
	 *
	 * Links to full multi-site dashboard view.
	 *
	 * @return string Dashboard URL
	 */
	public static function get_dashboard_url(): string {
		$site_id = get_option( 'wpshadow_site_id' );
		if ( ! $site_id ) {
			return 'https://dashboard.wpshadow.com';
		}

		return sprintf(
			'https://dashboard.wpshadow.com/sites/%s',
			urlencode( (string) $site_id )
		);
	}

	/**
	 * Render cloud dashboard widget
	 *
	 * HTML widget showing multisite overview.
	 *
	 * @return string HTML widget
	 */
	public static function render_dashboard_widget(): string {
		$network = self::get_network_health();

		if ( isset( $network['error'] ) ) {
			return '<p>' . esc_html( $network['error'] ) . '</p>';
		}

		ob_start();
		?>
		<div class="wpshadow-multisite-widget">
			<h3><?php esc_html_e( 'Network Health', 'wpshadow' ); ?></h3>

			<div class="network-stats">
				<div class="stat">
					<strong><?php echo intval( $network['total_sites'] ); ?></strong>
					<span><?php esc_html_e( 'Sites', 'wpshadow' ); ?></span>
				</div>

				<div class="stat">
					<strong><?php echo intval( $network['average_health'] ); ?>%</strong>
					<span><?php esc_html_e( 'Avg Health', 'wpshadow' ); ?></span>
				</div>

				<div class="stat warning">
					<strong><?php echo intval( $network['critical_findings'] ); ?></strong>
					<span><?php esc_html_e( 'Critical', 'wpshadow' ); ?></span>
				</div>
			</div>

			<a href="<?php echo esc_url( self::get_dashboard_url() ); ?>" class="button button-secondary" target="_blank">
				<?php esc_html_e( 'View Full Dashboard', 'wpshadow' ); ?>
			</a>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Clear all cached dashboard data
	 *
	 * Called when site data changes significantly.
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'registered_sites_list', 'wpshadow_cloud' );
		\WPShadow\Core\Cache_Manager::delete( 'network_alerts', 'wpshadow_cloud' );
		\WPShadow\Core\Cache_Manager::delete( 'trends_week', 'wpshadow_cloud' );
		\WPShadow\Core\Cache_Manager::delete( 'trends_month', 'wpshadow_cloud' );
		\WPShadow\Core\Cache_Manager::delete( 'trends_quarter', 'wpshadow_cloud' );

		// Clear site-specific caches
		$site_id = get_option( 'wpshadow_site_id' );
		if ( $site_id ) {
			\WPShadow\Core\Cache_Manager::delete( 'site_status_{$site_id}', 'wpshadow_cloud' );
		}
	}

	/**
	 * Calculate percentile for health score
	 *
	 * @param int   $health_score Current score
	 * @param array $network Network data
	 *
	 * @return int Percentile (0-100)
	 */
	private function calculate_percentile( int $health_score, array $network ): int {
		// Simplified: return based on average
		$average = $network['average_health'] ?? 50;
		if ( $health_score >= $average ) {
			return 75;
		} elseif ( $health_score >= ( $average * 0.7 ) ) {
			return 50;
		}
		return 25;
	}
}
