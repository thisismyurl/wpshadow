<?php
/**
 * No Accessibility Statement Diagnostic
 *
 * Detects when accessibility statement is missing,
 * limiting transparency about accessibility commitment.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Accessibility Statement
 *
 * Checks whether accessibility statement exists
 * documenting compliance efforts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Accessibility_Statement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-accessibility-statement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Statement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether accessibility statement exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility statement page
		$pages = get_posts( array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$has_statement = false;
		foreach ( $pages as $page ) {
			if ( preg_match( '/accessibility.*statement|accessibility.*policy/i', $page->post_title ) ) {
				$has_statement = true;
				break;
			}
		}

		if ( ! $has_statement ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You don\'t have an accessibility statement, which limits transparency about your commitment. Accessibility statement should include: conformance level (WCAG AA/AAA), known issues and workarounds, how to report problems, assistive technologies tested. This shows: you care about accessibility, users with disabilities are welcome, commitment to improvement. Many jurisdictions require accessibility statements (ADA, AODA, European Accessibility Act). Creates trust and reduces legal risk.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Accessibility Transparency & Trust',
					'potential_gain' => 'Demonstrate commitment to accessibility, reduce legal risk',
					'roi_explanation' => 'Accessibility statement shows users with disabilities they\'re welcome and reduces legal liability.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-statement',
			);
		}

		return null;
	}
}
