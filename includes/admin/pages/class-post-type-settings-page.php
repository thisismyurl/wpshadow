<?php
/**
 * Post Type Settings Page
 *
 * Settings screen for enabling and configuring WPShadow custom post types.
 *
 * @package    WPShadow
 * @subpackage Admin\Pages
 * @since      1.7036.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Content\Post_Types_Manager;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post_Type_Settings_Page Class
 *
 * @since 1.7036.1200
 */
class Post_Type_Settings_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7036.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_post_wpshadow_save_post_type_settings' => 'save_settings',
		);
	}

	/**
	 * Render the post type settings page.
	 *
	 * @since  1.7036.1200
	 * @return void
	 */
	public static function render_page(): void {
		$can_manage = self::can_manage_settings();

		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$available = Post_Types_Manager::get_available_post_types();

		if ( empty( $post_type ) || ! isset( $available[ $post_type ] ) ) {
			self::render_missing_post_type();
			return;
		}

		$config         = $available[ $post_type ];
		$settings       = Post_Types_Manager::get_post_type_settings( $post_type );
		$active_types   = get_option( 'wpshadow_active_post_types', array() );
		$is_active      = in_array( $post_type, $active_types, true );

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				$settings['enabled'] || $is_active ? sprintf( __( '%s Settings', 'wpshadow' ), $config['plural'] ) : __( 'Post Type Settings', 'wpshadow' ),
				__( 'Enable this post type and adjust how it appears in WordPress.', 'wpshadow' ),
				'dashicons-admin-post'
			);
			?>

			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--success',
						'body'       => '<p>' . esc_html__( 'Post type settings saved successfully.', 'wpshadow' ) . '</p>',
					)
				);
				?>
			<?php endif; ?>

			<?php if ( ! $can_manage ) : ?>
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--warning',
						'body'       => '<p>' . esc_html__( 'You can view these settings, but only administrators can make changes.', 'wpshadow' ) . '</p>',
					)
				);
				?>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wps-settings-form">
				<?php wp_nonce_field( 'wpshadow_post_type_settings', 'wpshadow_post_type_settings_nonce' ); ?>
				<input type="hidden" name="action" value="wpshadow_save_post_type_settings" />
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Status', 'wpshadow' ),
						'description' => __( 'Turn this post type on to add it to your WordPress menu.', 'wpshadow' ),
						'icon'        => 'dashicons-yes',
						'body'        => function() use ( $is_active, $can_manage ) {
							?>
							<div class="wps-form-group wps-toggle-field">
								<label class="wps-toggle" for="wpshadow_post_type_enabled">
									<input
										type="checkbox"
										id="wpshadow_post_type_enabled"
										name="wpshadow_post_type_enabled"
										value="1"
										<?php checked( $is_active ); ?>
										<?php disabled( ! $can_manage ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<span class="wps-toggle-text"><?php esc_html_e( 'Enable this post type', 'wpshadow' ); ?></span>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Enabled post types appear in the WordPress admin menu and can be used immediately.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Display Settings', 'wpshadow' ),
						'description' => __( 'Fine-tune how this post type shows up in menus and URLs.', 'wpshadow' ),
						'icon'        => 'dashicons-admin-generic',
						'body'        => function() use ( $settings, $config, $can_manage ) {
							$default_slug = $config['rewrite']['slug'] ?? '';
							?>
							<div class="wps-form-group">
								<label for="wpshadow_post_type_slug" class="wps-form-label">
									<?php esc_html_e( 'URL Slug', 'wpshadow' ); ?>
								</label>
								<input
									type="text"
									id="wpshadow_post_type_slug"
									name="wpshadow_post_type_slug"
									class="wps-input"
									value="<?php echo esc_attr( $settings['slug'] ); ?>"
									placeholder="<?php echo esc_attr( $default_slug ); ?>"
									<?php disabled( ! $can_manage ); ?>
								/>
								<p class="wps-form-description">
									<?php esc_html_e( 'Leave blank to use the default slug.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_post_type_menu_icon" class="wps-form-label">
									<?php esc_html_e( 'Menu Icon', 'wpshadow' ); ?>
								</label>
								<input
									type="text"
									id="wpshadow_post_type_menu_icon"
									name="wpshadow_post_type_menu_icon"
									class="wps-input"
									value="<?php echo esc_attr( $settings['menu_icon'] ); ?>"
									placeholder="<?php echo esc_attr( $config['icon'] ); ?>"
									<?php disabled( ! $can_manage ); ?>
								/>
								<p class="wps-form-description">
									<?php esc_html_e( 'Use a Dashicons class (example: dashicons-testimonial).', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-toggle-field wps-mt-4">
								<label class="wps-toggle" for="wpshadow_post_type_has_archive">
									<input
										type="checkbox"
										id="wpshadow_post_type_has_archive"
										name="wpshadow_post_type_has_archive"
										value="1"
										<?php checked( $settings['has_archive'] ); ?>
										<?php disabled( ! $can_manage ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<span class="wps-toggle-text"><?php esc_html_e( 'Enable archive page', 'wpshadow' ); ?></span>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Archive pages show a list of all entries for this post type.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-toggle-field wps-mt-4">
								<label class="wps-toggle" for="wpshadow_post_type_show_in_rest">
									<input
										type="checkbox"
										id="wpshadow_post_type_show_in_rest"
										name="wpshadow_post_type_show_in_rest"
										value="1"
										<?php checked( $settings['show_in_rest'] ); ?>
										<?php disabled( ! $can_manage ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<span class="wps-toggle-text"><?php esc_html_e( 'Enable block editor (REST API)', 'wpshadow' ); ?></span>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Keeps the block editor and REST API available for this post type.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() use ( $can_manage ) {
							?>
							<?php
							if ( $can_manage ) {
								submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false );
							} else {
								submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false, array( 'disabled' => true ) );
							}
							?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-post-types' ) ); ?>" class="wps-btn wps-btn--secondary">
								<?php esc_html_e( 'Back to Post Types', 'wpshadow' ); ?>
							</a>
							<?php
						},
					)
				);
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save post type settings.
	 *
	 * @since  1.7036.1200
	 * @return void
	 */
	public static function save_settings(): void {
		if ( ! self::can_manage_settings() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		check_admin_referer( 'wpshadow_post_type_settings', 'wpshadow_post_type_settings_nonce' );

		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		$available = Post_Types_Manager::get_available_post_types();

		if ( empty( $post_type ) || ! isset( $available[ $post_type ] ) ) {
			wp_die( esc_html__( 'That post type could not be found.', 'wpshadow' ) );
		}

		$previous = Post_Types_Manager::get_post_type_settings( $post_type );

		$settings = array(
			'enabled'      => isset( $_POST['wpshadow_post_type_enabled'] ),
			'slug'         => isset( $_POST['wpshadow_post_type_slug'] ) ? sanitize_title( wp_unslash( $_POST['wpshadow_post_type_slug'] ) ) : '',
			'menu_icon'    => isset( $_POST['wpshadow_post_type_menu_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_post_type_menu_icon'] ) ) : '',
			'has_archive'  => isset( $_POST['wpshadow_post_type_has_archive'] ),
			'show_in_rest' => isset( $_POST['wpshadow_post_type_show_in_rest'] ),
		);

		Post_Types_Manager::save_post_type_settings( $post_type, $settings );

		if ( $settings['enabled'] ) {
			Post_Types_Manager::activate_post_type( $post_type );
		} else {
			Post_Types_Manager::deactivate_post_type( $post_type );
		}

		$should_flush = $previous['slug'] !== $settings['slug']
			|| $previous['has_archive'] !== $settings['has_archive']
			|| $previous['show_in_rest'] !== $settings['show_in_rest'];

		if ( $should_flush ) {
			flush_rewrite_rules();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => 'wpshadow-post-type-settings',
					'post_type' => $post_type,
					'updated'   => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render a fallback message when the post type is missing.
	 *
	 * @since  1.7036.1200
	 * @return void
	 */
	private static function render_missing_post_type(): void {
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Post Type Settings', 'wpshadow' ),
				__( 'Choose a post type from the list to configure its settings.', 'wpshadow' ),
				'dashicons-admin-post'
			);
			?>

			<?php
			wpshadow_render_card(
				array(
					'card_class' => 'wps-card--warning',
					'body'       => '<p>' . esc_html__( 'We could not find that post type. Please go back and pick one from the list.', 'wpshadow' ) . '</p>',
				)
			);
			?>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-post-types' ) ); ?>" class="wps-btn wps-btn--secondary">
				<?php esc_html_e( 'Back to Post Types', 'wpshadow' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Check if the current user can manage post type settings.
	 *
	 * @since  1.7036.1200
	 * @return bool True when the user can manage settings.
	 */
	private static function can_manage_settings(): bool {
		if ( is_multisite() && is_network_admin() ) {
			return current_user_can( 'manage_network_options' );
		}

		if ( is_multisite() && is_super_admin() ) {
			return true;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$user = wp_get_current_user();
		if ( $user && in_array( 'administrator', (array) $user->roles, true ) ) {
			return true;
		}

		if ( current_user_can( 'activate_plugins' ) ) {
			return true;
		}

		if ( current_user_can( 'edit_others_posts' ) || current_user_can( 'edit_pages' ) ) {
			return true;
		}

		return false;
	}
}
