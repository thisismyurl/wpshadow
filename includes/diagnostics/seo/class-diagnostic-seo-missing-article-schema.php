<?php
declare(strict_types=1);
/**
 * Missing Article Schema Diagnostic
 *
 * Philosophy: SEO rich results - Article schema enables AMP stories
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Article schema on blog posts.
 */
class Diagnostic_SEO_Missing_Article_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-missing-article-schema',
			'title'       => 'Posts Missing Article Schema',
			'description' => 'Blog posts should have Article (or BlogPosting/NewsArticle) schema. Includes headline, author, datePublished, image. Enables rich results and Google Discover.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/add-article-schema/',
			'training_link' => 'https://wpshadow.com/training/article-markup/',
			'auto_fixable' => false,
			'threat_level' => 55,
		);
	}

}