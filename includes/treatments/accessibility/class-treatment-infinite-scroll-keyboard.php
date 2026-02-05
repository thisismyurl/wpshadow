<?php
/**
 * Infinite Scroll Accessibility Treatment
 *
 * Issue #4762: Infinite Scroll Without Keyboard Bypass
 * Pillar: 🌍 Accessibility First
 *
 * Checks if infinite scroll provides keyboard alternatives.
 * Keyboard users need a way to reach footer and skip infinite content.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6036.1455
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Infinite_Scroll_Keyboard Class
 *
 * Checks for keyboard-accessible infinite scroll implementations.
 *
 * @since 1.6036.1455
 */
class Treatment_Infinite_Scroll_Keyboard extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'infinite-scroll-keyboard';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Infinite Scroll Without Keyboard Bypass';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if infinite scroll allows keyboard users to reach footer';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6036.1455
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Provide "Skip to footer" link before infinite content', 'wpshadow' );
		$issues[] = __( 'Add "Load More" button instead of automatic loading', 'wpshadow' );
		$issues[] = __( 'Show item count and total: "Showing 20 of 100"', 'wpshadow' );
		$issues[] = __( 'Announce new items to screen readers (aria-live)', 'wpshadow' );
		$issues[] = __( 'Ensure footer links remain accessible via keyboard', 'wpshadow' );
		$issues[] = __( 'Provide pagination alternative for keyboard users', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Infinite scroll traps keyboard users in an endless content loop. Imagine trying to reach the footer (with contact links, privacy policy, etc.) but content keeps loading every time you get close—like trying to reach the end of a hallway that keeps extending. Mouse users can scroll past the content, but keyboard users must Tab through every single item before reaching the footer. If you have 100 posts, that\'s potentially 100+ Tab presses. The solution: provide a "Skip to footer" link before infinite content, or use a "Load More" button instead of automatic loading. This gives keyboard users control over when more content loads.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/infinite-scroll-keyboard',
				'details'      => array(
					'recommendations'       => $issues,
					'wcag_requirement'      => 'WCAG 2.1 2.4.1 Bypass Blocks, 2.1.1 Keyboard',
					'affected_users'        => 'Keyboard users, screen reader users, power users',
					'better_pattern'        => 'Use "Load More" button instead of automatic infinite scroll',
					'best_pattern'          => 'Provide pagination with infinite scroll as progressive enhancement',
					'footer_trap_analogy'   => 'Like trying to reach the end of a hallway that keeps extending',
				),
			);
		}

		return null;
	}
}
