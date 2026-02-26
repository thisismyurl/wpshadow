<?php
declare(strict_types=1);

namespace WPShadow\Gamification;

/**
 * Achievement System
 *
 * Manages user achievements and progress milestones.
 * Philosophy: Show Value (#9) - Recognize user achievements
 * Philosophy: Inspire Confidence (#8) - Celebrate progress visually
 *
 * @since 1.6030
 * @package WPShadow
 */
class Achievement_System {

	/**
	 * User achievements meta key
	 */
	const USER_ACHIEVEMENTS_KEY   = 'wpshadow_achievements';
	const ACHIEVEMENTS_OPTION_KEY = 'wpshadow_achievement_definitions';

	/**
	 * Get all available achievements
	 *
	 * @return array Achievement definitions
	 */
	public static function get_all_achievements(): array {
		if ( class_exists( '\\WPShadow\\Gamification\\Gamification_Release_Gate' ) && ! \WPShadow\Gamification\Gamification_Release_Gate::is_released() ) {
			return array();
		}

		$cached = \WPShadow\Core\Cache_Manager::get(
			'achievements_cached',
			'wpshadow_engagement'
		);
		if ( $cached ) {
			return $cached;
		}

		$achievements = self::define_achievements();
		\WPShadow\Core\Cache_Manager::set(
			'achievements_cached',
			$achievements,
			WEEK_IN_SECONDS,
			'wpshadow_engagement'
		);

		return $achievements;
	}

	/**
	 * Define all available achievements
	 *
	 * @return array Achievement definitions
	 */
	private static function define_achievements(): array {
		return array(
			// Beginner Achievements
			'first_scan'           => array(
				'name'        => __( 'First Scan', 'wpshadow' ),
				'description' => __( 'Run your first diagnostic scan', 'wpshadow' ),
				'icon'        => 'dashicons-search',
				'color'       => '#4CAF50',
				'points'      => 10,
				'trigger'     => 'first_diagnostic_run',
				'difficulty'  => 'beginner',
			),
			'first_fix'            => array(
				'name'        => __( 'First Fix', 'wpshadow' ),
				'description' => __( 'Apply your first treatment', 'wpshadow' ),
				'icon'        => 'dashicons-yes-alt',
				'color'       => '#2196F3',
				'points'      => 25,
				'trigger'     => 'first_treatment_applied',
				'difficulty'  => 'beginner',
			),
			'security_advocate'    => array(
				'name'          => __( 'Security Advocate', 'wpshadow' ),
				'description'   => __( 'Fix 5 security issues', 'wpshadow' ),
				'icon'          => 'dashicons-shield-alt',
				'color'         => '#FF6B6B',
				'points'        => 50,
				'trigger'       => 'security_fixes_milestone',
				'trigger_value' => 5,
				'difficulty'    => 'intermediate',
			),
			'performance_hero'     => array(
				'name'          => __( 'Performance Hero', 'wpshadow' ),
				'description'   => __( 'Fix 10 performance issues', 'wpshadow' ),
				'icon'          => 'dashicons-performance',
				'color'         => '#FFC107',
				'points'        => 75,
				'trigger'       => 'performance_fixes_milestone',
				'trigger_value' => 10,
				'difficulty'    => 'intermediate',
			),
			'code_cleaner'         => array(
				'name'          => __( 'Code Cleaner', 'wpshadow' ),
				'description'   => __( 'Fix 8 code quality issues', 'wpshadow' ),
				'icon'          => 'dashicons-editor-code',
				'color'         => '#9C27B0',
				'points'        => 60,
				'trigger'       => 'code_quality_fixes_milestone',
				'trigger_value' => 8,
				'difficulty'    => 'intermediate',
			),

			// Intermediate Achievements
			'consistency_starter'  => array(
				'name'          => __( 'Consistency Starter', 'wpshadow' ),
				'description'   => __( 'Run scans 7 days in a row', 'wpshadow' ),
				'icon'          => 'dashicons-calendar',
				'color'         => '#00BCD4',
				'points'        => 40,
				'trigger'       => 'scan_streak_milestone',
				'trigger_value' => 7,
				'difficulty'    => 'intermediate',
			),
			'issue_resolver'       => array(
				'name'          => __( 'Issue Resolver', 'wpshadow' ),
				'description'   => __( 'Fix 20 total issues', 'wpshadow' ),
				'icon'          => 'dashicons-hammer',
				'color'         => '#FF9800',
				'points'        => 100,
				'trigger'       => 'total_fixes_milestone',
				'trigger_value' => 20,
				'difficulty'    => 'intermediate',
			),
			'all_category_master'  => array(
				'name'        => __( 'All-Category Master', 'wpshadow' ),
				'description' => __( 'Fix issues in all 10 categories', 'wpshadow' ),
				'icon'        => 'dashicons-awards',
				'color'       => '#E91E63',
				'points'      => 150,
				'trigger'     => 'all_categories_fixed',
				'difficulty'  => 'intermediate',
			),

			// Advanced Achievements
			'consistency_champion' => array(
				'name'          => __( 'Consistency Champion', 'wpshadow' ),
				'description'   => __( 'Run scans 30 days in a row', 'wpshadow' ),
				'icon'          => 'dashicons-star-filled',
				'color'         => '#FFD700',
				'points'        => 200,
				'trigger'       => 'scan_streak_milestone',
				'trigger_value' => 30,
				'difficulty'    => 'advanced',
			),
			'perfect_site_health'  => array(
				'name'        => __( 'Perfect Site Health', 'wpshadow' ),
				'description' => __( 'Achieve 100% site health score', 'wpshadow' ),
				'icon'        => 'dashicons-heart',
				'color'       => '#E74C3C',
				'points'      => 500,
				'trigger'     => 'perfect_site_health',
				'difficulty'  => 'advanced',
			),
			'maintenance_master'   => array(
				'name'          => __( 'Maintenance Master', 'wpshadow' ),
				'description'   => __( 'Fix 50 total issues', 'wpshadow' ),
				'icon'          => 'dashicons-wrench',
				'color'         => '#8E44AD',
				'points'        => 250,
				'trigger'       => 'total_fixes_milestone',
				'trigger_value' => 50,
				'difficulty'    => 'advanced',
			),
			'security_legend'      => array(
				'name'          => __( 'Security Legend', 'wpshadow' ),
				'description'   => __( 'Fix 25 security issues', 'wpshadow' ),
				'icon'          => 'dashicons-shield',
				'color'         => '#C0392B',
				'points'        => 300,
				'trigger'       => 'security_fixes_milestone',
				'trigger_value' => 25,
				'difficulty'    => 'advanced',
			),
		);
	}

