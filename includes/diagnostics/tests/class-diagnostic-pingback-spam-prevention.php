<?php
/**
 * Pingback Spam Prevention Diagnostic
 *
 * Verifies pingback/trackback settings are configured to prevent spam.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pingback Spam Prevention Diagnostic Class
 *
 * Checks pingback and trackback security configuration.
 *
 * @since 1.26032.1755
 */
class Diagnostic_Pingback_Spam_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pingback-spam-prevention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pingback Spam Prevention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies pingback/trackback spam protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if pingbacks are enabled.
		$default_ping_status = get_option( 'default_ping_status', 'open' );
		$default_pingback_flag = get_option( 'default_pingback_flag', 1 );

		if ( $default_ping_status === 'open' ) {
			$issues[] = __( 'Pingbacks/trackbacks enabled - common spam vector', 'wpshadow' );

			// Check for pingback spam.
			global $wpdb;
			$pingback_spam_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = 'pingback' 
				AND comment_approved = 'spam'"
			);

			if ( $pingback_spam_count > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: spam pingbacks */
					__( 'Found %d spam pingbacks - strong indication of abuse', 'wpshadow' ),
					$pingback_spam_count
				);
			}

			// Check total pingbacks vs regular comments.
			$pingback_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = 'pingback' 
				AND comment_approved = '1'"
			);

			$comment_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = '' 
				AND comment_approved = '1'"
			);

			if ( $pingback_count > 100 && $comment_count > 0 ) {
				$ratio = ( $pingback_count / $comment_count ) * 100;
				if ( $ratio > 50 ) {
					$issues[] = sprintf(
						/* translators: %d: percentage */
						__( 'Pingbacks represent %d%% of comments - may clutter discussions', 'wpshadow' ),
						round( $ratio )
					);
				}
			}
		}

		// Check if XML-RPC is enabled (used for pingbacks).
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );
		if ( $xmlrpc_enabled && $default_ping_status === 'open' ) {
			$issues[] = __( 'XML-RPC enabled with pingbacks - DDoS amplification risk', 'wpshadow' );
		}

		// Check for security plugins that block pingbacks.
		$security_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'better-wp-security/better-wp-security.php',
		);

		$has_security_plugin = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security_plugin = true;
				break;
			}
		}

		if ( $default_ping_status === 'open' && ! $has_security_plugin ) {
			$issues[] = __( 'No security plugin detected to filter malicious pingbacks', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/pingback-spam-prevention',
			);
		}

		return null;
	}
}
