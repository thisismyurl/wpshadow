<?php
/**
 * CPT Conditional Display
 *
 * Provides conditional display logic for blocks and content,
 * allowing content to be shown/hidden based on user-defined rules.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Conditional_Display Class
 *
 * Rules engine for conditional content display based on
 * user role, device, date/time, and custom conditions.
 *
 * @since 1.6093.1200
 */
class CPT_Conditional_Display {

	/**
	 * Initialize conditional display system.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_conditions_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_conditions' ), 10, 2 );
		add_filter( 'the_content', array( __CLASS__, 'apply_conditions' ), 999 );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_query_by_conditions' ) );
	}

	/**
	 * Add conditional display meta box.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_conditions_meta_box() {
		$post_types = array(
			'testimonial',
			'team_member',
			'portfolio_item',
			'wps_event',
			'resource',
			'case_study',
			'service',
			'location',
			'documentation',
			'wps_product',
		);

		foreach ( $post_types as $post_type ) {
			// Only add meta box if post type is registered.
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			add_meta_box(
				'wpshadow_conditional_display',
				__( 'Display Conditions', 'wpshadow' ),
				array( __CLASS__, 'render_conditions_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render conditions meta box.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_conditions_meta_box( $post ) {
		wp_nonce_field( 'wpshadow_save_conditions', 'wpshadow_conditions_nonce' );

		$conditions = get_post_meta( $post->ID, '_wpshadow_display_conditions', true );
		$conditions = $conditions ? $conditions : array();

		$user_roles     = isset( $conditions['user_roles'] ) ? $conditions['user_roles'] : array();
		$devices        = isset( $conditions['devices'] ) ? $conditions['devices'] : array();
		$date_start     = isset( $conditions['date_start'] ) ? $conditions['date_start'] : '';
		$date_end       = isset( $conditions['date_end'] ) ? $conditions['date_end'] : '';
		$logged_in_only = isset( $conditions['logged_in_only'] ) ? $conditions['logged_in_only'] : false;
		?>
		<div class="wpshadow-conditions">
			<p>
				<label>
					<input type="checkbox" name="wpshadow_logged_in_only" value="1" <?php checked( $logged_in_only, true ); ?> />
					<?php esc_html_e( 'Logged-in users only', 'wpshadow' ); ?>
				</label>
			</p>

			<p>
				<label><strong><?php esc_html_e( 'User Roles:', 'wpshadow' ); ?></strong></label><br>
				<?php
				$roles = wp_roles()->get_names();
				foreach ( $roles as $role_key => $role_name ) :
					?>
					<label style="display:block;">
						<input type="checkbox" name="wpshadow_user_roles[]" value="<?php echo esc_attr( $role_key ); ?>" 
							<?php checked( in_array( $role_key, $user_roles, true ) ); ?> />
						<?php echo esc_html( $role_name ); ?>
					</label>
				<?php endforeach; ?>
			</p>

			<p>
				<label><strong><?php esc_html_e( 'Devices:', 'wpshadow' ); ?></strong></label><br>
				<label style="display:block;">
					<input type="checkbox" name="wpshadow_devices[]" value="desktop" 
						<?php checked( in_array( 'desktop', $devices, true ) ); ?> />
					<?php esc_html_e( 'Desktop', 'wpshadow' ); ?>
				</label>
				<label style="display:block;">
					<input type="checkbox" name="wpshadow_devices[]" value="tablet" 
						<?php checked( in_array( 'tablet', $devices, true ) ); ?> />
					<?php esc_html_e( 'Tablet', 'wpshadow' ); ?>
				</label>
				<label style="display:block;">
					<input type="checkbox" name="wpshadow_devices[]" value="mobile" 
						<?php checked( in_array( 'mobile', $devices, true ) ); ?> />
					<?php esc_html_e( 'Mobile', 'wpshadow' ); ?>
				</label>
			</p>

			<p>
				<label><strong><?php esc_html_e( 'Date Range:', 'wpshadow' ); ?></strong></label><br>
				<label><?php esc_html_e( 'Start:', 'wpshadow' ); ?></label>
				<input type="date" name="wpshadow_date_start" value="<?php echo esc_attr( $date_start ); ?>" style="width:100%;" />
			</p>

			<p>
				<label><?php esc_html_e( 'End:', 'wpshadow' ); ?></label>
				<input type="date" name="wpshadow_date_end" value="<?php echo esc_attr( $date_end ); ?>" style="width:100%;" />
			</p>

			<p class="description">
				<?php esc_html_e( 'Leave unchecked to show to all. Checked items will restrict visibility.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save conditions on post save.
	 *
	 * @since 1.6093.1200
	 * @param  int      $post_id Post ID.
	 * @param  \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_conditions( $post_id, $post ) {
		if ( ! isset( $_POST['wpshadow_conditions_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['wpshadow_conditions_nonce'], 'wpshadow_save_conditions' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$conditions = array(
			'logged_in_only' => isset( $_POST['wpshadow_logged_in_only'] ),
			'user_roles'     => isset( $_POST['wpshadow_user_roles'] ) ? array_map( 'sanitize_key', $_POST['wpshadow_user_roles'] ) : array(),
			'devices'        => isset( $_POST['wpshadow_devices'] ) ? array_map( 'sanitize_key', $_POST['wpshadow_devices'] ) : array(),
			'date_start'     => isset( $_POST['wpshadow_date_start'] ) ? sanitize_text_field( $_POST['wpshadow_date_start'] ) : '',
			'date_end'       => isset( $_POST['wpshadow_date_end'] ) ? sanitize_text_field( $_POST['wpshadow_date_end'] ) : '',
		);

		update_post_meta( $post_id, '_wpshadow_display_conditions', $conditions );
	}

	/**
	 * Apply conditions to content display.
	 *
	 * @since 1.6093.1200
	 * @param  string $content Post content.
	 * @return string Modified content or empty if conditions not met.
	 */
	public static function apply_conditions( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		if ( ! self::should_display( $post_id ) ) {
			return '';
		}

		return $content;
	}