	/**
	 * Unlock an achievement for a user
	 *
	 * @param int    $user_id User ID
	 * @param string $achievement_id Achievement ID
	 * @return bool Success status
	 */
	public static function unlock( $user_id, $achievement_id ): bool {
		if ( class_exists( '\\WPShadow\\Gamification\\Gamification_Release_Gate' ) && ! \WPShadow\Gamification\Gamification_Release_Gate::is_released() ) {
			return false;
		}

		$achievements = self::get_all_achievements();

		if ( ! isset( $achievements[ $achievement_id ] ) ) {
			return false;
		}

		$user_achievements = self::get_user_achievements( $user_id );

		// Already unlocked
		if ( isset( $user_achievements[ $achievement_id ] ) ) {
			return false;
		}

		// Add achievement
		$user_achievements[ $achievement_id ] = array(
			'timestamp' => current_time( 'mysql' ),
			'points'    => $achievements[ $achievement_id ]['points'] ?? 0,
		);

		$result = update_user_meta( $user_id, self::USER_ACHIEVEMENTS_KEY, $user_achievements );

		if ( $result ) {
			// Log activity
			if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'achievement_unlocked',
					sprintf( 'Achievement unlocked: %s (%d points)', $achievements[ $achievement_id ]['name'], $achievements[ $achievement_id ]['points'] ?? 0 ),
					'',
					array(
						'user_id'        => $user_id,
						'achievement_id' => $achievement_id,
					)
				);
			}
		}

		return (bool) $result;
	}

	/**
	 * Get user's achievements
	 *
	 * @param int $user_id User ID
	 * @return array User's achievements
	 */
	public static function get_user_achievements( $user_id ): array {
		if ( class_exists( '\\WPShadow\\Gamification\\Gamification_Release_Gate' ) && ! \WPShadow\Gamification\Gamification_Release_Gate::is_released() ) {
			return array();
		}

		return get_user_meta( $user_id, self::USER_ACHIEVEMENTS_KEY, true ) ?: array();
	}

	/**
	 * Get user's total achievement points
	 *
	 * @param int $user_id User ID
	 * @return int Total points
	 */
	public static function get_user_points( $user_id ): int {
		$achievements = self::get_user_achievements( $user_id );
		$total        = 0;

		foreach ( $achievements as $data ) {
			$total += $data['points'] ?? 0;
		}

		return $total;
	}

	/**
	 * Check if user has achievement
	 *
	 * @param int    $user_id User ID
	 * @param string $achievement_id Achievement ID
	 * @return bool Has achievement
	 */
	public static function has_achievement( $user_id, $achievement_id ): bool {
		$achievements = self::get_user_achievements( $user_id );
		return isset( $achievements[ $achievement_id ] );
	}

	/**
	 * Get achievement details
	 *
	 * @param string $achievement_id Achievement ID
	 * @return array|null Achievement details or null
	 */
	public static function get_achievement( $achievement_id ) {
		$achievements = self::get_all_achievements();
		return $achievements[ $achievement_id ] ?? null;
	}

	/**
	 * Get achievements by difficulty
	 *
	 * @param string $difficulty Difficulty level
	 * @return array Matching achievements
	 */
	public static function get_by_difficulty( $difficulty ): array {
		$achievements = self::get_all_achievements();

		return array_filter(
			$achievements,
			function ( $achievement ) use ( $difficulty ) {
				return ( $achievement['difficulty'] ?? '' ) === $difficulty;
			}
		);
	}

	/**
	 * Get user's achievement progress
	 *
	 * @param int $user_id User ID
	 * @return array Progress data
	 */
	public static function get_user_progress( $user_id ): array {
		$user_achievements = self::get_user_achievements( $user_id );
		$all_achievements  = self::get_all_achievements();

		return array(
			'unlocked'   => count( $user_achievements ),
			'total'      => count( $all_achievements ),
			'percentage' => count( $all_achievements ) > 0 ? round( ( count( $user_achievements ) / count( $all_achievements ) ) * 100 ) : 0,
			'points'     => self::get_user_points( $user_id ),
		);
	}

	/**
	 * Render achievements UI widget
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	public static function render_achievements_widget( $user_id ): void {
		$achievements     = self::get_user_achievements( $user_id );
		$all_achievements = self::get_all_achievements();
		$progress         = self::get_user_progress( $user_id );

		?>
		<div class="wps-card">
			<div class="wps-flex wps-items-center wps-gap-3" class="wps-achievement-section">
				<span class="dashicons dashicons-awards" class="wps-achievement-icon"></span>
				<h3 class="wps-m-0"><?php esc_html_e( 'Your Achievements', 'wpshadow' ); ?></h3>
			</div>

			<!-- Progress Bar -->
			<div class="wps-achievement-section">
				<div class="wps-flex-justify-space-between">
					<span class="wps-achievement-title">
						<?php printf( esc_html__( '%1$d / %2$d Achievements', 'wpshadow' ), $progress['unlocked'], $progress['total'] ); ?>
					</span>
					<span class="wps-achievement-progress-label">
						<?php printf( esc_html__( '%d Points', 'wpshadow' ), $progress['points'] ); ?>
					</span>
				</div>
				<div class="wps-rounded-4">
					<div class="wps-achievement-progress-bar" style="width: <?php echo esc_attr( (string) $progress['percentage'] ); ?>%;"></div>
				</div>
				<div class="wps-achievement-progress-text">
					<?php printf( esc_html__( '%d%% Complete', 'wpshadow' ), $progress['percentage'] ); ?>
				</div>
			</div>

			<!-- Achievement Grid -->
			<div class="wps-grid wps-grid-auto-140 wps-gap-3">
				<?php foreach ( $all_achievements as $id => $achievement ) : ?>
					<?php $unlocked = isset( $achievements[ $id ] ); ?>
					<div style="
						padding: 12px;
						background: <?php echo esc_attr( $unlocked ? $achievement['color'] : '#f5f5f5' ); ?>;
						border-radius: 8px;
						text-align: center;
						opacity: <?php echo esc_attr( $unlocked ? '1' : '0.5' ); ?>;
						transition: all 0.3s;
						cursor: pointer;
						border: 2px solid <?php echo esc_attr( $unlocked ? $achievement['color'] : '#ddd' ); ?>;
					" title="<?php echo esc_attr( $achievement['description'] ); ?>">
						<div class="wps-achievement-card-icon">
							<span class="dashicons <?php echo esc_attr( $achievement['icon'] ); ?> wps-achievement-card-icon-dashicon"></span>
						</div>
						<div class="wps-achievement-card-title" style="color: <?php echo esc_attr( $unlocked ? '#fff' : '#999' ); ?>;">
							<?php echo esc_html( $achievement['name'] ); ?>
						</div>
						<?php if ( $unlocked ) : ?>
							<div class="wps-achievement-card-date">
								<?php echo esc_html( date_i18n( 'M j, Y', strtotime( $achievements[ $id ]['timestamp'] ) ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
