<?php
declare(strict_types=1);
/**
 * Semantic HTML Usage Diagnostic
 *
 * Philosophy: SEO accessibility - semantic markup aids understanding
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for semantic HTML usage.
 */
class Diagnostic_SEO_Semantic_HTML extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-semantic-html',
			'title'       => 'Use Semantic HTML Elements',
			'description' => 'Use semantic HTML5 elements: <article>, <section>, <nav>, <aside>, <header>, <footer>, <main>. Helps search engines understand page structure. Improves accessibility.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/semantic-html/',
			'training_link' => 'https://wpshadow.com/training/html5-seo/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}

}