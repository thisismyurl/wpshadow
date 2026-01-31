<?php
/**
 * Member-Generated Content Moderation Diagnostic
 *
 * Verifies membership sites have content moderation systems
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_MemberContentModeration Class
 *
 * Checks for moderation workflow, anti-spam, content filtering
 *
 * @since 1.6031.1445
 */
class Diagnostic_MemberContentModeration extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'member-content-moderation';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Member-Generated Content Moderation';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies membership sites have content moderation systems';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'ecommerce';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for membership/community plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$membership_plugins = array( 'membership', 'memberpress', 'paid-memberships-pro', 'restrict-content' );
		$has_membership = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $membership_plugins as $mem_plugin ) {
				if ( stripos( $plugin, $mem_plugin ) !== false ) {
					$has_membership = true;
					break 2;
				}
			}
		}

		if ( ! $has_membership ) {
			return null;
		}

		$issues = array();

		// Check for content moderation.
		if ( get_option( 'comment_moderation' ) !== '1' ) {
			$issues[] = __( 'Comment moderation not enabled', 'wpshadow' );
		}

		// Check for spam filtering.
		$spam_plugins = array( 'akismet', 'antispam', 'cleantalk', 'stop-spammer' );
		$has_spam_filter = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $spam_plugins as $spam_plugin ) {
				if ( stripos( $plugin, $spam_plugin ) !== false ) {
					$has_spam_filter = true;
					break 2;
				}
			}
		}

		if ( ! $has_spam_filter ) {
			$issues[] = __( 'No spam filtering plugin detected', 'wpshadow' );
		}

		// Check for profanity filters.
		$profanity_plugins = array( 'profanity', 'word-filter', 'content-filter' );
		$has_profanity = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $profanity_plugins as $prof_plugin ) {
				if ( stripos( $plugin, $prof_plugin ) !== false ) {
					$has_profanity = true;
					break 2;
				}
			}
		}

		if ( ! $has_profanity ) {
			$issues[] = __( 'No profanity/content filter plugin detected', 'wpshadow' );
		}

		// Check for user reporting system.
		$reporting_plugins = array( 'report', 'flag', 'abuse' );
		$has_reporting = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $reporting_plugins as $rep_plugin ) {
				if ( stripos( $plugin, $rep_plugin ) !== false ) {
					$has_reporting = true;
					break 2;
				}
			}
		}

		if ( ! $has_reporting ) {
			$issues[] = __( 'No user reporting/flagging system detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Member content moderation concerns: %s. Membership sites need robust moderation tools.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/member-content-moderation',
		);
