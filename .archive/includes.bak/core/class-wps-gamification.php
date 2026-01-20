<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Gamification {

	private const BADGES = array(
		'perfect_health_week' => array(
			'title'       => 'Perfect Health Guardian',
			'description' => 'Maintained 100% site health for 7 consecutive days',
			'icon'        => '🏥',
			'color'       => '#46b450',
			'rarity'      => 'rare',
		),
		'a11y_champion' => array(
			'title'       => 'Accessibility Champion',
			'description' => 'Site passes accessibility audit with flying colors',
			'icon'        => '♿',
			'color'       => '#0073aa',
			'rarity'      => 'rare',
		),
		'performance_optimizer' => array(
			'title'       => 'Performance Optimizer',
			'description' => 'Achieved excellent performance score across all pages',
			'icon'        => '⚡',
			'color'       => '#ffb81c',
			'rarity'      => 'rare',
		),
		'security_hardened' => array(
			'title'       => 'Security Hardened',
			'description' => 'Enabled all recommended security features',
			'icon'        => '🔒',
			'color'       => '#d63638',
			'rarity'      => 'epic',
		),
		'cleanup_champion' => array(
			'title'       => 'Cleanup Champion',
			'description' => 'Fixed 50+ issues across your site',
			'icon'        => '🧹',
			'color'       => '#9b51e0',
			'rarity'      => 'rare',
		),
		'first_feature' => array(
			'title'       => 'First Step',
			'description' => 'Enabled your first WPShadow feature',
			'icon'        => '👣',
			'color'       => '#4CAF50',
			'rarity'      => 'common',
		),
		'five_features' => array(
			'title'       => 'Feature Explorer',
			'description' => 'Enabled 5 different WPShadow features',
			'icon'        => '🔭',
			'color'       => '#2196F3',
			'rarity'      => 'uncommon',
		),
		'ten_features' => array(
			'title'       => 'Feature Master',
			'description' => 'Enabled 10 different WPShadow features',
			'icon'        => '🎓',
			'color'       => '#9C27B0',
			'rarity'      => 'rare',
		),
		'clean_logs' => array(
			'title'       => 'Log Keeper',
			'description' => 'Maintained clean error logs for a week',
			'icon'        => '📋',
			'color'       => '#00bcd4',
			'rarity'      => 'uncommon',
		),
		'ssl_champion' => array(
			'title'       => 'HTTPS Champion',
			'description' => 'Proper SSL/HTTPS configuration detected',
			'icon'        => '🔐',
			'color'       => '#f44336',
			'rarity'      => 'rare',
		),
	);

	private const BADGES_OPTION = 'wpshadow_earned_badges';
	private const STATS_OPTION = 'wpshadow_gamification_stats';

	public static function init(): void {

		add_action( 'init', array( __CLASS__, 'check_achievements' ) );

		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		add_action( 'wpshadow_admin_page_header', array( __CLASS__, 'display_achievements_header' ) );
	}

	public static function check_achievements(): void {

		if ( ! self::should_check_today() ) {
			return;
		}

		self::check_site_health_achievement();
		self::check_a11y_achievement();
		self::check_performance_achievement();
		self::check_security_achievement();
		self::check_error_log_achievement();
		self::check_ssl_achievement();
		self::check_feature_usage_achievements();

		update_option( 'wpshadow_last_achievement_check', time() );
	}

	private static function should_check_today(): bool {
		$last_check = get_option( 'wpshadow_last_achievement_check', 0 );
		$today      = strtotime( 'today' );
		return (int) $last_check < $today;
	}

	private static function check_site_health_achievement(): void {

		if ( function_exists( 'get_site_health_count' ) ) {
			$status = get_site_health_count();
			if ( $status['recommended'] === 0 && $status['critical'] === 0 ) {

				$streak = get_option( 'wpshadow_perfect_health_days', 0 );
				$streak++;
				update_option( 'wpshadow_perfect_health_days', $streak );

				if ( $streak >= 7 ) {
					self::award_badge( 'perfect_health_week' );
					update_option( 'wpshadow_perfect_health_days', 0 );
				}
			} else {

				update_option( 'wpshadow_perfect_health_days', 0 );
			}
		}
	}

	private static function check_a11y_achievement(): void {

		$a11y_score = get_option( 'wpshadow_a11y_latest_score', 0 );

		if ( $a11y_score >= 90 ) {
			self::award_badge( 'a11y_champion' );
		}
	}

	private static function check_performance_achievement(): void {

		$performance_score = get_option( 'wpshadow_performance_latest_score', 0 );

		if ( $performance_score >= 90 ) {
			self::award_badge( 'performance_optimizer' );
		}
	}

	private static function check_security_achievement(): void {

		$required_features = array(
			'iframe-busting',
			'hotlink-protection',
			'external-fonts-disabler',
		);

		$enabled_count = 0;
		foreach ( $required_features as $feature ) {
			$enabled = get_option( "wpshadow_feature_{$feature}_enabled", false );
			if ( $enabled ) {
				$enabled_count++;
			}
		}

		if ( $enabled_count === count( $required_features ) ) {
			self::award_badge( 'security_hardened' );
		}
	}

	private static function check_error_log_achievement(): void {

		$error_count = get_option( 'wpshadow_error_count_today', 0 );

		if ( $error_count === 0 ) {
			$clean_days = get_option( 'wpshadow_clean_log_days', 0 );
			$clean_days++;
			update_option( 'wpshadow_clean_log_days', $clean_days );

			if ( $clean_days >= 7 ) {
				self::award_badge( 'clean_logs' );
				update_option( 'wpshadow_clean_log_days', 0 );
			}
		} else {
			update_option( 'wpshadow_clean_log_days', 0 );
		}
	}

	private static function check_ssl_achievement(): void {
		if ( is_ssl() && strpos( get_option( 'siteurl' ), 'https://' ) === 0 ) {
			self::award_badge( 'ssl_champion' );
		}
	}

	/**
	 * Check feature usage achievements.
	 *
	 * @return void
	 */
	private static function check_feature_usage_achievements(): void {

		$enabled_features = get_option( 'wpshadow_enabled_features', array() );

		if ( ! is_array( $enabled_features ) ) {
			return;
		}

		$count = count( $enabled_features );

		if ( $count >= 1 ) {
			self::award_badge( 'first_feature' );
		}
		if ( $count >= 5 ) {
			self::award_badge( 'five_features' );
		}
		if ( $count >= 10 ) {
			self::award_badge( 'ten_features' );
		}
		if ( $count >= 50 ) {
			self::award_badge( 'cleanup_champion' );
		}
	}

	/**
	 * Award a badge (only if not already earned).
	 *
	 * @param string $badge_id Badge identifier.
	 * @return bool
	 */
	public static function award_badge( string $badge_id ): bool {
		if ( ! isset( self::BADGES[ $badge_id ] ) ) {
			return false;
		}

		$badges = self::get_badges();

		if ( isset( $badges[ $badge_id ] ) ) {
			return false; 
		}

		$badges[ $badge_id ] = array(
			'earned_at' => current_time( 'mysql' ),
			'timestamp' => time(),
		);

		update_option( self::BADGES_OPTION, $badges );

		self::update_stats();

		return true;
	}

	/**
	 * Get all earned badges.
	 *
	 * @return array
	 */
	public static function get_badges(): array {
		$badges = get_option( self::BADGES_OPTION, array() );
		return is_array( $badges ) ? $badges : array();
	}

	/**
	 * Update gamification stats.
	 *
	 * @return void
	 */
	private static function update_stats(): void {
		$badges = self::get_badges();
		$stats  = array(
			'total_badges'     => count( $badges ),
			'common_badges'    => 0,
			'uncommon_badges'  => 0,
			'rare_badges'      => 0,
			'epic_badges'      => 0,
			'legendary_badges' => 0,
		);

		foreach ( $badges as $badge_id => $badge_data ) {
			if ( isset( self::BADGES[ $badge_id ]['rarity'] ) ) {
				$rarity = self::BADGES[ $badge_id ]['rarity'];
				$stats[ $rarity . '_badges' ]++;
			}
		}

		update_option( self::STATS_OPTION, $stats );
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow-achievements',
			__( '🎖️ WPShadow Achievements', 'wpshadow' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Render achievements widget.
	 *
	 * @return void
	 */
	public static function render_widget(): void {
		$badges = self::get_badges();
		$stats  = get_option( self::STATS_OPTION, array() );
		?>
		<div class="wpshadow-achievements-widget">
			<div class="achievements-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
				<div class="stat-box" style="padding: 15px; background: #f0f0f0; border-radius: 4px; text-align: center;">
					<div class="stat-number" style="font-size: 32px; font-weight: bold; color: #2271b1;">
						<?php echo count( $badges ); ?>
					</div>
					<div class="stat-label" style="font-size: 12px; color: #666; text-transform: uppercase;">
						<?php esc_html_e( 'Badges Earned', 'wpshadow' ); ?>
					</div>
				</div>
				<div class="stat-box" style="padding: 15px; background: #f0f0f0; border-radius: 4px; text-align: center;">
					<div class="stat-number" style="font-size: 32px; font-weight: bold; color: #46b450;">
						<?php echo isset( $stats['total_badges'] ) ? $stats['total_badges'] : 0; ?>/<?php echo count( self::BADGES ); ?>
					</div>
					<div class="stat-label" style="font-size: 12px; color: #666; text-transform: uppercase;">
						<?php esc_html_e( 'Total Available', 'wpshadow' ); ?>
					</div>
				</div>
			</div>

			<?php if ( empty( $badges ) ) : ?>
				<p style="text-align: center; color: #999; padding: 20px;">
					<?php esc_html_e( 'Keep using WPShadow features to earn your first badge! 🎯', 'wpshadow' ); ?>
				</p>
			<?php else : ?>
				<div class="badges-display" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 15px;">
					<?php foreach ( $badges as $badge_id => $badge_data ) : ?>
						<?php if ( isset( self::BADGES[ $badge_id ] ) ) : ?>
							<?php $badge = self::BADGES[ $badge_id ]; ?>
							<div class="badge" style="text-align: center;" title="<?php echo esc_attr( $badge['description'] ); ?>">
								<div class="badge-icon" style="font-size: 40px; margin-bottom: 5px;">
									<?php echo $badge['icon']; ?>
								</div>
								<div class="badge-title" style="font-size: 11px; font-weight: bold; word-break: break-word;">
									<?php echo esc_html( $badge['title'] ); ?>
								</div>
								<div class="badge-date" style="font-size: 9px; color: #999; margin-top: 3px;">
									<?php echo esc_html( wp_date( 'M d, Y', $badge_data['timestamp'] ) ); ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="achievements-footer" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #666;">
				<p>
					<?php esc_html_e( '💡 Tip: Maintain excellent site health and enable recommended features to unlock more badges!', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Display achievements header on WPShadow pages.
	 *
	 * @return void
	 */
	public static function display_achievements_header(): void {
		$badges = self::get_badges();
		if ( empty( $badges ) ) {
			return;
		}
		?>
		<div class="wpshadow-achievements-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; margin: -20px -20px 20px -20px; border-radius: 4px; display: flex; align-items: center; justify-content: space-between;">
			<div>
				<h3 style="margin: 0 0 5px; font-size: 18px;">
					<?php echo esc_html( sprintf( __( '🎖️ %d Badges Earned!', 'wpshadow' ), count( $badges ) ) ); ?>
				</h3>
				<p style="margin: 0; font-size: 12px; opacity: 0.9;">
					<?php esc_html_e( 'Keep optimizing to earn more achievements', 'wpshadow' ); ?>
				</p>
			</div>
			<div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
				<?php
				$display_badges = array_slice( $badges, -5 );
				foreach ( $display_badges as $badge_id => $badge_data ) :
					if ( isset( self::BADGES[ $badge_id ] ) ) :
						$badge = self::BADGES[ $badge_id ];
						?>
						<span style="font-size: 24px;" title="<?php echo esc_attr( $badge['title'] ); ?>">
							<?php echo $badge['icon']; ?>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {

	}

	/**
	 * Get badge by ID.
	 *
	 * @param string $badge_id Badge identifier.
	 * @return array|null
	 */
	public static function get_badge( string $badge_id ): ?array {
		return self::BADGES[ $badge_id ] ?? null;
	}

	/**
	 * Get all badge definitions.
	 *
	 * @return array
	 */
	public static function get_all_badges(): array {
		return self::BADGES;
	}
}
