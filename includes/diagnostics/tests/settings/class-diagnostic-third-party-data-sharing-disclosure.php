<?php
/**
 * Third-Party Data Sharing Disclosure Diagnostic
 *
 * Checks whether third-party data sharing is disclosed to users.
 *
 * @package    WPShadow
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
 * Third-Party Data Sharing Disclosure Diagnostic
 *
 * Validates disclosure of analytics and third-party data sharing.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Third_Party_Data_Sharing_Disclosure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-data-sharing-disclosure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Data Sharing Disclosure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether third-party data sharing is disclosed to users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$issues = array();

		$sharing_plugins = array(
			'google-site-kit/google-site-kit.php' => 'Google Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'facebook-pixel/facebook-pixel.php' => 'Facebook Pixel',
			'jetpack/jetpack.php' => 'Jetpack Stats',
		);

		$sharing_detected = array();
		foreach ( $sharing_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$sharing_detected[] = $name;
			}
		}

		$disclosure_url = get_option( 'wpshadow_third_party_disclosure_url', '' );
		$privacy_page = get_option( 'wp_page_for_privacy_policy', 0 );

		if ( ! empty( $sharing_detected ) && empty( $disclosure_url ) ) {
			$issues[] = __( 'Third-party data sharing detected but disclosure URL not configured', 'wpshadow' );
		}

		if ( ! empty( $sharing_detected ) && 0 === (int) $privacy_page ) {
			$issues[] = __( 'Privacy policy page not configured for third-party data sharing disclosure', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party data sharing disclosure appears incomplete', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-data-sharing-disclosure?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'           => $issues,
					'sharing_detected' => $sharing_detected,
					'disclosure_url'   => $disclosure_url,
				),
			);
		}

		return null;
	}
}
