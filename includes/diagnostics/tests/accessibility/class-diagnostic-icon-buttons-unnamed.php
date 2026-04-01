<?php
/**
 * Icon Buttons Missing Accessible Names Diagnostic
 *
 * Issue #4752: Icon Buttons Missing Accessible Names
 * Pillar: 🌍 Accessibility First
 * Commandment: #8 (Inspire Confidence)
 *
 * Checks if icon-only buttons have accessible names.
 * Screen readers need aria-label or aria-labelledby to describe icon buttons.
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
 * Diagnostic_Icon_Buttons_Unnamed Class
 *
 * Checks for accessible names on icon-only buttons.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Icon_Buttons_Unnamed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'icon-buttons-unnamed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Icon Buttons Missing Accessible Names';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if icon-only buttons have aria-label or aria-labelledby attributes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Add aria-label to icon-only buttons', 'wpshadow' );
		$issues[] = __( 'Use aria-labelledby to reference visible text nearby', 'wpshadow' );
		$issues[] = __( 'Add descriptive text with .screen-reader-text class', 'wpshadow' );
		$issues[] = __( 'Avoid empty or generic labels like "button" or "icon"', 'wpshadow' );
		$issues[] = __( 'Test with screen reader—what does it announce?', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your icon-only buttons might be silent to screen readers. When a screen reader user encounters a "🔍" icon button, it might announce nothing, "graphic", or just "button"—like having a door with no label. Sighted users see a magnifying glass and know it\'s search, but screen reader users hear nothing useful. Adding aria-label="Search" makes the button announce "Search button" so everyone knows what it does. This affects blind users (2% of population) and keyboard-only users who navigate by hearing button names.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/icon-buttons?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'      => $issues,
					'wcag_requirement'     => 'WCAG 2.1 4.1.2 Name, Role, Value (Level A)',
					'affected_users'       => 'Blind users (2%), screen reader users, keyboard-only users',
					'aria_label_example'   => '<button aria-label="Search"><i class="icon-search"></i></button>',
					'screen_reader_text'   => '<button><span class="screen-reader-text">Search</span><i class="icon-search" aria-hidden="true"></i></button>',
					'bad_example'          => '<button><i class="fa fa-search"></i></button> <!-- Announces "button" -->',
					'common_icons'         => 'Search, Menu, Close, Edit, Delete, Share, Download, Upload',
				),
			);
		}

		return null;
	}
}
