<?php
/**
 * No Breadcrumb Navigation Diagnostic
 *
 * Detects when breadcrumb navigation is missing,
 * hurting both user experience and SEO.
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
 * Diagnostic: No Breadcrumb Navigation
 *
 * Checks whether breadcrumb navigation is
 * implemented for UX and SEO.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Breadcrumb_Navigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-breadcrumb-navigation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Breadcrumb Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether breadcrumbs are implemented';

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
		// Check for breadcrumb implementation
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for breadcrumb patterns
		$has_breadcrumbs = preg_match( '/breadcrumb|BreadcrumbList/i', $body );

		if ( ! $has_breadcrumbs ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Breadcrumb navigation isn\'t implemented, which hurts both UX and SEO. Breadcrumbs show page hierarchy: Home > Category > Subcategory > Post. Benefits: users see where they are, can jump back to parent pages, improves navigation on mobile. SEO benefits: helps Google understand site structure, enables breadcrumb rich results in search, distributes link juice through hierarchy. This is especially important for category-heavy sites (ecommerce, news).',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Navigation & SEO Structure',
					'potential_gain' => 'Improved UX, better site structure for Google',
					'roi_explanation' => 'Breadcrumbs improve user navigation and help Google understand site hierarchy.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/breadcrumb-navigation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
