<?php
/**
 * Insecure Random Number Generation Diagnostic
 *
 * Checks for use of insecure random number generation functions (rand/mt_rand)
 * in security contexts and verifies usage of cryptographically secure alternatives.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insecure Random Number Generation Diagnostic Class
 *
 * Detects use of insecure randomness functions and promotes cryptographically
 * secure alternatives for security-sensitive operations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Insecure_Random extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'uses_secure_random';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Insecure Random Number Generation';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies cryptographically secure random number generation is used';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check PHP version for random_bytes/random_int support (30 points).
		$php_version = phpversion();
		if ( version_compare( $php_version, '7.0', '>=' ) ) {
			$earned_points += 30;
			$stats['secure_random_available'] = true;
			$stats['php_version'] = $php_version;
		} else {
			$issues[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP %s does not have native random_bytes()/random_int() support', 'wpshadow' ),
				$php_version
			);
			$stats['secure_random_available'] = false;
		}

		// Check for WordPress salt/key constants (25 points).
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

		$defined_count = 0;
		$weak_count    = 0;

		foreach ( $required_constants as $constant ) {
			if ( defined( $constant ) ) {
				$defined_count++;
				$value = constant( $constant );

				// Check if value is default (weak).
				if ( empty( $value ) || 'put your unique phrase here' === $value || strlen( $value ) < 64 ) {
					$weak_count++;
				}
			}
		}

		$stats['security_constants_defined'] = $defined_count . ' / ' . count( $required_constants );

		if ( $defined_count === count( $required_constants ) && $weak_count === 0 ) {
			$earned_points += 25;
		} elseif ( $defined_count >= 6 && $weak_count <= 2 ) {
			$earned_points += 15;
			$warnings[] = sprintf(
				/* translators: %d: Number of weak constants */
				_n(
					'%d security constant has weak/default value',
					'%d security constants have weak/default values',
					$weak_count,
					'wpshadow'
				),
				$weak_count
			);
		} else {
			$issues[] = sprintf(
				/* translators: 1: Defined count, 2: Required count */
				__( 'Only %1$d of %2$d required security constants are properly defined', 'wpshadow' ),
				$defined_count - $weak_count,
				count( $required_constants )
			);
		}

		if ( $weak_count > 0 ) {
			$stats['weak_constants'] = $weak_count;
		}

		// Check for security plugins with entropy improvements (20 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
				$earned_points    += 7; // Up to 20 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		} else {
			$warnings[] = 'No security plugins detected';
		}

		// Check for random library plugins (15 points).
		$random_plugins = array(
			'random-compat/random-compat.php'     => 'Random Compat',
			'wp-random/wp-random.php'             => 'WP Random',
		);

		$active_random = array();
		foreach ( $random_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_random[] = $plugin_name;
				$earned_points  += 8; // Up to 15 points.
			}
		}

		if ( count( $active_random ) > 0 ) {
			$stats['random_library_plugins'] = implode( ', ', $active_random );
		}

		// Check for HTTPS (10 points).
		if ( is_ssl() ) {
			$earned_points += 10;
			$stats['https_enabled'] = true;
		} else {
			$issues[] = 'HTTPS not enabled - secure random values transmitted without encryption';
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 70%.
		if ( $score < 70 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 80 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your random number generation security scored %s. Using insecure random functions like rand() or mt_rand() in security contexts (tokens, passwords, keys) makes them predictable. Attackers can guess these values and bypass security controls. Use random_bytes() or random_int() for cryptographically secure randomness.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/insecure-random-number-generation',
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
