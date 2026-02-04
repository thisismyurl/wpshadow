<?php
/**
 * WPShadow Academy - Admin UI
 *
 * Dashboard widgets, admin pages, and learning interface.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since      1.6030.1920
 */

declare(strict_types=1);

namespace WPShadow\Academy;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Academy_UI Class
 *
 * Handles all admin UI for WPShadow Academy.
 *
 * @since 1.6030.1920
 */
class Academy_UI extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_dashboard_setup'                             => 'register_dashboard_widget',
			'admin_enqueue_scripts'                          => 'enqueue_assets',
			'wp_ajax_wpshadow_dismiss_learning_suggestion'   => 'dismiss_learning_suggestion',
			'wp_ajax_wpshadow_track_article_view'            => 'track_article_view',
			'wp_ajax_wpshadow_track_video_completion'        => 'track_video_completion',
			'wp_ajax_wpshadow_get_learning_path'             => 'get_learning_path',
		);
	}

	/**
	 * Initialize UI (deprecated - use ::subscribe() instead)
	 *
	 * @deprecated 1.7035.1400 Use Academy_UI::subscribe() instead
	 * @since      1.6030.1920
	 * @return     void
	 */
	public static function init() {
		// Backwards compatibility: call subscribe()
		self::subscribe();
	}

	/**
	 * Register dashboard widget
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'wpshadow_academy_widget',
			__( '🎓 What You\'ve Learned So Far', 'wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function render_dashboard_widget() {
		$user_id  = get_current_user_id();
		$manager  = Academy_Manager::get_instance();
		$progress = $manager->get_user_progress( $user_id );

		?>
		<div class="wpshadow-academy-widget">
			<div class="academy-stats">
				<div class="stat">
					<span class="stat-value"><?php echo esc_html( $progress['articles_viewed'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Guides You\'ve Read', 'wpshadow' ); ?></span>
				</div>
				<div class="stat">
					<span class="stat-value"><?php echo esc_html( $progress['videos_completed'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Videos You\'ve Finished', 'wpshadow' ); ?></span>
				</div>
				<div class="stat">
					<span class="stat-value"><?php echo esc_html( $progress['courses_completed'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Full Courses Finished', 'wpshadow' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $progress['in_progress'] ) ) : ?>
				<div class="in-progress-courses">
					<h4><?php esc_html_e( 'Continue Learning', 'wpshadow' ); ?></h4>
					<?php foreach ( $progress['in_progress'] as $course_id => $course_progress ) : ?>
						<?php $course = Course_Registry::get( $course_id ); ?>
						<?php if ( $course ) : ?>
							<div class="course-progress">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-academy&course=' . $course_id ) ); ?>">
									<?php echo esc_html( $course['title'] ); ?>
								</a>
								<div class="progress-bar">
									<div class="progress-fill" style="width: <?php echo esc_attr( $course_progress['percent'] ); ?>%"></div>
								</div>
								<span class="progress-text">
									<?php
									printf(
										/* translators: %d: percentage */
										esc_html__( '%d%% complete', 'wpshadow' ),
										$course_progress['percent']
									);
									?>
								</span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// Show pending learning suggestions.
			$suggestions = get_option( 'wpshadow_academy_pending_suggestions', array() );
			if ( ! empty( $suggestions ) ) :
				$suggestion = reset( $suggestions );
				?>
				<div class="learning-suggestion">
					<h4><?php esc_html_e( '💡 Here\'s Something That Might Help', 'wpshadow' ); ?></h4>
					<p><?php echo esc_html( $suggestion['message'] ); ?></p>
					<?php if ( ! empty( $suggestion['article_id'] ) ) : ?>
						<?php $article = KB_Article_Registry::get( $suggestion['article_id'] ); ?>
						<?php if ( $article ) : ?>
							<a href="<?php echo esc_url( $article['url'] ); ?>" class="button button-primary" target="_blank">
								<?php echo esc_html( $article['title'] ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( ! empty( $suggestion['video_id'] ) ) : ?>
						<?php $video = Training_Video_Registry::get( $suggestion['video_id'] ); ?>
						<?php if ( $video ) : ?>
							<a href="<?php echo esc_url( $video['url'] ); ?>" class="button button-primary" target="_blank">
								<?php echo esc_html( $video['title'] ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( ! empty( $suggestion['course_id'] ) ) : ?>
						<?php $course = Course_Registry::get( $suggestion['course_id'] ); ?>
						<?php if ( $course ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-academy&course=' . $suggestion['course_id'] ) ); ?>" class="button button-primary">
								<?php echo esc_html( $course['title'] ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<button type="button" class="button dismiss-suggestion" data-suggestion-id="<?php echo esc_attr( $suggestion['id'] ); ?>">
						<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-academy' ) ); ?>" class="button">
					<?php esc_html_e( 'Visit Academy', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Academy page
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function render_academy_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		// If a specific tab is requested, render that content (only learning-path is supported)
		if ( ! empty( $tab ) ) {
			if ( 'learning-path' === $tab ) {
				?>
				<div class="wps-page-container">
					<?php wpshadow_render_page_header(
					__( 'Your Custom Study Plan', 'wpshadow' ),
					__( 'Lessons we picked specifically for your site (based on what we found when checking it).', 'wpshadow' ),
					); ?>

					<div style="margin-top: -10px;">
						<p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-academy' ) ); ?>">&larr; <?php esc_html_e( 'Back to Academy', 'wpshadow' ); ?></a>
						</p>
					</div>

					<div class="tab-content">
						<?php self::render_learning_path_tab(); ?>
					</div>
				</div>
				<?php
				return;
			}

			// For any other tab, redirect back to main academy page
			wp_safe_remote_get( admin_url( 'admin.php?page=wpshadow-academy' ) );
			return;
		}

		// Show academy overview grid
		?>
		<div class="wps-page-container">
			<?php wpshadow_render_page_header(
				__( 'Learning Center', 'wpshadow' ),
				__( 'Learn how to keep your WordPress site fast, safe, and running smoothly. Everything explained in plain English.', 'wpshadow' ),
				'dashicons-welcome-learn-more'
			); ?>

			<!-- Academy Grid -->
			<div class="wps-grid wps-grid-auto-320">
				<!-- Courses -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-media-video wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<a href="https://wpshadow.com/academy/courses?utm_source=wpshadow&utm_medium=plugin&utm_campaign=academy_page&utm_content=courses" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Courses', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
								<?php esc_html_e( 'Step-by-step classes that teach you everything (like taking a workshop).', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="https://wpshadow.com/academy/courses?utm_source=wpshadow&utm_medium=plugin&utm_campaign=academy_page&utm_content=courses" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'Browse Courses', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- My Learning Path -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-superhero wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
								<a href="https://wpshadow.com/academy/learning-path/" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
									<?php esc_html_e( 'My Learning Path', 'wpshadow' ); ?>
								</a>
							</h3>
							<p class="wps-card-description wps-m-0">
								<?php esc_html_e( 'Personalized recommendations based on your site\'s diagnostics.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>
				<div class="wps-card-body">
					<a href="https://wpshadow.com/academy/learning-path/" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'View Learning Path', 'wpshadow' ); ?>
					</a>
				</div>
			</div>

			<!-- KB Articles -->
										<?php esc_html_e( 'Quick Answer Guides', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Short articles that answer common questions (like having a cheat sheet handy).', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="https://wpshadow.com/kb?utm_source=wpshadow&utm_medium=plugin&utm_campaign=academy_page&utm_content=kb_articles" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'Browse Articles', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Training Videos -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-video-alt3 wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<a href="https://wpshadow.com/academy/videos?utm_source=wpshadow&utm_medium=plugin&utm_campaign=academy_page&utm_content=training_videos" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Video Lessons', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Watch and learn how to do things (like having someone show you in person).', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="https://wpshadow.com/academy/videos?utm_source=wpshadow&utm_medium=plugin&utm_campaign=academy_page&utm_content=training_videos" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'Start Watching', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- My Learning Path Summary Section -->
			<?php
			$user_id  = get_current_user_id();
			$manager  = Academy_Manager::get_instance();
			$progress = $manager->get_user_progress( $user_id );
			$learning_path = isset( $progress['learning_path'] ) ? $progress['learning_path'] : array();
			?>
			<div style="margin-top: 40px;">
				<h2 style="font-size: 20px; margin-bottom: 20px; color: #1d2327;">
					<?php esc_html_e( 'What We Think You Should Learn Next', 'wpshadow' ); ?>
				</h2>
				<div class="wps-card">
					<div class="wps-card-body">
						<?php if ( ! empty( $learning_path ) ) : ?>
							<p><?php esc_html_e( 'After looking at your site, here\'s what would help you most right now:', 'wpshadow' ); ?></p>
							<ul style="list-style: disc; margin-left: 20px;">
								<?php 
								foreach ( array_slice( $learning_path, 0, 5 ) as $item ) :
									$title = isset( $item['course'] ) ? $item['course'] : ( isset( $item['title'] ) ? $item['title'] : __( 'Course', 'wpshadow' ) );
									?>
									<li><?php echo esc_html( $title ); ?></li>
								<?php endforeach; ?>
							</ul>
							<div style="margin-top: 20px;">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-academy&tab=learning-path' ) ); ?>" class="wps-btn wps-btn--primary">
									<?php esc_html_e( 'View Full Learning Path', 'wpshadow' ); ?>
								</a>
							</div>
						<?php else : ?>
							<p><?php esc_html_e( 'Your site looks good! Browse our courses and videos to learn even more ways to improve.', 'wpshadow' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Recent Activity Section -->
			<div style="margin-top: 40px;">
				<?php wpshadow_render_activity_log( 'training', 10 ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render courses tab
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	private static function render_courses_tab() {
		$courses = Course_Registry::get_all();
		$progress = Academy_Manager::get_user_progress();

		?>
		<div class="wpshadow-courses-grid">
			<?php foreach ( $courses as $course ) : ?>
				<?php
				$is_completed  = in_array( $course['id'], $progress['courses_completed'], true );
				$is_in_progress = isset( $progress['in_progress'][ $course['id'] ] );
				$course_progress = $is_in_progress ? $progress['in_progress'][ $course['id'] ] : null;
				?>
				<div class="course-card <?php echo $is_completed ? 'completed' : ''; ?>">
					<?php if ( isset( $course['thumbnail'] ) ) : ?>
						<img src="<?php echo esc_url( $course['thumbnail'] ); ?>" alt="<?php echo esc_attr( $course['title'] ); ?>" class="course-thumbnail" />
					<?php endif; ?>
					<div class="course-content">
						<h3><?php echo esc_html( $course['title'] ); ?></h3>
						<p><?php echo esc_html( $course['description'] ); ?></p>
						<div class="course-meta">
							<span class="lesson-count">
								<?php
								printf(
									/* translators: %d: lesson count */
									esc_html__( '%d lessons', 'wpshadow' ),
									$course['lesson_count']
								);
								?>
							</span>
							<span class="duration"><?php echo esc_html( Course_Registry::format_duration( $course['total_duration'] ) ); ?></span>
							<span class="difficulty difficulty-<?php echo esc_attr( $course['difficulty'] ); ?>">
								<?php echo esc_html( ucfirst( $course['difficulty'] ) ); ?>
							</span>
							<?php if ( ! $course['free'] ) : ?>
								<span class="pro-badge"><?php esc_html_e( 'Pro', 'wpshadow' ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( $is_in_progress && $course_progress ) : ?>
							<div class="course-progress">
								<div class="progress-bar">
									<div class="progress-fill" style="width: <?php echo esc_attr( $course_progress['percent'] ); ?>%"></div>
								</div>
								<span class="progress-text">
									<?php
									printf(
										/* translators: %d: percentage */
										esc_html__( '%d%% complete', 'wpshadow' ),
										$course_progress['percent']
									);
									?>
								</span>
							</div>
						<?php endif; ?>
						<?php if ( $is_completed ) : ?>
							<p class="completed-badge">✅ <?php esc_html_e( 'Completed', 'wpshadow' ); ?></p>
						<?php endif; ?>
						<a href="<?php echo esc_url( $course['url'] ); ?>" class="button button-primary" target="_blank">
							<?php echo $is_in_progress ? esc_html__( 'Continue', 'wpshadow' ) : esc_html__( 'Start Course', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render learning path tab
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	private static function render_learning_path_tab() {
		$learning_path = Academy_Manager::get_learning_path();

		?>
		<div class="wpshadow-learning-path">
			<h2><?php esc_html_e( 'Your Custom Study Plan', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'After checking your site, here are the topics that would help you most:', 'wpshadow' ); ?></p>

			<?php if ( ! empty( $learning_path['courses'] ) ) : ?>
				<h3><?php esc_html_e( 'Courses That Would Help You', 'wpshadow' ); ?></h3>
				<div class="recommended-courses">
					<?php foreach ( $learning_path['courses'] as $course ) : ?>
						<div class="course-recommendation">
							<h4><?php echo esc_html( $course['title'] ); ?></h4>
							<p><?php echo esc_html( $course['description'] ); ?></p>
							<p class="reason"><?php echo esc_html( $course['reason'] ); ?></p>
							<a href="<?php echo esc_url( $course['url'] ); ?>" class="button button-primary" target="_blank">
								<?php esc_html_e( 'Start Course', 'wpshadow' ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Great news! Your site is in good shape. Check out our full course list to learn even more.', 'wpshadow' ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $learning_path['articles'] ) ) : ?>
				<h3><?php esc_html_e( 'Short Guides to Read', 'wpshadow' ); ?></h3>
				<ul class="recommended-articles">
					<?php foreach ( $learning_path['articles'] as $article ) : ?>
						<li>
							<a href="<?php echo esc_url( $article['url'] ); ?>" target="_blank">
								<?php echo esc_html( $article['title'] ); ?>
							</a>
							<span class="read-time"><?php echo esc_html( $article['read_time'] ); ?> min</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $learning_path['videos'] ) ) : ?>
				<h3><?php esc_html_e( 'Videos to Watch', 'wpshadow' ); ?></h3>
				<div class="recommended-videos">
					<?php foreach ( $learning_path['videos'] as $video ) : ?>
						<div class="video-recommendation">
							<h4><?php echo esc_html( $video['title'] ); ?></h4>
							<span class="duration"><?php echo esc_html( Training_Video_Registry::format_duration( $video['duration'] ) ); ?></span>
							<a href="<?php echo esc_url( $video['url'] ); ?>" class="button" target="_blank">
								<?php esc_html_e( 'Watch', 'wpshadow' ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render articles tab
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	private static function render_articles_tab() {
		$categories = KB_Article_Registry::get_categories();

		?>
		<div class="wpshadow-articles-browser">
			<div class="article-filters">
				<h3><?php esc_html_e( 'Browse by Category', 'wpshadow' ); ?></h3>
				<?php foreach ( $categories as $category => $count ) : ?>
					<button type="button" class="button filter-category" data-category="<?php echo esc_attr( $category ); ?>">
						<?php echo esc_html( ucfirst( $category ) ); ?> (<?php echo esc_html( $count ); ?>)
					</button>
				<?php endforeach; ?>
			</div>

			<div class="articles-list" id="articles-list">
				<?php self::render_articles_list(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render articles list
	 *
	 * @since  1.6030.1920
	 * @param  string $category Optional category filter.
	 * @return void
	 */
	private static function render_articles_list( $category = '' ) {
		$articles = $category ? KB_Article_Registry::get_by_category( $category ) : KB_Article_Registry::get_all();

		?>
		<div class="articles-grid">
			<?php foreach ( $articles as $article ) : ?>
				<div class="article-card">
					<h4><?php echo esc_html( $article['title'] ); ?></h4>
					<div class="article-meta">
						<span class="category"><?php echo esc_html( ucfirst( $article['category'] ) ); ?></span>
						<span class="difficulty"><?php echo esc_html( ucfirst( $article['difficulty'] ) ); ?></span>
						<span class="read-time"><?php echo esc_html( $article['read_time'] ); ?> min</span>
					</div>
					<a href="<?php echo esc_url( $article['url'] ); ?>" class="button" target="_blank">
						<?php esc_html_e( 'Read Article', 'wpshadow' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render videos tab
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	private static function render_videos_tab() {
		$videos = Training_Video_Registry::get_all();

		?>
		<div class="wpshadow-videos-grid">
			<?php foreach ( $videos as $video ) : ?>
				<div class="video-card">
					<h4><?php echo esc_html( $video['title'] ); ?></h4>
					<div class="video-meta">
						<span class="category"><?php echo esc_html( ucfirst( $video['category'] ) ); ?></span>
						<span class="duration"><?php echo esc_html( Training_Video_Registry::format_duration( $video['duration'] ) ); ?></span>
						<?php if ( ! $video['free'] ) : ?>
							<span class="pro-badge"><?php esc_html_e( 'Pro', 'wpshadow' ); ?></span>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( $video['url'] ); ?>" class="button button-primary" target="_blank">
						<?php esc_html_e( 'Watch Video', 'wpshadow' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Enqueue assets
	 *
	 * @since  1.6030.1920
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only on Academy pages and dashboard.
		if ( 'index.php' !== $hook && strpos( $hook, 'wpshadow-academy' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-academy',
			WPSHADOW_URL . 'assets/css/academy-ui.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-academy',
			WPSHADOW_URL . 'assets/js/academy-ui.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-academy',
			'wpShadowAcademy',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_academy' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Dismiss learning suggestion (AJAX)
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function dismiss_learning_suggestion() {
		self::verify_request( 'wpshadow_academy', 'read' );

		$suggestion_id = self::get_post_param( 'suggestion_id', 'text', '', true );

		// Remove from pending suggestions.
		$suggestions = get_option( 'wpshadow_academy_pending_suggestions', array() );
		unset( $suggestions[ $suggestion_id ] );
		update_option( 'wpshadow_academy_pending_suggestions', $suggestions );

		self::send_success( array(
			'message' => __( 'Suggestion dismissed', 'wpshadow' ),
		) );
	}

	/**
	 * Track article view (AJAX)
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function track_article_view() {
		self::verify_request( 'wpshadow_academy', 'read' );

		$article_id = self::get_post_param( 'article_id', 'text', '', true );

		Academy_Manager::track_article_view( $article_id );

		self::send_success( array(
			'message' => __( 'Article view tracked', 'wpshadow' ),
		) );
	}

	/**
	 * Track video completion (AJAX)
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function track_video_completion() {
		self::verify_request( 'wpshadow_academy', 'read' );

		$video_id = self::get_post_param( 'video_id', 'text', '', true );

		Academy_Manager::track_video_completion( $video_id );

		self::send_success( array(
			'message' => __( 'Video completion tracked', 'wpshadow' ),
		) );
	}

	/**
	 * Get learning path (AJAX)
	 *
	 * @since  1.6030.1920
	 * @return void
	 */
	public static function get_learning_path() {
		self::verify_request( 'wpshadow_academy', 'read' );

		$learning_path = Academy_Manager::get_learning_path();

		self::send_success( array(
			'learning_path' => $learning_path,
		) );
	}
}
