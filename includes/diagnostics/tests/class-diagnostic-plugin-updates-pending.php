<?php
/**
 * Plugin Updates Pending Diagnostic
 *
 * Detects when plugin updates are available and should be applied
 * to maintain security, stability, and compatibility.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PluginUpdatesPending Class
 *
 * Checks for available plugin updates that should be installed.
 * Outdated plugins can pose security risks, introduce bugs, or
 * miss important features and compatibility improvements.
 *
 * @since 1.2601.2148
 */
class Diagnostic_PluginUpdatesPending extends Diagnostic_Base {

	/**
	 * The diagnostic slug/identifier
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $slug = 'plugin-updates-pending';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $title = 'Plugin Updates Pending';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $description = 'Detects available plugin updates that should be applied for security and stability.';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if there are plugin updates available using WordPress core functions.
	 * Returns finding data if updates are pending, null if all plugins are up to date.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Finding array if updates are available, null otherwise.
	 *
	 *     @type string $id            Finding identifier (matches slug).
	 *     @type string $title         Human-readable title.
	 *     @type string $description   Detailed description of the finding.
	 *     @type string $severity      Issue severity level.
	 *     @type string $category      Finding category.
	 *     @type int    $threat_level  Threat rating (0-100).
	 *     @type bool   $auto_fixable  Whether this can be auto-fixed.
	 *     @type string $kb_link       Knowledge base article URL.
	 *     @type string $training_link Training resource URL.
	 * }
	 */
	public static function check(): ?array {
		// Ensure the required function is available.
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		// Get available plugin updates.
		$updates = get_plugin_updates();

		// No updates available - site is healthy.
		if ( empty( $updates ) ) {
			return null;
		}

		// Count updates and categorize by type.
		$update_count = count( $updates );

		// Build description with details.
		$description = sprintf(
			/* translators: %d: number of plugin updates available */
			_n(
				'%d plugin update is available. Keeping plugins up to date is essential for security, stability, and compatibility.',
				'%d plugin updates are available. Keeping plugins up to date is essential for security, stability, and compatibility.',
				$update_count,
				'wpshadow'
			),
			$update_count
		);

		return array(
			'id'            => static::$slug,
			'title'         => sprintf(
				/* translators: %d: number of plugin updates */
				_n(
					'%d Plugin Update Available',
					'%d Plugin Updates Available',
					$update_count,
					'wpshadow'
				),
				$update_count
			),
			'description'   => $description,
			'severity'      => 'medium',
			'category'      => 'maintenance',
			'threat_level'  => 50,
			'auto_fixable'  => false, // User should manually review and update.
			'kb_link'       => 'https://wpshadow.com/kb/plugin-updates-pending',
			'training_link' => 'https://wpshadow.com/training/managing-plugin-updates',
		);
	}

	/**
	 * Test the diagnostic check with live data.
	 *
	 * Validates that the check() method correctly detects plugin updates
	 * and returns appropriate results based on the current system state.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Test result message.
	 * }
	 */
	public static function test_live_plugin_updates_pending(): array {
		// Ensure function is available.
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		// Get actual updates.
		$updates = get_plugin_updates();

		// Run the check.
		$result = self::check();

		// Validate logic: if updates exist, should return finding; if not, should return null.
		if ( ! empty( $updates ) ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => sprintf(
						/* translators: %d: number of plugin updates */
						__( 'Plugin updates available (%d), but check() returned null.', 'wpshadow' ),
						count( $updates )
					),
				);
			}

			// Verify structure of returned finding.
			$required_keys = array( 'id', 'title', 'description', 'severity', 'threat_level', 'auto_fixable' );
			foreach ( $required_keys as $key ) {
				if ( ! isset( $result[ $key ] ) ) {
					return array(
						'passed'  => false,
						'message' => sprintf(
							/* translators: %s: missing key name */
							__( 'Finding array missing required key: %s', 'wpshadow' ),
							$key
						),
					);
				}
			}
		} elseif ( ! is_null( $result ) ) {
			return array(
				'passed'  => false,
				'message' => __( 'No plugin updates available, but check() returned a finding.', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => true,
			'message' => ! empty( $updates ) ?
				sprintf(
					/* translators: %d: number of plugin updates */
					__( 'Correctly detected %d plugin update(s).', 'wpshadow' ),
					count( $updates )
				) :
				__( 'Correctly detected no plugin updates.', 'wpshadow' ),
		);
	}
}
