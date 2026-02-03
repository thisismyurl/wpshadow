<?php
/**
 * Diagnostic: Member Exclusive Content
 *
 * Tests whether the site regularly creates premium content exclusively for
 * members to justify ongoing subscription value.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4546
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Exclusive Content Diagnostic
 *
 * Checks for gated content and regular premium additions. Member-only content
 * increases retention by 40% and justifies recurring payments.
 *
 * @since 1.6034.1450
 */
class Diagnostic_Behavioral_Exclusive_Content extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'creates-member-exclusive-content';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Exclusive Content';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site creates regular premium content for members';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for exclusive content implementation.
	 *
	 * Looks for content restriction and member-only posts/pages.
	 *
	 * @since  1.6034.1450
	 * @return array|null Finding array if no exclusivity, null if present.
	 */
	public static function check() {
		// Check for content restriction plugins.
		$restriction_plugins = array(
			'memberpress/memberpress.php'                    => 'MemberPress',
			'paid-memberships-pro/paid-memberships-pro.php'  => 'Paid Memberships Pro',
			'restrict-content-pro/restrict-content-pro.php'  => 'Restrict Content Pro',
			'members/members.php'                            => 'Members',
		);

		$has_restrictions = false;
		foreach ( $restriction_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_restrictions = true;
				break;
			}
		}

		if ( ! $has_restrictions ) {
			// No restriction plugins - check if membership site.
			$is_membership_site = false;
			
			if ( class_exists( 'WC_Subscriptions' ) ) {
				$is_membership_site = true;
			}

			if ( ! $is_membership_site ) {
				return null; // Not membership site.
			}

			// Membership site without content restrictions.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __(
					'No content restriction system detected. Subscription sites need member-only content to justify recurring payments. Exclusive content increases retention by 40% - members stay for unique value they can\'t get elsewhere. Implement content gating and create regular premium additions for members.',
					'wpshadow'
				),
				'severity'     => 'medium',
				'threat_level' => 47,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exclusive-content',
			);
		}

		// Has restriction plugin - check for recent exclusive content.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'date_query'     => array(
				array(
					'after' => '3 months ago',
				),
			),
		);

		$recent_posts    = get_posts( $args );
		$exclusive_count = 0;

		foreach ( $recent_posts as $post ) {
			// Check for restriction markers in content or metadata.
			$content = $post->post_content;
			
			if ( strpos( $content, '[mepr-' ) !== false ||
			     strpos( $content, '[members-' ) !== false ||
			     strpos( $content, '[pmpro_' ) !== false ) {
				++$exclusive_count;
			}

			// Check post meta for restrictions.
			$meta_keys = array( '_mepr_rules', '_pmpro_rules', '_rcp_restriction' );
			foreach ( $meta_keys as $key ) {
				if ( get_post_meta( $post->ID, $key, true ) ) {
					++$exclusive_count;
					break;
				}
			}
		}

		// Should have at least 4 exclusive posts in 3 months (monthly cadence).
		if ( $exclusive_count >= 4 ) {
			return null; // Regular exclusive content.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of exclusive posts found */
				__(
					'Only %d member-exclusive posts found in the last 3 months. Subscription sites need consistent exclusive content (minimum 1-2 premium additions monthly) to justify recurring payments. Members stay when they feel they\'re getting ongoing value. Create regular exclusive content - guides, tutorials, resources, community access.',
					'wpshadow'
				),
				$exclusive_count
			),
			'severity'     => 'medium',
			'threat_level' => 44,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/exclusive-content-frequency',
		);
	}
}
