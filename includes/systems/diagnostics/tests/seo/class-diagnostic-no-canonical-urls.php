<?php
/**
 * No Canonical URLs Diagnostic
 *
 * Detects when canonical URLs are not set,
 * causing duplicate content issues and diluted SEO value.
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
 * Diagnostic: No Canonical URLs
 *
 * Checks whether canonical URLs are properly set
 * to avoid duplicate content issues.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Canonical_URLs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-canonical-urls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URLs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether canonical URLs are set';

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
		// Check homepage for canonical tag
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		$has_canonical = strpos( $body, '<link rel="canonical"' ) !== false;

		if ( ! $has_canonical ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Canonical URLs aren\'t set, which means Google might see the same content at multiple URLs and split your SEO value. Example: yoursite.com/page, yoursite.com/page?ref=email, and yoursite.com/page?utm_source=facebook are all different URLs but same content. Canonical tags tell Google "this is the real version, ignore duplicates." Without canonicals, your SEO authority gets split across URLs instead of consolidated.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'SEO Authority Consolidation',
					'potential_gain' => 'Consolidate split SEO value',
					'roi_explanation' => 'Canonical URLs prevent duplicate content issues, consolidating SEO authority to the correct URL instead of splitting it.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/canonical-urls',
			);
		}

		return null;
	}
}
