<?php
/**
 * Forum Community Moderation Policy Diagnostic
 *
 * Verifies forums have clear community guidelines and moderation
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Forum
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_ForumModerationPolicy Class
 *
 * Checks for: community guidelines, moderation tools, anti-spam
 *
 * @since 1.6031.1445
 */
class Diagnostic_ForumModerationPolicy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'forum-moderation-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Forum Community Moderation Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies forums have clear community guidelines and moderation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for community-guidelines page.
		$page = get_page_by_path( 'community-guidelines' );
		if ( ! $page ) {
			$issues[] = __( 'No community guidelines page', 'wpshadow' );
		}

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'moderation', 'anti-spam', 'akismet', 'bbpress' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: No moderation tools detected

		// Additional checks would go here for: No anti-spam protection

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Moderation concerns: %s. Forums need clear community guidelines.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-moderation-policy',
		);
	}
}
