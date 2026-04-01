<?php
/**
 * No Community or Forum Strategy Diagnostic
 *
 * Detects when community engagement is not being leveraged,
 * missing network effects and organic growth.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Community or Forum Strategy
 *
 * Checks whether community engagement is
 * being used to drive organic growth.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Community_Or_Forum_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-community-forum-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community or Forum Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether community engagement exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for community plugins
		$has_community = is_plugin_active( 'buddypress/bp-loader.php' ) ||
			is_plugin_active( 'asgaros-forum/asgaros-forum.php' ) ||
			is_plugin_active( 'wp-user-frontend/wpuf.php' );

		// Check if comments are enabled and active
		$comment_count = wp_count_comments();
		$has_engagement = (int) $comment_count->total_comments > 0;

		if ( ! $has_community && ! $has_engagement ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not building community, which means missing network effects and organic growth. Community examples: forums where users help each other, member profiles, user groups, comment sections. Community benefits: users recruiting other users (viral growth), organic content creation (members post), customer support crowdsourced (members answer questions), switching costs (users invested in relationships). Companies with strong communities see 2-3x better retention.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'User Retention & Organic Growth',
					'potential_gain' => '+2-3x better retention from community investment',
					'roi_explanation' => 'Community creates network effects, viral growth, and higher switching costs, improving retention 2-3x.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/community-forum-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
