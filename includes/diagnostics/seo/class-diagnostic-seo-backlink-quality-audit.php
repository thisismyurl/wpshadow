<?php
declare(strict_types=1);
/**
 * Backlink Quality Audit Diagnostic
 *
 * Philosophy: SEO authority - quality > quantity for backlinks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check backlink profile quality.
 */
class Diagnostic_SEO_Backlink_Quality_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-backlink-quality-audit',
			'title'       => 'Audit Backlink Profile',
			'description' => 'Review backlinks in Google Search Console or Ahrefs. Check for: toxic links (spammy sites), anchor text over-optimization, low DR/DA sources. Disavow harmful links.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/audit-backlinks/',
			'training_link' => 'https://wpshadow.com/training/link-building/',
			'auto_fixable' => false,
			'threat_level' => 55,
		);
	}

}