<?php
/**
 * Pingback Trackback Settings Diagnostic
 *
 * Verifies pingback and trackback settings are appropriately configured.
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
 * Pingback Trackback Settings Diagnostic Class
 *
 * Checks pingback and trackback configuration.
 *
 * @since 1.26032.1900
 */
class Diagnostic_Pingback_Trackback_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pingback-trackback-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pingback Trackback Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies pingback and trackback settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check default ping status.
		$default_ping_status = get_option( 'default_ping_status', 'open' );

		if ( $default_ping_status === 'open' ) {
			$issues[] = __( 'Pingbacks/trackbacks enabled by default - common spam vector', 'wpshadow' );

			// Check for pingback spam.
			global $wpdb;
			$pingback_spam = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments}
				WHERE comment_type = 'pingback' AND comment_approved = 'spam'"
			);

			if ( $pingback_spam > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: spam count */
					__( '%d spam pingbacks detected - strong indicator of abuse', 'wpshadow' ),
					$pingback_spam
				);
			}

			// Check XML-RPC status.
			$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );
			if ( $xmlrpc_enabled ) {
				$issues[] = __( 'XML-RPC enabled - used for pingbacks and DDoS attacks', 'wpshadow' );
			}
		}

		// Check pingback queue.
		global $wpdb;
		$pending_pingbacks = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			WHERE comment_type = 'pingback' AND comment_approved = '0'"
		);

		if ( $pending_pingbacks > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: pending count */
				__( '%d pending pingbacks in moderation queue', 'wpshadow' ),
				$pending_pingbacks
			);
		}

		// Recommend disabling pingbacks for security.
		if ( $default_ping_status === 'open' ) {
			$issues[] = __( 'Consider disabling pingbacks/trackbacks to reduce spam', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/pingback-trackback-settings',
			);
		}

		return null;
	}
}
