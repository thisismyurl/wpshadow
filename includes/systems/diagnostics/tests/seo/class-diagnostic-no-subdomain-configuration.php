<?php
/**
 * No Subdomain Configuration Diagnostic
 *
 * Detects when subdomains are not optimally configured,
 * missing SEO and organizational benefits.
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
 * Diagnostic: No Subdomain Configuration
 *
 * Checks whether subdomains are optimally configured
 * for SEO and content organization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Subdomain_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-subdomain-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Subdomain Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether subdomains are configured';

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
		// This is a strategic diagnostic - not auto-detectable
		// Check if subdomains have been documented
		$has_subdomain_strategy = get_option( 'wpshadow_subdomain_strategy' );

		if ( ! $has_subdomain_strategy ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Subdomains haven\'t been strategically configured. Common uses: blog.example.com (blog), shop.example.com (ecommerce), help.example.com (support). Decision: subdomains vs subfolders. Subfolders keep authority together, subdomains separate properties (useful for different brands). SEO consideration: subfolders are typically better for authority consolidation. Subdomain decision depends on your content separation strategy.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'SEO Authority & Content Organization',
					'potential_gain' => 'Strategic content organization and SEO authority distribution',
					'roi_explanation' => 'Subdomain configuration affects SEO authority distribution and content organization strategy.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/subdomain-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
