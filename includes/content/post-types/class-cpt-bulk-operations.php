<?php
/**
 * CPT Bulk Operations Feature
 *
 * Provides bulk operations for custom post types including edit, delete, status change,
 * taxonomy assignment, and CSV/JSON export functionality.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since      1.6273.2359
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Bulk Operations Class
 *
 * Handles bulk operations for custom post types.
 *
 * @since 1.6273.2359
 */
class CPT_Bulk_Operations extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since  1.6035.1400
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_bulk_operations_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'wp_ajax_wpshadow_bulk_edit_posts', array( __CLASS__, 'ajax_bulk_edit' ) ),
				array( 'wp_ajax_wpshadow_bulk_delete_posts', array( __CLASS__, 'ajax_bulk_delete' ) ),
				array( 'wp_ajax_wpshadow_bulk_status_change', array( __CLASS__, 'ajax_bulk_status_change' ) ),
				array( 'wp_ajax_wpshadow_bulk_taxonomy_assign', array( __CLASS__, 'ajax_bulk_taxonomy_assign' ) ),
				array( 'wp_ajax_wpshadow_bulk_export', array( __CLASS__, 'ajax_bulk_export' ) ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '1.6273.2359';
	}

	/**
	 * Register bulk operations admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function register_bulk_operations_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Bulk Operations', 'wpshadow' ),
			__( 'Bulk Operations', 'wpshadow' ),
			'manage_options',
			'wpshadow-bulk-operations',
			array( __CLASS__, 'render_bulk_operations_page' )
		);
	}

	/**
	 * Enqueue admin assets for bulk operations.
	 *
	 * @since  1.6035.1400
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-bulk-operations' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-bulk-operations',
			plugins_url( 'assets/js/cpt-bulk-operations.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-bulk-operations',
			'wpShadowBulk',
			array(
				'nonce'       => wp_create_nonce( 'wpshadow_bulk_operations' ),
				'i18n'        => array(
					'confirm_delete'   => __( 'Are you sure you want to delete these posts? This cannot be undone.', 'wpshadow' ),
					'select_posts'     => __( 'Please select at least one post.', 'wpshadow' ),
					'processing'       => __( 'Processing...', 'wpshadow' ),
					'success'          => __( 'Operation completed successfully.', 'wpshadow' ),
					'error'            => __( 'An error occurred. Please try again.', 'wpshadow' ),
					'export_complete'  => __( 'Export complete. Downloading file...', 'wpshadow' ),
				),
			)
		);

		wp_enqueue_style(
			'wpshadow-bulk-operations',
			plugins_url( 'assets/css/cpt-bulk-operations.css', WPSHADOW_FILE ),
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render bulk operations admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function render_bulk_operations_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		$post_types = self::get_available_post_types();
		?>
		<div class="wrap wpshadow-bulk-operations">
			<h1><?php esc_html_e( 'Bulk Operations', 'wpshadow' ); ?></h1>
			
			<div class="wpshadow-bulk-controls">
				<div class="wpshadow-bulk-filters">
					<label for="post_type"><?php esc_html_e( 'Post Type:', 'wpshadow' ); ?></label>
					<select id="post_type" name="post_type">
						<?php foreach ( $post_types as $type_slug => $type_data ) : ?>
							<option value="<?php echo esc_attr( $type_slug ); ?>">
								<?php echo esc_html( $type_data['label'] ?? $type_slug ); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<button type="button" class="button" id="load_posts">
						<?php esc_html_e( 'Load Posts', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="wpshadow-bulk-actions">
					<label for="bulk_action"><?php esc_html_e( 'Action:', 'wpshadow' ); ?></label>
					<select id="bulk_action" name="bulk_action">
						<option value=""><?php esc_html_e( 'Select Action', 'wpshadow' ); ?></option>
						<option value="edit"><?php esc_html_e( 'Bulk Edit', 'wpshadow' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Bulk Delete', 'wpshadow' ); ?></option>
						<option value="status"><?php esc_html_e( 'Change Status', 'wpshadow' ); ?></option>
						<option value="taxonomy"><?php esc_html_e( 'Assign Taxonomy', 'wpshadow' ); ?></option>
						<option value="export_csv"><?php esc_html_e( 'Export to CSV', 'wpshadow' ); ?></option>
						<option value="export_json"><?php esc_html_e( 'Export to JSON', 'wpshadow' ); ?></option>
					</select>

					<button type="button" class="button button-primary" id="apply_bulk_action" disabled>
						<?php esc_html_e( 'Apply', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<div id="bulk_action_params" style="display:none; margin-top: 20px;">
				<!-- Dynamic parameters will be inserted here via JavaScript -->
			</div>

			<div id="posts_list_container" style="margin-top: 30px;">
				<p class="description">
					<?php esc_html_e( 'Select a post type and click "Load Posts" to begin.', 'wpshadow' ); ?>
				</p>
			</div>

			<div id="bulk_progress" style="display:none; margin-top: 20px;">
				<progress id="bulk_progress_bar" value="0" max="100"></progress>
				<p id="bulk_progress_text"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle bulk edit AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_bulk_edit(): void {
		check_ajax_referer( 'wpshadow_bulk_operations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$fields   = isset( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array();

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		$updated = 0;
		foreach ( $post_ids as $post_id ) {
			$post_data = array( 'ID' => $post_id );

			if ( isset( $fields['post_title'] ) && '' !== $fields['post_title'] ) {
				$post_data['post_title'] = sanitize_text_field( $fields['post_title'] );
			}

			if ( isset( $fields['post_content'] ) && '' !== $fields['post_content'] ) {
				$post_data['post_content'] = wp_kses_post( $fields['post_content'] );
			}

			if ( isset( $fields['post_excerpt'] ) && '' !== $fields['post_excerpt'] ) {
				$post_data['post_excerpt'] = sanitize_textarea_field( $fields['post_excerpt'] );
			}

			$result = wp_update_post( $post_data, true );
			if ( ! is_wp_error( $result ) ) {
				++$updated;
			}
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of posts updated */
					_n( '%d post updated successfully.', '%d posts updated successfully.', $updated, 'wpshadow' ),
					$updated
				),
				'updated' => $updated,
			)
		);
	}

	/**
	 * Handle bulk delete AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_bulk_delete(): void {
		check_ajax_referer( 'wpshadow_bulk_operations', 'nonce' );

		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		$deleted = 0;
		foreach ( $post_ids as $post_id ) {
			$result = wp_delete_post( $post_id, true );
			if ( false !== $result ) {
				++$deleted;
			}
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of posts deleted */
					_n( '%d post deleted successfully.', '%d posts deleted successfully.', $deleted, 'wpshadow' ),
					$deleted
				),
				'deleted' => $deleted,
			)
		);
	}

	/**
	 * Handle bulk status change AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_bulk_status_change(): void {
		check_ajax_referer( 'wpshadow_bulk_operations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids   = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$new_status = isset( $_POST['new_status'] ) ? sanitize_key( $_POST['new_status'] ) : '';

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		if ( ! in_array( $new_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid status', 'wpshadow' ) ) );
		}

		$updated = 0;
		foreach ( $post_ids as $post_id ) {
			$result = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => $new_status,
				)
			);

			if ( ! is_wp_error( $result ) && 0 !== $result ) {
				++$updated;
			}
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of posts updated */
					_n( '%d post status updated successfully.', '%d post statuses updated successfully.', $updated, 'wpshadow' ),
					$updated
				),
				'updated' => $updated,
			)
		);
	}

	/**
	 * Handle bulk taxonomy assignment AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_bulk_taxonomy_assign(): void {
		check_ajax_referer( 'wpshadow_bulk_operations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( $_POST['taxonomy'] ) : '';
		$terms    = isset( $_POST['terms'] ) ? array_map( 'absint', (array) $_POST['terms'] ) : array();
		$append   = isset( $_POST['append'] ) && '1' === $_POST['append'];

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid taxonomy', 'wpshadow' ) ) );
		}

		$updated = 0;
		foreach ( $post_ids as $post_id ) {
			$result = wp_set_object_terms( $post_id, $terms, $taxonomy, $append );
			if ( ! is_wp_error( $result ) ) {
				++$updated;
			}
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of posts updated */
					_n( '%d post taxonomy updated successfully.', '%d post taxonomies updated successfully.', $updated, 'wpshadow' ),
					$updated
				),
				'updated' => $updated,
			)
		);
	}

	/**
	 * Handle bulk export AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response or file download.
	 */
	public static function ajax_bulk_export(): void {
		check_ajax_referer( 'wpshadow_bulk_operations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$format   = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'csv';

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		$posts_data = self::get_posts_data( $post_ids );

		if ( 'csv' === $format ) {
			self::export_csv( $posts_data );
		} elseif ( 'json' === $format ) {
			self::export_json( $posts_data );
		}
	}

	/**
	 * Get posts data for export.
	 *
	 * @since  1.6035.1400
	 * @param  array $post_ids Array of post IDs.
	 * @return array Array of post data arrays.
	 */
	private static function get_posts_data( array $post_ids ): array {
		$posts_data = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$posts_data[] = array(
				'ID'            => $post->ID,
				'post_title'    => $post->post_title,
				'post_content'  => $post->post_content,
				'post_excerpt'  => $post->post_excerpt,
				'post_status'   => $post->post_status,
				'post_type'     => $post->post_type,
				'post_date'     => $post->post_date,
				'post_modified' => $post->post_modified,
				'post_author'   => get_the_author_meta( 'display_name', (int) $post->post_author ),
			);
		}

		return $posts_data;
	}

	/**
	 * Export posts data as CSV.
	 *
	 * @since  1.6035.1400
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_csv( array $posts_data ): void {
		$filename = 'wpshadow-posts-export-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		if ( ! empty( $posts_data ) ) {
			fputcsv( $output, array_keys( $posts_data[0] ) );

			foreach ( $posts_data as $row ) {
				fputcsv( $output, $row );
			}
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export posts data as JSON.
	 *
	 * @since  1.6035.1400
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_json( array $posts_data ): void {
		$filename = 'wpshadow-posts-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $posts_data, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Get available post types for bulk operations.
	 *
	 * @since  1.6035.1400
	 * @return array Available post types.
	 */
	private static function get_available_post_types(): array {
		if ( class_exists( 'WPShadow\Content\Post_Types_Manager' ) ) {
			return \WPShadow\Content\Post_Types_Manager::get_available_post_types();
		}

		return array();
	}
}
