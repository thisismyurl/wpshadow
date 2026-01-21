<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

use WPShadow\Core\KPI_Tracker;

/**
 * Cloud Usage Tracker
 * 
 * Tracks API usage against free/pro quotas.
 * Monitors scans, emails, sites, and storage.
 * 
 * Free Tier Quotas (monthly):
 * - 100 deep scans
 * - 50 email notifications
 * - 3 managed sites
 * - 7-day scan history
 * 
 * Pro Tier (unlimited with soft caps):
 * - Unlimited scans
 * - Unlimited emails
 * - Unlimited sites
 * - 365-day scan history
 * - Priority support
 * 
 * Philosophy: Generous free tier. Pro removes limits.
 */
class Usage_Tracker {
	
	/**
	 * Get usage statistics for current billing period
	 * 
	 * Queries cloud service for usage metrics.
	 * Falls back to local defaults if unavailable.
	 * 
	 * @return array Usage stats with period information
	 */
	public static function get_usage_stats(): array {
		// Check if registered first
		if ( ! Registration_Manager::is_registered() ) {
			return self::get_default_unregistered_stats();
		}
		
		// Check cache (5-minute TTL)
		$cached = get_transient( 'wpshadow_usage_stats' );
		if ( $cached ) {
			return $cached;
		}
		
		// Fetch from API
		$response = Cloud_Client::request( 'GET', '/usage' );
		
		if ( isset( $response['error'] ) ) {
			// Return cached stats or defaults
			$option = get_option( 'wpshadow_usage_stats_cached' );
			return $option ?: self::get_default_stats();
		}
		
		// Cache for 5 minutes
		set_transient( 'wpshadow_usage_stats', $response, 300 );
		
		// Also store as option for offline access
		update_option( 'wpshadow_usage_stats_cached', $response );
		
		return $response;
	}
	
	/**
	 * Get quota limits for tier
	 * 
	 * @return array Limits for current tier
	 */
	public static function get_quota_limits(): array {
		$status = Registration_Manager::get_registration_status();
		$tier = $status['tier'] ?? 'free';
		
		if ( $tier === 'pro' ) {
			return [
				'scans'       => 100000, // Effectively unlimited
				'emails'      => 100000,
				'sites'       => 1000,
				'storage_gb'  => 500,
				'history_days' => 365,
			];
		}
		
		// Free tier defaults
		return [
			'scans'        => 100,
			'emails'       => 50,
			'sites'        => 3,
			'storage_gb'   => 5,
			'history_days' => 7,
		];
	}
	
	/**
	 * Check if quota allows action
	 * 
	 * @param string $action 'scan'|'email'|'site'
	 * 
	 * @return bool Action is allowed under quota
	 */
	public static function can_perform_action( string $action ): bool {
		$action = sanitize_key( $action );
		$usage = self::get_usage_stats();
		
		$checks = [
			'scan' => [ 'used' => 'scans_used', 'limit' => 'scans_limit' ],
			'email' => [ 'used' => 'emails_used', 'limit' => 'emails_limit' ],
			'site' => [ 'used' => 'sites_used', 'limit' => 'sites_limit' ],
		];
		
		if ( ! isset( $checks[ $action ] ) ) {
			return true; // Unknown action, allow
		}
		
		$check = $checks[ $action ];
		$used = (int) ( $usage[ $check['used'] ] ?? 0 );
		$limit = (int) ( $usage[ $check['limit'] ] ?? 0 );
		
		return $used < $limit;
	}
	
	/**
	 * Get percentage of quota used
	 * 
	 * @param string $action 'scan'|'email'|'site'
	 * 
	 * @return int Percentage (0-100)
	 */
	public static function get_usage_percentage( string $action ): int {
		$action = sanitize_key( $action );
		$usage = self::get_usage_stats();
		
		$checks = [
			'scan' => [ 'used' => 'scans_used', 'limit' => 'scans_limit' ],
			'email' => [ 'used' => 'emails_used', 'limit' => 'emails_limit' ],
			'site' => [ 'used' => 'sites_used', 'limit' => 'sites_limit' ],
		];
		
		if ( ! isset( $checks[ $action ] ) ) {
			return 0;
		}
		
		$check = $checks[ $action ];
		$used = (int) ( $usage[ $check['used'] ] ?? 0 );
		$limit = (int) ( $usage[ $check['limit'] ] ?? 1 ); // Avoid division by zero
		
		return (int) ( ( $used / $limit ) * 100 );
	}
	
	/**
	 * Get upgrade URL for pro tier
	 * 
	 * @return string Upgrade link
	 */
	public static function get_upgrade_url(): string {
		return 'https://wpshadow.com/pricing?utm_source=plugin&utm_medium=quota_notice';
	}
	
