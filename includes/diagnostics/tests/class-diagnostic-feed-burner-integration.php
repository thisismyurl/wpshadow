<?php
/**
 * Feed Burner Integration Diagnostic
 *
 * Checks Feed Burner integration status (if configured).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feed Burner Integration Diagnostic Class
 *
 * Checks Feed Burner integration when configured.
 *
 * @since 1.26032.1900
 */
class Diagnostic_Feed_Burner_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-burner-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Burner Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks Feed Burner integration status';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if Feed Burner is configured.
		$feedburner_url = get_option( 'feedburner_url', '' );

		if ( empty( $feedburner_url ) ) {
			return null; // Feed Burner not configured.
		}

		// Verify Feed Burner URL is valid.
		if ( ! preg_match( '/^https?:\/\/feeds\.feedburner\.com\/.*/', $feedburner_url ) ) {
			$issues[] = sprintf(
				/* translators: %s: feedburner URL */
				__( 'Invalid Feed Burner URL: %s', 'wpshadow' ),
				esc_attr( $feedburner_url )
			);
		}

		// Check if Feed Burner redirect is working.
		$response = wp_remote_head( $feedburner_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Cannot reach Feed Burner: %s', 'wpshadow' ),
				$response->get_error_message()
			);
		} elseif ( isset( $response['response']['code'] ) && $response['response']['code'] !== 200 ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Feed Burner returned HTTP %d - may not be working properly', 'wpshadow' ),
				$response['response']['code']
			);
		}

		// Check if native feed is still accessible.
		$native_feed = get_feed_link();
		$response = wp_remote_head( $native_feed, array( 'timeout' => 5 ) );
		if ( ! is_wp_error( $response ) && isset( $response['response']['code'] ) && $response['response']['code'] === 200 ) {
			$issues[] = __( 'Native feed still accessible - Feed Burner redirect may not be working', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/feed-burner-integration',
			);
		}

		return null;
	}
}
