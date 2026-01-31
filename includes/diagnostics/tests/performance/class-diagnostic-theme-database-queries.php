<?php
/**
 * Theme Database Query Performance Diagnostic
 *
 * Detects excessive or inefficient database queries in theme templates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Database Query Performance Diagnostic Class
 *
 * Analyzes database query count and efficiency on theme pages.
 *
 * @since 1.5049.1200
 */
class Diagnostic_Theme_Database_Queries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-database-queries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Database Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive database queries in theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Only check if SAVEQUERIES is available.
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			// Enable query saving temporarily.
			$wpdb->queries = array();
			define( 'SAVEQUERIES', true );
			$temp_enabled = true;
		} else {
			$temp_enabled = false;
		}

		// Get starting query count.
		$start_queries = ! empty( $wpdb->queries ) ? count( $wpdb->queries ) : 0;

		// Fetch homepage to count queries.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		// Get ending query count.
		$end_queries = ! empty( $wpdb->queries ) ? count( $wpdb->queries ) : 0;
		$query_count = $end_queries - $start_queries;

		$theme = wp_get_theme();
		$issues = array();

		// Thresholds for query counts.
		if ( $query_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queries */
				__( 'Homepage generates %d database queries (very high)', 'wpshadow' ),
				$query_count
			);
			$severity = 'high';
			$threat_level = 80;
		} elseif ( $query_count > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queries */
				__( 'Homepage generates %d database queries (high)', 'wpshadow' ),
				$query_count
			);
			$severity = 'medium';
			$threat_level = 60;
		}

		// Check for uncached queries (if we can detect them).
		if ( SAVEQUERIES && ! empty( $wpdb->queries ) ) {
			$uncached_count = 0;
			foreach ( $wpdb->queries as $query ) {
				if ( isset( $query[0] ) && preg_match( '/SELECT.*FROM.*WHERE/i', $query[0] ) ) {
					$uncached_count++;
				}
			}

			if ( $uncached_count > 20 ) {
				$issues[] = sprintf(
					/* translators: %d: number of uncached queries */
					__( '%d potentially uncached SELECT queries detected', 'wpshadow' ),
					$uncached_count
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: theme name */
					__( 'Theme "%s" may be performing excessive database queries', 'wpshadow' ),
					$theme->get( 'Name' )
				),
				'severity'    => $severity ?? 'medium',
				'threat_level' => $threat_level ?? 60,
				'auto_fixable' => false,
				'details'     => array(
					'theme'       => $theme->get( 'Name' ),
					'query_count' => $query_count,
					'issues'      => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-database-queries',
			);
		}

		return null;
	}
}
