<?php
/**
 * Gamification UI
 *
 * Admin pages for achievements, leaderboard, and rewards.
 * Phase 8: Gamification System - User Interface
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.6004.0400
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gamification UI Class
 *
 * Renders admin pages for gamification features.
 *
 * @since 1.6004.0400
 */
class Gamification_UI extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_menu'            => array( 'register_menu_pages', 10 ),
			'admin_enqueue_scripts' => 'enqueue_assets',
		);
	}

	/**
	 * Initialize UI (deprecated - use ::subscribe() instead).
	 *
	 * @deprecated 1.7035.1400 Use Gamification_UI::subscribe() instead
	 * @since      1.6004.0400
	 * @return     void
	 */
	public static function init() {
		// Backwards compatibility
		self::subscribe();
	}

	/**
	 * Register admin menu pages.
	 *
	 * Achievements is registered as a submenu under wpshadow (parent),
	 * with Leaderboard and Rewards as submenus under Achievements.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function register_menu_pages() {
		// Leaderboard page (under Achievements parent)
		add_submenu_page(
			'wpshadow-achievements',
			__( 'Leaderboard', 'wpshadow' ),
			__( 'Leaderboard', 'wpshadow' ),
			'read',
			'wpshadow-leaderboard',
			array( __CLASS__, 'render_leaderboard_page' )
		);

		// Rewards page (under Achievements parent)
		add_submenu_page(
			'wpshadow-achievements',
			__( 'Rewards', 'wpshadow' ),
			__( 'Rewards', 'wpshadow' ),
			'read',
			'wpshadow-rewards',
			array( __CLASS__, 'render_rewards_page' )
		);

		// Privacy page (under Achievements parent)
		add_submenu_page(
			'wpshadow-achievements',
			__( 'Privacy Dashboard', 'wpshadow' ),
			__( 'Privacy', 'wpshadow' ),
			'manage_options',
			'wpshadow-privacy',
			array( 'WPShadow\Admin\Privacy_Dashboard_Page', 'render_page' )
		);
	}

	/**
	 * Enqueue assets.
	 *
	 * @since  1.6004.0400
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! strpos( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-gamification',
			WPSHADOW_URL . 'assets/css/gamification.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-gamification',
			WPSHADOW_URL . 'assets/js/gamification.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-gamification',
			'wpShadowGamification',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpshadow_gamification' ),
			)
		);
	}

	/**
	 * Render achievements page.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function render_achievements_page() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		$user_id = get_current_user_id();
		$progress = Achievement_Registry::get_progress( $user_id );
		$unlocked = Achievement_Registry::get_unlocked( $user_id );
		$locked = Achievement_Registry::get_locked( $user_id );
		?>
		<div class="wps-page-container">
			<?php wpshadow_render_page_header(
				__( 'Achievements', 'wpshadow' ),
				__( 'Unlock achievements and earn points by maintaining your WordPress site.', 'wpshadow' ),
				'dashicons-awards'
			); ?>

			<div class="wpshadow-achievements-stats">
				<div class="stat-card">
					<span class="stat-value"><?php echo esc_html( $progress['unlocked'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Unlocked', 'wpshadow' ); ?></span>
				</div>
				<div class="stat-card">
					<span class="stat-value"><?php echo esc_html( $progress['percentage'] ); ?>%</span>
					<span class="stat-label"><?php esc_html_e( 'Complete', 'wpshadow' ); ?></span>
				</div>
				<div class="stat-card">
					<span class="stat-value"><?php echo esc_html( $progress['locked'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Remaining', 'wpshadow' ); ?></span>
				</div>
			</div>

			<!-- Categories tabs -->
			<div class="wpshadow-achievements-tabs">
				<button class="tab-button active" data-category="all">
					<?php esc_html_e( 'All', 'wpshadow' ); ?>
				</button>
				<?php foreach ( Achievement_Registry::get_categories() as $cat_id => $cat_name ) : ?>
					<button class="tab-button" data-category="<?php echo esc_attr( $cat_id ); ?>">
						<?php echo esc_html( $cat_name ); ?>
						<span class="badge"><?php echo esc_html( $progress['by_category'][ $cat_id ]['unlocked'] ?? 0 ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<!-- Unlocked achievements -->
			<div class="wpshadow-achievements-section">
				<h2><?php esc_html_e( 'Unlocked Achievements', 'wpshadow' ); ?></h2>
				<div class="achievements-grid">
					<?php foreach ( $unlocked as $id => $achievement ) : ?>
						<div class="achievement-card unlocked" data-category="<?php echo esc_attr( $achievement['category'] ); ?>">
							<span class="achievement-emoji"><?php echo esc_html( $achievement['emoji'] ); ?></span>
							<h3><?php echo esc_html( $achievement['name'] ); ?></h3>
							<p><?php echo esc_html( $achievement['description'] ); ?></p>
							<div class="achievement-meta">
								<span class="points"><?php echo esc_html( $achievement['points'] ); ?> pts</span>
								<span class="date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $achievement['unlocked_at'] ) ) ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Locked achievements -->
			<div class="wpshadow-achievements-section">
				<h2><?php esc_html_e( 'Locked Achievements', 'wpshadow' ); ?></h2>
				<div class="achievements-grid">
					<?php foreach ( $locked as $id => $achievement ) : ?>
						<div class="achievement-card locked" data-category="<?php echo esc_attr( $achievement['category'] ); ?>">
							<span class="achievement-emoji grayscale"><?php echo esc_html( $achievement['emoji'] ); ?></span>
							<h3><?php echo esc_html( $achievement['name'] ); ?></h3>
							<p><?php echo esc_html( $achievement['description'] ); ?></p>
							<div class="achievement-meta">
								<span class="points"><?php echo esc_html( $achievement['points'] ); ?> pts</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Achievements Activity Log -->
		<?php wpshadow_render_activity_log( 'achievements', 10 ); ?>
		<?php
	}

	/**
	 * Render leaderboard page.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function render_leaderboard_page() {
		$user_id = get_current_user_id();
		$opted_in = Leaderboard::is_opted_in( $user_id );
		$period = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : 'all_time';
		$leaderboard = Leaderboard::get_global( $period );
		$user_rank = Leaderboard::get_user_rank( $user_id, $period );
		?>
		<div class="wrap wpshadow-gamification-page wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Leaderboard', 'wpshadow' ),
				__( 'See how your achievements compare with other WPShadow users. Participation is optional and privacy-first.', 'wpshadow' ),
				'dashicons-awards'
			);
			?>

			<!-- Privacy notice -->
			<div class="wpshadow-leaderboard-privacy">
				<p>
					<strong><?php esc_html_e( 'Privacy First:', 'wpshadow' ); ?></strong>
					<?php esc_html_e( 'Leaderboard is opt-in only. Your data is never shared without permission.', 'wpshadow' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_leaderboard_optin' ); ?>
					<input type="hidden" name="action" value="wpshadow_leaderboard_optin" />
					<input type="hidden" name="optin" value="<?php echo $opted_in ? '0' : '1'; ?>" />
					<button type="submit" class="button">
						<?php echo $opted_in ? esc_html__( 'Opt Out', 'wpshadow' ) : esc_html__( 'Opt In', 'wpshadow' ); ?>
					</button>
				</form>
			</div>

			<?php if ( $opted_in ) : ?>
				<!-- Period tabs -->
				<div class="wpshadow-leaderboard-tabs">
					<a href="?page=wpshadow-leaderboard&period=all_time" class="<?php echo 'all_time' === $period ? 'active' : ''; ?>">
						<?php esc_html_e( 'All Time', 'wpshadow' ); ?>
					</a>
					<a href="?page=wpshadow-leaderboard&period=monthly" class="<?php echo 'monthly' === $period ? 'active' : ''; ?>">
						<?php esc_html_e( 'This Month', 'wpshadow' ); ?>
					</a>
					<a href="?page=wpshadow-leaderboard&period=weekly" class="<?php echo 'weekly' === $period ? 'active' : ''; ?>">
						<?php esc_html_e( 'This Week', 'wpshadow' ); ?>
					</a>
				</div>

				<?php if ( $user_rank ) : ?>
					<div class="wpshadow-user-rank">
						<strong><?php esc_html_e( 'Your Rank:', 'wpshadow' ); ?></strong>
						#<?php echo esc_html( $user_rank ); ?>
					</div>
				<?php endif; ?>

				<!-- Leaderboard table -->
				<table class="wp-list-table widefat fixed striped wpshadow-leaderboard-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Rank', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'User', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Points', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Badges', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Achievements', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $leaderboard as $entry ) : ?>
							<tr <?php echo $entry['user_id'] === $user_id ? 'class="highlight-row"' : ''; ?>>
								<td>
									<?php if ( $entry['rank'] <= 3 ) : ?>
										<span class="rank-medal rank-<?php echo esc_attr( $entry['rank'] ); ?>">
											<?php echo esc_html( $entry['rank'] ); ?>
										</span>
									<?php else : ?>
										<?php echo esc_html( $entry['rank'] ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $entry['display_name'] ); ?></td>
								<td><?php echo esc_html( number_format_i18n( $entry['points'] ) ); ?></td>
								<td><?php echo esc_html( $entry['badges'] ); ?></td>
								<td><?php echo esc_html( $entry['achievements'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="wpshadow-leaderboard-opt-in-message">
					<p><?php esc_html_e( 'Opt in to view the leaderboard and compete with other WPShadow users!', 'wpshadow' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render rewards page.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function render_rewards_page() {
		$user_id = get_current_user_id();
		$balance = Points_System::get_balance( $user_id );
		$rewards = Reward_System::get_all();
		$history = Reward_System::get_history( $user_id, 10 );
		$categories = Reward_System::get_categories();
		$actions = Earn_Actions::get_actions();
		$action_status = Earn_Actions::get_user_status( $user_id );
		?>
		<div class="wrap wpshadow-gamification-page wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Rewards', 'wpshadow' ),
				__( 'Use your points for rewards and find optional ways to earn more.', 'wpshadow' ),
				'dashicons-gift'
			);
			?>

			<!-- Points balance -->
			<div class="wpshadow-points-balance-card">
				<span class="balance-label"><?php esc_html_e( 'Your Points:', 'wpshadow' ); ?></span>
				<span class="balance-value"><?php echo esc_html( number_format_i18n( $balance ) ); ?></span>
			</div>

			<!-- Earn more points -->
			<div class="wpshadow-earn-actions">
				<h2><?php esc_html_e( 'Earn More Points', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Optional, privacy-friendly ways to earn points. Some actions use the honor system and are only available once.', 'wpshadow' ); ?>
				</p>
				<div class="rewards-grid">
					<?php foreach ( $actions as $action_id => $action ) : ?>
						<?php
							$status = $action_status[ $action_id ] ?? array();
							$is_auto = ! empty( $action['auto'] );
							$is_claimed = ! empty( $status['claimed'] );
							$is_completed = ! empty( $status['completed'] );
							$is_eligible = ! empty( $status['eligible'] );
							$button_label = $is_claimed ? __( 'Claimed', 'wpshadow' ) : __( 'Claim Points', 'wpshadow' );
							?>
							<div class="reward-card">
								<span class="reward-emoji">🎁</span>
								<h3><?php echo esc_html( $action['name'] ); ?></h3>
								<p><?php echo esc_html( $action['description'] ); ?></p>
								<div class="reward-footer">
									<span class="reward-cost"><?php echo esc_html( number_format_i18n( (int) $action['points'] ) ); ?> pts</span>
									<?php if ( $is_auto ) : ?>
										<span class="reward-status">
											<?php echo $is_completed ? esc_html__( 'Completed', 'wpshadow' ) : esc_html__( 'Not set up yet', 'wpshadow' ); ?>
										</span>
									<?php else : ?>
										<button
											class="button wpshadow-earn-action"
											data-action-id="<?php echo esc_attr( $action_id ); ?>"
											data-action-url="<?php echo esc_url( $action['url'] ?? '' ); ?>"
											<?php echo ( ! $is_eligible || $is_claimed ) ? 'disabled' : ''; ?>
										>
											<?php echo esc_html( $button_label ); ?>
										</button>
										<?php if ( ! empty( $status['message'] ) ) : ?>
											<div class="reward-hint">
												<?php echo esc_html( $status['message'] ); ?>
											</div>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Reward catalog -->
			<div class="wpshadow-rewards-catalog">
				<?php foreach ( $categories as $cat_id => $cat_name ) : ?>
					<?php $cat_rewards = Reward_System::get_all( $cat_id ); ?>
					<?php if ( ! empty( $cat_rewards ) ) : ?>
						<div class="rewards-category">
							<h2><?php echo esc_html( $cat_name ); ?></h2>
							<div class="rewards-grid">
								<?php foreach ( $cat_rewards as $reward_id => $reward ) : ?>
									<?php $can_afford = $balance >= $reward['cost']; ?>
									<div class="reward-card <?php echo $can_afford ? 'affordable' : 'expensive'; ?>">
										<span class="reward-emoji"><?php echo esc_html( $reward['emoji'] ); ?></span>
										<h3><?php echo esc_html( $reward['name'] ); ?></h3>
										<p><?php echo esc_html( $reward['description'] ); ?></p>
										<div class="reward-footer">
											<span class="reward-cost"><?php echo esc_html( number_format_i18n( $reward['cost'] ) ); ?> pts</span>
											<button
												class="button redeem-reward"
												data-reward-id="<?php echo esc_attr( $reward_id ); ?>"
												<?php echo ! $can_afford ? 'disabled' : ''; ?>
											>
												<?php esc_html_e( 'Redeem', 'wpshadow' ); ?>
											</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<!-- Redemption history -->
			<?php if ( ! empty( $history ) ) : ?>
				<div class="wpshadow-redemption-history">
					<h2><?php esc_html_e( 'Redemption History', 'wpshadow' ); ?></h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Reward', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Cost', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $history as $entry ) : ?>
								<tr>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $entry['timestamp'] ) ) ); ?></td>
									<td><?php echo esc_html( $entry['reward_name'] ); ?></td>
									<td><?php echo esc_html( number_format_i18n( $entry['cost'] ) ); ?> pts</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
