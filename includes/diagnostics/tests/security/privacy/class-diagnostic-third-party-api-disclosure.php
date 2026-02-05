<?php
/**
 * Third-Party API Disclosure Diagnostic
 *
 * Checks whether third-party services are disclosed in the privacy policy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party API Disclosure Diagnostic Class
 *
 * Verifies disclosure of third-party services in privacy policy content.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Third_Party_API_Disclosure extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-api-disclosure';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party API Calls Not Disclosed to Users';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if privacy policy mentions third-party services';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$privacy_content = '';

		if ( $privacy_page_id > 0 ) {
			$page = get_post( $privacy_page_id );
			$privacy_content = $page ? strtolower( wp_strip_all_tags( $page->post_content ) ) : '';
		}

		$stats['privacy_page_set'] = $privacy_page_id > 0 ? 'yes' : 'no';

		$third_party_keywords = array(
			'third party',
			'analytics',
			'google analytics',
			'stripe',
			'paypal',
			'mailchimp',
			'hubspot',
			'facebook',
			'tracking',
		);

		$mentions_third_party = false;
		foreach ( $third_party_keywords as $keyword ) {
			if ( $privacy_content && false !== strpos( $privacy_content, $keyword ) ) {
				$mentions_third_party = true;
				break;
			}
		}

		$stats['mentions_third_party'] = $mentions_third_party ? 'yes' : 'no';

		if ( $privacy_page_id > 0 && ! $mentions_third_party ) {
			$issues[] = __( 'Privacy policy does not mention third-party services or data sharing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'People deserve to know which outside services their data touches. Listing third-party tools in your privacy policy builds trust.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-api-disclosure',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
