<?php
/**
 * WPShadow Production Memory Fix
 *
 * Emergency fix for wp_options autoload bloat causing memory exhaustion.
 * Run via: wp-cli or upload as must-use plugin
 *
 * Usage (WP-CLI):
 *   wp eval-file fix-production-memory-issue.php
 *
 * Usage (Browser):
 *   Upload to wp-content/mu-plugins/
 *   Visit: yoursite.com/?wpshadow_fix_memory=1&key=CHANGE_THIS_SECRET_KEY
 *
 * @package WPShadow
 * @since   1.26028.1905
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Allow CLI execution.
	if ( PHP_SAPI === 'cli' ) {
		define( 'ABSPATH', dirname( __FILE__, 4 ) . '/' );
		require_once ABSPATH . 'wp-load.php';
	} else {
		exit;
	}
}

/**
 * Emergency Memory Fix
 */
class WPShadow_Emergency_Memory_Fix {

	/**
	 * Secret key for browser access
	 *
	 * CHANGE THIS before using!
	 */
	const SECRET_KEY = 'CHANGE_THIS_SECRET_KEY_2026';

	/**
	 * Run the fix
	 */
	public static function run() {
		// Security check for browser access.
		if ( PHP_SAPI !== 'cli' ) {
			if ( ! isset( $_GET['wpshadow_fix_memory'] ) || ! isset( $_GET['key'] ) ) {
				wp_die( 'Invalid access' );
			}

			if ( $_GET['key'] !== self::SECRET_KEY ) {
				wp_die( 'Invalid key' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Insufficient permissions' );
			}
		}

		echo "WPShadow Emergency Memory Fix\n";
		echo "==============================\n\n";

		// Step 1: Diagnose.
		echo "Step 1: Diagnosing autoload bloat...\n";
		$before = self::get_autoload_stats();
		self::display_stats( 'BEFORE', $before );

		// Step 2: Backup.
		echo "\nStep 2: Creating backup...\n";
		$backup_result = self::create_backup();
		if ( ! $backup_result['success'] ) {
			echo "ERROR: Backup failed - {$backup_result['message']}\n";
			echo "Fix aborted for safety.\n";
			return;
		}
		echo "✓ Backup created: {$backup_result['table']}\n";

		// Step 3: Identify culprits.
		echo "\nStep 3: Identifying largest autoloaded options...\n";
		$culprits = self::get_largest_autoloaded();
		foreach ( $culprits as $culprit ) {
			printf(
				"  - %s: %s (%s)\n",
				$culprit['option_name'],
				size_format( $culprit['size_bytes'] ),
				self::get_recommendation( $culprit['option_name'] )
			);
		}

		// Step 4: Fix transients.
		echo "\nStep 4: Fixing transients (should NEVER autoload)...\n";
		$transient_fixed = self::fix_transients();
		echo "✓ Fixed {$transient_fixed} transient options\n";

		// Step 5: Fix common plugin options.
		echo "\nStep 5: Fixing common plugin options...\n";
		$plugin_fixed = self::fix_plugin_options();
		echo "✓ Fixed {$plugin_fixed} plugin options\n";

		// Step 6: Fix large options.
		echo "\nStep 6: Fixing large options (>100KB)...\n";
		$large_fixed = self::fix_large_options();
		echo "✓ Fixed {$large_fixed} large options\n";

		// Step 7: Clean expired transients.
		echo "\nStep 7: Cleaning expired transients...\n";
		$expired_deleted = self::clean_expired_transients();
		echo "✓ Deleted {$expired_deleted} expired transients\n";

		// Step 8: Verify.
		echo "\nStep 8: Verifying fix...\n";
		wp_cache_flush(); // Clear object cache.
		$after = self::get_autoload_stats();
		self::display_stats( 'AFTER', $after );

		// Step 9: Calculate improvement.
		echo "\nResults:\n";
		echo "========\n";
		$memory_saved = $before['autoload_bytes'] - $after['autoload_bytes'];
		$percent_saved = round( ( $memory_saved / $before['autoload_bytes'] ) * 100, 2 );
		echo "Memory saved: " . size_format( $memory_saved ) . " ({$percent_saved}%)\n";
		echo "Options reduced: " . ( $before['autoload_count'] - $after['autoload_count'] ) . "\n";
		echo "\n✓ Fix complete! Clear all caches now.\n";
		echo "\nRecommendations:\n";
		echo "- Clear object cache (Redis/Memcached)\n";
		echo "- Clear page cache\n";
		echo "- Clear CDN cache if applicable\n";
		echo "- Monitor error logs for 24 hours\n";
		echo "- Test all critical pages\n";
	}

	/**
	 * Get autoload statistics
	 */
	private static function get_autoload_stats() {
		global $wpdb;

		return $wpdb->get_row(
			"SELECT 
				COUNT(*) as autoload_count,
				SUM(LENGTH(option_value)) as autoload_bytes
			FROM {$wpdb->options}
			WHERE autoload = 'yes'",
			ARRAY_A
		);
	}

	/**
	 * Display statistics
	 */
	private static function display_stats( $label, $stats ) {
		printf(
			"%s: %d options, %s\n",
			$label,
			$stats['autoload_count'],
			size_format( $stats['autoload_bytes'] )
		);
	}

	/**
	 * Get largest autoloaded options
	 */
	private static function get_largest_autoloaded() {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT 
				option_name,
				LENGTH(option_value) as size_bytes
			FROM {$wpdb->options}
			WHERE autoload = 'yes'
			ORDER BY size_bytes DESC
			LIMIT 20",
			ARRAY_A
		);
	}

