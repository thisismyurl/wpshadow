<?php
/**
 * Post Types Admin Page
 *
 * Manages the custom post types configuration interface.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
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
 * @since 1.6093.1200
 */
class Post_Types_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
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
	 * @deprecated1.0 Use Post_Types_Page::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Enqueue page assets.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! is_string( $hook ) || false === strpos( $hook, 'wpshadow-post-types' ) ) {
			return;
		}

		wp_enqueue_style( 'wpshadow-admin-pages' );
		wp_enqueue_style(
			'wpshadow-post-types',
			WPSHADOW_URL . 'assets/css/post-types.css',
			array( 'wpshadow-admin-pages' ),
			WPSHADOW_VERSION
		);

		wp_enqueue_script( 'wpshadow-admin-pages' );
		wp_enqueue_script(
			'wpshadow-post-types',
			WPSHADOW_URL . 'assets/js/post-types.js',
			array( 'jquery', 'wpshadow-admin-pages' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-post-types',
			'wpshadowPostTypes',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_post_types' ),
				'strings' => array(
					'active'            => __( 'Active', 'wpshadow' ),
					'inactive'          => __( 'Inactive', 'wpshadow' ),
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
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_page() {
		self::enqueue_assets( 'wpshadow_page_wpshadow-post-types' );
		wp_print_styles( array( 'wpshadow-admin-pages', 'wpshadow-post-types' ) );
		wp_print_scripts( array( 'wpshadow-admin-pages', 'wpshadow-post-types' ) );

		if ( ! wp_style_is( 'wpshadow-post-types', 'done' ) ) {
			printf(
				'<link rel="stylesheet" id="wpshadow-post-types-css" href="%sassets/css/post-types.css?ver=%s" media="all" />',
				esc_url( WPSHADOW_URL ),
				esc_attr( WPSHADOW_VERSION )
			);
		}

		if ( ! wp_script_is( 'wpshadow-post-types', 'done' ) ) {
			$localized = array(
				'nonce'   => wp_create_nonce( 'wpshadow_post_types' ),
				'strings' => array(
					'active'             => __( 'Active', 'wpshadow' ),
					'inactive'           => __( 'Inactive', 'wpshadow' ),
					'activating'         => __( 'Activating...', 'wpshadow' ),
					'deactivating'       => __( 'Deactivating...', 'wpshadow' ),
					'activated'          => __( 'Post type activated successfully', 'wpshadow' ),
					'deactivated'        => __( 'Post type deactivated successfully', 'wpshadow' ),
					'error'              => __( 'Operation failed. Please try again.', 'wpshadow' ),
					'saving'             => __( 'Saving...', 'wpshadow' ),
					'saved'              => __( 'Settings saved successfully', 'wpshadow' ),
					'confirm_deactivate' => __( 'Deactivating will hide this post type from your admin menu. Existing content will not be deleted. Continue?', 'wpshadow' ),
				),
			);
			printf(
				'<script>window.wpshadowPostTypes=%s;</script>',
				wp_json_encode( $localized )
			);
			printf(
				'<script id="wpshadow-post-types-js" src="%sassets/js/post-types.js?ver=%s"></script>',
				esc_url( WPSHADOW_URL ),
				esc_attr( WPSHADOW_VERSION )
			);
		}

		$all_post_types = Post_Types_Manager::get_available_post_types();
		$active         = get_option( 'wpshadow_active_post_types', array() );

		// Filter post types by feature status (hide far-future features)
		$post_types = wpshadow_filter_features_by_status( $all_post_types );

		?>
		<div class="wrap wpshadow-post-types wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Custom Post Types', 'wpshadow' ),
				__( 'Activate and configure custom post types to enhance your WordPress content structure.', 'wpshadow' ),
				'dashicons-admin-post'
			);
			?>

			<!-- Post Types Grid -->
			<div class="wps-grid wps-grid-auto-320">
				<?php foreach ( $post_types as $key => $config ) : ?>
					<?php
					$is_active = in_array( $key, $active, true );
					$settings  = Post_Types_Manager::get_post_type_settings( $key );
					
					// Get feature status for badges
					$feature_status = wpshadow_get_feature_status( $config['since'] );
					$is_coming_soon = ( 'coming_soon' === $feature_status['status'] );
					
					// Build badge data
					$badge = array();
					if ( $is_coming_soon ) {
						$badge = array(
							'label' => sprintf( __( 'Coming %s', 'wpshadow' ), $feature_status['launch_date'] ),
							'class' => 'wps-badge--info',
						);
					} elseif ( $is_active ) {
						$badge = array(
							'label' => __( 'Active', 'wpshadow' ),
							'class' => 'wps-badge--success',
						);
					}
					
					// Build card args
					$card_args = array(
						'title'       => $config['plural'],
						'description' => $config['description'],
						'icon'        => str_replace( 'dashicons-', '', $config['icon'] ),
						'icon_color'  => '#0084E4',
						'badge'       => $badge,
						'card_class'  => $is_active ? 'wps-card--active' : ( $is_coming_soon ? 'wps-card--coming-soon' : '' ),
						'attrs'       => array(
							'data-post-type' => $key,
						),
					);
					
					// Coming soon card Footer
					if ( $is_coming_soon ) {
						$card_args['footer'] = function() use ( $feature_status ) {
							?>
							<div class="wps-flex wps-items-center wps-gap-2">
								<span class="dashicons dashicons-calendar-alt wps-text-info"></span>
								<span class="wps-text-muted wps-text-sm"><?php echo esc_html( sprintf( __( 'Launches %s', 'wpshadow' ), $feature_status['launch_date'] ) ); ?></span>
							</div>
							<?php
						};
					} else {
						// Active/inactive card Body
						$card_args['body'] = function() use ( $is_active, $config, $key ) {
							if ( $is_active ) {
								?>
								<div class="wpshadow-cpt-info wps-mb-4">
									<div class="wpshadow-cpt-info-item">
										<span class="dashicons dashicons-admin-links wps-text-muted"></span>
										<span class="wpshadow-cpt-label"><?php esc_html_e( 'Slug:', 'wpshadow' ); ?></span>
										<code><?php echo esc_html( $config['rewrite']['slug'] ); ?></code>
									</div>
									<?php if ( ! empty( $config['taxonomies'] ) ) : ?>
										<div class="wpshadow-cpt-info-item">
											<span class="dashicons dashicons-category wps-text-muted"></span>
											<span class="wpshadow-cpt-label"><?php esc_html_e( 'Taxonomies:', 'wpshadow' ); ?></span>
											<span><?php echo esc_html( count( $config['taxonomies'] ) ); ?></span>
										</div>
									<?php endif; ?>
								</div>
								<?php
							}
						};
						
						// Active/inactive card Footer
						$card_args['footer'] = function() use ( $is_active, $config, $key ) {
							?>
							<div class="wps-flex wps-justify-between wps-items-center">
								<div class="wps-flex wps-items-center wps-gap-2">
									<label class="wps-toggle wpshadow-toggle-switch" aria-label="<?php echo esc_attr( sprintf( __( 'Toggle %s post type', 'wpshadow' ), $config['singular'] ) ); ?>">
										<input type="checkbox" 
											class="wpshadow-cpt-toggle" 
											data-post-type="<?php echo esc_attr( $key ); ?>"
											<?php checked( $is_active ); ?>
										>
											<span class="wps-toggle-slider wpshadow-toggle-slider"></span>
										</label>
									</div>
								<?php if ( $is_active ) : ?>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $key ) ); ?>" class="wps-btn wps-btn--primary wps-btn--sm">
										<?php esc_html_e( 'Manage', 'wpshadow' ); ?>
									</a>
								<?php endif; ?>
							</div>
							<?php
						};
					}
					
					wpshadow_render_card( $card_args );
					?>
				<?php endforeach; ?>
			</div>

			<!-- Recent Activity -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'settings', 10 );
			}
			?>
		</div>
		<?php
	}
}
