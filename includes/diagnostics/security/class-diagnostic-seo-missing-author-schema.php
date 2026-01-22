<?php
declare(strict_types=1);
/**
 * Missing Author Schema Diagnostic
 *
 * Philosophy: SEO credibility - author markup builds trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing author schema markup.
 */
class Diagnostic_SEO_Missing_Author_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-missing-author-schema',
			'title'       => 'Missing Author Schema Markup',
			'description' => 'Posts lack Author schema. Add Person schema with author name, bio, social links, and credentials. Strengthens E-E-A-T signals.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/add-author-schema/',
			'training_link' => 'https://wpshadow.com/training/author-markup/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
