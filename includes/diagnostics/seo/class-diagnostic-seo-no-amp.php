<?php
declare(strict_types=1);
/**
 * No AMP Implementation Diagnostic
 *
 * Philosophy: SEO mobile - AMP pages load instantly on mobile
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for AMP implementation.
 */
class Diagnostic_SEO_No_AMP extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_amp = is_plugin_active( 'amp/amp.php' ) || function_exists( 'amp_init' );
		
		if ( ! $has_amp ) {
			return array(
				'id'          => 'seo-no-amp',
				'title'       => 'Consider AMP Implementation',
				'description' => 'AMP (Accelerated Mobile Pages) not detected. AMP pages load instantly on mobile, improving user experience. Optional but beneficial for news/blog sites.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/implement-amp/',
				'training_link' => 'https://wpshadow.com/training/amp-setup/',
				'auto_fixable' => false,
				'threat_level' => 40,
			);
		}
		
		return null;
	}
}
