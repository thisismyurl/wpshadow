<?php
/**
 * Third-Party Data Sharing Not Disclosed Diagnostic
 *
 * Checks if third-party integrations are disclosed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party Data Sharing Not Disclosed Diagnostic Class
 *
 * Detects undisclosed third-party integrations.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Third_Party_Data_Sharing_Not_Disclosed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-data-sharing-not-disclosed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Data Sharing Not Disclosed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if third-party sharing is disclosed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for external integrations/tracking
		$third_party_plugins = array(
			'google-analytics',
			'facebook',
			'hotjar',
			'intercom',
			'segment',
			'mixpanel',
		);

		$found_plugins = array();

		foreach ( $third_party_plugins as $plugin_name ) {
			$plugins = get_plugins();
			foreach ( $plugins as $plugin_file => $plugin_data ) {
				if ( is_plugin_active( $plugin_file ) && stripos( $plugin_file . ' ' . $plugin_data['Name'] . ' ' . $plugin_data['Description'], $plugin_name ) !== false ) {
					$found_plugins[] = $plugin_data['Name'];
				}
			}
		}

		if ( ! empty( $found_plugins ) ) {
			// Check if privacy policy mentions these
			$privacy_policy_page = get_option( 'wp_page_for_privacy_policy', 0 );
			if ( $privacy_policy_page ) {
				$privacy_page = get_post( $privacy_policy_page );
				if ( $privacy_page ) {
					$policy_content = strtolower( $privacy_page->post_content );
					foreach ( $found_plugins as $plugin ) {
						if ( stripos( $policy_content, $plugin ) === false ) {
							return array(
								'id'            => self::$slug,
								'title'         => self::$title,
								'description'   => sprintf(
									__( 'Third-party plugin "%s" is active but not mentioned in privacy policy. Update privacy policy to disclose all data sharing.', 'wpshadow' ),
									$plugin
								),
								'severity'      => 'high',
								'threat_level'  => 70,
								'auto_fixable'  => false,
								'kb_link'       => 'https://wpshadow.com/kb/third-party-data-sharing-not-disclosed',
							);
						}
					}
				}
			} else {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Third-party plugins are active but privacy policy not configured. Cannot verify GDPR compliance for data sharing.', 'wpshadow' ),
					),
					'severity'      => 'high',
					'threat_level'  => 75,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/third-party-data-sharing-not-disclosed',
				);
			}
		}

		return null;
	}
}
