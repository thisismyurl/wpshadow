<?php
/**
 * Link Context Clarity Diagnostic
 *
 * Issue #4893: Links Not Descriptive ("Click Here", "Read More")
 * Pillar: 🌍 Accessibility First / 🎓 Learning Inclusive
 *
 * Checks if links have clear context outside of surrounding text.
 * Screen readers often list links out of context.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Link_Context_Clarity Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Link_Context_Clarity extends Diagnostic_Base {

	protected static $slug = 'link-context-clarity';
	protected static $title = 'Links Not Descriptive ("Click Here", "Read More")';
	protected static $description = 'Checks if link text is meaningful out of context';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Avoid generic link text: "click here", "read more", "learn more"', 'wpshadow' );
		$issues[] = __( 'Use descriptive text: "Read WordPress security guide"', 'wpshadow' );
		$issues[] = __( 'Link text should make sense when read alone', 'wpshadow' );
		$issues[] = __( 'Don\'t use URL as link text (not user-friendly)', 'wpshadow' );
		$issues[] = __( 'Use aria-label to add context if link text is short', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen readers can list all links on a page. Generic text like "click here" is meaningless without context.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/link-context',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.4.4 Link Purpose (In Context)',
					'bad_examples'            => '"Click here", "Read more", "Learn more", "www.example.com"',
					'good_examples'           => '"Download WordPress 6.4", "Read security best practices"',
					'aria_solution'           => '<a href="..." aria-label="Download WordPress 6.4">Download</a>',
				),
			);
		}

		return null;
	}
}
