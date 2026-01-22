<?php
declare(strict_types=1);
/**
 * Missing LSI Keywords Diagnostic
 *
 * Philosophy: SEO semantic - LSI keywords show topical relevance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for latent semantic indexing (LSI) keywords.
 */
class Diagnostic_SEO_Missing_LSI_Keywords extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-missing-lsi-keywords',
			'title'       => 'Add LSI Keywords to Content',
			'description' => 'Use LSI (Latent Semantic Indexing) keywords - related terms Google expects to see. For "SEO": include "search engine", "rankings", "SERP", "keywords". Use tools like LSIGraph.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/use-lsi-keywords/',
			'training_link' => 'https://wpshadow.com/training/semantic-seo/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
