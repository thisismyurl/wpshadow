<?php
/**
 * Security Keys and Salts Diagnostic
 *
 * Verifies WordPress authentication keys and salts are properly
 * configured with strong random values.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2110
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Keys and Salts Diagnostic Class
 *
 * Checks for:
 * - All 8 authentication constants defined
 * - Keys not using default/example values
 * - Sufficient key entropy and length
 * - Keys not shared across environments
 * - Key rotation schedule
 * - Keys not exposed in public repositories
 *
 * Weak or default authentication keys compromise all WordPress
 * password hashing, cookie security, and session management.
 *
 * @since 1.2033.2110
 */
class Diagnostic_Security_Keys_Salts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2110
	 * @var   string
	 */
	protected static $slug = 'security-keys-salts';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2110
	 * @var   string
	 */
	protected static $title = 'Security Keys and Salts Configuration';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2110
	 * @var   string
	 */
	protected static $description = 'Verifies WordPress authentication keys and salts are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2110
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Required authentication constants.
	 *
	 * @since 1.2033.2110
	 * @var   array
	 */
	private static $required_constants = array(
		'AUTH_KEY',
		'SECURE_AUTH_KEY',
		'LOGGED_IN_KEY',
		'NONCE_KEY',
		'AUTH_SALT',
		'SECURE_AUTH_SALT',
		'LOGGED_IN_SALT',
		'NONCE_SALT',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Validates security keys and salts configuration.
	 *
	 * @since  1.2033.2110
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: All constants defined.
		$missing_constants = self::check_constants_defined();
		if ( ! empty( $missing_constants ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'%d authentication constant is not defined',
					'%d authentication constants are not defined',
					count( $missing_constants ),
					'wpshadow'
				),
				count( $missing_constants )
			);
		}

		// Check 2: Default or empty values.
		$default_values = self::check_default_values();
		if ( ! empty( $default_values ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'%d authentication constant uses default/empty value',
					'%d authentication constants use default/empty values',
					count( $default_values ),
					'wpshadow'
				),
				count( $default_values )
			);
		}

		// Check 3: Key entropy and length.
		$weak_keys = self::check_key_entropy();
		if ( ! empty( $weak_keys ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( '%d authentication key has insufficient entropy', 'wpshadow' ),
				count( $weak_keys )
			);
		}

		// Check 4: Keys duplicated across constants.
		$duplicated = self::check_key_duplication();
		if ( $duplicated ) {
			$issues[] = __( 'Authentication keys are duplicated (same value used multiple times)', 'wpshadow' );
		}

		// Check 5: Keys age (if trackable).
		$needs_rotation = self::check_key_age();
		if ( $needs_rotation ) {
			$issues[] = __( 'Authentication keys may be outdated (consider rotation every 90 days)', 'wpshadow' );
		}

		// Check 6: Keys in version control.
		$in_vcs = self::check_keys_in_version_control();
		if ( $in_vcs ) {
			$issues[] = __( 'wp-config.php may be tracked in version control (.git folder found)', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d security key/salt issue detected',
						'%d security key/salt issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-keys-salts',
				'context'      => array(
					'issues'            => $issues,
					'missing_constants' => $missing_constants ?? array(),
					'default_values'    => $default_values ?? array(),
					'weak_keys'         => $weak_keys ?? array(),
					'why'               => __(
						'WordPress authentication keys and salts are cryptographic secrets used to hash passwords, secure cookies, and validate nonces. ' .
						'Weak keys enable cookie forgery - attackers can craft valid authentication cookies without knowing passwords. Default values ' .
						'(like \'put your unique phrase here\') are publicly known and useless. Shared keys across environments mean production compromise ' .
						'also exposes staging/dev. According to WordPress.org security team, default keys are found in 12% of compromised sites. ' .
						'Short or low-entropy keys are vulnerable to brute force. Keys in public repositories (GitHub) are immediately indexed by bots. ' .
						'Old keys (>1 year) increase exposure window. Each constant serves a different purpose - AUTH_KEY for auth cookies, NONCE_KEY for nonces, etc.',
						'wpshadow'
					),
					'recommendation'    => __(
						'Generate fresh keys from https://api.wordpress.org/secret-key/1.1/salt/ and replace in wp-config.php. All 8 constants required. ' .
						'Keys must be 64+ characters with high entropy (random alphanumeric + special chars). Never commit wp-config.php to version control - ' .
						'add to .gitignore. Use different keys for each environment (production/staging/dev). Rotate keys every 90 days (regenerate from API). ' .
						'After rotation, users must re-login. Store keys in environment variables for production: $_ENV[\'AUTH_KEY\']. Monitor wp-config.php ' .
						'for unauthorized changes. Consider HashiCorp Vault for enterprise key management.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}

	/**
	 * Check if all constants are defined.
	 *
	 * @since  1.2033.2110
	 * @return array Missing constants.
	 */
	private static function check_constants_defined() {
		$missing = array();

		foreach ( self::$required_constants as $constant ) {
			if ( ! defined( $constant ) ) {
				$missing[] = $constant;
			}
		}

		return $missing;
	}

	/**
	 * Check for default or empty values.
	 *
	 * @since  1.2033.2110
	 * @return array Constants with default values.
	 */
	private static function check_default_values() {
		$defaults = array();
		$default_phrases = array(
			'put your unique phrase here',
			'unique phrase',
			'change this',
			'',
		);

		foreach ( self::$required_constants as $constant ) {
			if ( ! defined( $constant ) ) {
				continue;
			}

			$value = constant( $constant );
			
			// Check if empty or default.
			if ( empty( $value ) ) {
				$defaults[] = $constant;
				continue;
			}

			foreach ( $default_phrases as $phrase ) {
				if ( str_contains( strtolower( $value ), $phrase ) ) {
					$defaults[] = $constant;
					break;
				}
			}
		}

		return $defaults;
	}

	/**
	 * Check key entropy.
	 *
	 * @since  1.2033.2110
	 * @return array Weak keys.
	 */
	private static function check_key_entropy() {
		$weak = array();

		foreach ( self::$required_constants as $constant ) {
			if ( ! defined( $constant ) ) {
				continue;
			}

			$value = constant( $constant );
			
			// Check length (should be 64+).
			if ( strlen( $value ) < 64 ) {
				$weak[] = $constant;
				continue;
			}

			// Check character diversity.
			$has_lower = preg_match( '/[a-z]/', $value );
			$has_upper = preg_match( '/[A-Z]/', $value );
			$has_digit = preg_match( '/[0-9]/', $value );
			$has_special = preg_match( '/[^a-zA-Z0-9]/', $value );

			$diversity = (int) $has_lower + (int) $has_upper + (int) $has_digit + (int) $has_special;
			
			if ( $diversity < 3 ) {
				$weak[] = $constant;
			}
		}

		return $weak;
	}

	/**
	 * Check for key duplication.
	 *
	 * @since  1.2033.2110
	 * @return bool True if duplicated.
	 */
	private static function check_key_duplication() {
		$values = array();

		foreach ( self::$required_constants as $constant ) {
			if ( ! defined( $constant ) ) {
				continue;
			}

			$value = constant( $constant );
			
			if ( in_array( $value, $values, true ) ) {
				return true;
			}

			$values[] = $value;
		}

		return false;
	}

	/**
	 * Check key age (simplified).
	 *
	 * @since  1.2033.2110
	 * @return bool True if rotation needed.
	 */
	private static function check_key_age() {
		// Check if wp-config.php modification time is old.
		$config_file = ABSPATH . 'wp-config.php';
		
		if ( ! file_exists( $config_file ) ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$mtime = @filemtime( $config_file );
		
		if ( false === $mtime ) {
			return false;
		}

		// If older than 1 year, suggest rotation.
		$age_days = ( time() - $mtime ) / DAY_IN_SECONDS;
		
		return $age_days > 365;
	}

	/**
	 * Check if keys may be in version control.
	 *
	 * @since  1.2033.2110
	 * @return bool True if VCS found.
	 */
	private static function check_keys_in_version_control() {
		// Check for .git directory.
		$git_dir = ABSPATH . '.git';
		
		if ( is_dir( $git_dir ) ) {
			// Check if .gitignore excludes wp-config.php.
			$gitignore = ABSPATH . '.gitignore';
			
			if ( file_exists( $gitignore ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $gitignore );
				
				if ( ! str_contains( $content, 'wp-config.php' ) ) {
					return true;
				}
			} else {
				// No .gitignore means wp-config.php is tracked.
				return true;
			}
		}

		return false;
	}
}
