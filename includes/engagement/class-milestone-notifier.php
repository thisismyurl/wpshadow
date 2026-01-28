<?php
declare(strict_types=1);

namespace WPShadow\Gamification;

/**
 * Milestone Notifier
 *
 * Sends notifications for achievements, streaks, and milestones.
 * Philosophy: Inspire Confidence (#8) - Celebrate progress
 * Philosophy: Helpful Neighbor (#1) - Encouraging feedback
 *
 * @since 1.2601
 * @package WPShadow
 */
class Milestone_Notifier {

	/**
	 * Achievement unlock notification
	 *
	 * @param int    $user_id User ID
	 * @param string $achievement_id Achievement ID
	 * @return void
	 */
	public static function notify_achievement_unlocked( $user_id, $achievement_id ): void {
		$achievement = Achievement_System::get_achievement( $achievement_id );
		if ( ! $achievement ) {
			return;
		}

		$messages = array(
			'first_scan'           => __( '🎉 Welcome aboard! You\'ve completed your first scan!', 'wpshadow' ),
			'first_fix'            => __( '⭐ Fantastic! You\'ve applied your first treatment!', 'wpshadow' ),
			'security_advocate'    => __( '🛡️ Security Superstar! You\'ve fixed 5 security issues!', 'wpshadow' ),
			'performance_hero'     => __( '⚡ Performance Pro! You\'ve optimized 10 performance issues!', 'wpshadow' ),
			'code_cleaner'         => __( '💻 Code Master! You\'ve cleaned 8 code quality issues!', 'wpshadow' ),
			'consistency_starter'  => __( '🔥 On Fire! You\'ve maintained a 7-day scan streak!', 'wpshadow' ),
			'issue_resolver'       => __( '🎯 Prolific Fixer! You\'ve resolved 20 issues!', 'wpshadow' ),
			'all_category_master'  => __( '🏆 All-Star! You\'ve fixed issues in every category!', 'wpshadow' ),
			'consistency_champion' => __( '👑 Consistency Champion! 30-day streak achieved!', 'wpshadow' ),
			'perfect_site_health'  => __( '💯 Perfect Score! Your site is in perfect health!', 'wpshadow' ),
			'maintenance_master'   => __( '🔧 Maintenance Master! 50 treatments completed!', 'wpshadow' ),
			'security_legend'      => __( '🗡️ Security Legend! You\'ve conquered security challenges!', 'wpshadow' ),
		);

		$message = $messages[ $achievement_id ] ?? sprintf(
			__( '🎊 Achievement Unlocked: %s!', 'wpshadow' ),
			$achievement['name']
		);

		self::create_notification( $user_id, 'achievement', $message, $achievement );
	}

	/**
	 * Streak milestone notification
	 *
	 * @param int    $user_id User ID
	 * @param string $type Streak type (daily_scans, fixes)
	 * @param int    $count Current streak count
	 * @return void
	 */
	public static function notify_streak_milestone( $user_id, $type, $count ): void {
		$milestones = array(
			7   => __( 'One week! 🔥', 'wpshadow' ),
			14  => __( 'Two weeks! 🔥🔥', 'wpshadow' ),
			30  => __( 'One month! ⭐🔥', 'wpshadow' ),
			60  => __( 'Two months! 💪', 'wpshadow' ),
			100 => __( '100 days! 🏆', 'wpshadow' ),
		);

		if ( ! isset( $milestones[ $count ] ) ) {
			return;
		}

		$type_name = 'daily_scans' === $type ? __( 'Scan Streak', 'wpshadow' ) : __( 'Fix Streak', 'wpshadow' );

		$message = sprintf(
			__( '%1$s - %2$s: %3$d days! Keep it up!', 'wpshadow' ),
			$milestones[ $count ],
			$type_name,
			$count
		);

		self::create_notification(
			$user_id,
			'streak',
			$message,
			array(
				'type'  => $type,
				'count' => $count,
			)
		);
	}

	/**
	 * Rank change notification
	 *
	 * @param int $user_id User ID
	 * @param int $old_rank Old rank
	 * @param int $new_rank New rank
	 * @return void
	 */
	public static function notify_rank_change( $user_id, $old_rank, $new_rank ): void {
		if ( $new_rank >= $old_rank ) {
			return; // Only notify on improvement
		}

		if ( $new_rank === 1 ) {
			$message = __( '👑 You\'re #1 on the leaderboard! Amazing!', 'wpshadow' );
		} elseif ( $new_rank <= 3 ) {
			$medal   = array( '🥇', '🥈', '🥉' )[ $new_rank - 1 ];
			$message = sprintf(
				__( '%1$s You\'re in the top 3! (#%2$d)', 'wpshadow' ),
				$medal,
				$new_rank
			);
		} else {
			$message = sprintf(
				__( '📈 You climbed to rank #%d! Keep going!', 'wpshadow' ),
				$new_rank
			);
		}

		self::create_notification(
			$user_id,
			'rank',
			$message,
			array(
				'old_rank' => $old_rank,
				'new_rank' => $new_rank,
			)
		);
	}

