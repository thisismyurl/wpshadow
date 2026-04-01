<?php
/**
 * No Terms of Service Page Diagnostic
 *
 * Detects when terms of service are missing,
 * creating legal risk and violating platform requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Terms of Service Page
 *
 * Checks whether terms of service exist
 * and are properly linked.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Terms_Of_Service_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-terms-of-service-page';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Terms of Service Page';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether terms of service exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

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
		// Check for TOS pages
		$pages = get_posts( array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$has_tos = false;
		foreach ( $pages as $page ) {
			if ( preg_match( '/terms\s*(?:of|&)\s*(?:service|use|conditions)/i', $page->post_title ) ||
				preg_match( '/terms\s*(?:of|&)\s*(?:service|use|conditions)/i', $page->post_name ) ) {
				$has_tos = true;
				break;
			}
		}

		if ( ! $has_tos ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You don\'t have terms of service, which creates legal risk and violates many platform policies. ToS protect you by: defining acceptable use (what users can/cannot do), limiting liability (for user-generated content, service interruptions), protecting intellectual property, defining dispute resolution. Many payment processors (Stripe, PayPal) and app stores require ToS. Without them, you have no legal recourse if users abuse your service.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Legal Protection',
					'potential_gain' => 'Avoid legal disputes and platform violations',
					'roi_explanation' => 'ToS protect against user abuse and are required by payment processors. Missing them creates legal risk.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/terms-of-service-page?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
