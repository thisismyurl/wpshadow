<?php
/**
 * Security Keys and Salts Diagnostic
 *
 * Checks if AUTH_KEY, SECURE_AUTH_KEY, and salts are unique, verifies they're
 * not default values, and tests for key rotation mechanisms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.1620
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Keys and Salts Diagnostic Class
 *
 * Verifies WordPress security keys and salts are properly configured
 * with unique, strong values to protect authentication and sessions.
 *
 * @since 1.6035.1620
 */
class Diagnostic_Security_Keys extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'has_secure_keys_salts';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Security Keys and Salts';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress security keys and salts are unique and not default values';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1620
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Required security constants.
		$required_constants = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		$defined_count   = 0;
		$weak_count      = 0;
		$default_count   = 0;
		$duplicate_count = 0;

		$values = array();

		// Check each constant (10 points each = 80 points total).
		foreach ( $required_constants as $constant ) {
			if ( defined( $constant ) ) {
				$defined_count++;
				$value = constant( $constant );
				$values[] = $value;

				// Check for default/weak values.
				if ( empty( $value ) || 'put your unique phrase here' === $value ) {
					$default_count++;
					$issues[] = sprintf(
						/* translators: %s: Constant name */
						__( '%s has default value', 'wpshadow' ),
						$constant
					);
				} elseif ( strlen( $value ) < 64 ) {
					$weak_count++;
					$warnings[] = sprintf(
						/* translators: %s: Constant name */
						__( '%s is shorter than recommended 64 characters', 'wpshadow' ),
						$constant
					);
				} else {
					$earned_points += 10;
				}
			} else {
				$issues[] = sprintf(
					/* translators: %s: Constant name */
					__( '%s is not defined', 'wpshadow' ),
					$constant
				);
			}
		}

		// Check for duplicate values (security risk).
		$unique_values = array_unique( $values );
		if ( count( $values ) !== count( $unique_values ) ) {
			$duplicate_count = count( $values ) - count( $unique_values );
			$issues[] = sprintf(
				/* translators: %d: Number of duplicates */
				_n(
					'%d security constant uses a duplicate value',
					'%d security constants use duplicate values',
					$duplicate_count,
					'wpshadow'
				),
				$duplicate_count
			);
		}

		$stats['constants_defined'] = $defined_count . ' / ' . count( $required_constants );
		$stats['default_values']    = $default_count;
		$stats['weak_values']       = $weak_count;
		$stats['duplicate_values']  = $duplicate_count;

		// Check for key rotation plugins (10 points).
		$key_rotation_plugins = array(
			'salt-shaker/salt-shaker.php'           => 'Salt Shaker',
			'wp-config-file-editor/wp-config-file-editor.php' => 'WP Config File Editor',
		);

		$active_rotation = array();
		foreach ( $key_rotation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_rotation[] = $plugin_name;
				$earned_points    += 5; // Up to 10 points.
			}
		}

		if ( count( $active_rotation ) > 0 ) {
			$stats['key_rotation_plugins'] = implode( ', ', $active_rotation );
		} else {
			$warnings[] = 'No key rotation plugins detected - consider rotating keys periodically';
		}

		// Check for security plugins (10 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                   => 'Wordfence Security',
			'better-wp-security/better-wp-security.php' => 'iThemes Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
				$earned_points    += 5; // Up to 10 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 80% (critical for authentication).
		if ( $score < 80 ) {
			$severity     = $score < 60 ? 'high' : 'medium';
			$threat_level = $score < 60 ? 80 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your security keys and salts scored %s. WordPress uses these keys to encrypt authentication cookies and other sensitive data. Default or weak keys make it easy for attackers to forge authentication cookies and impersonate users. Strong, unique keys are essential for site security.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-keys-and-salts',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
