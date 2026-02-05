<?php
/**
 * Heading Structure Treatment
 *
 * Checks for proper heading hierarchy (H1-H6) which helps screen reader
 * users navigate page structure and understand content organization.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.6035.1700
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heading Structure Treatment Class
 *
 * Verifies proper heading hierarchy and structure.
 * WCAG 2.1 Level A Success Criterion 1.3.1 (Info and Relationships).
 *
 * @since 1.6035.1700
 */
class Treatment_Heading_Structure extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'heading_structure';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Heading Structure';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper heading hierarchy (H1-H6)';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1700
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for accessibility validation tools.
		$validation_plugins = array(
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
		);

		$active_validation = array();
		foreach ( $validation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_validation[] = $plugin_name;
			}
		}

		if ( count( $active_validation ) > 0 ) {
			$stats['validation_tools'] = implode( ', ', $active_validation );
		} else {
			$warnings[] = 'No automated heading structure validation detected';
		}

		// Check theme for heading structure.
		$theme                       = wp_get_theme();
		$stats['theme']              = $theme->get( 'Name' );
		$theme_tags                  = $theme->get( 'Tags' );
		$is_a11y_ready               = is_array( $theme_tags ) && in_array( 'accessibility-ready', $theme_tags, true );

		if ( $is_a11y_ready ) {
			$stats['accessibility_ready'] = 'Yes';
		}

		// Check theme templates for heading usage.
		$template_files = array(
			get_template_directory() . '/index.php',
			get_template_directory() . '/single.php',
			get_template_directory() . '/page.php',
			get_template_directory() . '/header.php',
		);

		$heading_patterns = array();
		foreach ( $template_files as $template_path ) {
			if ( file_exists( $template_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $template_path );
				
				// Count heading tags.
				for ( $i = 1; $i <= 6; $i++ ) {
					$count = preg_match_all( "/<h{$i}[\s>]/i", $content );
					if ( $count > 0 ) {
						$heading_patterns[ "h{$i}" ] = ( $heading_patterns[ "h{$i}" ] ?? 0 ) + $count;
					}
				}
			}
		}

		if ( ! empty( $heading_patterns ) ) {
			$stats['heading_tags_found'] = implode( ', ', array_keys( $heading_patterns ) );

			// Check for potential issues.
			if ( ! isset( $heading_patterns['h1'] ) ) {
				$issues[] = 'No H1 tags found in theme templates';
			}

			// Check if skipping levels (e.g., H1 to H3).
			$heading_levels = array_keys( $heading_patterns );
			sort( $heading_levels );
			for ( $i = 0; $i < count( $heading_levels ) - 1; $i++ ) {
				$current = (int) str_replace( 'h', '', $heading_levels[ $i ] );
				$next    = (int) str_replace( 'h', '', $heading_levels[ $i + 1 ] );
				if ( $next > $current + 1 ) {
					$warnings[] = sprintf(
						/* translators: 1: current heading level, 2: next heading level */
						__( 'Potential heading skip: %1$s jumps to %2$s', 'wpshadow' ),
						strtoupper( $heading_levels[ $i ] ),
						strtoupper( $heading_levels[ $i + 1 ] )
					);
				}
			}
		}

		// Return finding if issues detected or no validation tools.
		if ( count( $issues ) > 0 || ( count( $active_validation ) === 0 && ! $is_a11y_ready ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site doesn\'t have automated heading structure checking. Headings are like a table of contents for screen reader users—they navigate pages by jumping between headings (H1 to H2 to H3). Bad heading structure is like a book with Chapter 1, then suddenly Chapter 5, then back to Chapter 2. Proper hierarchy (H1 for title, H2 for sections, H3 for subsections) helps 2% of blind users and improves SEO.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/heading-structure',
				'context'      => array(
					'stats'          => $stats,
					'issues'         => $issues,
					'warnings'       => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level A - 1.3.1 Info and Relationships',
					'proper_hierarchy' => 'H1 (page title) → H2 (main sections) → H3 (subsections) → H4 (details)',
				),
			);
		}

		return null;
	}
}
