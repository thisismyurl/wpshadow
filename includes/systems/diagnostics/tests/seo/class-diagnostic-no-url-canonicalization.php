<?php
/**
 * No URL Canonicalization Diagnostic
 *
 * Detects when URL canonicalization is not properly configured,
 * causing duplicate content and split PageRank.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No URL Canonicalization
 *
 * Checks whether canonical URLs are properly
 * configured to prevent duplicate content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_URL_Canonicalization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-url-canonicalization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'URL Canonicalization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether canonical URLs are configured';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for canonical tag
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for canonical link tag
		$has_canonical = preg_match( '/<link[^>]*rel=["\']canonical["\']/i', $body );

		if ( ! $has_canonical ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Canonical URLs aren\'t configured, which creates duplicate content issues. Problem: example.com, www.example.com, example.com/?ref=twitter all show same content = split PageRank 3 ways. Canonical tag tells Google: "this is the real URL, ignore duplicates." Implementation: <link rel="canonical" href="https://example.com/page/"> in <head>. SEO plugins (Yoast, Rank Math) add this automatically. This consolidates ranking signals to one URL, improving SEO.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Duplicate Content & PageRank',
					'potential_gain' => 'Consolidate ranking signals to primary URL',
					'roi_explanation' => 'Canonical URLs prevent duplicate content penalties and consolidate PageRank to your preferred URL.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/url-canonicalization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
