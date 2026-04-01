<?php
/**
 * Weekly Tips Widget
 *
 * Displays weekly learning tips and recommendations on the dashboard.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

use WPShadow\Core\UTM_Link_Manager;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weekly Tips Widget Class
 *
 * Provides rotating weekly tips and learning recommendations.
 *
 * @since 0.6093.1200
 */
class Weekly_Tips_Widget extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_enqueue_scripts'             => 'enqueue_assets',
			'wp_ajax_wpshadow_mark_tip_helpful' => 'ajax_mark_helpful',
		);
	}

	/**
	 * Enqueue widget assets on dashboard.
	 *
	 * @since 0.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-weekly-tips-widget',
			WPSHADOW_URL . 'assets/css/weekly-tips-widget.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-weekly-tips-widget',
			WPSHADOW_URL . 'assets/js/weekly-tips-widget.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-weekly-tips-widget',
			'wpshadowWeeklyTipsWidget',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_tip_feedback' ),
			)
		);
	}

	/**
	 * Initialize widget (deprecated)
	 *
	 * @deprecated1.0 Use Weekly_Tips_Widget::subscribe() instead
	 * @since 0.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Register dashboard widget
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'wpshadow_weekly_tips',
			__( '💡 WPShadow Weekly Tip', 'wpshadow' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Render dashboard widget
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_widget() {
		$tip = self::get_current_week_tip();

		if ( ! $tip ) {
			?>
			<p><?php esc_html_e( 'Check back next week for a new tip!', 'wpshadow' ); ?></p>
			<?php
			return;
		}

		?>
		<div class="wpshadow-weekly-tip">
			<div class="wpshadow-weekly-tip__content">
				<h4 class="wpshadow-weekly-tip__title"><?php echo esc_html( $tip['title'] ); ?></h4>
				<p class="wpshadow-weekly-tip__description"><?php echo esc_html( $tip['description'] ); ?></p>

				<?php if ( ! empty( $tip['key_points'] ) ) : ?>
					<ul class="wpshadow-weekly-tip__points">
						<?php foreach ( $tip['key_points'] as $point ) : ?>
							<li><?php echo esc_html( $point ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="wpshadow-weekly-tip__actions">
					<?php if ( ! empty( $tip['video_url'] ) ) : ?>
						<a href="<?php echo esc_url( $tip['video_url'] ); ?>"
						   target="_blank"
						   class="button button-primary">
							<span class="dashicons dashicons-video-alt3"></span>
							<?php esc_html_e( 'Watch 5-Min Video', 'wpshadow' ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $tip['kb_url'] ) ) : ?>
						<a href="<?php echo esc_url( $tip['kb_url'] ); ?>"
						   target="_blank"
						   class="button button-secondary">
							<span class="dashicons dashicons-book-alt"></span>
							<?php esc_html_e( 'Read Guide', 'wpshadow' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="wpshadow-weekly-tip__feedback">
					<button type="button"
					        class="wpshadow-tip-helpful"
					        data-tip-id="<?php echo esc_attr( $tip['id'] ); ?>">
						<span class="dashicons dashicons-thumbs-up"></span>
						<?php esc_html_e( 'This was helpful', 'wpshadow' ); ?>
					</button>
					<span class="wpshadow-tip-helpful-thanks wpshadow-tip-helpful-thanks-hidden">
						<?php esc_html_e( 'Thanks for the feedback!', 'wpshadow' ); ?>
					</span>
				</div>
			</div>

			<div class="wpshadow-weekly-tip__footer">
				<small>
					<?php
					printf(
						/* translators: %s: week number */
						esc_html__( 'Week %s Tip', 'wpshadow' ),
						esc_html( self::get_current_week_number() )
					);
					?>
					&bull;
					<a href="<?php echo esc_url( UTM_Link_Manager::academy_link( 'all-tips', 'weekly-widget' ) ); ?>"
					   target="_blank">
						<?php esc_html_e( 'View All Tips', 'wpshadow' ); ?>
					</a>
				</small>
			</div>
		</div>
		<?php
	}

	/**
	 * Get current week's tip
	 *
	 * @since 0.6093.1200
	 * @return array|null Tip data or null if no tip available.
	 */
	private static function get_current_week_tip(): ?array {
		$tips = self::get_all_tips();
		$week_number = self::get_current_week_number();

		// Rotate through tips based on week number
		$tip_index = ( $week_number - 1 ) % count( $tips );

		return $tips[ $tip_index ] ?? null;
	}

	/**
	 * Get current week number (1-52)
	 *
	 * @since 0.6093.1200
	 * @return int Week number.
	 */
	private static function get_current_week_number(): int {
		return (int) date( 'W' );
	}

	/**
	 * Get all available tips
	 *
	 * @since 0.6093.1200
	 * @return array Array of tip data.
	 */
	private static function get_all_tips(): array {
		$tips = array(
			array(
				'id'          => 'tip-security-basics',
				'title'       => __( 'Security is a Journey, Not a Destination', 'wpshadow' ),
				'description' => __( 'WordPress security isn\'t about one-time fixes. It\'s about building habits: regular updates, strong passwords, and staying informed about threats. Start small, but stay consistent.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Update WordPress, plugins, and themes weekly', 'wpshadow' ),
					__( 'Use unique passwords for admin accounts', 'wpshadow' ),
					__( 'Enable two-factor authentication', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'wordpress-security-essentials', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'security-checklist', 'weekly-tip' ),
			),
			array(
				'id'          => 'tip-performance-caching',
				'title'       => __( 'Caching: Your Site\'s Secret Speed Boost', 'wpshadow' ),
				'description' => __( 'Caching can cut your page load time in half. By storing pre-built versions of your pages, you reduce server work and deliver content faster. It\'s one of the easiest performance wins.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Page caching stores full HTML pages', 'wpshadow' ),
					__( 'Object caching stores database query results', 'wpshadow' ),
					__( 'Browser caching saves assets on visitor devices', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'caching-strategies', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'page-caching', 'weekly-tip' ),
			),
			array(
				'id'          => 'tip-image-optimization',
				'title'       => __( 'Images: Your Biggest Performance Opportunity', 'wpshadow' ),
				'description' => __( 'Images often account for 50-70% of page weight. Optimize them and you\'ll see dramatic speed improvements. Modern formats like WebP can reduce file sizes by 30% with no visible quality loss.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Use WebP format for modern browsers', 'wpshadow' ),
					__( 'Implement lazy loading for below-fold images', 'wpshadow' ),
					__( 'Resize images to actual display dimensions', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'image-optimization', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'image-optimization', 'weekly-tip' ),
			),
			array(
				'id'          => 'tip-seo-meta',
				'title'       => __( 'Meta Descriptions: Your Search Result Pitch', 'wpshadow' ),
				'description' => __( 'Meta descriptions don\'t directly affect rankings, but they\'re crucial for click-through rates. Write them like ad copy: clear, compelling, and specific to what the page offers.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Keep them between 150-160 characters', 'wpshadow' ),
					__( 'Include your target keyword naturally', 'wpshadow' ),
					__( 'Make each one unique for your pages', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'meta-descriptions', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'meta-descriptions', 'weekly-tip' ),
			),
			array(
				'id'          => 'tip-plugin-management',
				'title'       => __( 'Less is More: The Plugin Minimalism Principle', 'wpshadow' ),
				'description' => __( 'Every plugin adds code your server must execute. Before installing, ask: "Is there a built-in WordPress feature for this?" Audit quarterly and remove unused plugins.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Deactivate before deleting to check for issues', 'wpshadow' ),
					__( 'Look for multi-purpose plugins to reduce count', 'wpshadow' ),
					__( 'Check plugin last update date before installing', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'plugin-management', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'plugin-performance', 'weekly-tip' ),
			),
			array(
				'id'          => 'tip-database-optimization',
				'title'       => __( 'Database Maintenance: Spring Cleaning for Speed', 'wpshadow' ),
				'description' => __( 'Your database accumulates revisions, spam comments, and transients over time. Regular cleanup can improve query performance and reduce backup sizes significantly.', 'wpshadow' ),
				'key_points'  => array(
					__( 'Optimize database tables monthly', 'wpshadow' ),
					__( 'Limit post revisions to 3-5 per post', 'wpshadow' ),
					__( 'Delete spam and trashed comments regularly', 'wpshadow' ),
				),
				'video_url'   => UTM_Link_Manager::academy_link( 'database-optimization', 'weekly-tip' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'database-optimization', 'weekly-tip' ),
			),
		);

		/**
		 * Filter weekly tips
		 *
		 * @since 0.6093.1200
		 *
		 * @param array $tips Array of tip data.
		 */
		return apply_filters( 'wpshadow_weekly_tips', $tips );
	}

	/**
	 * Handle AJAX mark tip as helpful
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function ajax_mark_helpful() {
		// Use Security_Validator for consistent security checks
		\WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_tip_feedback', 'nonce', true );

		$tip_id = isset( $_POST['tip_id'] ) ? sanitize_key( wp_unslash( $_POST['tip_id'] ) ) : '';
		if ( empty( $tip_id ) ) {
			wp_send_json_error();
		}

		// Track helpful feedback (if user has consented)
		if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'tip_helpful',
				array(
					'tip_id'  => $tip_id,
					'user_id' => get_current_user_id(),
				)
			);
		}

		wp_send_json_success();
	}
}

// Initialize
Weekly_Tips_Widget::init();
