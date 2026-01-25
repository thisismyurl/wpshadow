<?php
declare(strict_types=1);

namespace WPShadow\Gamification;

/**
 * Badge Manager
 *
 * Manages visual badges and skill levels.
 * Philosophy: Inspire Confidence (#8) - Visual status symbols
 * Philosophy: Show Value (#9) - Visible skill progression
 *
 * @since 1.2601
 * @package WPShadow
 */
class Badge_Manager {

	/**
	 * Get all available badges
	 *
	 * @return array Badge definitions
	 */
	public static function get_all_badges(): array {
		return array(
			// Skill Level Badges
			'novice'              => array(
				'name'        => __( 'Novice', 'wpshadow' ),
				'description' => __( '0-100 points', 'wpshadow' ),
				'icon'        => '🌱',
				'min_points'  => 0,
				'max_points'  => 100,
				'color'       => '#90EE90',
			),
			'apprentice'          => array(
				'name'        => __( 'Apprentice', 'wpshadow' ),
				'description' => __( '100-250 points', 'wpshadow' ),
				'icon'        => '📚',
				'min_points'  => 100,
				'max_points'  => 250,
				'color'       => '#4169E1',
			),
			'expert'              => array(
				'name'        => __( 'Expert', 'wpshadow' ),
				'description' => __( '250-500 points', 'wpshadow' ),
				'icon'        => '⭐',
				'min_points'  => 250,
				'max_points'  => 500,
				'color'       => '#FFD700',
			),
			'master'              => array(
				'name'        => __( 'Master', 'wpshadow' ),
				'description' => __( '500+ points', 'wpshadow' ),
				'icon'        => '👑',
				'min_points'  => 500,
				'max_points'  => PHP_INT_MAX,
				'color'       => '#FF6347',
			),

			// Specialty Badges
			'security_specialist' => array(
				'name'        => __( 'Security Specialist', 'wpshadow' ),
				'description' => __( 'Fix 10+ security issues', 'wpshadow' ),
				'icon'        => '🛡️',
				'trigger'     => 'security_specialist',
				'color'       => '#8B0000',
			),
			'performance_tuner'   => array(
				'name'        => __( 'Performance Tuner', 'wpshadow' ),
				'description' => __( 'Fix 15+ performance issues', 'wpshadow' ),
				'icon'        => '⚡',
				'trigger'     => 'performance_tuner',
				'color'       => '#FFD700',
			),
			'code_master'         => array(
				'name'        => __( 'Code Master', 'wpshadow' ),
				'description' => __( 'Fix 10+ code quality issues', 'wpshadow' ),
				'icon'        => '💻',
				'trigger'     => 'code_master',
				'color'       => '#9370DB',
			),
			'consistency_king'    => array(
				'name'        => __( 'Consistency King', 'wpshadow' ),
				'description' => __( '30+ day scan streak', 'wpshadow' ),
				'icon'        => '👑',
				'trigger'     => 'consistency_king',
				'color'       => '#FFD700',
			),
		);
	}

	/**
	 * Get user's skill level badge
	 *
	 * @param int $user_id User ID
	 * @return array Badge information
	 */
	public static function get_skill_badge( $user_id ): array {
		$points = Achievement_System::get_user_points( $user_id );
		$badges = self::get_all_badges();

		// Find appropriate skill level
		foreach ( array( 'master', 'expert', 'apprentice', 'novice' ) as $badge_id ) {
			if ( isset( $badges[ $badge_id ] ) ) {
				$badge = $badges[ $badge_id ];
				if ( $points >= $badge['min_points'] && $points <= $badge['max_points'] ) {
					return array_merge(
						$badge,
						array(
							'id'     => $badge_id,
							'points' => $points,
						)
					);
				}
			}
		}

		return $badges['novice'] + array(
			'id'     => 'novice',
			'points' => 0,
		);
	}

	/**
	 * Get user's specialty badges
	 *
	 * @param int $user_id User ID
	 * @return array User's specialty badges
	 */
	public static function get_user_badges( $user_id ): array {
		$user_badges = get_user_meta( $user_id, 'wpshadow_specialty_badges', true ) ?: array();
		$all_badges  = self::get_all_badges();

		$result = array( self::get_skill_badge( $user_id ) );

		foreach ( $user_badges as $badge_id ) {
			if ( isset( $all_badges[ $badge_id ] ) ) {
				$result[] = array_merge( $all_badges[ $badge_id ], array( 'id' => $badge_id ) );
			}
		}

		return $result;
	}

	/**
	 * Award specialty badge to user
	 *
	 * @param int    $user_id User ID
	 * @param string $badge_id Badge ID
	 * @return bool Success status
	 */
	public static function award_badge( $user_id, $badge_id ): bool {
		$badges = get_user_meta( $user_id, 'wpshadow_specialty_badges', true ) ?: array();

		if ( in_array( $badge_id, $badges, true ) ) {
			return false; // Already has badge
		}

		$badges[] = $badge_id;
		$result   = update_user_meta( $user_id, 'wpshadow_specialty_badges', $badges );

		if ( $result ) {
			if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'badge_awarded',
					sprintf( 'Badge awarded: %s', $badge_id ),
					'',
					array(
						'user_id'  => $user_id,
						'badge_id' => $badge_id,
					)
				);
			}
		}

		return (bool) $result;
	}

	/**
	 * Render badge display
	 *
	 * @param array $badge Badge information
	 * @return void
	 */
	public static function render_badge( $badge ): void {
		?>
		<div style="
			display: inline-block;
			padding: 8px 12px;
			background: <?php echo esc_attr( $badge['color'] ?? '#ccc' ); ?>;
			border-radius: 50%;
			text-align: center;
			min-width: 60px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			margin: 4px;
		" title="<?php echo esc_attr( $badge['description'] ?? '' ); ?>">
			<div style="font-size: 24px; margin-bottom: 4px;">
				<?php echo $badge['icon'] ?? ''; ?>
			</div>
			<div style="font-size: 10px; font-weight: 500; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
				<?php echo esc_html( $badge['name'] ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render all user badges
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	public static function render_user_badges( $user_id ): void {
		$badges = self::get_user_badges( $user_id );

		?>
		<div class="wps-flex-gap-8-items-center">
			<?php foreach ( $badges as $badge ) : ?>
				<?php self::render_badge( $badge ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
