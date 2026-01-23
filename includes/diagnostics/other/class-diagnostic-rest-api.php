<?php
declare(strict_types=1);
/**
 * REST API Headers Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if REST API headers are exposed when not needed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_REST_API extends Diagnostic_Base {

	protected static $slug        = 'rest-api';
	protected static $title       = 'REST API Links in Head';
	protected static $description = 'WordPress REST API links are in the head. If you do not use the REST API, these can be removed for security.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_rest_api_headers_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if REST API link header is enabled
		$has_rest_link   = has_action( 'wp_head', 'rest_output_link_wp_head' ) !== false;
		$has_rest_header = has_action( 'template_redirect', 'rest_output_link_header' ) !== false;

		if ( ! $has_rest_link && ! $has_rest_header ) {
			return null;
		}

		return array(
			'id'          => 'rest-api',
			'title'       => 'REST API Links Exposed',
			'description' => 'Your site exposes REST API endpoints in the HTML head and HTTP headers. If you don\'t use the REST API (most sites don\'t), removing these improves security by hiding the API discovery.',
			'severity'    => 'warning',
			'category'    => 'security',
			'impact'      => 'Exposes API endpoints to scrapers and bots',
			'fix_time'    => '1 second',
			'kb_article'  => 'rest-api',
		);
	}

}