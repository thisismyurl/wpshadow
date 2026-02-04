<?php
/**
 * Modal Custom Post Type
 *
 * Registers the wpshadow_modal CPT for creating reusable modals
 * with display rules and conditions.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6034.1530
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modal_Post_Type Class
 *
 * Handles registration and management of the modal custom post type.
 *
 * @since 1.6034.1530
 */
class Modal_Post_Type {

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'wpshadow_modal';

	/**
	 * Initialize the modal post type.
	 *
	 * @since 1.6034.1530
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'wp_footer', array( __CLASS__, 'render_active_modals' ) );
	}

	/**
	 * Register the modal custom post type.
	 *
	 * @since  1.6034.1530
	 * @return void
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => __( 'Modals', 'wpshadow' ),
			'singular_name'         => __( 'Modal', 'wpshadow' ),
			'menu_name'             => __( 'Modals', 'wpshadow' ),
			'name_admin_bar'        => __( 'Modal', 'wpshadow' ),
			'add_new'               => __( 'Add New', 'wpshadow' ),
			'add_new_item'          => __( 'Add New Modal', 'wpshadow' ),
			'new_item'              => __( 'New Modal', 'wpshadow' ),
			'edit_item'             => __( 'Edit Modal', 'wpshadow' ),
			'view_item'             => __( 'View Modal', 'wpshadow' ),
			'all_items'             => __( 'All Modals', 'wpshadow' ),
			'search_items'          => __( 'Search Modals', 'wpshadow' ),
			'parent_item_colon'     => __( 'Parent Modals:', 'wpshadow' ),
			'not_found'             => __( 'No modals found.', 'wpshadow' ),
			'not_found_in_trash'    => __( 'No modals found in Trash.', 'wpshadow' ),
			'featured_image'        => __( 'Modal Image', 'wpshadow' ),
			'set_featured_image'    => __( 'Set modal image', 'wpshadow' ),
			'remove_featured_image' => __( 'Remove modal image', 'wpshadow' ),
			'use_featured_image'    => __( 'Use as modal image', 'wpshadow' ),
			'archives'              => __( 'Modal archives', 'wpshadow' ),
			'insert_into_item'      => __( 'Insert into modal', 'wpshadow' ),
			'uploaded_to_this_item' => __( 'Uploaded to this modal', 'wpshadow' ),
			'filter_items_list'     => __( 'Filter modals list', 'wpshadow' ),
			'items_list_navigation' => __( 'Modals list navigation', 'wpshadow' ),
			'items_list'            => __( 'Modals list', 'wpshadow' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-welcome-view-site',
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Add meta boxes for modal configuration.
	 *
	 * @since  1.6034.1530
	 * @return void
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'wpshadow_modal_display_rules',
			__( 'Display Rules', 'wpshadow' ),
			array( __CLASS__, 'render_display_rules_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wpshadow_modal_settings',
			__( 'Modal Settings', 'wpshadow' ),
			array( __CLASS__, 'render_settings_meta_box' ),
			self::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Render display rules meta box.
	 *
	 * @since  1.6034.1530
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_display_rules_meta_box( $post ) {
		wp_nonce_field( 'wpshadow_modal_meta', 'wpshadow_modal_meta_nonce' );

		$trigger_type     = get_post_meta( $post->ID, '_wpshadow_modal_trigger', true ) ?: 'time';
		$trigger_value    = get_post_meta( $post->ID, '_wpshadow_modal_trigger_value', true ) ?: '3';
		$display_location = get_post_meta( $post->ID, '_wpshadow_modal_location', true ) ?: 'all';
		$specific_pages   = get_post_meta( $post->ID, '_wpshadow_modal_pages', true ) ?: '';
		$user_roles       = get_post_meta( $post->ID, '_wpshadow_modal_roles', true ) ?: array();
		$device_type      = get_post_meta( $post->ID, '_wpshadow_modal_device', true ) ?: 'all';
		$frequency        = get_post_meta( $post->ID, '_wpshadow_modal_frequency', true ) ?: 'always';
		?>
		<div class="wpshadow-modal-rules">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="wpshadow_modal_trigger"><?php esc_html_e( 'Trigger Type', 'wpshadow' ); ?></label>
					</th>
					<td>
						<select name="wpshadow_modal_trigger" id="wpshadow_modal_trigger" class="regular-text">
							<option value="time" <?php selected( $trigger_type, 'time' ); ?>><?php esc_html_e( 'Time Delay', 'wpshadow' ); ?></option>
							<option value="scroll" <?php selected( $trigger_type, 'scroll' ); ?>><?php esc_html_e( 'Scroll Percentage', 'wpshadow' ); ?></option>
							<option value="exit" <?php selected( $trigger_type, 'exit' ); ?>><?php esc_html_e( 'Exit Intent', 'wpshadow' ); ?></option>
							<option value="immediate" <?php selected( $trigger_type, 'immediate' ); ?>><?php esc_html_e( 'Immediate (Page Load)', 'wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'When should this modal appear?', 'wpshadow' ); ?></p>
					</td>
				</tr>
				<tr class="trigger-value-row">
					<th scope="row">
						<label for="wpshadow_modal_trigger_value"><?php esc_html_e( 'Trigger Value', 'wpshadow' ); ?></label>
					</th>
					<td>
						<input type="number" name="wpshadow_modal_trigger_value" id="wpshadow_modal_trigger_value" value="<?php echo esc_attr( $trigger_value ); ?>" class="small-text" min="0" max="100" step="1" />
						<span class="trigger-unit"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
						<p class="description trigger-description"><?php esc_html_e( 'Number of seconds to wait before showing modal', 'wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_modal_location"><?php esc_html_e( 'Display Location', 'wpshadow' ); ?></label>
					</th>
					<td>
						<select name="wpshadow_modal_location" id="wpshadow_modal_location" class="regular-text">
							<option value="all" <?php selected( $display_location, 'all' ); ?>><?php esc_html_e( 'All Pages', 'wpshadow' ); ?></option>
							<option value="home" <?php selected( $display_location, 'home' ); ?>><?php esc_html_e( 'Homepage Only', 'wpshadow' ); ?></option>
							<option value="posts" <?php selected( $display_location, 'posts' ); ?>><?php esc_html_e( 'All Posts', 'wpshadow' ); ?></option>
							<option value="pages" <?php selected( $display_location, 'pages' ); ?>><?php esc_html_e( 'All Pages', 'wpshadow' ); ?></option>
							<option value="specific" <?php selected( $display_location, 'specific' ); ?>><?php esc_html_e( 'Specific Pages/Posts', 'wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Where should this modal appear?', 'wpshadow' ); ?></p>
					</td>
				</tr>
				<tr class="specific-pages-row" style="<?php echo 'specific' !== $display_location ? 'display:none;' : ''; ?>">
					<th scope="row">
						<label for="wpshadow_modal_pages"><?php esc_html_e( 'Specific Pages/Posts', 'wpshadow' ); ?></label>
					</th>
					<td>
						<textarea name="wpshadow_modal_pages" id="wpshadow_modal_pages" class="large-text" rows="3"><?php echo esc_textarea( $specific_pages ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Comma-separated post/page IDs (e.g., 1, 5, 42)', 'wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'User Roles', 'wpshadow' ); ?></label>
					</th>
					<td>
						<?php
						$roles = wp_roles()->get_names();
						foreach ( $roles as $role_key => $role_name ) :
							$checked = in_array( $role_key, (array) $user_roles, true );
							?>
							<label style="display: block; margin-bottom: 5px;">
								<input type="checkbox" name="wpshadow_modal_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( $checked ); ?> />
								<?php echo esc_html( $role_name ); ?>
							</label>
						<?php endforeach; ?>
						<label style="display: block; margin-top: 5px;">
							<input type="checkbox" name="wpshadow_modal_roles[]" value="guest" <?php checked( in_array( 'guest', (array) $user_roles, true ) ); ?> />
							<?php esc_html_e( 'Guest (Not Logged In)', 'wpshadow' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Leave all unchecked to show to everyone', 'wpshadow' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_modal_device"><?php esc_html_e( 'Device Type', 'wpshadow' ); ?></label>
					</th>
					<td>
						<select name="wpshadow_modal_device" id="wpshadow_modal_device" class="regular-text">
							<option value="all" <?php selected( $device_type, 'all' ); ?>><?php esc_html_e( 'All Devices', 'wpshadow' ); ?></option>
							<option value="desktop" <?php selected( $device_type, 'desktop' ); ?>><?php esc_html_e( 'Desktop Only', 'wpshadow' ); ?></option>
							<option value="mobile" <?php selected( $device_type, 'mobile' ); ?>><?php esc_html_e( 'Mobile Only', 'wpshadow' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpshadow_modal_frequency"><?php esc_html_e( 'Display Frequency', 'wpshadow' ); ?></label>
					</th>
					<td>
						<select name="wpshadow_modal_frequency" id="wpshadow_modal_frequency" class="regular-text">
							<option value="always" <?php selected( $frequency, 'always' ); ?>><?php esc_html_e( 'Every Visit', 'wpshadow' ); ?></option>
							<option value="once" <?php selected( $frequency, 'once' ); ?>><?php esc_html_e( 'Once Per Session', 'wpshadow' ); ?></option>
							<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Once Per Day', 'wpshadow' ); ?></option>
							<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Once Per Week', 'wpshadow' ); ?></option>
							<option value="permanent" <?php selected( $frequency, 'permanent' ); ?>><?php esc_html_e( 'Until Closed (Permanent)', 'wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How often should users see this modal?', 'wpshadow' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<script>
		jQuery(document).ready(function($) {
			var $triggerSelect = $('#wpshadow_modal_trigger');
			var $triggerValueRow = $('.trigger-value-row');
			var $triggerUnit = $('.trigger-unit');
			var $triggerDesc = $('.trigger-description');
			
			var $locationSelect = $('#wpshadow_modal_location');
			var $specificPagesRow = $('.specific-pages-row');

			function updateTriggerUI() {
				var trigger = $triggerSelect.val();
				
				if (trigger === 'exit' || trigger === 'immediate') {
					$triggerValueRow.hide();
				} else {
					$triggerValueRow.show();
					
					if (trigger === 'time') {
						$triggerUnit.text('<?php esc_html_e( 'seconds', 'wpshadow' ); ?>');
						$triggerDesc.text('<?php esc_html_e( 'Number of seconds to wait before showing modal', 'wpshadow' ); ?>');
						$('#wpshadow_modal_trigger_value').attr('max', '999');
					} else if (trigger === 'scroll') {
						$triggerUnit.text('<?php esc_html_e( '%', 'wpshadow' ); ?>');
						$triggerDesc.text('<?php esc_html_e( 'Show modal after user scrolls this percentage of the page', 'wpshadow' ); ?>');
						$('#wpshadow_modal_trigger_value').attr('max', '100');
					}
				}
			}

			function updateLocationUI() {
				if ($locationSelect.val() === 'specific') {
					$specificPagesRow.show();
				} else {
					$specificPagesRow.hide();
				}
			}

			$triggerSelect.on('change', updateTriggerUI);
			$locationSelect.on('change', updateLocationUI);
			
			updateTriggerUI();
			updateLocationUI();
		});
		</script>

		<style>
		.wpshadow-modal-rules .form-table th {
			width: 200px;
		}
		</style>
		<?php
	}

	/**
	 * Render settings meta box.
	 *
	 * @since  1.6034.1530
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_settings_meta_box( $post ) {
		$width            = get_post_meta( $post->ID, '_wpshadow_modal_width', true ) ?: '600';
		$animation        = get_post_meta( $post->ID, '_wpshadow_modal_animation', true ) ?: 'fade';
		$overlay_close    = get_post_meta( $post->ID, '_wpshadow_modal_overlay_close', true ) !== 'no';
		$show_close_btn   = get_post_meta( $post->ID, '_wpshadow_modal_show_close', true ) !== 'no';
		$close_on_esc     = get_post_meta( $post->ID, '_wpshadow_modal_esc_close', true ) !== 'no';
		?>
		<p>
			<label for="wpshadow_modal_width"><?php esc_html_e( 'Width (px)', 'wpshadow' ); ?></label><br>
			<input type="number" name="wpshadow_modal_width" id="wpshadow_modal_width" value="<?php echo esc_attr( $width ); ?>" class="widefat" min="300" max="1200" step="50" />
		</p>

		<p>
			<label for="wpshadow_modal_animation"><?php esc_html_e( 'Animation', 'wpshadow' ); ?></label><br>
			<select name="wpshadow_modal_animation" id="wpshadow_modal_animation" class="widefat">
				<option value="fade" <?php selected( $animation, 'fade' ); ?>><?php esc_html_e( 'Fade', 'wpshadow' ); ?></option>
				<option value="slide-up" <?php selected( $animation, 'slide-up' ); ?>><?php esc_html_e( 'Slide Up', 'wpshadow' ); ?></option>
				<option value="slide-down" <?php selected( $animation, 'slide-down' ); ?>><?php esc_html_e( 'Slide Down', 'wpshadow' ); ?></option>
				<option value="zoom" <?php selected( $animation, 'zoom' ); ?>><?php esc_html_e( 'Zoom', 'wpshadow' ); ?></option>
			</select>
		</p>

		<p>
			<label>
				<input type="checkbox" name="wpshadow_modal_overlay_close" value="yes" <?php checked( $overlay_close ); ?> />
				<?php esc_html_e( 'Close on overlay click', 'wpshadow' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input type="checkbox" name="wpshadow_modal_show_close" value="yes" <?php checked( $show_close_btn ); ?> />
				<?php esc_html_e( 'Show close button', 'wpshadow' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input type="checkbox" name="wpshadow_modal_esc_close" value="yes" <?php checked( $close_on_esc ); ?> />
				<?php esc_html_e( 'Close on ESC key', 'wpshadow' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save modal meta data.
	 *
	 * @since  1.6034.1530
	 * @param  int      $post_id Post ID.
	 * @param  \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_meta( $post_id, $post ) {
		// Verify nonce.
		if ( ! isset( $_POST['wpshadow_modal_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_modal_meta_nonce'], 'wpshadow_modal_meta' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save display rules.
		$trigger = isset( $_POST['wpshadow_modal_trigger'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_modal_trigger'] ) ) : 'time';
		update_post_meta( $post_id, '_wpshadow_modal_trigger', $trigger );

		$trigger_value = isset( $_POST['wpshadow_modal_trigger_value'] ) ? absint( $_POST['wpshadow_modal_trigger_value'] ) : 3;
		update_post_meta( $post_id, '_wpshadow_modal_trigger_value', $trigger_value );

		$location = isset( $_POST['wpshadow_modal_location'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_modal_location'] ) ) : 'all';
		update_post_meta( $post_id, '_wpshadow_modal_location', $location );

		$pages = isset( $_POST['wpshadow_modal_pages'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpshadow_modal_pages'] ) ) : '';
		update_post_meta( $post_id, '_wpshadow_modal_pages', $pages );

		$roles = isset( $_POST['wpshadow_modal_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wpshadow_modal_roles'] ) ) : array();
		update_post_meta( $post_id, '_wpshadow_modal_roles', $roles );

		$device = isset( $_POST['wpshadow_modal_device'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_modal_device'] ) ) : 'all';
		update_post_meta( $post_id, '_wpshadow_modal_device', $device );

		$frequency = isset( $_POST['wpshadow_modal_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_modal_frequency'] ) ) : 'always';
		update_post_meta( $post_id, '_wpshadow_modal_frequency', $frequency );

		// Save settings.
		$width = isset( $_POST['wpshadow_modal_width'] ) ? absint( $_POST['wpshadow_modal_width'] ) : 600;
		update_post_meta( $post_id, '_wpshadow_modal_width', $width );

		$animation = isset( $_POST['wpshadow_modal_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['wpshadow_modal_animation'] ) ) : 'fade';
		update_post_meta( $post_id, '_wpshadow_modal_animation', $animation );

		$overlay_close = isset( $_POST['wpshadow_modal_overlay_close'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_wpshadow_modal_overlay_close', $overlay_close );

		$show_close = isset( $_POST['wpshadow_modal_show_close'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_wpshadow_modal_show_close', $show_close );

		$esc_close = isset( $_POST['wpshadow_modal_esc_close'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_wpshadow_modal_esc_close', $esc_close );
	}

	/**
	 * Render active modals in footer.
	 *
	 * @since  1.6034.1530
	 * @return void
	 */
	public static function render_active_modals() {
		$modals = self::get_active_modals();

		if ( empty( $modals ) ) {
			return;
		}

		foreach ( $modals as $modal ) {
			self::render_modal( $modal );
		}
	}

