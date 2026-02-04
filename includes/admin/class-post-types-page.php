<?php
/**
 * Post Types Admin Page
 *
 * Manages the custom post types configuration interface.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.6033.1530
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Content\Post_Types_Manager;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Types Page Class
 *
 * Provides admin interface for managing custom post types.
 *
 * @since 1.6033.1530
 */
class Post_Types_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_enqueue_scripts' => 'enqueue_assets',
		);
	}

	/**
	 * Initialize the post types page (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Post_Types_Page::subscribe() instead
	 * @since      1.6033.1530
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Enqueue page assets.
	 *
	 * @since  1.6033.1530
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-post-types' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wpshadow-admin' );
		wp_enqueue_style(
			'wpshadow-post-types',
			WPSHADOW_URL . 'assets/css/post-types.css',
			array( 'wpshadow-admin' ),
			WPSHADOW_VERSION
		);

		wp_enqueue_script( 'wpshadow-admin' );
		wp_enqueue_script(
			'wpshadow-post-types',
			WPSHADOW_URL . 'assets/js/post-types.js',
			array( 'jquery', 'wpshadow-admin' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-post-types',
			'wpshadowPostTypes',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_post_types' ),
				'strings' => array(
					'activating'        => __( 'Activating...', 'wpshadow' ),
					'deactivating'      => __( 'Deactivating...', 'wpshadow' ),
					'activated'         => __( 'Post type activated successfully', 'wpshadow' ),
					'deactivated'       => __( 'Post type deactivated successfully', 'wpshadow' ),
					'error'             => __( 'Operation failed. Please try again.', 'wpshadow' ),
					'saving'            => __( 'Saving...', 'wpshadow' ),
					'saved'             => __( 'Settings saved successfully', 'wpshadow' ),
					'confirm_deactivate' => __( 'Deactivating will hide this post type from your admin menu. Existing content will not be deleted. Continue?', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render post types page.
	 *
	 * @since  1.6033.1530
	 * @return void
	 */
	public static function render_page() {
		$post_types = Post_Types_Manager::get_available_post_types();
		$active     = get_option( 'wpshadow_active_post_types', array() );

		?>
		<div class="wrap wpshadow-post-types wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Custom Post Types', 'wpshadow' ),
				__( 'Activate and configure custom post types to enhance your WordPress content structure.', 'wpshadow' ),
				'dashicons-admin-post'
			);
			?>

			<!-- Overview Card -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'What are Custom Post Types?', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<p><?php esc_html_e( 'Custom Post Types extend WordPress beyond posts and pages, allowing you to create specialized content types with their own admin menus, taxonomies, and templates. Perfect for portfolios, testimonials, team members, and more.', 'wpshadow' ); ?></p>
					
					<div class="wpshadow-cpt-stats">
						<div class="stat-item">
							<span class="stat-number"><?php echo esc_html( count( $active ) ); ?></span>
							<span class="stat-label"><?php esc_html_e( 'Active', 'wpshadow' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-number"><?php echo esc_html( count( $post_types ) ); ?></span>
							<span class="stat-label"><?php esc_html_e( 'Available', 'wpshadow' ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Post Types Grid -->
			<div class="wpshadow-cpt-grid">
				<?php foreach ( $post_types as $key => $config ) : ?>
					<?php
					$is_active = in_array( $key, $active, true );
					$settings  = Post_Types_Manager::get_post_type_settings( $key );
					?>
					<div class="wpshadow-cpt-card <?php echo $is_active ? 'active' : ''; ?>" data-post-type="<?php echo esc_attr( $key ); ?>">
						<div class="cpt-card-header">
							<span class="cpt-icon <?php echo esc_attr( $config['icon'] ); ?>"></span>
							<h3><?php echo esc_html( $config['plural'] ); ?></h3>
							<div class="cpt-status">
								<?php if ( $is_active ) : ?>
									<span class="status-badge active"><?php esc_html_e( 'Active', 'wpshadow' ); ?></span>
								<?php else : ?>
									<span class="status-badge inactive"><?php esc_html_e( 'Inactive', 'wpshadow' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="cpt-card-body">
							<p class="cpt-description"><?php echo esc_html( $config['description'] ); ?></p>

							<?php if ( $is_active ) : ?>
								<div class="cpt-info">
									<div class="info-item">
										<span class="dashicons dashicons-admin-links"></span>
										<span><?php esc_html_e( 'Slug:', 'wpshadow' ); ?></span>
										<code><?php echo esc_html( $config['rewrite']['slug'] ); ?></code>
									</div>
									<?php if ( ! empty( $config['taxonomies'] ) ) : ?>
										<div class="info-item">
											<span class="dashicons dashicons-category"></span>
											<span><?php esc_html_e( 'Taxonomies:', 'wpshadow' ); ?></span>
											<span><?php echo esc_html( count( $config['taxonomies'] ) ); ?></span>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $config['taxonomies'] ) ) : ?>
								<div class="cpt-taxonomies">
									<strong><?php esc_html_e( 'Includes:', 'wpshadow' ); ?></strong>
									<ul>
										<?php
										$taxonomies = Post_Types_Manager::get_available_taxonomies();
										foreach ( $config['taxonomies'] as $tax_key ) :
											if ( isset( $taxonomies[ $tax_key ] ) ) :
												?>
												<li><?php echo esc_html( $taxonomies[ $tax_key ]['plural'] ); ?></li>
											<?php endif; ?>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>

						<div class="cpt-card-footer">
							<?php if ( $is_active ) : ?>
								<button type="button" class="button button-secondary wpshadow-deactivate-cpt" data-post-type="<?php echo esc_attr( $key ); ?>">
									<?php esc_html_e( 'Deactivate', 'wpshadow' ); ?>
								</button>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $key ) ); ?>" class="button button-primary">
									<?php esc_html_e( 'Manage', 'wpshadow' ); ?>
								</a>
							<?php else : ?>
								<button type="button" class="button button-primary wpshadow-activate-cpt" data-post-type="<?php echo esc_attr( $key ); ?>">
									<?php esc_html_e( 'Activate', 'wpshadow' ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Help Section -->
			<div class="wps-card wpshadow-help-section">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Need Help?', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<div class="help-grid">
						<div class="help-item">
							<span class="dashicons dashicons-book"></span>
							<h4><?php esc_html_e( 'Documentation', 'wpshadow' ); ?></h4>
							<p><?php esc_html_e( 'Learn how to use custom post types effectively in your WordPress site.', 'wpshadow' ); ?></p>
							<a href="https://wpshadow.com/kb/custom-post-types" target="_blank" class="button button-secondary">
								<?php esc_html_e( 'Read Guide', 'wpshadow' ); ?>
							</a>
						</div>
						<div class="help-item">
							<span class="dashicons dashicons-video-alt3"></span>
							<h4><?php esc_html_e( 'Video Tutorials', 'wpshadow' ); ?></h4>
							<p><?php esc_html_e( 'Watch step-by-step tutorials on creating and managing custom content.', 'wpshadow' ); ?></p>
							<a href="https://wpshadow.com/academy/custom-post-types" target="_blank" class="button button-secondary">
								<?php esc_html_e( 'Watch Now', 'wpshadow' ); ?>
							</a>
						</div>
						<div class="help-item">
							<span class="dashicons dashicons-admin-generic"></span>
							<h4><?php esc_html_e( 'Best Practices', 'wpshadow' ); ?></h4>
							<p><?php esc_html_e( 'Discover recommended approaches for organizing your content structure.', 'wpshadow' ); ?></p>
							<a href="https://wpshadow.com/kb/cpt-best-practices" target="_blank" class="button button-secondary">
								<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
