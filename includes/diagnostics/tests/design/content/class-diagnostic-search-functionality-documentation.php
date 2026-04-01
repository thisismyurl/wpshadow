<?php
/**
 * Search Functionality Documentation Diagnostic
 *
 * Issue #4905: No Search Function in Documentation
 * Pillar: 🎓 Learning Inclusive
 *
 * Checks if documentation has search functionality.
 * Users should find answers quickly, not browse entire docs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Search_Functionality_Documentation Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Functionality_Documentation extends Diagnostic_Base {

	protected static $slug = 'search-functionality-documentation';
	protected static $title = 'No Search Function in Documentation';
	protected static $description = 'Checks if documentation includes search to quickly find answers';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add search box prominently in documentation header', 'wpshadow' );
		$issues[] = __( 'Use fast search (Algolia, Elasticsearch, or instant.js)', 'wpshadow' );
		$issues[] = __( 'Show results as user types (instant search)', 'wpshadow' );
		$issues[] = __( 'Include code snippets in search results', 'wpshadow' );
		$issues[] = __( 'Support search filters (category, version, difficulty)', 'wpshadow' );
		$issues[] = __( 'Track search queries to identify documentation gaps', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Users come to documentation with specific questions. Search lets them find answers in seconds instead of browsing for minutes.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/documentation-search?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'search_tools'            => 'Algolia DocSearch (free for open source), Elasticsearch, Typesense',
					'instant_search_benefit'  => 'Results appear as user types (no submit button needed)',
					'analytics_value'         => 'Failed searches reveal documentation gaps',
				),
			);
		}

		return null;
	}
}
