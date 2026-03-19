<?php
/**
 * Mobile Search Performance
 *
 * Validates search functionality performance on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Performance
 *
 * Ensures search results load quickly on mobile and provide
 * relevant results without excessive page weight.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Search_Performance extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-performance';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Performance';

	/**

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