	/**
	 * Get recommendation for option
	 */
	private static function get_recommendation( $option_name ) {
		if ( strpos( $option_name, 'transient' ) !== false ) {
			return 'transient - should never autoload';
		}

		if ( strpos( $option_name, 'widget_' ) === 0 ) {
			return 'widget - only needed on widget render';
		}

		if ( strpos( $option_name, 'wpseo' ) !== false ) {
			return 'Yoast SEO - load on-demand';
		}

		if ( strpos( $option_name, 'woocommerce' ) !== false ) {
			return 'WooCommerce - load on-demand';
		}

		if ( strpos( $option_name, 'elementor' ) !== false ) {
			return 'Elementor - load on-demand';
		}

		return 'safe to disable autoload';
	}

	/**
	 * Create backup
	 */
	private static function create_backup() {
		global $wpdb;

		$backup_table = $wpdb->options . '_backup_' . date( 'Ymd_His' );

		$result = $wpdb->query( "CREATE TABLE {$backup_table} AS SELECT * FROM {$wpdb->options}" );

		if ( false === $result ) {
			return array(
				'success' => false,
				'message' => $wpdb->last_error,
			);
		}

		return array(
			'success' => true,
			'table'   => $backup_table,
		);
	}

	/**
	 * Fix transients
	 */
	private static function fix_transients() {
		global $wpdb;

		return $wpdb->query(
			"UPDATE {$wpdb->options}
			SET autoload = 'no'
			WHERE autoload = 'yes'
			AND (
				option_name LIKE '_transient_%'
				OR option_name LIKE '_site_transient_%'
			)"
		);
	}

	/**
	 * Fix common plugin options
	 */
	private static function fix_plugin_options() {
		global $wpdb;

		$options_to_fix = array(
			'widget_recent-posts',
			'widget_recent-comments',
			'widget_archives',
			'widget_meta',
			'widget_search',
			'widget_text',
			'widget_categories',
			'widget_calendar',
			'widget_nav_menu',
			'widget_tag_cloud',
			'widget_pages',
			'widget_media_audio',
			'widget_media_image',
			'widget_media_video',
			'widget_media_gallery',
			'widget_custom_html',
			'sidebars_widgets',
			'wpseo_titles',
			'wpseo_social',
			'wpseo_xml',
			'woocommerce_meta_box_errors',
			'woocommerce_single_image_width',
			'woocommerce_thumbnail_image_width',
			'elementor_global_css',
			'elementor_container_width',
			'wordfence_blockedIPs',
			'wordfence_whitelisted',
			'akismet_spam_count',
			'akismet_comment_count',
		);

		$placeholders = implode( ',', array_fill( 0, count( $options_to_fix ), '%s' ) );

		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->options}
				SET autoload = 'no'
				WHERE autoload = 'yes'
				AND option_name IN ($placeholders)",
				$options_to_fix
			)
		);
	}

	/**
	 * Fix large options
	 */
	private static function fix_large_options() {
		global $wpdb;

		return $wpdb->query(
			"UPDATE {$wpdb->options}
			SET autoload = 'no'
			WHERE autoload = 'yes'
			AND LENGTH(option_value) > 102400"
		);
	}

	/**
	 * Clean expired transients
	 */
	private static function clean_expired_transients() {
		global $wpdb;

		$current_time = time();

		// Delete expired timeout entries.
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'_transient_timeout_%',
				$current_time
			)
		);

		// Delete orphaned transient values.
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'
			AND option_name NOT LIKE '_transient_timeout_%'
			AND option_name NOT IN (
				SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
				FROM {$wpdb->options}
				WHERE option_name LIKE '_transient_timeout_%'
			)"
		);

		return $deleted;
	}
}

// Execute if running via CLI or browser with key.
if ( PHP_SAPI === 'cli' || ( isset( $_GET['wpshadow_fix_memory'] ) && isset( $_GET['key'] ) ) ) {
	WPShadow_Emergency_Memory_Fix::run();
}
