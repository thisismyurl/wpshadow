<?php
/**
 * No Structured Data Schema Markup Diagnostic
 *
 * Detects when schema markup is missing,
 * preventing rich search results.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Structured Data Schema Markup
 *
 * Checks whether structured data (schema.org)
 * is implemented for rich search results.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Structured_Data_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-structured-data-schema';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data (Schema Markup)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether schema markup is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for schema markup
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		$has_schema = strpos( $body, 'schema.org' ) !== false ||
			strpos( $body, 'application/ld+json' ) !== false;

		if ( ! $has_schema ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Structured data isn\'t implemented, which means Google can\'t create rich search results. Schema markup tells search engines: what type of content (article, product, recipe, event), who wrote it, when it was published, ratings, prices, etc. Rich results get: star ratings in search, recipe cards with cook time, event dates and locations, product prices. Sites with schema see 30-40% higher CTR from rich snippets.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Search Result Click-Through',
					'potential_gain' => '+30-40% CTR from rich snippets',
					'roi_explanation' => 'Schema markup enables rich search results (stars, prices, images), increasing CTR by 30-40%.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/structured-data-schema',
			);
		}

		return null;
	}
}
