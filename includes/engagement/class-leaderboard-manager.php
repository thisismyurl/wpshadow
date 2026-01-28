<?php
declare(strict_types=1);

namespace WPShadow\Gamification;

/**
 * Leaderboard Manager
 *
 * Manages site-wide leaderboards for multi-site competition.
 * Philosophy: Show Value (#9) - Visualize achievements
 * Philosophy: Helpful Neighbor (#1) - Friendly competition
 *
 * @since 1.2601
 * @package WPShadow
 */
class Leaderboard_Manager {

	/**
	 * Get top users by achievements
	 *
	 * @param int $limit Number of results
	 * @return array Top users
	 */
	public static function get_top_achievers( $limit = 10 ): array {
		if ( ! is_multisite() ) {
			return self::get_single_site_achievers( $limit );
		}

		global $wpdb;
		$results = array();

		$sites = get_sites( array( 'number' => 100 ) );

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$users = get_users( array( 'meta_key' => 'wpshadow_achievements' ) );

			foreach ( $users as $user ) {
				$points = Achievement_System::get_user_points( $user->ID );

				if ( $points > 0 ) {
					$results[] = array(
						'user_id'    => $user->ID,
						'user_name'  => $user->user_login,
						'user_email' => $user->user_email,
						'points'     => $points,
						'blog_id'    => $site->blog_id,
						'blog_name'  => $site->blogname,
					);
				}
			}

			restore_current_blog();
		}

		// Sort by points descending
		usort(
			$results,
			function ( $a, $b ) {
				return $b['points'] <=> $a['points'];
			}
		);

		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get top users for single site
	 *
	 * @param int $limit Number of results
	 * @return array Top users
	 */
	private static function get_single_site_achievers( $limit = 10 ): array {
		$users   = get_users( array( 'meta_key' => 'wpshadow_achievements' ) );
		$results = array();

		foreach ( $users as $user ) {
			$points = Achievement_System::get_user_points( $user->ID );

			if ( $points > 0 ) {
				$results[] = array(
					'user_id'      => $user->ID,
					'user_name'    => $user->user_login,
					'user_email'   => $user->user_email,
					'points'       => $points,
					'achievements' => count( Achievement_System::get_user_achievements( $user->ID ) ),
				);
			}
		}

		usort(
			$results,
			function ( $a, $b ) {
				return $b['points'] <=> $a['points'];
			}
		);

		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get user's rank
	 *
	 * @param int $user_id User ID
	 * @return int User's rank (1-indexed)
	 */
	public static function get_user_rank( $user_id ): int {
		$points = Achievement_System::get_user_points( $user_id );

		if ( ! is_multisite() ) {
			$users = get_users( array( 'meta_key' => 'wpshadow_achievements' ) );
		} else {
			$users = get_users( array( 'meta_key' => 'wpshadow_achievements' ) );
		}

		$rank = 1;

		foreach ( $users as $user ) {
			$user_points = Achievement_System::get_user_points( $user->ID );
			if ( $user_points > $points ) {
				++$rank;
			}
		}

		return $rank;
	}

	/**
	 * Render leaderboard widget
	 *
	 * @param int $limit Number of entries to show
	 * @return void
	 */
	public static function render_leaderboard( $limit = 10 ): void {
		$top_users = self::get_top_achievers( $limit );
		?>
		<div class="wps-p-20-rounded-8">
			<div class="wps-flex-gap-12-items-center">
				<span class="dashicons dashicons-chart-bar" style="font-size: 24px; color: #FF6B6B;"></span>
				<h3 class="wps-m-0"><?php esc_html_e( 'Top Achievers', 'wpshadow' ); ?></h3>
			</div>
			
			<div class="wps-overflow-x-auto">
				<table class="wps-table-collapse wps-text-sm">
					<thead>
						<tr class="wps-border-b" style="border-bottom: 2px solid #f0f0f0;">
							<th class="wps-p-8">Rank</th>
							<th class="wps-p-8">User</th>
							<th class="wps-p-8">Points</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $top_users as $index => $user ) : ?>
							<tr style="border-bottom: 1px solid #f5f5f5; background: <?php echo $index < 3 ? '#fafafa' : '#fff'; ?>;">
								<td class="wps-p-10">
									<?php
									if ( $index === 0 ) {
										echo '🥇';
									} elseif ( $index === 1 ) {
										echo '🥈';
									} elseif ( $index === 2 ) {
										echo '🥉';
									} else {
										echo ( $index + 1 );
									}
									?>
								</td>
								<td class="wps-p-10">
									<?php echo esc_html( $user['user_name'] ); ?>
									<?php if ( ! empty( $user['blog_name'] ) ) : ?>
										<br/><span style="font-size: 11px; color: #999;"><?php echo esc_html( $user['blog_name'] ); ?></span>
									<?php endif; ?>
								</td>
								<td class="wps-p-10">
									<?php echo (int) $user['points']; ?> pts
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