	/**
	 * Get active modals for current page.
	 *
	 * @since  1.6034.1530
	 * @return array Array of modal post objects.
	 */
	public static function get_active_modals() {
		$modals = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		if ( empty( $modals ) ) {
			return array();
		}

		$active_modals = array();

		foreach ( $modals as $modal ) {
			if ( self::should_display_modal( $modal->ID ) ) {
				$active_modals[] = $modal;
			}
		}

		return $active_modals;
	}

	/**
	 * Check if modal should be displayed on current page.
	 *
	 * @since  1.6034.1530
	 * @param  int $modal_id Modal post ID.
	 * @return bool True if should display.
	 */
	public static function should_display_modal( $modal_id ) {
		// Check location.
		$location = get_post_meta( $modal_id, '_wpshadow_modal_location', true ) ?: 'all';

		if ( 'home' === $location && ! is_front_page() ) {
			return false;
		}

		if ( 'posts' === $location && ! is_single() ) {
			return false;
		}

		if ( 'pages' === $location && ! is_page() ) {
			return false;
		}

		if ( 'specific' === $location ) {
			$pages      = get_post_meta( $modal_id, '_wpshadow_modal_pages', true ) ?: '';
			$page_ids   = array_map( 'trim', explode( ',', $pages ) );
			$current_id = get_the_ID();

			if ( ! in_array( (string) $current_id, $page_ids, true ) ) {
				return false;
			}
		}

		// Check user roles.
		$roles = get_post_meta( $modal_id, '_wpshadow_modal_roles', true );

		if ( ! empty( $roles ) ) {
			$user = wp_get_current_user();

			if ( ! is_user_logged_in() ) {
				if ( ! in_array( 'guest', $roles, true ) ) {
					return false;
				}
			} else {
				$user_roles      = (array) $user->roles;
				$has_valid_role = false;

				foreach ( $user_roles as $user_role ) {
					if ( in_array( $user_role, $roles, true ) ) {
						$has_valid_role = true;
						break;
					}
				}

				if ( ! $has_valid_role ) {
					return false;
				}
			}
		}

		// Check device type.
		$device = get_post_meta( $modal_id, '_wpshadow_modal_device', true ) ?: 'all';

		if ( 'desktop' === $device && wp_is_mobile() ) {
			return false;
		}

		if ( 'mobile' === $device && ! wp_is_mobile() ) {
			return false;
		}

		return true;
	}

