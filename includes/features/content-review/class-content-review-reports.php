<?php
/**
 * Content Review Reports Integration
 *
 * Integrates content review reports into the main Reports menu for formal
 * post/page content quality analysis with full issue details.
 *
 * @package    WPShadow
 * @subpackage Features/ContentReview
 * @since      1.6034.0000
 */

declare(strict_types=1);

namespace WPShadow\Features\ContentReview;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Review Reports Class
 *
 * Adds content review functionality to the Reports menu for formal analysis.
 *
 * @since 1.6034.0000
 */
class Content_Review_Reports extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_reports_submenu_items'            => 'register_report_menu',
			'wp_ajax_wpshadow_get_post_review_data'     => 'handle_get_review_data',
		);
	}

	/**
	 * Initialize hooks (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Content_Review_Reports::subscribe() instead
	 * @since      1.6034.0000
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Register report menu item
	 *
	 * @since  1.6034.0000
	 * @param  array $menu_items Existing menu items.
	 * @return array Modified menu items.
	 */
	public static function register_report_menu( $menu_items ) {
		$menu_items[] = array(
			'id'         => 'content-review',
			'title'      => __( 'Content Quality Report', 'wpshadow' ),
			'description' => __( 'Analyze and review all posts and pages for content quality, SEO, and accessibility issues.', 'wpshadow' ),
			'icon'       => 'dashicons-excerpt-view',
			'callback'   => array( __CLASS__, 'render_content_review_report' ),
			'capability' => 'edit_posts',
		);

		return $menu_items;
	}

	/**
	 * Render content review report page
	 *
	 * @since 1.6034.0000
	 * @return void
	 */
	public static function render_content_review_report() {
		?>
		<div class="wpshadow-content-review-report">
			<div class="wpshadow-report-header">
				<h2><?php esc_html_e( 'Content Quality Report', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Review and analyze the content quality of all your posts and pages.', 'wpshadow' ); ?></p>
			</div>

			<div class="wpshadow-report-filters">
				<div class="wpshadow-filter-group">
					<label for="post-type-filter"><?php esc_html_e( 'Post Type:', 'wpshadow' ); ?></label>
					<select id="post-type-filter">
						<option value=""><?php esc_html_e( 'All Types', 'wpshadow' ); ?></option>
						<?php self::render_post_type_options(); ?>
					</select>
				</div>

				<div class="wpshadow-filter-group">
					<label for="severity-filter"><?php esc_html_e( 'Show Issues:', 'wpshadow' ); ?></label>
					<select id="severity-filter">
						<option value=""><?php esc_html_e( 'All Severities', 'wpshadow' ); ?></option>
						<option value="critical"><?php esc_html_e( 'Critical Only', 'wpshadow' ); ?></option>
						<option value="high"><?php esc_html_e( 'Critical & High', 'wpshadow' ); ?></option>
					</select>
				</div>

				<div class="wpshadow-filter-group">
					<label for="content-search"><?php esc_html_e( 'Search:', 'wpshadow' ); ?></label>
					<input type="text" id="content-search" placeholder="<?php esc_attr_e( 'Search posts...', 'wpshadow' ); ?>">
				</div>

				<button class="button button-primary" id="generate-report-btn">
					<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
				</button>
			</div>

			<div class="wpshadow-report-content">
				<div class="wpshadow-posts-list" id="posts-list">
					<p class="wpshadow-placeholder">
						<?php esc_html_e( 'Select filters and click Generate Report to analyze your content.', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="wpshadow-post-detail" id="post-detail" style="display: none;">
					<div class="wpshadow-detail-header">
						<button class="button" id="back-to-list"><?php esc_html_e( '← Back to List', 'wpshadow' ); ?></button>
						<h3 id="post-title"></h3>
					</div>
					<div class="wpshadow-detail-content" id="detail-content"></div>
				</div>
			</div>
		</div>

		<?php
		self::enqueue_report_assets();
	}

	/**
	 * Render post type filter options
	 *
	 * @since 1.6034.0000
	 * @return void
	 */
	private static function render_post_type_options() {
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		// Add built-in post types.
		$post_types['post'] = (object) array( 'label' => 'Posts', 'name' => 'post' );
		$post_types['page'] = (object) array( 'label' => 'Pages', 'name' => 'page' );

		foreach ( $post_types as $post_type ) {
			printf(
				'<option value="%s">%s</option>',
				esc_attr( $post_type->name ),
				esc_html( $post_type->label )
			);
		}
	}

	/**
	 * Handle AJAX request to get review data
	 *
	 * @since 1.6034.0000
	 * @return void
	 */
	public static function handle_get_review_data() {
		check_ajax_referer( 'wpshadow_content_review' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$severity  = isset( $_POST['severity'] ) ? sanitize_text_field( wp_unslash( $_POST['severity'] ) ) : '';
		$search    = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		// Get posts to analyze.
		$args = array(
			'posts_per_page' => 50,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);

		if ( $post_type ) {
			$args['post_type'] = $post_type;
		} else {
			$args['post_type'] = array( 'post', 'page' );
		}

		if ( $search ) {
			$args['s'] = $search;
		}

		$posts = get_posts( $args );

		// Analyze each post.
		$results = array();
		foreach ( $posts as $post ) {
			$diagnostics = Content_Review_Manager::get_content_diagnostics( $post->ID );

			// Count and summarize.
			$summary = array(
				'critical' => 0,
				'high'     => 0,
				'medium'   => 0,
				'low'      => 0,
			);

			foreach ( $diagnostics as $family_diagnostics ) {
				foreach ( $family_diagnostics as $diagnostic ) {
					$severity_level = $diagnostic['severity'] ?? 'medium';
					if ( isset( $summary[ $severity_level ] ) ) {
						$summary[ $severity_level ]++;
					}
				}
			}

			// Filter by severity if specified.
			if ( $severity ) {
				$has_match = false;
				switch ( $severity ) {
					case 'critical':
						$has_match = $summary['critical'] > 0;
						break;
					case 'high':
						$has_match = $summary['critical'] > 0 || $summary['high'] > 0;
						break;
				}
				if ( ! $has_match ) {
					continue;
				}
			}

			$results[] = array(
				'id'       => $post->ID,
				'title'    => $post->post_title,
				'url'      => get_edit_post_link( $post->ID, 'url' ),
				'type'     => $post->post_type,
				'status'   => $post->post_status,
				'modified' => $post->post_modified,
				'summary'  => $summary,
			);
		}

		wp_send_json_success(
			array(
				'posts'  => $results,
				'count'  => count( $results ),
			)
		);
	}

	/**
	 * Enqueue report page assets
	 *
	 * @since 1.6034.0000
	 * @return void
	 */
	private static function enqueue_report_assets() {
		wp_enqueue_script(
			'wpshadow-content-review-report',
			WPSHADOW_URL . 'assets/js/content-review-report.js',
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_enqueue_style(
			'wpshadow-content-review-report',
			WPSHADOW_URL . 'assets/css/content-review-report.css',
			array(),
			WPSHADOW_VERSION
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-content-review-report',
			'wpShadowContentReview',
			'wpshadow_content_review',
			array(),
			'nonce',
			'ajax_url'
		);
	}
}

// Initialize on plugins_loaded.
add_action(
	'plugins_loaded',
	function () {
		Content_Review_Reports::init();
	}
);
