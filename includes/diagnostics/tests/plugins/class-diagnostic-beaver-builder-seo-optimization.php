<?php
/**
 * Beaver Builder SEO Optimization Diagnostic
 *
 * Beaver Builder output not SEO friendly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.347.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder SEO Optimization Diagnostic Class
 *
 * @since 1.347.0000
 */
class Diagnostic_BeaverBuilderSeoOptimization extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-seo-optimization';
	protected static $title = 'Beaver Builder SEO Optimization';
	protected static $description = 'Beaver Builder output not SEO friendly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Semantic HTML enabled
		$semantic = get_option( 'bb_semantic_html_enabled', 0 );
		if ( ! $semantic ) {
			$issues[] = 'Semantic HTML not enabled';
		}

		// Check 2: Heading structure validation
		$heading_struct = get_option( 'bb_heading_structure_validation', 0 );
		if ( ! $heading_struct ) {
			$issues[] = 'Heading structure validation not enabled';
		}

		// Check 3: Alt text for images
		$alt_text = get_option( 'bb_enforce_image_alt_text', 0 );
		if ( ! $alt_text ) {
			$issues[] = 'Image alt text not enforced';
		}

		// Check 4: Meta descriptions
		$meta_desc = get_option( 'bb_meta_description_enabled', 0 );
		if ( ! $meta_desc ) {
			$issues[] = 'Meta description support not configured';
		}

		// Check 5: Structured data/schema
		$schema = get_option( 'bb_structured_data_enabled', 0 );
		if ( ! $schema ) {
			$issues[] = 'Structured data/schema markup not enabled';
		}

		// Check 6: Robot meta tags
		$robots = get_option( 'bb_robots_meta_tags_enabled', 0 );
		if ( ! $robots ) {
			$issues[] = 'Robot meta tags not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SEO optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-seo-optimization',
			);
		}

		return null;
	}
}
