<?php
/**
 * Yoast SEO Performance Impact Assessment Diagnostic
 *
 * Verify Yoast SEO isn't causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6030.1305
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast SEO Performance Diagnostic Class
 *
 * @since 1.6030.1305
 */
class Diagnostic_YoastSeoPerformance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'yoast-seo-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Yoast SEO Performance Impact Assessment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Yoast SEO isn\'t causing performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1305
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Yoast SEO is active
		if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'WPSEO_Options' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check 1: Check Yoast admin scripts loading only on edit screens
		$disable_admin_bar = get_option( 'wpseo_disable_adminbar', false );

		if ( ! $disable_admin_bar ) {
			$issues[] = 'admin bar menu enabled (loads scripts on every admin page)';
		}

		// Check 2: Verify REST API calls optimized
		$rest_api_routes = rest_get_server()->get_routes();
		$yoast_routes = array_filter( array_keys( $rest_api_routes ), function( $route ) {
			return strpos( $route, 'yoast' ) !== false;
		});

		if ( count( $yoast_routes ) > 10 ) {
			$issues[] = sprintf( '%d Yoast REST API routes registered (potential overhead)', count( $yoast_routes ) );
		}

		// Check 3: Check database table optimization
		$indexables_table = $wpdb->prefix . 'yoast_indexable';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $indexables_table ) );

		if ( $table_exists ) {
			$indexables_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$indexables_table}" );

			if ( $indexables_count > 10000 ) {
				$issues[] = sprintf( '%d indexable records (large table may slow queries)', $indexables_count );
			}

			// Check for unindexed records
			$unindexed = $wpdb->get_var( "SELECT COUNT(*) FROM {$indexables_table} WHERE object_last_modified > object_published_at" );
			if ( $unindexed > 1000 ) {
				$issues[] = sprintf( '%d unindexed records (rebuild needed)', $unindexed );
			}
		}

		// Check 4: Verify indexables feature performance
		$indexables_enabled = get_option( 'wpseo_indexables_enabled', true );

		if ( $indexables_enabled ) {
			// Check last indexation time
			$indexation_started = get_option( 'wpseo_indexables_indexation_started', 0 );
			$indexation_completed = get_option( 'wpseo_indexables_indexation_completed', 0 );

			if ( $indexation_started && ! $indexation_completed ) {
				$issues[] = 'indexables rebuild in progress (may cause performance impact)';
			}
		}

		// Check 5: Test for excessive database queries
		/**
		 * NOTE: Using $wpdb for direct COUNT() query is intentional.
		 *
		 * WordPress alternative considered: wp_load_alloptions()
		 * Not suitable because:
		 * - We only need the count, not the actual option values
		 * - wp_load_alloptions() loads all options into memory (~800KB+)
		 * - Direct COUNT() returns single integer (4 bytes)
		 * - Pattern matching ('wpseo%') not supported by WordPress API
		 */
		$yoast_options_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE 'wpseo%'"
		);

		if ( $yoast_options_count > 50 ) {
			$issues[] = sprintf( '%d Yoast options in database (many autoloaded)', $yoast_options_count );
		}

		// Check 6: Verify no JavaScript errors from Yoast
		$has_premium = defined( 'WPSEO_PREMIUM_PLUGIN_FILE' );
		if ( $has_premium ) {
			// Premium version has more features that can cause issues
			$premium_features = get_option( 'wpseo_premium_features', array() );
			if ( count( $premium_features ) > 5 ) {
				$issues[] = sprintf( '%d premium features enabled (increases script weight)', count( $premium_features ) );
			}
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 50 + ( count( $issues ) * 6 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Yoast SEO performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-performance',
			);
		}

		return null;
	}
}