	/**
	 * Render a single modal.
	 *
	 * @since  1.6034.1530
	 * @param  \WP_Post $modal Modal post object.
	 * @return void
	 */
	public static function render_modal( $modal ) {
		$trigger        = get_post_meta( $modal->ID, '_wpshadow_modal_trigger', true ) ?: 'time';
		$trigger_value  = get_post_meta( $modal->ID, '_wpshadow_modal_trigger_value', true ) ?: '3';
		$width          = get_post_meta( $modal->ID, '_wpshadow_modal_width', true ) ?: '600';
		$animation      = get_post_meta( $modal->ID, '_wpshadow_modal_animation', true ) ?: 'fade';
		$overlay_close  = get_post_meta( $modal->ID, '_wpshadow_modal_overlay_close', true ) !== 'no';
		$show_close_btn = get_post_meta( $modal->ID, '_wpshadow_modal_show_close', true ) !== 'no';
		$close_on_esc   = get_post_meta( $modal->ID, '_wpshadow_modal_esc_close', true ) !== 'no';
		$frequency      = get_post_meta( $modal->ID, '_wpshadow_modal_frequency', true ) ?: 'always';

		$modal_classes = array(
			'wpshadow-modal',
			'wpshadow-modal-animation-' . esc_attr( $animation ),
		);

		$modal_data = array(
			'modal-id'        => $modal->ID,
			'trigger'         => $trigger,
			'trigger-value'   => $trigger_value,
			'overlay-close'   => $overlay_close ? 'true' : 'false',
			'esc-close'       => $close_on_esc ? 'true' : 'false',
			'frequency'       => $frequency,
		);
		?>
		<div 
			class="<?php echo esc_attr( implode( ' ', $modal_classes ) ); ?>" 
			<?php
			foreach ( $modal_data as $key => $value ) {
				echo 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
			}
			?>
			style="display: none;"
			role="dialog"
			aria-modal="true"
			aria-labelledby="wpshadow-modal-title-<?php echo esc_attr( $modal->ID ); ?>"
		>
			<div class="wpshadow-modal__overlay"></div>
			<div class="wpshadow-modal__container" style="max-width: <?php echo esc_attr( $width ); ?>px;">
				<?php if ( $show_close_btn ) : ?>
					<button type="button" class="wpshadow-modal__close" aria-label="<?php esc_attr_e( 'Close modal', 'wpshadow' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				<?php endif; ?>
				<div class="wpshadow-modal__content">
					<h2 id="wpshadow-modal-title-<?php echo esc_attr( $modal->ID ); ?>" class="wpshadow-modal__title">
						<?php echo esc_html( $modal->post_title ); ?>
					</h2>
					<div class="wpshadow-modal__body">
						<?php echo wp_kses_post( apply_filters( 'the_content', $modal->post_content ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