	/**
	 * Create notification
	 *
	 * @param int    $user_id User ID
	 * @param string $type Notification type
	 * @param string $message Message text
	 * @param array  $data Additional data
	 * @return void
	 */
	private static function create_notification( $user_id, $type, $message, $data = array() ): void {
		$notifications = get_user_meta( $user_id, 'wpshadow_notifications', true ) ?: array();

		$notification = array(
			'type'      => $type,
			'message'   => $message,
			'timestamp' => current_time( 'timestamp' ),
			'read'      => false,
			'data'      => $data,
		);

		array_unshift( $notifications, $notification );

		// Keep last 50 notifications
		$notifications = array_slice( $notifications, 0, 50 );

		update_user_meta( $user_id, 'wpshadow_notifications', $notifications );

		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'milestone_notification',
				sprintf( 'Notification: %s', $message ),
				'',
				array(
					'user_id' => $user_id,
					'type'    => $type,
				)
			);
		}
	}

	/**
	 * Get user notifications
	 *
	 * @param int $user_id User ID
	 * @param int $limit Limit notifications
	 * @return array Notifications
	 */
	public static function get_notifications( $user_id, $limit = 10 ): array {
		$notifications = get_user_meta( $user_id, 'wpshadow_notifications', true ) ?: array();
		return array_slice( $notifications, 0, $limit );
	}

	/**
	 * Mark notification as read
	 *
	 * @param int $user_id User ID
	 * @param int $index Notification index
	 * @return bool Success status
	 */
	public static function mark_read( $user_id, $index ): bool {
		$notifications = get_user_meta( $user_id, 'wpshadow_notifications', true ) ?: array();

		if ( ! isset( $notifications[ $index ] ) ) {
			return false;
		}

		$notifications[ $index ]['read'] = true;
		return (bool) update_user_meta( $user_id, 'wpshadow_notifications', $notifications );
	}

	/**
	 * Clear all notifications
	 *
	 * @param int $user_id User ID
	 * @return bool Success status
	 */
	public static function clear_all( $user_id ): bool {
		return (bool) delete_user_meta( $user_id, 'wpshadow_notifications' );
	}

	/**
	 * Render notification
	 *
	 * @param array $notification Notification data
	 * @return void
	 */
	public static function render_notification( $notification ): void {
		$colors = array(
			'achievement' => '#FFD700',
			'streak'      => '#FF6347',
			'rank'        => '#4169E1',
		);

		$bg_color   = $colors[ $notification['type'] ] ?? '#ccc';
		$read_class = $notification['read'] ? 'read' : 'unread';

		?>
		<div class="wpshadow-notification <?php echo esc_attr( $read_class ); ?>" 
			style="
				padding: 12px;
				background: <?php echo esc_attr( $bg_color ); ?>;
				color: white;
				border-radius: 4px;
				margin: 8px 0;
				font-size: 13px;
				border-left: 4px solid <?php echo esc_attr( $bg_color ); ?>;
				opacity: <?php echo $notification['read'] ? '0.6' : '1'; ?>;
			">
			<?php echo esc_html( $notification['message'] ); ?>
			<div class="wps-text-xs wps-mt-1 wps-opacity-80">
				<?php
					$time = strtotime( $notification['timestamp'] );
					printf(
						/* translators: %s: Time ago */
						esc_html__( '%s ago', 'wpshadow' ),
						esc_html( self::time_ago( $notification['timestamp'] ) )
					);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Human-readable time ago
	 *
	 * @param int $timestamp Unix timestamp
	 * @return string Time ago string
	 */
	private static function time_ago( $timestamp ): string {
		$diff = current_time( 'timestamp' ) - $timestamp;

		if ( $diff < 60 ) {
			return __( 'now', 'wpshadow' );
		} elseif ( $diff < 3600 ) {
			$mins = floor( $diff / 60 );
			return sprintf( _n( '%d min', '%d mins', $mins, 'wpshadow' ), $mins );
		} elseif ( $diff < 86400 ) {
			$hours = floor( $diff / 3600 );
			return sprintf( _n( '%d hour', '%d hours', $hours, 'wpshadow' ), $hours );
		} else {
			$days = floor( $diff / 86400 );
			return sprintf( _n( '%d day', '%d days', $days, 'wpshadow' ), $days );
		}
	}

	/**
	 * Render all unread notifications for user
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	public static function render_unread_notifications( $user_id ): void {
		$notifications = self::get_notifications( $user_id );
		$unread        = array_filter(
			$notifications,
			function ( $n ) {
				return ! $n['read'];
			}
		);

		if ( empty( $unread ) ) {
			echo '<p class="wps-text-center" style="color: #999;">' . esc_html__( 'No new notifications', 'wpshadow' ) . '</p>';
			return;
		}

		foreach ( $unread as $index => $notification ) {
			self::render_notification( $notification );
		}
	}
}