	/**
	 * Check if post should be displayed based on conditions.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return bool True if should display.
	 */
	public static function should_display( $post_id ) {
		$conditions = get_post_meta( $post_id, '_wpshadow_display_conditions', true );

		if ( ! $conditions || ! is_array( $conditions ) ) {
			return true;
		}

		// Check logged in condition.
		if ( ! empty( $conditions['logged_in_only'] ) && ! is_user_logged_in() ) {
			return false;
		}

		// Check user roles.
		if ( ! empty( $conditions['user_roles'] ) ) {
			$user = wp_get_current_user();
			if ( ! $user || ! array_intersect( $conditions['user_roles'], $user->roles ) ) {
				return false;
			}
		}

		// Check date range.
		if ( ! empty( $conditions['date_start'] ) || ! empty( $conditions['date_end'] ) ) {
			$current_date = current_time( 'Y-m-d' );

			if ( ! empty( $conditions['date_start'] ) && $current_date < $conditions['date_start'] ) {
				return false;
			}

			if ( ! empty( $conditions['date_end'] ) && $current_date > $conditions['date_end'] ) {
				return false;
			}
		}

		// Check device (basic user agent detection).
		if ( ! empty( $conditions['devices'] ) ) {
			$device = self::detect_device();
			if ( ! in_array( $device, $conditions['devices'], true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Detect current device type.
	 *
	 * @since 1.6093.1200
	 * @return string Device type (desktop, tablet, mobile).
	 */
	private static function detect_device() {
		if ( wp_is_mobile() ) {
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
			if ( preg_match( '/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent ) ) {
				return 'tablet';
			}
			return 'mobile';
		}
		return 'desktop';
	}

	/**
	 * Filter queries by display conditions.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Query $query WP_Query object.
	 * @return void
	 */
	public static function filter_query_by_conditions( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Add meta query to exclude posts with unmet conditions.
		add_filter( 'posts_where', array( __CLASS__, 'filter_posts_where' ), 10, 2 );
	}

	/**
	 * Filter WHERE clause for conditional posts.
	 *
	 * @since 1.6093.1200
	 * @param  string    $where WHERE clause.
	 * @param  \WP_Query $query WP_Query object.
	 * @return string Modified WHERE clause.
	 */
	public static function filter_posts_where( $where, $query ) {
		// This is a placeholder - full implementation would require more complex filtering.
		return $where;
	}
}
