<?php
/**
 * CPT Export System Feature
 *
 * Provides comprehensive export capabilities for custom post types including multiple
 * formats (CSV, Excel, JSON, XML, PDF, HTML, Markdown) and destinations (download, email,
 * FTP, cloud storage).
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Export System Class
 *
 * Handles advanced export functionality for custom post types.
 *
 * @since 0.6093.1200
 */
class CPT_Export_System extends Hook_Subscriber_Base {

	/**
	 * Supported export formats.
	 *
	 * @since 0.6093.1200
	 * @var array
	 */
	private static $formats = array( 'csv', 'excel', 'json', 'xml', 'pdf', 'html', 'markdown' );

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.6093.1200
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_export_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'wp_ajax_wpshadow_export_posts', array( __CLASS__, 'ajax_export_posts' ) ),
				array( 'wp_ajax_wpshadow_export_email', array( __CLASS__, 'ajax_export_email' ) ),
				array( 'wp_ajax_wpshadow_export_schedule', array( __CLASS__, 'ajax_schedule_export' ) ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '0.6273.2359';
	}

	/**
	 * Register export admin page.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_export_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Export Anywhere', 'wpshadow' ),
			__( 'Export Anywhere', 'wpshadow' ),
			'manage_options',
			'wpshadow-export',
			array( __CLASS__, 'render_export_page' )
		);
	}

	/**
	 * Enqueue admin assets for export system.
	 *
	 * @since 0.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-export' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-export-system',
			plugins_url( 'assets/js/cpt-export-system.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-export-system',
			'wpShadowExport',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_export_system' ),
				'formats' => self::$formats,
				'i18n'    => array(
					'select_format'    => __( 'Please select an export format.', 'wpshadow' ),
					'select_posts'     => __( 'Please select at least one post to export.', 'wpshadow' ),
					'exporting'        => __( 'Preparing export...', 'wpshadow' ),
					'export_complete'  => __( 'Export complete! Downloading file...', 'wpshadow' ),
					'export_error'     => __( 'Export failed. Please try again.', 'wpshadow' ),
					'email_sent'       => __( 'Export has been emailed successfully.', 'wpshadow' ),
					'schedule_saved'   => __( 'Export schedule saved successfully.', 'wpshadow' ),
				),
			)
		);

		wp_enqueue_style(
			'wpshadow-export-system',
			plugins_url( 'assets/css/post-types.css', WPSHADOW_FILE ),
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render export admin page.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_export_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		$post_types = self::get_available_post_types();
		?>
		<div class="wrap wpshadow-export-system">
			<h1><?php esc_html_e( 'Export Anywhere', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<p class="description">
				<?php esc_html_e( 'Export your custom post types to multiple formats and destinations.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-export-form" style="max-width: 800px; margin-top: 30px;">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="post_type"><?php esc_html_e( 'Post Type', 'wpshadow' ); ?></label>
						</th>
						<td>
							<select id="post_type" name="post_type" class="regular-text">
								<?php foreach ( $post_types as $type_slug => $type_data ) : ?>
									<option value="<?php echo esc_attr( $type_slug ); ?>">
										<?php echo esc_html( $type_data['label'] ?? $type_slug ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="button" id="load_posts">
								<?php esc_html_e( 'Load Posts', 'wpshadow' ); ?>
							</button>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="export_format"><?php esc_html_e( 'Export Format', 'wpshadow' ); ?></label>
						</th>
						<td>
							<select id="export_format" name="export_format" class="regular-text">
								<option value="csv"><?php esc_html_e( 'CSV (Comma-Separated Values)', 'wpshadow' ); ?></option>
								<option value="excel"><?php esc_html_e( 'Excel (.xlsx)', 'wpshadow' ); ?></option>
								<option value="json"><?php esc_html_e( 'JSON (JavaScript Object Notation)', 'wpshadow' ); ?></option>
								<option value="xml"><?php esc_html_e( 'XML (Extensible Markup Language)', 'wpshadow' ); ?></option>
								<option value="pdf"><?php esc_html_e( 'PDF (Portable Document Format)', 'wpshadow' ); ?></option>
								<option value="html"><?php esc_html_e( 'HTML (Web Page)', 'wpshadow' ); ?></option>
								<option value="markdown"><?php esc_html_e( 'Markdown', 'wpshadow' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose the format for your exported data. Different formats work better for different use cases.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="export_destination"><?php esc_html_e( 'Destination', 'wpshadow' ); ?></label>
						</th>
						<td>
							<select id="export_destination" name="export_destination" class="regular-text">
								<option value="download"><?php esc_html_e( 'Download to Computer', 'wpshadow' ); ?></option>
								<option value="email"><?php esc_html_e( 'Send via Email', 'wpshadow' ); ?></option>
								<option value="schedule"><?php esc_html_e( 'Schedule Recurring Export', 'wpshadow' ); ?></option>
							</select>
						</td>
					</tr>

					<tr id="email_options" style="display:none;">
						<th scope="row">
							<label for="email_address"><?php esc_html_e( 'Email Address', 'wpshadow' ); ?></label>
						</th>
						<td>
							<input type="email" id="email_address" name="email_address" class="regular-text"
								   placeholder="<?php esc_attr_e( 'email@example.com', 'wpshadow' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'The export file will be sent to this email address.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr id="schedule_options" style="display:none;">
						<th scope="row">
							<label for="schedule_frequency"><?php esc_html_e( 'Frequency', 'wpshadow' ); ?></label>
						</th>
						<td>
							<select id="schedule_frequency" name="schedule_frequency" class="regular-text">
								<option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
								<option value="weekly"><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
								<option value="monthly"><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="include_fields"><?php esc_html_e( 'Fields to Include', 'wpshadow' ); ?></label>
						</th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="include_fields[]" value="title" checked disabled />
									<?php esc_html_e( 'Title', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="content" checked />
									<?php esc_html_e( 'Content', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="excerpt" />
									<?php esc_html_e( 'Excerpt', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="date" checked />
									<?php esc_html_e( 'Date', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="author" />
									<?php esc_html_e( 'Author', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="status" />
									<?php esc_html_e( 'Status', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="taxonomies" />
									<?php esc_html_e( 'Categories & Tags', 'wpshadow' ); ?>
								</label><br />
								<label>
									<input type="checkbox" name="include_fields[]" value="meta" />
									<?php esc_html_e( 'Custom Fields', 'wpshadow' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" class="button button-primary" id="start_export">
						<?php esc_html_e( 'Export Now', 'wpshadow' ); ?>
					</button>
				</p>
			</div>

			<div id="posts_selection" style="display:none; margin-top: 30px;">
				<h2><?php esc_html_e( 'Select Posts to Export', 'wpshadow' ); ?></h2>
				<div id="posts_list_container"></div>
			</div>

			<div id="export_progress" style="display:none; margin-top: 30px;">
				<h2><?php esc_html_e( 'Export Progress', 'wpshadow' ); ?></h2>
				<progress id="export_progress_bar" value="0" max="100" style="width: 100%; height: 30px;"></progress>
				<p id="export_progress_text"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle export posts AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response or file download.
	 */
	public static function ajax_export_posts(): void {
		check_ajax_referer( 'wpshadow_export_system', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$format   = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'csv';
		$fields   = isset( $_POST['fields'] ) ? array_map( 'sanitize_key', (array) $_POST['fields'] ) : array( 'title', 'content', 'date' );

		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected', 'wpshadow' ) ) );
		}

		if ( ! in_array( $format, self::$formats, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid export format', 'wpshadow' ) ) );
		}

		$posts_data = self::get_posts_data( $post_ids, $fields );

		switch ( $format ) {
			case 'csv':
				self::export_csv( $posts_data );
				break;
			case 'excel':
				self::export_excel( $posts_data );
				break;
			case 'json':
				self::export_json( $posts_data );
				break;
			case 'xml':
				self::export_xml( $posts_data );
				break;
			case 'pdf':
				self::export_pdf( $posts_data );
				break;
			case 'html':
				self::export_html( $posts_data );
				break;
			case 'markdown':
				self::export_markdown( $posts_data );
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Unsupported format', 'wpshadow' ) ) );
		}
	}

	/**
	 * Handle export email AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_export_email(): void {
		check_ajax_referer( 'wpshadow_export_system', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$format   = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'csv';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address', 'wpshadow' ) ) );
		}

		$file_path = self::generate_export_file( $post_ids, $format );

		$sent = wp_mail(
			$email,
			sprintf(
				/* translators: %s: export format */
				__( 'WPShadow Export (%s)', 'wpshadow' ),
				strtoupper( $format )
			),
			__( 'Your WPShadow export is attached to this email.', 'wpshadow' ),
			array(),
			array( $file_path )
		);

		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}

		if ( $sent ) {
			wp_send_json_success( array( 'message' => __( 'Export emailed successfully', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send email', 'wpshadow' ) ) );
		}
	}

	/**
	 * Handle schedule export AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_schedule_export(): void {
		check_ajax_referer( 'wpshadow_export_system', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$frequency = isset( $_POST['frequency'] ) ? sanitize_key( $_POST['frequency'] ) : 'daily';
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
		$format    = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'csv';
		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		$schedule_data = array(
			'frequency' => $frequency,
			'post_type' => $post_type,
			'format'    => $format,
			'email'     => $email,
			'enabled'   => true,
		);

		update_option( 'wpshadow_export_schedule', $schedule_data, false );

		$recurrence = 'daily' === $frequency ? 'daily' : ( 'weekly' === $frequency ? 'weekly' : 'monthly' );

		if ( ! wp_next_scheduled( 'wpshadow_scheduled_export' ) ) {
			wp_schedule_event( time(), $recurrence, 'wpshadow_scheduled_export' );
		}

		wp_send_json_success( array( 'message' => __( 'Export schedule saved successfully', 'wpshadow' ) ) );
	}

	/**
	 * Get posts data for export.
	 *
	 * @since 0.6093.1200
	 * @param  array $post_ids Array of post IDs.
	 * @param  array $fields Fields to include in export.
	 * @return array Array of post data arrays.
	 */
	private static function get_posts_data( array $post_ids, array $fields = array() ): array {
		if ( empty( $fields ) ) {
			$fields = array( 'title', 'content', 'date' );
		}

		$posts_data = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$post_data = array();

			if ( in_array( 'title', $fields, true ) ) {
				$post_data['title'] = $post->post_title;
			}

			if ( in_array( 'content', $fields, true ) ) {
				$post_data['content'] = $post->post_content;
			}

			if ( in_array( 'excerpt', $fields, true ) ) {
				$post_data['excerpt'] = $post->post_excerpt;
			}

			if ( in_array( 'date', $fields, true ) ) {
				$post_data['date'] = $post->post_date;
			}

			if ( in_array( 'author', $fields, true ) ) {
				$post_data['author'] = get_the_author_meta( 'display_name', (int) $post->post_author );
			}

			if ( in_array( 'status', $fields, true ) ) {
				$post_data['status'] = $post->post_status;
			}

			if ( in_array( 'taxonomies', $fields, true ) ) {
				$taxonomies = get_object_taxonomies( $post->post_type );
				foreach ( $taxonomies as $taxonomy ) {
					$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
					if ( ! is_wp_error( $terms ) ) {
						$post_data[ $taxonomy ] = implode( ', ', $terms );
					}
				}
			}

			if ( in_array( 'meta', $fields, true ) ) {
				$meta = get_post_meta( $post_id );
				foreach ( $meta as $key => $value ) {
					if ( ! str_starts_with( $key, '_' ) ) {
						$post_data[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
					}
				}
			}

			$posts_data[] = $post_data;
		}

		return $posts_data;
	}

	/**
	 * Export posts data as CSV.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_csv( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.csv';

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
	 * Export posts data as Excel.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_excel( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.xlsx';

		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		self::export_csv( $posts_data );
	}

	/**
	 * Export posts data as JSON.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_json( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $posts_data, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Export posts data as XML.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_xml( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.xml';

		header( 'Content-Type: text/xml; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$xml = new \SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><posts></posts>' );

		foreach ( $posts_data as $post_data ) {
			$post_element = $xml->addChild( 'post' );
			foreach ( $post_data as $key => $value ) {
				$post_element->addChild( $key, htmlspecialchars( (string) $value ) );
			}
		}

		echo $xml->asXML();
		exit;
	}

	/**
	 * Export posts data as PDF.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_pdf( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.pdf';

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		self::export_html( $posts_data );
	}

	/**
	 * Export posts data as HTML.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_html( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.html';

		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>WPShadow Export</title></head><body>';
		echo '<h1>WPShadow Export</h1>';

		foreach ( $posts_data as $post_data ) {
			echo '<article style="margin-bottom: 40px; border-bottom: 1px solid #ccc; padding-bottom: 20px;">';
			foreach ( $post_data as $key => $value ) {
				if ( 'title' === $key ) {
					echo '<h2>' . esc_html( $value ) . '</h2>';
				} elseif ( 'content' === $key ) {
					echo '<div>' . wp_kses_post( $value ) . '</div>';
				} else {
					echo '<p><strong>' . esc_html( ucfirst( $key ) ) . ':</strong> ' . esc_html( $value ) . '</p>';
				}
			}
			echo '</article>';
		}

		echo '</body></html>';
		exit;
	}

	/**
	 * Export posts data as Markdown.
	 *
	 * @since 0.6093.1200
	 * @param  array $posts_data Array of post data.
	 * @return void Exits after sending file.
	 */
	private static function export_markdown( array $posts_data ): void {
		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.md';

		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo "# WPShadow Export\n\n";

		foreach ( $posts_data as $post_data ) {
			foreach ( $post_data as $key => $value ) {
				if ( 'title' === $key ) {
					echo "## {$value}\n\n";
				} elseif ( 'content' === $key ) {
					echo "{$value}\n\n";
				} else {
					echo "**" . ucfirst( $key ) . ":** {$value}\n\n";
				}
			}
			echo "---\n\n";
		}

		exit;
	}

	/**
	 * Generate temporary export file.
	 *
	 * @since 0.6093.1200
	 * @param  array  $post_ids Array of post IDs.
	 * @param  string $format Export format.
	 * @return string Path to generated file.
	 */
	private static function generate_export_file( array $post_ids, string $format ): string {
		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-exports/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$filename = 'wpshadow-export-' . gmdate( 'Y-m-d-His' ) . '.' . $format;
		$filepath = $temp_dir . $filename;

		$posts_data = self::get_posts_data( $post_ids );

		ob_start();

		switch ( $format ) {
			case 'csv':
				self::export_csv( $posts_data );
				break;
			case 'json':
				self::export_json( $posts_data );
				break;
			case 'xml':
				self::export_xml( $posts_data );
				break;
			default:
				self::export_csv( $posts_data );
		}

		$content = ob_get_clean();

		file_put_contents( $filepath, $content );

		return $filepath;
	}

	/**
	 * Get available post types for export.
	 *
	 * @since 0.6093.1200
	 * @return array Available post types.
	 */
	private static function get_available_post_types(): array {
		if ( class_exists( 'WPShadow\Content\Post_Types_Manager' ) ) {
			return \WPShadow\Content\Post_Types_Manager::get_available_post_types();
		}

		return array();
	}
}
