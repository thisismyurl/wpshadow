<?php
/**
 * Missing Structured Data Breadcrumbs Diagnostic
 *
 * Detects when breadcrumb navigation lacks structured data markup,
 * missing SEO benefits and UX improvements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Structured Data Breadcrumbs
 *
 * Checks whether breadcrumb navigation includes structured data
 * (JSON-LD or Microdata) for search engine understanding.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Structured_Data_Breadcrumbs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-structured-data-breadcrumbs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data Breadcrumbs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether breadcrumbs include structured data markup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for breadcrumb plugins
		$has_breadcrumb_plugin = is_plugin_active( 'yoast-seo/wp-seo.php' ) ||
			is_plugin_active( 'rank-math-seo/rank-math.php' ) ||
			is_plugin_active( 'the-seo-framework/the-seo-framework.php' );

		// Check for structured data in theme/custom
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			$has_breadcrumb_markup = strpos( $body, '"BreadcrumbList"' ) !== false ||
				strpos( $body, 'schema.org/BreadcrumbList' ) !== false;
		} else {
			$has_breadcrumb_markup = false;
		}

		if ( ! $has_breadcrumb_plugin && ! $has_breadcrumb_markup ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your breadcrumb navigation isn\'t using structured data markup. Think of breadcrumbs as a map showing visitors and Google how your site is organized. When you add structured data markup (JSON-LD), Google can display breadcrumbs in search results, helping users navigate from the search results to your deeper pages. This improves click-through rate and SEO rankings. It\'s like putting detailed signs on a hiking trail instead of just markers.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Search Result Display',
					'potential_gain' => 'Breadcrumb display in search results',
					'roi_explanation' => 'Structured breadcrumbs display in search results, improving CTR and helping users navigate directly to deeper pages.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/structured-data-breadcrumbs',
			);
		}

		return null;
	}
}
