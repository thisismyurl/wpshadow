<?php
/**
 * Member-Generated Content Moderation Diagnostic
 *
 * Checks if membership sites implement proper content moderation for
 * user-submitted content including approval workflows and spam prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Membership
 * @since      1.6031.1502
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Membership;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Content Moderation Diagnostic Class
 *
 * Verifies membership sites implement proper content moderation.
 *
 * @since 1.6031.1502
 */
class Diagnostic_Member_Content_Moderation extends Diagnostic_Base {

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
	protected static $description = 'Verifies membership sites implement content moderation and spam prevention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1502
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for membership plugins.
		$membership_plugins = array(
			'memberpress',
			'paid-memberships-pro',
			'restrict-content',
			's2member',
			'wishlist-member',
		);

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
			return null; // No membership.
		}

		$issues = array();

		// Check for moderation workflow.
		if ( ! get_option( 'comment_moderation' ) ) {
			$issues[] = __( 'Content moderation not enabled (posts go live immediately)', 'wpshadow' );
		}

		// Check for anti-spam.
		$has_antispam = false;
		$antispam_plugins = array(
			'akismet',
			'antispam-bee',
			'spam-protection',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $antispam_plugins as $spam_plugin ) {
				if ( stripos( $plugin, $spam_plugin ) !== false ) {
					$has_antispam = true;
					break 2;
				}
			}
		}

		if ( ! $has_antispam ) {
			$issues[] = __( 'No anti-spam plugin detected', 'wpshadow' );
		}

		// Check for content filtering.
		$has_filtering = false;
		$filter_plugins = array(
			'word-filter',
			'profanity-filter',
			'content-control',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $filter_plugins as $filt_plugin ) {
				if ( stripos( $plugin, $filt_plugin ) !== false ) {
					$has_filtering = true;
					break 2;
				}
			}
		}

		if ( ! $has_filtering ) {
			$issues[] = __( 'No content filtering plugin found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Member content moderation concerns: %s. Membership sites should implement moderation workflows and spam prevention for user-generated content.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/member-content-moderation',
		);
	}
}
