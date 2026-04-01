<?php
/**
 * Portfolio Accessibility for Visual Content Diagnostic
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

class Diagnostic_Portfolio_Accessibility_Visual_Content extends Diagnostic_Base {
	protected static $slug = 'portfolio-accessibility-visual-content';
	protected static $title = 'Portfolio Accessibility for Visual Content';
	protected static $description = 'Makes portfolio accessible to visually impaired visitors';
	protected static $family = 'portfolio';

	public static function check() {
		// Check for portfolio/gallery plugins or custom post types.
		$has_portfolio = post_type_exists( 'portfolio' ) ||
		                 post_type_exists( 'jetpack-portfolio' ) ||
		                 class_exists( 'Essential_Grid' ) ||
		                 class_exists( 'Envira_Gallery' );

		if ( ! $has_portfolio ) {
			return null; // No portfolio detected.
		}

		$issues = array();

		// Check for images without alt text.
		global $wpdb;
		$images_without_alt = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
			WHERE p.post_type = 'attachment'
			AND p.post_mime_type LIKE 'image/%'
			AND (pm.meta_value IS NULL OR pm.meta_value = '')
			LIMIT 100"
		);

		if ( $images_without_alt > 10 ) {
			$issues[] = array(
				'issue'       => 'missing_alt_text',
				'count'       => $images_without_alt,
				'description' => sprintf(
					__( '%d images missing alt text - critical for screen readers', 'wpshadow' ),
					$images_without_alt
				),
				'severity'    => 'high',
			);
		}

		// Check if portfolio items have descriptions.
		$portfolio_types = array( 'portfolio', 'jetpack-portfolio', 'project' );
		foreach ( $portfolio_types as $type ) {
			if ( post_type_exists( $type ) ) {
				$items_without_content = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status = 'publish'
						AND (post_content = '' OR post_content IS NULL)
						LIMIT 50",
						$type
					)
				);

				if ( $items_without_content > 5 ) {
					$issues[] = array(
						'issue'       => 'missing_descriptions',
						'count'       => $items_without_content,
						'description' => sprintf(
							__( '%d portfolio items missing descriptions - important for context', 'wpshadow' ),
							$items_without_content
						),
						'severity'    => 'medium',
					);
				}
				break; // Only check the first portfolio type found.
			}
		}

		// Check for ARIA landmarks in theme.
		$theme_file = get_template_directory() . '/index.php';
		if ( file_exists( $theme_file ) ) {
			$theme_content = file_get_contents( $theme_file );
			$has_aria = strpos( $theme_content, 'role=' ) !== false ||
			            strpos( $theme_content, 'aria-' ) !== false;

			if ( ! $has_aria ) {
				$issues[] = array(
					'issue'       => 'no_aria_landmarks',
					'description' => __( 'Theme may lack ARIA landmarks for screen reader navigation', 'wpshadow' ),
					'severity'    => 'medium',
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d portfolio accessibility issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 75,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/portfolio-accessibility-visual-content?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
