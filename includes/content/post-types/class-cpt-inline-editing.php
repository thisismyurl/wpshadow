<?php
/**
 * CPT Inline Editing
 *
 * Provides quick inline editing functionality for custom post types,
 * allowing users to edit content without leaving the list view.
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
 * CPT_Inline_Editing Class
 *
 * Adds quick edit functionality to CPT admin screens.
 *
 * @since 1.6093.1200
 */
class CPT_Inline_Editing {

	/**
	 * Initialize inline editing system.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'quick_edit_custom_box', array( __CLASS__, 'add_quick_edit_fields' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_inline_save', array( __CLASS__, 'handle_inline_save' ) );
	}

	/**
	 * Add custom fields to quick edit.
	 *
	 * @since 1.6093.1200
	 * @param  string $column_name Column name.
	 * @param  string $post_type   Post type.
	 * @return void
	 */
	public static function add_quick_edit_fields( $column_name, $post_type ) {
		// Verify post type exists before adding fields.
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}

		$supported = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		if ( ! in_array( $post_type, $supported, true ) ) {
			return;
		}

		switch ( $post_type ) {
			case 'testimonial':
				?>
				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<label>
							<span class="title"><?php esc_html_e( 'Rating', 'wpshadow' ); ?></span>
							<select name="testimonial_rating">
								<option value="">—</option>
								<option value="5">★★★★★</option>
								<option value="4">★★★★☆</option>
								<option value="3">★★★☆☆</option>
								<option value="2">★★☆☆☆</option>
								<option value="1">★☆☆☆☆</option>
							</select>
						</label>
					</div>
				</fieldset>
				<?php
				break;

			case 'team_member':
				?>
				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<label>
							<span class="title"><?php esc_html_e( 'Job Title', 'wpshadow' ); ?></span>
							<input type="text" name="team_member_job_title" value="" />
						</label>
					</div>
				</fieldset>
				<?php
				break;

			case 'wps_event':
				?>
				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<label>
							<span class="title"><?php esc_html_e( 'Start Date', 'wpshadow' ); ?></span>
							<input type="datetime-local" name="event_start_datetime" value="" />
						</label>
					</div>
				</fieldset>
				<?php
				break;

			case 'service':
				?>
				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<label>
							<span class="title"><?php esc_html_e( 'Price', 'wpshadow' ); ?></span>
							<input type="text" name="service_price" value="" />
						</label>
					</div>
				</fieldset>
				<?php
				break;
		}
	}

	/**
	 * Enqueue inline editing assets.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'edit.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		$supported = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		if ( ! $screen || ! in_array( $screen->post_type, $supported, true ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-inline-edit',
			WPSHADOW_URL . 'assets/js/cpt-inline-edit.js',
			array( 'jquery', 'inline-edit-post' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-inline-edit',
			'wpShadowInlineEdit',
			'wpshadow_inline_edit'
		);
	}

	/**
	 * Handle inline save AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_inline_save() {
		check_ajax_referer( 'wpshadow_inline_edit', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID', 'wpshadow' ) ) );
		}

		$post_type = get_post_type( $post_id );

		// Save custom fields based on post type.
		switch ( $post_type ) {
			case 'testimonial':
				if ( isset( $_POST['testimonial_rating'] ) ) {
					update_post_meta( $post_id, 'testimonial_rating', absint( $_POST['testimonial_rating'] ) );
				}
				break;

			case 'team_member':
				if ( isset( $_POST['team_member_job_title'] ) ) {
					update_post_meta( $post_id, 'team_member_job_title', sanitize_text_field( wp_unslash( $_POST['team_member_job_title'] ) ) );
				}
				break;

			case 'wps_event':
				if ( isset( $_POST['event_start_datetime'] ) ) {
					update_post_meta( $post_id, 'event_start_datetime', sanitize_text_field( wp_unslash( $_POST['event_start_datetime'] ) ) );
				}
				break;

			case 'service':
				if ( isset( $_POST['service_price'] ) ) {
					update_post_meta( $post_id, 'service_price', sanitize_text_field( wp_unslash( $_POST['service_price'] ) ) );
				}
				break;
		}

		wp_send_json_success( array(
			'message' => __( 'Post updated successfully', 'wpshadow' ),
		) );
	}
}
