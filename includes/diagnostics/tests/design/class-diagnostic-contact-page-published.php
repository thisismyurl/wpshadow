<?php
/**
 * Contact Page Published Diagnostic
 *
 * A Contact page gives visitors a clear path to reach the business or
 * author. Without one, potential customers, media, and partners have no
 * obvious way to get in touch, which directly impacts conversions and
 * opportunities.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Contact_Page_Published Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Contact_Page_Published extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'contact-page-published';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Contact Page Published';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that a published Contact page exists so visitors have a clear path to reach the business, improving trust and conversion rates.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Slug fragments and title keywords associated with contact pages.
	 */
	private const CONTACT_KEYWORDS = array(
		'contact',
		'contact-us',
		'get-in-touch',
		'reach-us',
		'work-with-us',
		'hire-us',
		'enquiry',
		'inquiry',
		'support',
		'help',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches all published pages and checks whether any of them have a slug
	 * or post title that matches common Contact page naming conventions.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);

		if ( empty( $pages ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No published pages were found on this site. A Contact page is essential for visitors and potential customers to reach you.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'kb_link'      => 'https://wpshadow.com/kb/contact-page-published?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'fix' => __( 'Create and publish a Contact page. Include a contact form, email address, phone number, or other ways for visitors to reach you. Link to it from your main navigation.', 'wpshadow' ),
				),
			);
		}

		foreach ( $pages as $page ) {
			$slug  = strtolower( $page->post_name );
			$title = strtolower( $page->post_title );

			foreach ( self::CONTACT_KEYWORDS as $keyword ) {
				if ( str_contains( $slug, $keyword ) || str_contains( $title, str_replace( '-', ' ', $keyword ) ) ) {
					return null;
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No published Contact page was detected. Visitors who want to enquire, hire, or get support have no obvious way to reach you, which reduces conversions and trust.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'kb_link'      => 'https://wpshadow.com/kb/contact-page-published?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'fix' => __( 'Create a published page with a URL slug or title containing "contact". Add a contact form using a plugin such as Contact Form 7 or WPForms, and link the page from your primary navigation menu.', 'wpshadow' ),
			),
		);
	}
}
