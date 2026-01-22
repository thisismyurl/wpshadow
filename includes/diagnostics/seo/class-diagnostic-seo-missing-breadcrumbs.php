<?php
declare(strict_types=1);
/**
 * Missing Breadcrumbs Diagnostic
 *
 * Philosophy: SEO navigation - breadcrumbs improve crawlability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing breadcrumb navigation.
 */
class Diagnostic_SEO_Missing_Breadcrumbs extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if breadcrumbs exist
		$has_breadcrumbs = function_exists( 'yoast_breadcrumb' ) || 
		                   function_exists( 'bcn_display' ) ||
		                   has_action( 'wp_footer', 'breadcrumbs' );
		
		if ( ! $has_breadcrumbs ) {
			return array(
				'id'          => 'seo-missing-breadcrumbs',
				'title'       => 'Missing Breadcrumb Navigation',
				'description' => 'No breadcrumb navigation detected. Breadcrumbs improve site structure, help users navigate, and appear in search results. Add breadcrumbs with BreadcrumbList schema.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-breadcrumbs/',
				'training_link' => 'https://wpshadow.com/training/breadcrumb-seo/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
