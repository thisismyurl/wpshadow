<?php
/**
 * Mobile Search Performance
 *
 * Validates search functionality performance on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Performance
 *
 * Ensures search results load quickly on mobile and provide
 * relevant results without excessive page weight.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Search_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-performance';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Performance';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates search performance on mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_search_performance_issues();

		if ( empty( $issues['all'] ) ) {
			return null;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of search performance issues */
				__( 'Found %d mobile search performance issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'medium',
			'threat_level'    => 55,
			'issues'          => $issues['all'],
			'response_time'   => $issues['response_time'] ?? null,
			'user_impact'     => __( 'Search is slow on mobile, users may abandon', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/search-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Find search performance issues.
	 *
	 * @since 0.6093.1200
	 * @return array Issues found.
	 */
	private static function find_search_performance_issues(): array {
		$issues = array();

		// Test search performance
		$search_url = add_query_arg( 's', 'test', home_url( '/' ) );
		$start_time = microtime( true );

		$response = wp_remote_get(
			$search_url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		$response_time = microtime( true ) - $start_time;

		if ( is_wp_error( $response ) ) {
			$issues[] = array(
				'type'  => 'search-error',
				'issue' => 'Search request failed',
				'error' => $response->get_error_message(),
			);
			return array( 'all' => $issues );
		}

		// Check response time
		if ( $response_time > 3.0 ) {
			$issues[] = array(
				'type'          => 'slow-search',
				'response_time' => round( $response_time, 2 ),
				'threshold'     => 3.0,
				'issue'         => 'Search results load slowly on mobile',
			);
		}

		// Check page size
		$body = wp_remote_retrieve_body( $response );
		$page_size = strlen( $body ) / 1024; // KB

		if ( $page_size > 500 ) {
			$issues[] = array(
				'type'       => 'heavy-search-results',
				'page_size'  => round( $page_size, 0 ),
				'threshold'  => 500,
				'issue'      => 'Search results page too large for mobile',
			);
		}

		// Check for AJAX search
		$has_ajax_search = preg_match( '/ajax.*?search|search.*?ajax/i', $body );

		if ( ! $has_ajax_search ) {
			$issues[] = array(
				'type'  => 'no-ajax-search',
				'issue' => 'No AJAX search detected (full page reloads)',
			);
		}

		// Check for search result pagination
		$has_pagination = preg_match( '/class\s*=\s*["\'][^"\']*(?:pagination|paging)[^"\']*["\']/i', $body );

		if ( ! $has_pagination ) {
			$issues[] = array(
				'type'  => 'no-search-pagination',
				'issue' => 'Search results missing pagination',
			);
		}

		return array(
			'all'           => $issues,
			'response_time' => round( $response_time, 2 ),
		);
	}
}
