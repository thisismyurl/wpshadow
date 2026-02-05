<?php
/**
 * Privacy Policy Page Configuration Treatment
 *
 * Ensures a privacy policy page is configured and published.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Page Configuration Treatment
 *
 * Validates privacy policy page configuration and content.
 *
 * @since 1.6030.2240
 */
class Treatment_Privacy_Policy_Page_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures a privacy policy page is configured and published';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$issues  = array();
		$details = array();

		if ( 0 === $page_id ) {
			$issues[] = __( 'Privacy policy page is not configured', 'wpshadow' );
		} else {
			$page = get_post( $page_id );
			if ( empty( $page ) ) {
				$issues[] = __( 'Configured privacy policy page not found', 'wpshadow' );
			} else {
				if ( 'publish' !== $page->post_status ) {
					$issues[] = __( 'Privacy policy page is not published', 'wpshadow' );
				}

				$word_count = str_word_count( wp_strip_all_tags( $page->post_content ) );
				$details['word_count'] = $word_count;
				if ( $word_count < 150 ) {
					$issues[] = __( 'Privacy policy content appears too short', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Privacy policy page configuration issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/privacy-policy-page-configuration',
				'details'      => array(
					'issues'  => $issues,
					'page_id' => $page_id,
					'info'    => $details,
				),
			);
		}

		return null;
	}
}
