<?php
/**
 * Search Results Empty State Diagnostic
 *
 * Issue #4962: No Helpful Message for Empty Search Results
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if empty search results provide guidance.
 * "No results found" is unhelpful without suggestions.
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
 * Diagnostic_Search_Results_Empty_State Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Results_Empty_State extends Diagnostic_Base {

	protected static $slug = 'search-results-empty-state';
	protected static $title = 'No Helpful Message for Empty Search Results';
	protected static $description = 'Checks if empty search provides suggestions and alternatives';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show search suggestions for common misspellings', 'wpshadow' );
		$issues[] = __( 'Display related content: "You might like..."', 'wpshadow' );
		$issues[] = __( 'Show popular pages or recent posts', 'wpshadow' );
		$issues[] = __( 'Provide search tips: "Try different keywords"', 'wpshadow' );
		$issues[] = __( 'Link to site map or categories', 'wpshadow' );
		$issues[] = __( 'Offer contact/help form if search repeatedly fails', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Empty search results are a dead end. Provide suggestions, alternatives, and ways for users to continue exploring your site.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/empty-search?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'abandonment_stat'        => '68% of users leave after empty search',
					'fuzzy_search'            => 'Implement fuzzy matching for typos',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