	/**
	 * Display quota usage widget
	 * 
	 * Returns HTML for embedding in admin pages.
	 * Shows visual progress bars for quotas.
	 * 
	 * @return string HTML widget
	 */
	public static function render_quota_widget(): string {
		$usage = self::get_usage_stats();
		$status = Registration_Manager::get_registration_status();
		$tier = $status['tier'] ?? 'free';
		
		$scans_pct = self::get_usage_percentage( 'scan' );
		$emails_pct = self::get_usage_percentage( 'email' );
		$sites_pct = self::get_usage_percentage( 'site' );
		
		ob_start();
		?>
		<div class="wpshadow-quota-widget">
			<div class="quota-header">
				<h3><?php esc_html_e( 'Cloud Quota Usage', 'wpshadow' ); ?></h3>
				<span class="quota-tier"><?php echo esc_html( ucfirst( $tier ) ); ?> Tier</span>
			</div>
			
			<div class="quota-item">
				<label><?php esc_html_e( 'Deep Scans', 'wpshadow' ); ?></label>
				<div class="quota-bar">
					<div class="quota-progress" style="width: <?php echo intval( $scans_pct ); ?>%"></div>
				</div>
				<span class="quota-text">
					<?php printf(
						esc_html__( '%d / %d', 'wpshadow' ),
						intval( $usage['scans_used'] ?? 0 ),
						intval( $usage['scans_limit'] ?? 100 )
					); ?>
				</span>
			</div>
			
			<div class="quota-item">
				<label><?php esc_html_e( 'Email Notifications', 'wpshadow' ); ?></label>
				<div class="quota-bar">
					<div class="quota-progress" style="width: <?php echo intval( $emails_pct ); ?>%"></div>
				</div>
				<span class="quota-text">
					<?php printf(
						esc_html__( '%d / %d', 'wpshadow' ),
						intval( $usage['emails_used'] ?? 0 ),
						intval( $usage['emails_limit'] ?? 50 )
					); ?>
				</span>
			</div>
			
			<div class="quota-item">
				<label><?php esc_html_e( 'Managed Sites', 'wpshadow' ); ?></label>
				<div class="quota-bar">
					<div class="quota-progress" style="width: <?php echo intval( $sites_pct ); ?>%"></div>
				</div>
				<span class="quota-text">
					<?php printf(
						esc_html__( '%d / %d', 'wpshadow' ),
						intval( $usage['sites_used'] ?? 1 ),
						intval( $usage['sites_limit'] ?? 3 )
					); ?>
				</span>
			</div>
			
			<?php if ( $tier === 'free' && ( $scans_pct > 80 || $emails_pct > 80 ) ) : ?>
				<div class="quota-notice">
					<p><?php esc_html_e( 'Approaching quota limits.', 'wpshadow' ); ?></p>
					<a href="<?php echo esc_url( self::get_upgrade_url() ); ?>" class="button button-primary">
						<?php esc_html_e( 'Upgrade to Pro', 'wpshadow' ); ?>
					</a>
				</div>
			<?php endif; ?>
			
			<?php if ( isset( $usage['period_end'] ) ) : ?>
				<div class="quota-period">
					<small>
						<?php printf(
							esc_html__( 'Period ends: %s', 'wpshadow' ),
							esc_html( $usage['period_end'] )
						); ?>
					</small>
				</div>
			<?php endif; ?>
		</div>
		<?php
		
		return ob_get_clean();
	}
	
	/**
	 * Get default stats for unregistered sites
	 * 
	 * @return array Default unregistered stats
	 */
	private static function get_default_unregistered_stats(): array {
		return [
			'registered'      => false,
			'scans_used'      => 0,
			'scans_limit'     => 0,
			'emails_used'     => 0,
			'emails_limit'    => 0,
			'sites_used'      => 0,
			'sites_limit'     => 0,
			'status'          => 'unregistered',
			'period_start'    => date( 'Y-m-d' ),
			'period_end'      => date( 'Y-m-d', strtotime( '+30 days' ) ),
		];
	}
	
	/**
	 * Get default stats for registered sites
	 * 
	 * @return array Default registered stats
	 */
	private static function get_default_stats(): array {
		$status = Registration_Manager::get_registration_status();
		$tier = $status['tier'] ?? 'free';
		
		$limits = self::get_quota_limits();
		
		return [
			'registered'      => true,
			'tier'            => $tier,
			'scans_used'      => 0,
			'scans_limit'     => $limits['scans'],
			'emails_used'     => 0,
			'emails_limit'    => $limits['emails'],
			'sites_used'      => 1,
			'sites_limit'     => $limits['sites'],
			'storage_used_gb' => 0,
			'storage_limit_gb' => $limits['storage_gb'],
			'period_start'    => date( 'Y-m-d', strtotime( 'first day of this month' ) ),
			'period_end'      => date( 'Y-m-d', strtotime( 'last day of this month' ) ),
		];
	}
	
	/**
	 * Check if action quota exceeded
	 * 
	 * @param string $action 'scan'|'email'|'site'
	 * 
	 * @return bool Quota is exceeded
	 */
	public static function is_quota_exceeded( string $action ): bool {
		return ! self::can_perform_action( $action );
	}
	
	/**
	 * Get remaining quota for action
	 * 
	 * @param string $action 'scan'|'email'|'site'
	 * 
	 * @return int Remaining quota
	 */
	public static function get_remaining( string $action ): int {
		$action = sanitize_key( $action );
		$usage = self::get_usage_stats();
		
		$checks = [
			'scan' => [ 'used' => 'scans_used', 'limit' => 'scans_limit' ],
			'email' => [ 'used' => 'emails_used', 'limit' => 'emails_limit' ],
			'site' => [ 'used' => 'sites_used', 'limit' => 'sites_limit' ],
		];
		
		if ( ! isset( $checks[ $action ] ) ) {
			return 0;
		}
		
		$check = $checks[ $action ];
		$used = (int) ( $usage[ $check['used'] ] ?? 0 );
		$limit = (int) ( $usage[ $check['limit'] ] ?? 0 );
		
		return max( 0, $limit - $used );
	}
}
