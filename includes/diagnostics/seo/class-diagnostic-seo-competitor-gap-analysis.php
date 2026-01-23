<?php
declare(strict_types=1);
/**
 * Competitor Gap Analysis Diagnostic
 *
 * Philosophy: SEO strategy - learn from competitors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if competitor analysis has been performed.
 */
class Diagnostic_SEO_Competitor_Gap_Analysis extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-competitor-gap-analysis',
			'title'       => 'Perform Competitor Gap Analysis',
			'description' => 'Analyze top competitors: Which keywords do they rank for that you don\'t? What content types perform well? Use tools like Ahrefs, SEMrush, or Moz to identify content gaps.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/competitor-analysis/',
			'training_link' => 'https://wpshadow.com/training/competitive-research/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}

}