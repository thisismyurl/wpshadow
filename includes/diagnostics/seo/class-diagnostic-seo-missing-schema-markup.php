<?php
declare(strict_types=1);
/**
 * Missing Schema Markup Diagnostic
 *
 * Philosophy: SEO rich snippets - structured data drives visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing Schema.org markup.
 */
class Diagnostic_SEO_Missing_Schema_Markup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if any schema markup exists
		$has_schema = has_action( 'wp_head' ) && has_action( 'wp_footer' );
		
		if ( ! $has_schema ) {
			return array(
				'id'          => 'seo-missing-schema-markup',
				'title'       => 'No Schema.org Structured Data',
				'description' => 'No Schema.org markup detected. Structured data enables rich snippets in search results (ratings, prices, etc). Implement JSON-LD schema.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-schema-markup/',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
