<?php
/**
 * Feature: Image Optimizer
 *
 * Automatic image compression and format conversion.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Image_Optimizer
 *
 * Automatic image compression, WebP conversion, and bulk optimization.
 */
final class WPS_Feature_Image_Optimizer extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'image-optimizer',
				'name'               => __( 'Image Optimizer', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Automatic image compression, WebP/AVIF conversion, and bulk optimization for faster page loads', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'media',
				'widget_label'       => __( 'Media Optimization', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Image and media optimization tools', 'plugin-wp-support-thisismyurl' ),
				'license_level'      => 2,
				'minimum_capability' => 'upload_files',
				'icon'               => 'dashicons-format-image',
				'category'           => 'performance',
				'priority'           => 15,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 15,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Hook into upload process.
		add_filter( 'wp_handle_upload', array( $this, 'optimize_on_upload' ), 10, 2 );

		// Media Library columns.
		add_filter( 'manage_media_columns', array( $this, 'add_optimization_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'render_optimization_column' ), 10, 2 );

		// Bulk actions.
		add_filter( 'bulk_actions-upload', array( $this, 'add_bulk_actions' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_optimize_image', array( $this, 'ajax_optimize_image' ) );

		// Scheduled optimization.
		if ( ! wp_next_scheduled( 'wps_scheduled_image_optimization' ) ) {
			wp_schedule_event( time(), 'hourly', 'wps_scheduled_image_optimization' );
		}
		add_action( 'wps_scheduled_image_optimization', array( $this, 'process_optimization_queue' ) );
	}

	/**
	 * Optimize image on upload.
	 *
	 * @param array  $upload Upload data.
	 * @param string $context Upload context.
	 * @return array Modified upload data.
	 */
	public function optimize_on_upload( array $upload, string $context ): array {
		if ( ! isset( $upload['file'] ) || ! $this->is_image_file( $upload['file'] ) ) {
			return $upload;
		}

		$settings = $this->get_optimization_settings();
		if ( ! $settings['auto_optimize'] ) {
			return $upload;
		}

		// Backup original if configured.
		if ( $settings['backup_original'] ) {
			$this->backup_image( $upload['file'] );
		}

		// Compress image.
		$result = $this->compress_image( $upload['file'], $settings['compression_level'] );

		if ( ! is_wp_error( $result ) ) {
			update_post_meta( $upload['id'], '_wps_optimized', time() );
		}

		return $upload;
	}

	/**
	 * Add optimization column to Media Library.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_optimization_column( array $columns ): array {
		$columns['wps_optimization'] = __( 'Optimized', 'plugin-wp-support-thisismyurl' );
		return $columns;
	}

	/**
	 * Render optimization column content.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_optimization_column( string $column_name, int $post_id ): void {
		if ( 'wps_optimization' !== $column_name ) {
			return;
		}

		$optimized = get_post_meta( $post_id, '_wps_optimized', true );
		if ( $optimized ) {
			echo '<span class="dashicons dashicons-yes-alt" style="color:green;"></span>';
		} else {
			echo '<button class="button button-small wps-optimize-btn" data-id="' . esc_attr( (string) $post_id ) . '">' .
				esc_html__( 'Optimize', 'plugin-wp-support-thisismyurl' ) . '</button>';
		}
	}

	/**
	 * Add bulk actions.
	 *
	 * @param array $actions Existing actions.
	 * @return array Modified actions.
	 */
	public function add_bulk_actions( array $actions ): array {
		$actions['wps_optimize_images'] = __( 'Optimize Images', 'plugin-wp-support-thisismyurl' );
		return $actions;
	}

	/**
	 * AJAX handler for image optimization.
	 *
	 * @return void
	 */
	public function ajax_optimize_image(): void {
		check_ajax_referer( 'wps-optimize-image', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;
		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid attachment ID', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! $this->is_image_file( $file_path ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid image file', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$settings = $this->get_optimization_settings();

		// Backup original.
		if ( $settings['backup_original'] ) {
			$this->backup_image( $file_path );
		}

		// Compress.
		$result = $this->compress_image( $file_path, $settings['compression_level'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		update_post_meta( $attachment_id, '_wps_optimized', time() );

		wp_send_json_success( array(
			'message' => __( 'Image optimized successfully', 'plugin-wp-support-thisismyurl' ),
		) );
	}

	/**
	 * Process optimization queue.
	 *
	 * @return void
	 */
	public function process_optimization_queue(): void {
		$queue = get_option( 'wps_optimization_queue', array() );
		
		if ( empty( $queue ) ) {
			return;
		}

		// Process first 10 images.
		$batch = array_slice( $queue, 0, 10 );
		$settings = $this->get_optimization_settings();

		foreach ( $batch as $attachment_id ) {
			$file_path = get_attached_file( $attachment_id );
			if ( $file_path && $this->is_image_file( $file_path ) ) {
				if ( $settings['backup_original'] ) {
					$this->backup_image( $file_path );
				}
				$this->compress_image( $file_path, $settings['compression_level'] );
				update_post_meta( $attachment_id, '_wps_optimized', time() );
			}
		}

		// Update queue.
		$remaining = array_slice( $queue, 10 );
		update_option( 'wps_optimization_queue', $remaining );
	}

	/**
	 * Get optimization settings.
	 *
	 * @return array Optimization settings.
	 */
	private function get_optimization_settings(): array {
		return array(
			'compression_level' => 85,
			'enable_webp'       => true,
			'enable_avif'       => false,
			'strip_metadata'    => true,
			'backup_original'   => true,
			'auto_optimize'     => true,
			'max_width'         => 0,
			'max_height'        => 0,
		);
	}

	/**
	 * Check if image library is available.
	 *
	 * @return string|false Library name or false.
	 */
	private function get_available_library(): string|false {
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			return 'imagick';
		}

		if ( extension_loaded( 'gd' ) && function_exists( 'imagecreatefromjpeg' ) ) {
			return 'gd';
		}

		return false;
	}

	/**
	 * Compress image using available library.
	 *
	 * @param string $file_path Path to image file.
	 * @param int    $quality Compression quality (0-100).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function compress_image( string $file_path, int $quality ): bool|\WP_Error {
		$library = $this->get_available_library();

		if ( ! $library ) {
			return new \WP_Error( 'no_library', __( 'No image library available (GD or Imagick)', 'plugin-wp-support-thisismyurl' ) );
		}

		$image_type = wp_check_filetype( $file_path );

		if ( 'imagick' === $library ) {
			return $this->compress_with_imagick( $file_path, $quality, $image_type['type'] );
		}

		return $this->compress_with_gd( $file_path, $quality, $image_type['type'] );
	}

	/**
	 * Compress image using Imagick.
	 *
	 * @param string $file_path File path.
	 * @param int    $quality Quality.
	 * @param string $mime_type MIME type.
	 * @return bool|WP_Error Result.
	 */
	private function compress_with_imagick( string $file_path, int $quality, string $mime_type ): bool|\WP_Error {
		try {
			$image = new \Imagick( $file_path );
			$image->setImageCompressionQuality( $quality );
			$image->stripImage(); // Remove metadata.
			$image->writeImage( $file_path );
			$image->clear();
			$image->destroy();
			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'imagick_error', $e->getMessage() );
		}
	}

	/**
	 * Compress image using GD.
	 *
	 * @param string $file_path File path.
	 * @param int    $quality Quality.
	 * @param string $mime_type MIME type.
	 * @return bool|WP_Error Result.
	 */
	private function compress_with_gd( string $file_path, int $quality, string $mime_type ): bool|\WP_Error {
		$image = null;

		switch ( $mime_type ) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg( $file_path );
				break;
			case 'image/png':
				$image = imagecreatefrompng( $file_path );
				break;
			case 'image/gif':
				$image = imagecreatefromgif( $file_path );
				break;
			default:
				return new \WP_Error( 'unsupported_type', __( 'Unsupported image type', 'plugin-wp-support-thisismyurl' ) );
		}

		if ( ! $image ) {
			return new \WP_Error( 'gd_error', __( 'Failed to load image with GD', 'plugin-wp-support-thisismyurl' ) );
		}

		// Save compressed image.
		switch ( $mime_type ) {
			case 'image/jpeg':
				imagejpeg( $image, $file_path, $quality );
				break;
			case 'image/png':
				$png_quality = (int) ( ( 100 - $quality ) / 11.111111 );
				imagepng( $image, $file_path, $png_quality );
				break;
			case 'image/gif':
				imagegif( $image, $file_path );
				break;
		}

		imagedestroy( $image );
		return true;
	}

	/**
	 * Backup image before optimization.
	 *
	 * @param string $file_path Path to image file.
	 * @return bool Success.
	 */
	private function backup_image( string $file_path ): bool {
		$backup_path = $file_path . '.wps-backup';
		return copy( $file_path, $backup_path );
	}

	/**
	 * Check if file is an image.
	 *
	 * @param string $file_path File path.
	 * @return bool True if image.
	 */
	private function is_image_file( string $file_path ): bool {
		$image_type = wp_check_filetype( $file_path );
		return strpos( $image_type['type'], 'image/' ) === 0;
	}
}
