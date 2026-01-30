<?php
/**
 * HTML Confirm Breadcrumbs Use Schema.org Markup Diagnostic
 *
 * Verifies breadcrumbs use proper schema.org markup.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Confirm Breadcrumbs Use Schema.org Markup Diagnostic Class
 *
 * Identifies pages with breadcrumb navigation that lack proper
 * schema.org BreadcrumbList markup for SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Confirm_Breadcrumbs_Use_Schemaorg_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-confirm-breadcrumbs-use-schemaorg-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Breadcrumbs Missing Schema.org Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates breadcrumbs use schema.org structured data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$breadcrumb_found  = false;
		$schema_found      = false;
		$breadcrumb_type   = 'unknown';
		$has_breadcrumb_nav = false;

		// Check scripts for breadcrumb patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for breadcrumb nav elements.
					if ( preg_match( '/<nav[^>]*(?:class="[^"]*breadcrumb[^"]*"|aria-label=["\']breadcrumb["\'])[^>]*>/i', $data ) ) {
						$breadcrumb_found  = true;
						$has_breadcrumb_nav = true;
					}

					// Check for schema.org BreadcrumbList markup.
					if ( preg_match( '/"@type"\s*:\s*"BreadcrumbList"|breadcrumblist|itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/i', $data ) ) {
						$schema_found = true;
						$breadcrumb_type = 'Schema.org JSON-LD';
					}

					// Check for RDFa breadcrumb markup.
					if ( preg_match( '/vocab=["\']https?:\/\/schema\.org["\']|property=["\']breadcrumb["\']|typeof=["\']breadcrumb["\']/i', $data ) ) {
						$schema_found = true;
						$breadcrumb_type = 'RDFa Markup';
					}

					// Check for microdata breadcrumb markup.
					if ( preg_match( '/itemtype=["\']https?:\/\/schema\.org\/Breadcrumb["\']|itemprop=["\']breadcrumb["\']/i', $data ) ) {
						$schema_found = true;
						$breadcrumb_type = 'Microdata Markup';
					}
				}
			}
		}

		// If breadcrumb found but no schema.org markup, that's an issue.
		if ( $has_breadcrumb_nav && ! $schema_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: */
					__( 'Breadcrumbs detected but missing schema.org markup. Search engines use BreadcrumbList schema to enhance search results with breadcrumb trails. Add JSON-LD structured data to your breadcrumb navigation: <script type="application/ld+json">{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[...]}</script>', 'wpshadow' )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-confirm-breadcrumbs-use-schemaorg-markup',
				'meta'         => array(
					'breadcrumb_found' => $breadcrumb_found,
					'schema_type'      => $breadcrumb_type,
				),
			);
		}

		return null;
	}
}
