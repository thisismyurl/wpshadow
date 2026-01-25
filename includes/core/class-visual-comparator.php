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
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		// Check if table exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( $table_exists === $table_name ) {
			return;
		}

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			finding_id varchar(100) NOT NULL,
			treatment_class varchar(255) NOT NULL,
			before_url varchar(512) DEFAULT NULL,
			after_url varchar(512) DEFAULT NULL,
			before_path varchar(512) DEFAULT NULL,
			after_path varchar(512) DEFAULT NULL,
			page_url varchar(512) NOT NULL,
			diff_data longtext DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY finding_id (finding_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
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
			// Store in transient for later use in after hook
			set_transient(
				'wpshadow_before_screenshot_' . $finding_id,
				array(
					'path'  => $screenshot_path,
					'url'   => $page_url,
					'class' => $class,
				),
				HOUR_IN_SECONDS
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
			delete_transient( 'wpshadow_before_screenshot_' . $finding_id );
			return;
		}

		// Get before screenshot data
		$before_data = get_transient( 'wpshadow_before_screenshot_' . $finding_id );
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

		// Clean up transient
		delete_transient( 'wpshadow_before_screenshot_' . $finding_id );
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
		global $wpdb;

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

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table_name,
			array(
				'finding_id'      => $finding_id,
				'treatment_class' => $treatment_class,
				'before_url'      => $before_url,
				'after_url'       => $after_url,
				'before_path'     => $before_path,
				'after_path'      => $after_path,
				'page_url'        => $page_url,
				'diff_data'       => $diff_data,
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get all comparison records
	 *
	 * @param array $args Query arguments.
	 * @return array Array of comparison records.
	 */
	public static function get_comparisons( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'      => 50,
			'offset'     => 0,
			'finding_id' => null,
			'orderby'    => 'created_at',
			'order'      => 'DESC',
		);

		$args       = wp_parse_args( $args, $defaults );
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = '1=1';
		if ( $args['finding_id'] ) {
			$where .= $wpdb->prepare( ' AND finding_id = %s', $args['finding_id'] );
		}

		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		if ( ! $orderby ) {
			$orderby = 'created_at DESC';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE $where ORDER BY $orderby LIMIT %d OFFSET %d",
				$args['limit'],
				$args['offset']
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return array();
		}

		return $results;
	}

	/**
	 * Get a single comparison by ID
	 *
	 * @param int $id Comparison ID.
	 * @return array|null Comparison record or null if not found.
	 */
	public static function get_comparison( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( empty( $result ) ) {
			return null;
		}

		return $result;
	}

	/**
	 * Delete old comparison records and screenshots
	 *
	 * @param int $days Number of days to keep records (default: 30).
	 * @return int Number of records deleted.
	 */
	public static function cleanup_old_comparisons( $days = 30 ) {
		global $wpdb;

		$table_name  = $wpdb->prefix . self::TABLE_NAME;
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// Get old records to delete files
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$old_records = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE created_at < %s",
				$cutoff_date
			),
			ARRAY_A
		);

		// Delete screenshot files
		foreach ( $old_records as $record ) {
			if ( ! empty( $record['before_path'] ) && file_exists( $record['before_path'] ) ) {
				wp_delete_file( $record['before_path'] );
			}
			if ( ! empty( $record['after_path'] ) && file_exists( $record['after_path'] ) ) {
				wp_delete_file( $record['after_path'] );
			}
		}

		// Delete database records
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name WHERE created_at < %s",
				$cutoff_date
			)
		);

		return (int) $deleted;
	}

	/**
	 * Get comparison statistics
	 *
	 * @return array Statistics about comparisons.
	 */
	public static function get_statistics() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$last_30_days = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE created_at > %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		return array(
			'total'        => (int) $total,
			'last_30_days' => (int) $last_30_days,
		);
	}
}
