<?php
/**
 * Landmark Regions Unlabeled Diagnostic
 *
 * Checks if ARIA landmark regions have descriptive labels.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Landmark Regions Diagnostic Class
 *
 * Validates that landmark regions (<nav>, <aside>) have descriptive labels.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Landmark_Regions_Unlabeled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'landmark-regions-unlabeled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Landmark Regions Not Labeled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if ARIA landmark regions have descriptive labels';

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

		// Check theme templates for landmarks.
		$templates = array(
			get_template_directory() . '/header.php',
			get_template_directory() . '/footer.php',
			get_template_directory() . '/sidebar.php',
			get_template_directory() . '/index.php',
		);

		$nav_count = 0;
		$nav_labeled = 0;
		$aside_count = 0;
		$aside_labeled = 0;

		foreach ( $templates as $template ) {
			if ( ! file_exists( $template ) ) {
				continue;
			}

			$content = file_get_contents( $template );

			// Count <nav> elements.
			if ( preg_match_all( '/<nav[^>]*>/i', $content, $nav_matches ) ) {
				$nav_count += count( $nav_matches[0] );

				// Count labeled <nav> elements.
				foreach ( $nav_matches[0] as $nav_tag ) {
					if ( preg_match( '/aria-label(?:ledby)?=/i', $nav_tag ) ) {
						$nav_labeled++;
					}
				}
			}

			// Count <aside> elements.
			if ( preg_match_all( '/<aside[^>]*>/i', $content, $aside_matches ) ) {
				$aside_count += count( $aside_matches[0] );

				// Count labeled <aside> elements.
				foreach ( $aside_matches[0] as $aside_tag ) {
					if ( preg_match( '/aria-label(?:ledby)?=/i', $aside_tag ) ) {
						$aside_labeled++;
					}
				}
			}
		}

		// Multiple nav elements without labels.
		if ( $nav_count > 1 && $nav_labeled < $nav_count ) {
			$issues[] = sprintf(
				/* translators: 1: number of unlabeled nav elements, 2: total nav elements */
				__( 'Found %2$d navigation regions, but only %1$d have descriptive labels', 'wpshadow' ),
				$nav_labeled,
				$nav_count
			);
		}

		// Aside elements without labels.
		if ( $aside_count > 0 && $aside_labeled === 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of aside elements */
				__( 'Found %d sidebar regions without descriptive labels', 'wpshadow' ),
				$aside_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your navigation regions are all called "navigation"—like a building with three doors all labeled "Door" where visitors can\'t tell which is which. Screen reader users navigate by landmarks (like chapter headings in a book), jumping directly to navigation areas, sidebars, and main content. When you have multiple navigation regions without descriptive labels, they hear "Navigation 1, Navigation 2, Navigation 3" with no context about which is the main menu, footer links, or breadcrumbs.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/landmark-labels?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
