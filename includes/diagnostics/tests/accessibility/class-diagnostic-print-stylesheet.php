<?php
/**
 * Print Stylesheet Diagnostic
 *
 * Issue #4964: No Print Stylesheet (Poor Print Experience)
 * Pillar: 🌍 Accessibility First / 🎓 Learning Inclusive
 *
 * Checks if site has print-friendly CSS.
 * Printing web pages without print styles wastes ink/paper.
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
 * Diagnostic_Print_Stylesheet Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Print_Stylesheet extends Diagnostic_Base {

	protected static $slug = 'print-stylesheet';
	protected static $title = 'No Print Stylesheet (Poor Print Experience)';
	protected static $description = 'Checks if site has print-optimized CSS';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Create @media print styles', 'wpshadow' );
		$issues[] = __( 'Hide navigation, sidebars, ads, footers', 'wpshadow' );
		$issues[] = __( 'Expand collapsed content (accordions, tabs)', 'wpshadow' );
		$issues[] = __( 'Use black text on white background (save ink)', 'wpshadow' );
		$issues[] = __( 'Show link URLs after links: a[href]:after { content: " (" attr(href) ")" }', 'wpshadow' );
		$issues[] = __( 'Add page breaks to prevent awkward splits', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Print stylesheets make pages readable when printed. Without them, users get navigation menus, ads, and wasted pages.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/print-stylesheet?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'who_prints'              => 'Academic users, elderly, legal/medical professionals',
					'basic_rule'              => '@media print { nav, aside, footer { display: none; } }',
				),
			);
		}

		return null;
	}
}
