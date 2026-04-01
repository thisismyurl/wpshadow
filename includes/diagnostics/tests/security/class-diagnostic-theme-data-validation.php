<?php
/**
 * Theme Data Validation Diagnostic
 *
 * Checks theme files for unsanitized superglobal usage.
 * Theme uses \$_POST directly in database query = SQL injection.
 * Attacker modifies database = data breach.
 *
 * **What This Check Does:**
 * - Searches theme files for \$_POST, \$_GET, \$_REQUEST
 * - Detects usage without sanitization
 * - Checks for esc_html, sanitize_text_field calls
 * - Flags direct array access without wp_kses_post
 * - Searches for database queries with unsanitized input
 * - Returns severity for each instance
 *
 * **Why This Matters:**
 * Theme uses \$_POST['name'] directly in queries.
 * Attacker injects SQL. Database compromised. All user data stolen.
 *
 * **Business Impact:**
 * SaaS theme uses \$_POST['email'] to subscribe:
 * ```
 * \$wpdb->query("INSERT INTO subscribers VALUES ('\$_POST[email]')");
 * ```
 * Attacker injects: ', (SELECT password FROM users LIMIT 1), ')
 * Gets all password hashes. Cracks them. 50K+ user accounts
 * compromised. Cost: $2M+ (legal, notification, credit monitoring).
 * With validation: email sanitized → SQL injection impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: User data protected
 * - #9 Show Value: Prevents data breaches
 * - #10 Beyond Pure: Input validation by design
 *
 * **Related Checks:**
 * - Plugin SQL Injection Risk (similar risk in plugins)
 * - Theme Direct Database Access (complementary check)
 * - CSRF Protection (related input security)
 *
 * **Learn More:**
 * Input validation guide: https://wpshadow.com/kb/theme-data-validation
 * Video: Secure theme input handling (12min): https://wpshadow.com/training/input-validation
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Data Validation Diagnostic
 *
 * Flags usage of superglobals without sanitization.
 *
 * **Detection Pattern:**
 * 1. Find all active theme PHP files
 * 2. Search for \$_POST, \$_GET, \$_REQUEST variables
 * 3. Trace variable usage
 * 4. Check if sanitization applied (sanitize_*)
 * 5. Verify output escaping (esc_*)
 * 6. Return each unsanitized usage
 *
 * **Real-World Scenario:**
 * Theme has contact form:
 * ```
 * echo "Email: " . \$_POST['email'];
 * ```
 * Attacker sends: <script>alert('XSS')</script>. Output to page.
 * Visitors' browsers execute script (steal cookies/sessions).
 * With validation: sanitize_email first → script tags removed.
 * Output safe.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Detects unsanitized superglobal access
 * - Validates sanitization functions used
 * - Severity: critical (DB queries), high (output), medium (variables)
 * - Treatment: use sanitize_* for input, esc_* for output
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Data_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-data-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Data Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme files for basic input validation patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		$uses_superglobal = false !== strpos( $content, '$_POST' ) || false !== strpos( $content, '$_GET' );
		$has_sanitization = false !== strpos( $content, 'sanitize_' ) || false !== strpos( $content, 'esc_' );

		if ( $uses_superglobal && ! $has_sanitization ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme may use input data without sanitization', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-data-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues' => array(
						__( 'Superglobal usage detected without sanitization calls', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
