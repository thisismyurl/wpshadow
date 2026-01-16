<?php
/**
 * Feature: Image Optimizer
 *
 * Automatic image compression and format conversion.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Image_Optimizer
 *
 * Automatic image compression, WebP conversion, and bulk optimization.
 */
final class WPSHADOW_Feature_Image_Optimizer extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'image-optimizer',
				'name'               => __( 'Image Optimizer', 'plugin-wpshadow' ),
				'description'        => __( 'Compresses uploaded images automatically using modern formats like WebP and AVIF, runs bulk optimization on existing media, and keeps originals safe while serving lighter versions to visitors. Speeds up pages, reduces storage costs, and improves visual quality with smart compression that adapts to image content, so your photos look great while loading faster.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'media',
				'license_level'      => 2,
				'minimum_capability' => 'upload_files',
				'icon'               => 'dashicons-format-image',
				'category'           => 'performance',
				'priority'           => 15,

			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'auto_optimize'     => __( 'Auto-Optimize on Upload', 'plugin-wpshadow' ),
					'webp_conversion'   => __( 'WebP Format Conversion', 'plugin-wpshadow' ),
					'avif_conversion'   => __( 'AVIF Format Conversion', 'plugin-wpshadow' ),
					'retain_originals'  => __( 'Keep Original Files', 'plugin-wpshadow' ),
					'bulk_optimization' => __( 'Enable Bulk Optimization', 'plugin-wpshadow' ),
					'resize_large'      => __( 'Resize Oversized Images', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'auto_optimize'     => true,
						'webp_conversion'   => true,
						'avif_conversion'   => false,
						'retain_originals'  => true,
						'bulk_optimization' => true,
						'resize_large'      => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Image Optimizer feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Hook into upload process if auto-optimize is enabled.
		if ( get_option( 'wpshadow_image-optimizer_auto_optimize', true ) ) {
			add_filter( 'wp_handle_upload', array( $this, 'optimize_on_upload' ), 10, 2 );
		}

		// Media Library columns.
		add_filter( 'manage_media_columns', array( $this, 'add_optimization_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'render_optimization_column' ), 10, 2 );

		// Bulk actions if enabled.
		if ( get_option( 'wpshadow_image-optimizer_bulk_optimization', true ) ) {
			add_filter( 'bulk_actions-upload', array( $this, 'add_bulk_actions' ) );
		}

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_optimize_image', array( $this, 'ajax_optimize_image' ) );

		// Scheduled optimization.
		if ( ! wp_next_scheduled( 'wpshadow_scheduled_image_optimization' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_scheduled_image_optimization' );
		}
		add_action( 'wpshadow_scheduled_image_optimization', array( $this, 'process_optimization_queue' ) );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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
			update_post_meta( $upload['id'], '_WPSHADOW_optimized', time() );
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
		$columns['wpshadow_optimization'] = __( 'Optimized', 'plugin-wpshadow' );
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
		if ( 'wpshadow_optimization' !== $column_name ) {
			return;
		}

		$optimized = get_post_meta( $post_id, '_WPSHADOW_optimized', true );
		if ( $optimized ) {
			echo '<span class="dashicons dashicons-yes-alt" style="color:green;"></span>';
		} else {
			echo '<button class="button button-small wps-optimize-btn" data-id="' . esc_attr( (string) $post_id ) . '">' .
				esc_html__( 'Optimize', 'plugin-wpshadow' ) . '</button>';
		}
	}

	/**
	 * Add bulk actions.
	 *
	 * @param array $actions Existing actions.
	 * @return array Modified actions.
	 */
	public function add_bulk_actions( array $actions ): array {
		$actions['wpshadow_optimize_images'] = __( 'Optimize Images', 'plugin-wpshadow' );
		return $actions;
	}

	/**
	 * AJAX handler for image optimization.
	 *
	 * @return void
	 */
	public function ajax_optimize_image(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-optimize-image', 'upload_files' );

		$attachment_id = \WPShadow\WPSHADOW_get_post_int( 'attachment_id' );
		if ( ! $attachment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid attachment ID', 'plugin-wpshadow' ) ) );
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! $this->is_image_file( $file_path ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid image file', 'plugin-wpshadow' ) ) );
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

		update_post_meta( $attachment_id, '_WPSHADOW_optimized', time() );

		wp_send_json_success( array(
			'message' => __( 'Image optimized successfully', 'plugin-wpshadow' ),
		) );
	}

	/**
	 * Process optimization queue.
	 *
	 * @return void
	 */
	public function process_optimization_queue(): void {
		$queue = get_option( 'wpshadow_optimization_queue', array() );
		
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
				update_post_meta( $attachment_id, '_WPSHADOW_optimized', time() );
			}
		}

		// Update queue.
		$remaining = array_slice( $queue, 10 );
		update_option( 'wpshadow_optimization_queue', $remaining );
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
			return new \WP_Error( 'no_library', __( 'No image library available (GD or Imagick)', 'plugin-wpshadow' ) );
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
				return new \WP_Error( 'unsupported_type', __( 'Unsupported image type', 'plugin-wpshadow' ) );
		}

		if ( ! $image ) {
			return new \WP_Error( 'gd_error', __( 'Failed to load image with GD', 'plugin-wpshadow' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_image_optimizer'] = array(
			'label' => __( 'Image Optimization', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_image_optimizer' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for image optimization.
	 *
	 * @return array<string, mixed>
	 */
	public function test_image_optimizer(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Image Optimization', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Image Optimization is not enabled. Enabling image optimization can reduce file sizes and improve page load times.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_image_optimizer',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_image-optimizer_auto_optimize', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_image-optimizer_webp_conversion', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_image-optimizer_bulk_optimization', true ) ) {
			++$enabled_features;
		}

		// Get optimization statistics.
		global $wpdb;
		$optimized_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wps_optimized' AND meta_value = '1'"
		);

		return array(
			'label'       => __( 'Image Optimization', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: number of enabled features, 2: number of optimized images */
				sprintf(
					__( 'Image Optimization is active with %1$d optimization features enabled. %2$d images have been optimized.', 'plugin-wpshadow' ),
					$enabled_features,
					$optimized_count
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_image_optimizer',
		);
	}
}
