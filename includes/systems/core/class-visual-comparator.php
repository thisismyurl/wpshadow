<?php
/**
 * Visual Comparator Class
 *
 * Handles visual comparison of pages before and after treatments are applied.
 * Takes screenshots, stores them, and provides comparison capabilities.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Comparator Class
 *
 * Captures visual snapshots of pages and compares them to detect changes.
 */
class Visual_Comparator {
	/**
	 * Database table name for storing comparison records
	 */
	const TABLE_NAME = 'wpshadow_visual_comparisons';

	/**
	 * Option key for comparison ID index.
	 */
	const OPTION_COMPARISON_IDS = 'wpshadow_visual_comparison_ids';

	/**
	 * Option key for next comparison ID.
	 */
	const OPTION_NEXT_COMPARISON_ID = 'wpshadow_visual_comparison_next_id';

	/**
	 * Directory name for storing screenshots
	 */
	const SCREENSHOT_DIR = 'wpshadow-screenshots';

	/**
	 * Initialize the visual comparator
	 *
	 * @return void
	 */
	public static function init() {
		// Hook into treatment lifecycle
		add_action( 'wpshadow_before_treatment_apply', array( __CLASS__, 'capture_before_screenshot' ), 10, 2 );
		add_action( 'wpshadow_after_treatment_apply', array( __CLASS__, 'capture_after_screenshot' ), 10, 3 );

		// Register database table
		add_action( 'admin_init', array( __CLASS__, 'maybe_create_table' ) );
	}

	/**
	 * Create database table if it doesn't exist
	 *
	 * @return void
	 */
	public static function maybe_create_table() {
		if ( false === get_option( self::OPTION_COMPARISON_IDS, false ) ) {
			update_option( self::OPTION_COMPARISON_IDS, array(), false );
		}

		if ( false === get_option( self::OPTION_NEXT_COMPARISON_ID, false ) ) {
			update_option( self::OPTION_NEXT_COMPARISON_ID, 1, false );
		}
	}

	/**
	 * Check if visual comparison is enabled
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) get_option( 'wpshadow_visual_comparison_enabled', true );
	}

	/**
	 * Capture "before" screenshot when treatment is about to be applied
	 *
	 * @param string $class Treatment class name.
	 * @param string $finding_id Finding identifier.
	 * @return void
	 */
	public static function capture_before_screenshot( $class, $finding_id ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		$page_url        = home_url( '/' );
		$screenshot_path = self::capture_screenshot( $page_url, 'before' );

		if ( $screenshot_path ) {
			// Store in cache for later use in after hook
			\WPShadow\Core\Cache_Manager::set(
				'before_screenshot_' . $finding_id,
				array(
					'path'  => $screenshot_path,
					'url'   => $page_url,
					'class' => $class,
				),
				HOUR_IN_SECONDS,
				'wpshadow_visual'
				);
		}
	}

	/**
	 * Capture "after" screenshot when treatment has been applied
	 *
	 * @param string $class Treatment class name.
	 * @param string $finding_id Finding identifier.
	 * @param array  $result Treatment result.
	 * @return void
	 */
	public static function capture_after_screenshot( $class, $finding_id, $result ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		// Only capture after screenshot if treatment was successful
		if ( empty( $result['success'] ) ) {
			// Clean up before screenshot transient
			\WPShadow\Core\Cache_Manager::delete(
				'before_screenshot_' . $finding_id,
				'wpshadow_visual'
			);
			return;
		}

		// Get before screenshot data
		$before_data = \WPShadow\Core\Cache_Manager::get(
			'before_screenshot_' . $finding_id,
			'wpshadow_visual'
		);
		if ( ! $before_data ) {
			return;
		}

		$page_url        = home_url( '/' );
		$screenshot_path = self::capture_screenshot( $page_url, 'after' );

		if ( $screenshot_path ) {
			// Store comparison record
			self::store_comparison(
				$finding_id,
				$class,
				$before_data['path'],
				$screenshot_path,
				$page_url
			);
		}

		// Clean up cache
		\WPShadow\Core\Cache_Manager::delete(
			'before_screenshot_' . $finding_id,
			'wpshadow_visual'
		);
	}

	/**
	 * Capture a screenshot of a URL
	 *
	 * @param string $url URL to capture.
	 * @param string $type Screenshot type (before/after).
	 * @return string|false Screenshot file path on success, false on failure.
	 */
	private static function capture_screenshot( $url, $type = 'before' ) {
		// Create screenshots directory if it doesn't exist
		$upload_dir     = wp_upload_dir();
		$screenshot_dir = trailingslashit( $upload_dir['basedir'] ) . self::SCREENSHOT_DIR;

		if ( ! file_exists( $screenshot_dir ) ) {
			wp_mkdir_p( $screenshot_dir );
		}

		// Generate unique filename
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( empty( $host ) ) {
			$host = 'site';
		}
		$filename = sprintf(
			'%s-%s-%s.png',
			sanitize_title( $host ),
			$type,
			gmdate( 'Y-m-d-His' )
		);
		$filepath = trailingslashit( $screenshot_dir ) . $filename;

		// Use WordPress screenshot API if available, otherwise use basic capture
		$captured = self::perform_screenshot_capture( $url, $filepath );

		return $captured ? $filepath : false;
	}

