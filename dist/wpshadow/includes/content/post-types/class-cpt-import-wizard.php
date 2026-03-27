<?php
/**
 * CPT Import Wizard Feature
 *
 * Provides comprehensive import capabilities for custom post types including CSV, JSON, XML
 * with field mapping, validation, and preview functionality.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Import Wizard Class
 *
 * Handles import functionality for custom post types with wizard interface.
 *
 * @since 1.6093.1200
 */
class CPT_Import_Wizard extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.6093.1200
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_import_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'wp_ajax_wpshadow_import_upload', array( __CLASS__, 'ajax_import_upload' ) ),
				array( 'wp_ajax_wpshadow_import_preview', array( __CLASS__, 'ajax_import_preview' ) ),
				array( 'wp_ajax_wpshadow_import_execute', array( __CLASS__, 'ajax_import_execute' ) ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '1.6273.2359';
	}

	/**
	 * Register import admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_import_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Import Wizard', 'wpshadow' ),
			__( 'Import Wizard', 'wpshadow' ),
			'manage_options',
			'wpshadow-import',
			array( __CLASS__, 'render_import_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-import' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-import-wizard',
			plugins_url( 'assets/js/cpt-import-wizard.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-import-wizard',
			'wpShadowImport',
			array(
				'nonce'     => wp_create_nonce( 'wpshadow_import_wizard' ),
				'maxSize'   => wp_max_upload_size(),
				'i18n'      => array(
					'uploading'       => __( 'Uploading file...', 'wpshadow' ),
					'processing'      => __( 'Processing import...', 'wpshadow' ),
					'preview_ready'   => __( 'Preview ready. Review and confirm import.', 'wpshadow' ),
					'import_complete' => __( 'Import completed successfully!', 'wpshadow' ),
					'import_error'    => __( 'Import failed. Please check the file format.', 'wpshadow' ),
				),
			)
		);

		wp_enqueue_style(
			'wpshadow-import-wizard',
			plugins_url( 'assets/css/post-types.css', WPSHADOW_FILE ),
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render import admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_import_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		$post_types = self::get_available_post_types();
		?>
		<div class="wrap wpshadow-import-wizard">
			<h1><?php esc_html_e( 'Import Wizard', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<p class="description">
				<?php esc_html_e( 'Import custom post types from CSV, JSON, or XML files with field mapping and validation.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-wizard-steps">
				<div class="wizard-step active" data-step="1">
					<span class="step-number">1</span>
					<span class="step-title"><?php esc_html_e( 'Upload File', 'wpshadow' ); ?></span>
				</div>
				<div class="wizard-step" data-step="2">
					<span class="step-number">2</span>
					<span class="step-title"><?php esc_html_e( 'Map Fields', 'wpshadow' ); ?></span>
				</div>
				<div class="wizard-step" data-step="3">
					<span class="step-number">3</span>
					<span class="step-title"><?php esc_html_e( 'Preview', 'wpshadow' ); ?></span>
				</div>
				<div class="wizard-step" data-step="4">
					<span class="step-number">4</span>
					<span class="step-title"><?php esc_html_e( 'Import', 'wpshadow' ); ?></span>
				</div>
			</div>

			<div id="step-1" class="wizard-content active">
				<h2><?php esc_html_e( 'Step 1: Upload Your File', 'wpshadow' ); ?></h2>
				<form id="import-upload-form" enctype="multipart/form-data">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="post_type"><?php esc_html_e( 'Import To', 'wpshadow' ); ?></label>
							</th>
							<td>
								<select id="post_type" name="post_type" class="regular-text" required>
									<option value=""><?php esc_html_e( 'Select Post Type', 'wpshadow' ); ?></option>
									<?php foreach ( $post_types as $slug => $data ) : ?>
										<option value="<?php echo esc_attr( $slug ); ?>">
											<?php echo esc_html( $data['label'] ?? $slug ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="import_file"><?php esc_html_e( 'Choose File', 'wpshadow' ); ?></label>
							</th>
							<td>
								<input type="file" id="import_file" name="import_file" accept=".csv,.json,.xml" required />
								<p class="description">
									<?php
									printf(
										/* translators: %s: maximum file size */
										esc_html__( 'Supported formats: CSV, JSON, XML. Maximum file size: %s', 'wpshadow' ),
										esc_html( size_format( wp_max_upload_size() ) )
									);
									?>
								</p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Upload and Continue', 'wpshadow' ); ?>
						</button>
					</p>
				</form>
			</div>

			<div id="step-2" class="wizard-content" style="display:none;">
				<h2><?php esc_html_e( 'Step 2: Map Your Fields', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Match columns from your file to WordPress post fields.', 'wpshadow' ); ?>
				</p>
				<div id="field-mapping-container"></div>
				<p class="submit">
					<button type="button" class="button" id="back-to-upload">
						<?php esc_html_e( 'Back', 'wpshadow' ); ?>
					</button>
					<button type="button" class="button button-primary" id="continue-to-preview">
						<?php esc_html_e( 'Continue to Preview', 'wpshadow' ); ?>
					</button>
				</p>
			</div>

			<div id="step-3" class="wizard-content" style="display:none;">
				<h2><?php esc_html_e( 'Step 3: Preview Import', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Review the first few posts before importing. Make sure everything looks correct.', 'wpshadow' ); ?>
				</p>
				<div id="preview-container"></div>
				<p class="submit">
					<button type="button" class="button" id="back-to-mapping">
						<?php esc_html_e( 'Back to Field Mapping', 'wpshadow' ); ?>
					</button>
					<button type="button" class="button button-primary" id="start-import">
						<?php esc_html_e( 'Start Import', 'wpshadow' ); ?>
					</button>
				</p>
			</div>

			<div id="step-4" class="wizard-content" style="display:none;">
				<h2><?php esc_html_e( 'Step 4: Importing...', 'wpshadow' ); ?></h2>
				<div id="import-progress">
					<progress id="import-progress-bar" value="0" max="100" style="width:100%; height:30px;"></progress>
					<p id="import-status-text"></p>
				</div>
				<div id="import-results" style="display:none;">
					<h3><?php esc_html_e( 'Import Complete!', 'wpshadow' ); ?></h3>
					<div id="import-summary"></div>
					<p class="submit">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-import' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Import Another File', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle file upload AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_import_upload(): void {
		check_ajax_referer( 'wpshadow_import_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		if ( ! isset( $_FILES['import_file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded', 'wpshadow' ) ) );
		}

		$file = $_FILES['import_file'];
		$ext  = pathinfo( $file['name'], PATHINFO_EXTENSION );

		if ( ! in_array( $ext, array( 'csv', 'json', 'xml' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file format', 'wpshadow' ) ) );
		}

		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-imports/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$filename = 'import-' . gmdate( 'YmdHis' ) . '.' . $ext;
		$filepath = $temp_dir . $filename;

		if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save uploaded file', 'wpshadow' ) ) );
		}

		$data = self::parse_import_file( $filepath, $ext );

		if ( is_wp_error( $data ) ) {
			unlink( $filepath );
			wp_send_json_error( array( 'message' => $data->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'file_id' => $filename,
				'columns' => $data['columns'],
				'rows'    => count( $data['rows'] ),
			)
		);
	}

	/**
	 * Handle import preview AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_import_preview(): void {
		check_ajax_referer( 'wpshadow_import_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$file_id = isset( $_POST['file_id'] ) ? sanitize_file_name( wp_unslash( $_POST['file_id'] ) ) : '';
		$mapping = isset( $_POST['mapping'] ) ? (array) $_POST['mapping'] : array();

		$upload_dir = wp_upload_dir();
		$filepath   = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-imports/' . $file_id;

		if ( ! file_exists( $filepath ) ) {
			wp_send_json_error( array( 'message' => __( 'Import file not found', 'wpshadow' ) ) );
		}

		$ext  = pathinfo( $filepath, PATHINFO_EXTENSION );
		$data = self::parse_import_file( $filepath, $ext );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'message' => $data->get_error_message() ) );
		}

		$preview = array_slice( $data['rows'], 0, 5 );
		$mapped  = self::map_data( $preview, $data['columns'], $mapping );

		wp_send_json_success( array( 'preview' => $mapped ) );
	}

	/**
	 * Handle import execution AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_import_execute(): void {
		check_ajax_referer( 'wpshadow_import_wizard', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$file_id   = isset( $_POST['file_id'] ) ? sanitize_file_name( wp_unslash( $_POST['file_id'] ) ) : '';
		$mapping   = isset( $_POST['mapping'] ) ? (array) $_POST['mapping'] : array();
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';

		$upload_dir = wp_upload_dir();
		$filepath   = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-imports/' . $file_id;

		if ( ! file_exists( $filepath ) ) {
			wp_send_json_error( array( 'message' => __( 'Import file not found', 'wpshadow' ) ) );
		}

		$ext  = pathinfo( $filepath, PATHINFO_EXTENSION );
		$data = self::parse_import_file( $filepath, $ext );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'message' => $data->get_error_message() ) );
		}

		$mapped   = self::map_data( $data['rows'], $data['columns'], $mapping );
		$imported = 0;
		$skipped  = 0;

		foreach ( $mapped as $post_data ) {
			$post_args = array(
				'post_type'    => $post_type,
				'post_status'  => 'draft',
				'post_title'   => $post_data['title'] ?? '',
				'post_content' => $post_data['content'] ?? '',
				'post_excerpt' => $post_data['excerpt'] ?? '',
			);

			$post_id = wp_insert_post( $post_args, true );

			if ( ! is_wp_error( $post_id ) ) {
				++$imported;
			} else {
				++$skipped;
			}
		}

		unlink( $filepath );

		wp_send_json_success(
			array(
				'imported' => $imported,
				'skipped'  => $skipped,
				'total'    => count( $mapped ),
			)
		);
	}

	/**
	 * Parse import file based on format.
	 *
	 * @since 1.6093.1200
	 * @param  string $filepath Path to import file.
	 * @param  string $format File format (csv|json|xml).
	 * @return array|\WP_Error Parsed data or error.
	 */
	private static function parse_import_file( string $filepath, string $format ) {
		if ( 'csv' === $format ) {
			return self::parse_csv( $filepath );
		} elseif ( 'json' === $format ) {
			return self::parse_json( $filepath );
		} elseif ( 'xml' === $format ) {
			return self::parse_xml( $filepath );
		}

		return new \WP_Error( 'invalid_format', __( 'Unsupported file format', 'wpshadow' ) );
	}

	/**
	 * Parse CSV file.
	 *
	 * @since 1.6093.1200
	 * @param  string $filepath Path to CSV file.
	 * @return array Parsed data with columns and rows.
	 */
	private static function parse_csv( string $filepath ): array {
		$handle = fopen( $filepath, 'r' );
		if ( ! $handle ) {
			return new \WP_Error( 'file_read_error', __( 'Could not read file', 'wpshadow' ) );
		}

		$columns = fgetcsv( $handle );
		$rows    = array();

		while ( ( $data = fgetcsv( $handle ) ) !== false ) {
			$rows[] = $data;
		}

		fclose( $handle );

		return array(
			'columns' => $columns,
			'rows'    => $rows,
		);
	}

	/**
	 * Parse JSON file.
	 *
	 * @since 1.6093.1200
	 * @param  string $filepath Path to JSON file.
	 * @return array Parsed data.
	 */
	private static function parse_json( string $filepath ): array {
		$content = file_get_contents( $filepath );
		$data    = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error( 'json_parse_error', __( 'Invalid JSON format', 'wpshadow' ) );
		}

		$columns = ! empty( $data ) ? array_keys( $data[0] ) : array();

		return array(
			'columns' => $columns,
			'rows'    => $data,
		);
	}

	/**
	 * Parse XML file.
	 *
	 * @since 1.6093.1200
	 * @param  string $filepath Path to XML file.
	 * @return array Parsed data.
	 */
	private static function parse_xml( string $filepath ): array {
		$xml = simplexml_load_file( $filepath );

		if ( ! $xml ) {
			return new \WP_Error( 'xml_parse_error', __( 'Invalid XML format', 'wpshadow' ) );
		}

		$rows    = array();
		$columns = array();

		foreach ( $xml->children() as $item ) {
			$row = array();
			foreach ( $item as $key => $value ) {
				$row[ $key ] = (string) $value;
				if ( ! in_array( $key, $columns, true ) ) {
					$columns[] = $key;
				}
			}
			$rows[] = $row;
		}

		return array(
			'columns' => $columns,
			'rows'    => $rows,
		);
	}

	/**
	 * Map imported data to post fields.
	 *
	 * @since 1.6093.1200
	 * @param  array $rows Data rows.
	 * @param  array $columns Column names.
	 * @param  array $mapping Field mapping.
	 * @return array Mapped data.
	 */
	private static function map_data( array $rows, array $columns, array $mapping ): array {
		$mapped = array();

		foreach ( $rows as $row ) {
			$post_data = array();

			foreach ( $mapping as $wp_field => $csv_column ) {
				$column_index = array_search( $csv_column, $columns, true );
				if ( false !== $column_index && isset( $row[ $column_index ] ) ) {
					$post_data[ $wp_field ] = $row[ $column_index ];
				}
			}

			$mapped[] = $post_data;
		}

		return $mapped;
	}

	/**
	 * Get available post types.
	 *
	 * @since 1.6093.1200
	 * @return array Available post types.
	 */
	private static function get_available_post_types(): array {
		if ( class_exists( 'WPShadow\Content\Post_Types_Manager' ) ) {
			return \WPShadow\Content\Post_Types_Manager::get_available_post_types();
		}

		return array();
	}
}
