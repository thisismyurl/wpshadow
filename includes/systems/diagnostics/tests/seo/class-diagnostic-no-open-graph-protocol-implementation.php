<?php
/**
 * No Open Graph Protocol Implementation Diagnostic
 *
 * Detects when Open Graph tags are missing,
 * causing poor social media sharing appearance.
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
 * Diagnostic: No Open Graph Protocol Implementation
 *
 * Checks whether Open Graph meta tags are
 * implemented for social media sharing.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Open_Graph_Protocol_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-open-graph-protocol-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Protocol Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Open Graph tags exist';

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
		// Check homepage for Open Graph tags
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for OG meta tags
		$has_og = preg_match( '/<meta[^>]*property=["\']og:/i', $body );

		if ( ! $has_og ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Open Graph tags aren\'t implemented, which makes social sharing look unprofessional. When someone shares your content on Facebook/LinkedIn/etc, OG tags control: title, description, image, URL. Without OG tags: random text pulled from page, first image found (maybe logo or ad), ugly preview. With OG tags: you control exactly what appears. OG tags increase social click-through by 2-3x. SEO plugins add these automatically: Yoast, Rank Math, All in One SEO.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Social Media Sharing CTR',
					'potential_gain' => '2-3x higher CTR from social shares',
					'roi_explanation' => 'Open Graph tags control how content appears when shared, increasing social click-through 2-3x.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/open-graph-protocol-implementation',
			);
		}

		return null;
	}
}
