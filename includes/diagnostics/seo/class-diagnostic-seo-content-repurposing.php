<?php
declare(strict_types=1);
/**
 * Content Repurposing Strategy Diagnostic
 *
 * Philosophy: SEO efficiency - maximize content value
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for content repurposing opportunities.
 */
class Diagnostic_SEO_Content_Repurposing extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-content-repurposing',
			'title'       => 'Repurpose Top Content',
			'description' => 'Identify high-performing posts (traffic, engagement). Repurpose into: YouTube videos, infographics, podcasts, social posts, email series. Multiply reach and create more entry points.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/repurpose-content/',
			'training_link' => 'https://wpshadow.com/training/content-multiplication/',
			'auto_fixable' => false,
			'threat_level' => 40,
		);
	}
}