	/**
	 * Perform the actual screenshot capture
	 *
	 * This is a placeholder implementation. In production, this would:
	 * - Use a headless browser service (Puppeteer, Playwright)
	 * - Call a screenshot API (like ScreenshotAPI.net)
	 * - Use browser automation tools
	 *
	 * For now, we'll create a placeholder image to demonstrate the feature.
	 *
	 * @param string $url URL to capture.
	 * @param string $filepath Where to save the screenshot.
	 * @return bool True on success, false on failure.
	 */
	private static function perform_screenshot_capture( $url, $filepath ) {
		// Create a placeholder image (400x300 PNG)
		// In production, this would be replaced with actual screenshot logic
		$width  = 1200;
		$height = 800;
		$image  = imagecreatetruecolor( $width, $height );

		if ( ! $image ) {
			return false;
		}

		// Set background color (light gray)
		$bg_color = imagecolorallocate( $image, 240, 240, 240 );
		imagefill( $image, 0, 0, $bg_color );

		// Add text showing this is a placeholder
		$text_color = imagecolorallocate( $image, 100, 100, 100 );
		$text       = 'Screenshot Placeholder';
		$text2      = esc_url( $url );
		$text3      = gmdate( 'Y-m-d H:i:s' );

		// Add text to image (requires GD with FreeType support)
		if ( function_exists( 'imagettftext' ) ) {
			// Try to use system font
			$font_path = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
			if ( file_exists( $font_path ) ) {
				imagettftext( $image, 24, 0, 50, 100, $text_color, $font_path, $text );
				imagettftext( $image, 14, 0, 50, 150, $text_color, $font_path, $text2 );
				imagettftext( $image, 12, 0, 50, 180, $text_color, $font_path, $text3 );
			}
		}

		// Save the image
		$result = imagepng( $image, $filepath );
		imagedestroy( $image );

		return $result;
	}

	/**
	 * Store comparison record in database
	 *
	 * @param string $finding_id Finding ID.
	 * @param string $treatment_class Treatment class name.
	 * @param string $before_path Path to before screenshot.
	 * @param string $after_path Path to after screenshot.
	 * @param string $page_url URL of the page.
	 * @return int|false Database insert ID on success, false on failure.
	 */
	private static function store_comparison( $finding_id, $treatment_class, $before_path, $after_path, $page_url ) {
		$upload_dir     = wp_upload_dir();
		$screenshot_dir = trailingslashit( $upload_dir['basedir'] ) . self::SCREENSHOT_DIR;

		// Convert file paths to URLs
		$before_url = str_replace(
			$screenshot_dir,
			trailingslashit( $upload_dir['baseurl'] ) . self::SCREENSHOT_DIR . '/',
			$before_path
		);
		$after_url  = str_replace(
			$screenshot_dir,
			trailingslashit( $upload_dir['baseurl'] ) . self::SCREENSHOT_DIR . '/',
			$after_path
		);

		// Calculate basic diff data (in production, this would use image comparison libraries)
		$diff_data = wp_json_encode(
			array(
				'method'      => 'placeholder',
				'differences' => 'Visual comparison pending implementation',
			)
		);

		$comparison_id = self::get_next_comparison_id();
		$comparison    = array(
			'id'              => $comparison_id,
			'finding_id'      => sanitize_key( $finding_id ),
			'treatment_class' => sanitize_text_field( $treatment_class ),
			'before_url'      => esc_url_raw( $before_url ),
			'after_url'       => esc_url_raw( $after_url ),
			'before_path'     => $before_path,
			'after_path'      => $after_path,
			'page_url'        => esc_url_raw( $page_url ),
			'diff_data'       => $diff_data,
			'created_at'      => current_time( 'mysql' ),
		);

		$saved = update_option( self::get_comparison_option_key( $comparison_id ), $comparison, false );
		if ( ! $saved ) {
			return false;
		}

		$ids = get_option( self::OPTION_COMPARISON_IDS, array() );
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		array_unshift( $ids, $comparison_id );
		update_option( self::OPTION_COMPARISON_IDS, array_values( array_unique( array_map( 'absint', $ids ) ) ), false );

		return $comparison_id;
	}

