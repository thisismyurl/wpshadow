<?php
/**
 * No Open Graph Social Meta Tags Diagnostic
 *
 * Detects when Open Graph tags are missing,
 * causing poor social media link previews.
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
 * Diagnostic: No Open Graph Social Meta Tags
 *
 * Checks whether Open Graph tags are set
 * for better social media sharing.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Open_Graph_Social_Meta_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-open-graph-social-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Social Meta Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Open Graph tags are set';

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
		$has_og_tags = strpos( $body, 'property="og:' ) !== false;

		if ( ! $has_og_tags ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Open Graph tags aren\'t set, which means social media can\'t display rich previews when people share your links. Without OG tags, Facebook/LinkedIn show: generic title, no image, no description. With OG tags, you control: the title (og:title), image (og:image), description (og:description), type (og:type). Rich previews get 2-5x more clicks than plain links. This is especially important for content marketing and social sharing.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Social Media Engagement',
					'potential_gain' => '+2-5x more clicks on social shares',
					'roi_explanation' => 'Open Graph tags create rich social previews, increasing click-through rate by 2-5x compared to plain links.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/open-graph-social-tags',
			);
		}

		return null;
	}
}
