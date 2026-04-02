<?php
/**
 * Search Function Effectiveness Diagnostic
 *
 * Checks whether search is visible and usable for visitors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Function Effectiveness Diagnostic Class
 *
 * Ensures visitors can find content through search.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Search_Function_Effectiveness extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-function-effectiveness';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Function Missing or Ineffective';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for visible search access and functional search results';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$issues = array();
		$meta   = array();

		$html = Diagnostic_HTML_Helper::fetch_homepage_html_cached( 'wpshadow_search_homepage', 300 );
		if ( null === $html ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'We could not evaluate search because the homepage HTML could not be fetched.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-function',
			);
		}

		$has_search_form = (bool) preg_match( '/<form[^>]*>.*?(type=["\']search["\']|name=["\']s["\'])/is', $html );
		$meta['has_search_form'] = $has_search_form;

		if ( ! $has_search_form ) {
			$issues[] = __( 'No search form detected on the homepage.', 'wpshadow' );
		}

		$search_template = get_query_template( 'search' );
		$meta['has_search_template'] = ! empty( $search_template );
		if ( empty( $search_template ) ) {
			$issues[] = __( 'Search results template not detected; results may be hard to use.', 'wpshadow' );
		}

		$relevance_plugins = array(
			'relevanssi/relevanssi.php'       => 'Relevanssi',
			'relevanssi-premium/relevanssi.php' => 'Relevanssi Premium',
			'searchwp/index.php'              => 'SearchWP',
		);

		$relevance_active = array();
		foreach ( $relevance_plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				$relevance_active[] = $label;
			}
		}
		$meta['relevance_plugins'] = $relevance_active;

		if ( empty( $relevance_active ) ) {
			$issues[] = __( 'No search relevance plugin detected; results may feel less helpful.', 'wpshadow' );
		}

		$search_response = wp_remote_get( add_query_arg( 's', 'test', home_url( '/' ) ), array( 'timeout' => 10 ) );
		if ( is_wp_error( $search_response ) ) {
			$issues[] = __( 'Search results page did not respond during a basic test.', 'wpshadow' );
		} else {
			$meta['search_status_code'] = wp_remote_retrieve_response_code( $search_response );
			$body = wp_remote_retrieve_body( $search_response );
			if ( '' === $body ) {
				$issues[] = __( 'Search results page returned empty content.', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Search can be hard to find or may not be delivering helpful results. Improving search helps visitors find what they need.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/search-function',
			'meta'         => array(
				'issues' => $issues,
				'details' => $meta,
			),
		);
	}
}
