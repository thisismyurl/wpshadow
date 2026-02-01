<?php
/**
 * Discussion Settings Security Diagnostic
 *
 * Verifies discussion settings are securely configured.
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
 * Discussion Settings Security Diagnostic Class
 *
 * Performs comprehensive security check of all discussion settings.
 *
 * @since 1.26032.1755
 */
class Diagnostic_Discussion_Settings_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-settings-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Settings Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive discussion settings security check';

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
		$security_score = 100;

		// Check comment registration requirement.
		$comment_registration = get_option( 'comment_registration', 0 );
		if ( ! $comment_registration ) {
			$issues[] = __( 'Anonymous commenting allowed - higher spam risk', 'wpshadow' );
			$security_score -= 15;
		}

		// Check comment moderation.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		if ( ! $comment_moderation ) {
			$issues[] = __( 'New comments auto-approved - spam may be published', 'wpshadow' );
			$security_score -= 20;
		}

		// Check comment author approval.
		$comment_previously_approved = get_option( 'comment_previously_approved', 1 );
		if ( ! $comment_previously_approved ) {
			$issues[] = __( 'First-time commenter approval not required - more spam exposure', 'wpshadow' );
			$security_score -= 10;
		}

		// Check moderation keys.
		$moderation_keys = get_option( 'moderation_keys', '' );
		if ( empty( $moderation_keys ) ) {
			$issues[] = __( 'No moderation keywords configured - automated spam filtering disabled', 'wpshadow' );
			$security_score -= 15;
		}

		// Check blacklist keys (now called disallowed_keys in WP 5.5+).
		$disallowed_keys = get_option( 'disallowed_keys', get_option( 'blacklist_keys', '' ) );
		if ( empty( $disallowed_keys ) ) {
			$issues[] = __( 'No disallowed keywords configured - spam may bypass filters', 'wpshadow' );
			$security_score -= 10;
		}

		// Check comment flooding.
		$comment_max_links = (int) get_option( 'comment_max_links', 2 );
		if ( $comment_max_links > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: max links */
				__( 'Maximum %d links allowed - spammers may abuse this', 'wpshadow' ),
				$comment_max_links
			);
			$security_score -= 10;
		}

		// Check pingbacks/trackbacks.
		$default_ping_status = get_option( 'default_ping_status', 'open' );
		if ( $default_ping_status === 'open' ) {
			$issues[] = __( 'Pingbacks enabled - DDoS vulnerability', 'wpshadow' );
			$security_score -= 15;
		}

		// Check for anti-spam protection.
		$antispam_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
		);

		$has_antispam = false;
		foreach ( $antispam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_antispam = true;
				break;
			}
		}

		if ( ! $has_antispam && ! $comment_moderation ) {
			$issues[] = __( 'No anti-spam plugin and weak moderation - high spam risk', 'wpshadow' );
			$security_score -= 20;
		}

		// Determine severity based on security score.
		$severity = 'low';
		if ( $security_score < 50 ) {
			$severity = 'critical';
		} elseif ( $security_score < 70 ) {
			$severity = 'high';
		} elseif ( $security_score < 85 ) {
			$severity = 'medium';
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: security score, 2: issues list */
					__( 'Discussion security score: %1$d/100. Issues: %2$s', 'wpshadow' ),
					$security_score,
					implode( '. ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => 100 - $security_score,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/discussion-settings-security',
			);
		}

		return null;
	}
}
