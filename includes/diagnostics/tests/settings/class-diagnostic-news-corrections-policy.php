<?php
/**
 * Journalism News Corrections Policy Diagnostic
 *
 * Verifies news sites have a published corrections policy and system
 * for tracking and displaying content corrections transparently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * News Corrections Policy Diagnostic Class
 *
 * Checks if journalism sites have proper corrections policies in place.
 *
 * @since 1.6093.1200
 */
class Diagnostic_News_Corrections_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'news-corrections-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Journalism News Corrections Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news sites have a corrections policy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site is journalism-focused.
		$site_name    = get_bloginfo( 'name' );
		$site_tagline = get_bloginfo( 'description' );
		$journalism_terms = array( 'news', 'journalism', 'reporter', 'press', 'media' );

		$is_journalism_site = false;
		foreach ( $journalism_terms as $term ) {
			if ( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
				$is_journalism_site = true;
				break;
			}
		}

		if ( ! $is_journalism_site ) {
			return null;
		}

		$issues = array();

		// Check for corrections policy page.
		$corrections_page = get_page_by_path( 'corrections' );
		if ( ! $corrections_page ) {
			$issues[] = __( 'No corrections policy page found', 'wpshadow' );
		}

		// Check for revision tracking.
		if ( ! wp_revisions_enabled() ) {
			$issues[] = __( 'Post revisions not enabled', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Corrections policy concerns: %s. News sites should maintain transparency about content corrections.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/news-corrections-policy',
		);
	}
}