	/**
	 * Get all comparison records
	 *
	 * @param array $args Query arguments.
	 * @return array Array of comparison records.
	 */
	public static function get_comparisons( $args = array() ) {
		$defaults = array(
			'limit'      => 50,
			'offset'     => 0,
			'finding_id' => null,
			'orderby'    => 'created_at',
			'order'      => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );
		$ids  = get_option( self::OPTION_COMPARISON_IDS, array() );

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return array();
		}

		$records = array();
		foreach ( $ids as $comparison_id ) {
			$record = self::get_comparison( (int) $comparison_id );
			if ( null === $record ) {
				continue;
			}

			if ( ! empty( $args['finding_id'] ) && $record['finding_id'] !== $args['finding_id'] ) {
				continue;
			}

			$records[] = $record;
		}

		$allowed_orderby = array( 'created_at', 'id', 'finding_id' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

		usort(
			$records,
			function ( $left, $right ) use ( $orderby, $order ) {
				$left_value  = (string) ( $left[ $orderby ] ?? '' );
				$right_value = (string) ( $right[ $orderby ] ?? '' );
				$cmp         = strcmp( $left_value, $right_value );

				if ( 'ASC' === $order ) {
					return $cmp;
				}

				return -1 * $cmp;
			}
		);

		return array_slice( $records, max( 0, (int) $args['offset'] ), max( 1, (int) $args['limit'] ) );
	}

	/**
	 * Get a single comparison by ID
	 *
	 * @param int $id Comparison ID.
	 * @return array|null Comparison record or null if not found.
	 */
	public static function get_comparison( $id ) {
		$result = get_option( self::get_comparison_option_key( absint( $id ) ), null );

		if ( ! is_array( $result ) ) {
			return null;
		}

		$result['id'] = (int) ( $result['id'] ?? 0 );

		return $result;
	}

	/**
	 * Delete old comparison records and screenshots
	 *
	 * @param int $days Number of days to keep records (default: 30).
	 * @return int Number of records deleted.
	 */
	public static function cleanup_old_comparisons( $days = 30 ) {
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$ids         = get_option( self::OPTION_COMPARISON_IDS, array() );
		$old_records = array();

		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		foreach ( $ids as $comparison_id ) {
			$record = self::get_comparison( (int) $comparison_id );
			if ( null === $record ) {
				continue;
			}

			if ( isset( $record['created_at'] ) && $record['created_at'] < $cutoff_date ) {
				$old_records[] = $record;
			}
		}

		// Delete screenshot files
		foreach ( $old_records as $record ) {
			if ( ! empty( $record['before_path'] ) && file_exists( $record['before_path'] ) ) {
				wp_delete_file( $record['before_path'] );
			}
			if ( ! empty( $record['after_path'] ) && file_exists( $record['after_path'] ) ) {
				wp_delete_file( $record['after_path'] );
			}
		}

		$deleted       = 0;
		$remaining_ids = array();

		foreach ( $ids as $comparison_id ) {
			$record = self::get_comparison( (int) $comparison_id );
			if ( null === $record ) {
				continue;
			}

			if ( isset( $record['created_at'] ) && $record['created_at'] < $cutoff_date ) {
				delete_option( self::get_comparison_option_key( (int) $comparison_id ) );
				$deleted++;
				continue;
			}

			$remaining_ids[] = (int) $comparison_id;
		}

		update_option( self::OPTION_COMPARISON_IDS, $remaining_ids, false );

		return $deleted;
	}

	/**
	 * Get comparison statistics
	 *
	 * @return array Statistics about comparisons.
	 */
	public static function get_statistics() {
		$records       = self::get_comparisons( array( 'limit' => PHP_INT_MAX, 'offset' => 0 ) );
		$total         = count( $records );
		$cutoff        = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		$last_30_days  = 0;

		foreach ( $records as $record ) {
			if ( isset( $record['created_at'] ) && $record['created_at'] > $cutoff ) {
				$last_30_days++;
			}
		}

		return array(
			'total'        => (int) $total,
			'last_30_days' => (int) $last_30_days,
		);
	}

	/**
	 * Get option key for a comparison record.
	 *
	 * @param  int $comparison_id Comparison ID.
	 * @return string Option key.
	 */
	private static function get_comparison_option_key( $comparison_id ) {
		return 'wpshadow_visual_comparison_' . absint( $comparison_id );
	}

	/**
	 * Get next comparison ID.
	 *
	 * @return int Next ID.
	 */
	private static function get_next_comparison_id() {
		$next_id = (int) get_option( self::OPTION_NEXT_COMPARISON_ID, 1 );
		if ( $next_id < 1 ) {
			$next_id = 1;
		}

		update_option( self::OPTION_NEXT_COMPARISON_ID, $next_id + 1, false );

		return $next_id;
	}
}
