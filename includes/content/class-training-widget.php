<?php
/**
 * Training Widget
 *
 * Displays contextual training recommendations on admin pages.
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
 * Training Widget Class
 *
 * Provides training recommendations and progress tracking.
 *
 * @since 0.6093.1200
 */
class Training_Widget extends Hook_Subscriber_Base {

	/**
	 * Determine if the current admin context is a WPShadow page.
	 *
	 * @since 0.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return bool True when on a WPShadow admin screen.
	 */
	private static function is_wpshadow_admin_context( string $hook = '' ): bool {
		if ( '' !== $hook && false !== strpos( $hook, 'wpshadow' ) ) {
			return true;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return $screen && isset( $screen->id ) && false !== strpos( (string) $screen->id, 'wpshadow' );
	}

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_enqueue_scripts'                         => 'enqueue_assets',
			'wp_ajax_wpshadow_dismiss_training_widget'      => 'ajax_dismiss_widget',
			'wp_ajax_wpshadow_track_training_click'         => 'ajax_track_click',
		);
	}

	/**
	 * Initialize widget (deprecated)
	 *
	 * @deprecated1.0 Use Training_Widget::subscribe() instead
	 * @since 0.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Enqueue widget assets
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function enqueue_assets( $hook = '' ) {
		if ( ! self::is_wpshadow_admin_context( (string) $hook ) ) {
			return;
		}

		if ( wp_style_is( 'wpshadow-admin-pages', 'enqueued' ) || wp_style_is( 'wpshadow-admin-pages', 'registered' ) ) {
			wp_enqueue_style(
				'wpshadow-training-widget',
				WPSHADOW_URL . 'assets/css/training-widget.css',
				array( 'wpshadow-admin-pages' ),
				WPSHADOW_VERSION
			);
		}

		if ( wp_script_is( 'wpshadow-admin-pages', 'enqueued' ) || wp_script_is( 'wpshadow-admin-pages', 'registered' ) ) {
			wp_enqueue_script(
				'wpshadow-training-widget',
				WPSHADOW_URL . 'assets/js/training-widget.js',
				array( 'jquery' ),
				WPSHADOW_VERSION,
				true
			);

			wp_localize_script(
				'wpshadow-training-widget',
				'wpshadowTrainingWidget',
				array(
					'nonce' => wp_create_nonce( 'wpshadow_training_widget' ),
				)
			);
		}
	}

	/**
	 * Render training widget
	 *
	 * @since 0.6093.1200
	 * @param  array $args Widget configuration.
	 * @return void
	 */
	public static function render( array $args = array() ) {
		$defaults = array(
			'title'            => __( 'Recommended Training', 'wpshadow' ),
			'courses'          => array(),
			'context'          => 'general',
			'show_progress'    => false,
			'show_dismiss'     => true,
			'max_courses'      => 3,
			'style'            => 'card', // 'card', 'sidebar', 'inline'
		);

		$args = wp_parse_args( $args, $defaults );

		// Get recommended courses if not provided
		if ( empty( $args['courses'] ) ) {
			$args['courses'] = self::get_recommended_courses( $args['context'] );
		}

		// Limit courses
		$args['courses'] = array_slice( $args['courses'], 0, $args['max_courses'] );

		if ( empty( $args['courses'] ) ) {
			return;
		}

		// Check if user has dismissed this widget
		$user_id = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'wpshadow_dismissed_training_' . $args['context'], true );
		if ( $dismissed && $args['show_dismiss'] ) {
			return;
		}

		// Render widget based on style
		switch ( $args['style'] ) {
			case 'sidebar':
				self::render_sidebar_widget( $args );
				break;
			case 'inline':
				self::render_inline_widget( $args );
				break;
			case 'card':
			default:
				self::render_card_widget( $args );
				break;
		}
	}

	/**
	 * Render card-style widget
	 *
	 * @since 0.6093.1200
	 * @param  array $args Widget arguments.
	 * @return void
	 */
	private static function render_card_widget( array $args ) {
		?>
		<div class="wpshadow-training-widget wpshadow-training-widget--card" data-context="<?php echo esc_attr( $args['context'] ); ?>">
			<div class="wpshadow-training-widget__header">
				<h3 class="wpshadow-training-widget__title">
					<span class="dashicons dashicons-video-alt3"></span>
					<?php echo esc_html( $args['title'] ); ?>
				</h3>
				<?php if ( $args['show_dismiss'] ) : ?>
					<button type="button" class="wpshadow-training-widget__dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'wpshadow' ); ?>">
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				<?php endif; ?>
			</div>
			<div class="wpshadow-training-widget__body">
				<p class="wpshadow-training-widget__intro">
					<?php esc_html_e( 'These free training courses will help you understand and optimize your WordPress site:', 'wpshadow' ); ?>
				</p>
				<div class="wpshadow-training-courses">
					<?php foreach ( $args['courses'] as $course ) : ?>
						<div class="wpshadow-training-course">
							<div class="wpshadow-training-course__icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $course['icon'] ?? 'video-alt3' ); ?>"></span>
							</div>
							<div class="wpshadow-training-course__content">
								<h4 class="wpshadow-training-course__title"><?php echo esc_html( $course['title'] ); ?></h4>
								<p class="wpshadow-training-course__description"><?php echo esc_html( $course['description'] ); ?></p>
								<div class="wpshadow-training-course__meta">
									<span class="wpshadow-training-course__duration">
										<span class="dashicons dashicons-clock"></span>
										<?php echo esc_html( $course['duration'] ?? '5 min' ); ?>
									</span>
									<?php if ( ! empty( $course['level'] ) ) : ?>
										<span class="wpshadow-training-course__level">
											<?php echo esc_html( $course['level'] ); ?>
										</span>
									<?php endif; ?>
								</div>
								<div class="wpshadow-training-course__actions">
									<a href="<?php echo esc_url( $course['video_url'] ); ?>"
									   target="_blank"
									   class="button button-primary wpshadow-training-click"
									   data-course="<?php echo esc_attr( $course['slug'] ); ?>">
										<?php esc_html_e( 'Watch Free Video', 'wpshadow' ); ?>
									</a>
									<?php if ( ! empty( $course['kb_url'] ) ) : ?>
										<a href="<?php echo esc_url( $course['kb_url'] ); ?>"
										   target="_blank"
										   class="button button-secondary">
											<?php esc_html_e( 'Read Guide', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render sidebar-style widget
	 *
	 * @since 0.6093.1200
	 * @param  array $args Widget arguments.
	 * @return void
	 */
	private static function render_sidebar_widget( array $args ) {
		?>
		<div class="wpshadow-training-widget wpshadow-training-widget--sidebar" data-context="<?php echo esc_attr( $args['context'] ); ?>">
			<h4><?php echo esc_html( $args['title'] ); ?></h4>
			<ul class="wpshadow-training-list">
				<?php foreach ( $args['courses'] as $course ) : ?>
					<li class="wpshadow-training-item">
						<a href="<?php echo esc_url( $course['video_url'] ); ?>"
						   target="_blank"
						   class="wpshadow-training-click"
						   data-course="<?php echo esc_attr( $course['slug'] ); ?>">
							<span class="dashicons dashicons-video-alt3"></span>
							<span class="wpshadow-training-item__title"><?php echo esc_html( $course['title'] ); ?></span>
							<span class="wpshadow-training-item__duration">(<?php echo esc_html( $course['duration'] ?? '5 min' ); ?>)</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render inline-style widget
	 *
	 * @since 0.6093.1200
	 * @param  array $args Widget arguments.
	 * @return void
	 */
	private static function render_inline_widget( array $args ) {
		$course = $args['courses'][0] ?? null;
		if ( ! $course ) {
			return;
		}
		?>
		<div class="wpshadow-training-widget wpshadow-training-widget--inline" data-context="<?php echo esc_attr( $args['context'] ); ?>">
			<span class="dashicons dashicons-lightbulb wpshadow-training-widget__inline-icon"></span>
			<span>
				<strong><?php esc_html_e( 'Learn more:', 'wpshadow' ); ?></strong>
				<a href="<?php echo esc_url( $course['video_url'] ); ?>"
				   target="_blank"
				   class="wpshadow-training-click"
				   data-course="<?php echo esc_attr( $course['slug'] ); ?>">
					<?php echo esc_html( $course['title'] ); ?>
				</a>
				(<?php echo esc_html( $course['duration'] ?? '5 min' ); ?> <?php esc_html_e( 'free video', 'wpshadow' ); ?>)
			</span>
		</div>
		<?php
	}

	/**
	 * Get recommended courses based on context
	 *
	 * @since 0.6093.1200
	 * @param  string $context Context identifier.
	 * @return array Array of course data.
	 */
	private static function get_recommended_courses( string $context ): array {
		$all_courses = self::get_course_catalog();

		// Map contexts to relevant courses
		$context_map = array(
			'dashboard'   => array( 'wordpress-security-essentials', 'site-performance-basics', 'seo-fundamentals' ),
			'security'    => array( 'wordpress-security-essentials', 'ssl-certificates', 'file-permissions' ),
			'performance' => array( 'site-performance-basics', 'caching-strategies', 'image-optimization' ),
			'seo'         => array( 'seo-fundamentals', 'meta-descriptions', 'sitemap-setup' ),
			'admin'       => array( 'plugin-management', 'wordpress-updates', 'database-optimization' ),
			'general'     => array( 'wordpress-security-essentials', 'site-performance-basics', 'backup-strategies' ),
		);

		$course_slugs = $context_map[ $context ] ?? $context_map['general'];
		$courses = array();

		foreach ( $course_slugs as $slug ) {
			if ( isset( $all_courses[ $slug ] ) ) {
				$courses[] = $all_courses[ $slug ];
			}
		}

		/**
		 * Filter recommended courses
		 *
		 * @since 0.6093.1200
		 *
		 * @param array  $courses Recommended courses.
		 * @param string $context Context identifier.
		 */
		return apply_filters( 'wpshadow_recommended_courses', $courses, $context );
	}

	/**
	 * Get full course catalog
	 *
	 * @since 0.6093.1200
	 * @return array Course catalog indexed by slug.
	 */
	private static function get_course_catalog(): array {
		return array(
			'wordpress-security-essentials' => array(
				'slug'        => 'wordpress-security-essentials',
				'title'       => __( 'WordPress Security Essentials', 'wpshadow' ),
				'description' => __( 'Learn the fundamentals of keeping your WordPress site secure.', 'wpshadow' ),
				'duration'    => '12 min',
				'level'       => __( 'Beginner', 'wpshadow' ),
				'icon'        => 'shield',
				'video_url'   => UTM_Link_Manager::academy_link( 'wordpress-security-essentials', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'security-checklist', 'training-widget' ),
			),
			'site-performance-basics' => array(
				'slug'        => 'site-performance-basics',
				'title'       => __( 'Site Performance Basics', 'wpshadow' ),
				'description' => __( 'Speed up your site with simple optimizations.', 'wpshadow' ),
				'duration'    => '10 min',
				'level'       => __( 'Beginner', 'wpshadow' ),
				'icon'        => 'dashboard',
				'video_url'   => UTM_Link_Manager::academy_link( 'site-performance-basics', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'performance-optimization', 'training-widget' ),
			),
			'seo-fundamentals' => array(
				'slug'        => 'seo-fundamentals',
				'title'       => __( 'SEO Fundamentals', 'wpshadow' ),
				'description' => __( 'Improve your search rankings with proper SEO.', 'wpshadow' ),
				'duration'    => '15 min',
				'level'       => __( 'Beginner', 'wpshadow' ),
				'icon'        => 'search',
				'video_url'   => UTM_Link_Manager::academy_link( 'seo-fundamentals', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'seo-guide', 'training-widget' ),
			),
			'ssl-certificates' => array(
				'slug'        => 'ssl-certificates',
				'title'       => __( 'SSL Certificates Explained', 'wpshadow' ),
				'description' => __( 'Understand and implement HTTPS for your site.', 'wpshadow' ),
				'duration'    => '8 min',
				'level'       => __( 'Intermediate', 'wpshadow' ),
				'icon'        => 'lock',
				'video_url'   => UTM_Link_Manager::academy_link( 'ssl-certificates', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'ssl-certificate', 'training-widget' ),
			),
			'caching-strategies' => array(
				'slug'        => 'caching-strategies',
				'title'       => __( 'WordPress Caching Strategies', 'wpshadow' ),
				'description' => __( 'Master caching for maximum performance.', 'wpshadow' ),
				'duration'    => '18 min',
				'level'       => __( 'Intermediate', 'wpshadow' ),
				'icon'        => 'performance',
				'video_url'   => UTM_Link_Manager::academy_link( 'caching-strategies', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'page-caching', 'training-widget' ),
			),
			'plugin-management' => array(
				'slug'        => 'plugin-management',
				'title'       => __( 'Plugin Management Best Practices', 'wpshadow' ),
				'description' => __( 'Keep plugins organized and optimized.', 'wpshadow' ),
				'duration'    => '10 min',
				'level'       => __( 'Beginner', 'wpshadow' ),
				'icon'        => 'admin-plugins',
				'video_url'   => UTM_Link_Manager::academy_link( 'plugin-management', 'training-widget' ),
				'kb_url'      => UTM_Link_Manager::kb_link( 'plugin-performance', 'training-widget' ),
			),
		);
	}

	/**
	 * Handle AJAX dismiss widget
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function ajax_dismiss_widget() {
		\WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_training_widget', 'nonce', true );

		$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'general';
		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'wpshadow_dismissed_training_' . $context, time() );

		wp_send_json_success();
	}

	/**
	 * Handle AJAX track training click
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function ajax_track_click() {
		\WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_training_widget', 'nonce', true );

		$course = isset( $_POST['course'] ) ? sanitize_key( wp_unslash( $_POST['course'] ) ) : '';
		if ( empty( $course ) ) {
			wp_send_json_error();
		}

		// Track click in activity log (if user has consented)
		if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'training_click',
				array(
					'course' => $course,
					'user_id' => get_current_user_id(),
				)
			);
		}

		wp_send_json_success();
	}
}

// Initialize
Training_Widget::init();
