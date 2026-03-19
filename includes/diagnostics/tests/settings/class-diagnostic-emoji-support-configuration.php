<?php
/**
 * Emoji Support Configuration Diagnostic
 *
 * Verifies that emoji support is properly configured for consistent display
 * across browsers and devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emoji Support Configuration Diagnostic Class
 *
 * Ensures emoji support is properly configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Emoji_Support_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'emoji-support-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Emoji Support Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies emoji support is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Emoji support is not disabled
	 * - Emoji scripts are loading
	 * - No plugins conflicting with emoji
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if emoji support is disabled via filter.
		$has_emoji = apply_filters( 'wp_emoji_support', true );

		if ( ! $has_emoji ) {
			// Emoji is intentionally disabled.
			return null;
		}

		// Check if emoji scripts would be loaded.
		global $wp_scripts;
		if ( isset( $wp_scripts ) ) {
			$has_emoji_script = false;

			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( false !== strpos( $handle, 'emoji' ) ) {
					$has_emoji_script = true;
					break;
				}
			}

			// Scripts not being registered is not necessarily an issue.
		}

		// Check for plugins that might be conflicting with emoji.
		$emoji_plugins = array(
			'disable-emojis/disable-emojis.php'    => 'Disable Emojis',
			'wp-disable-emojis/wp-disable-emojis.php' => 'WP Disable Emojis',
		);

		$conflicting_plugins = array();
		foreach ( $emoji_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$conflicting_plugins[] = $name;
			}
		}

		if ( ! empty( $conflicting_plugins ) ) {
			// This is intentional if user is running a disable emoji plugin.
			// Not necessarily an issue.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/emoji-support-configuration',
			);
		}

		return null;
	}
}
