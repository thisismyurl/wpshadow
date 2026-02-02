<?php
/**
 * Data Masking in UI Diagnostic
 *
 * Detects sensitive data displayed without proper masking in
 * admin interfaces and user-facing pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2107
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Masking in UI Diagnostic Class
 *
 * Checks for:
 * - Credit card numbers displayed in full
 * - Password fields using type="text" instead of type="password"
 * - API keys visible in settings pages
 * - Sensitive data in HTML comments
 * - Personal data in JavaScript variables
 * - Unmasked SSN or identification numbers
 *
 * According to PCI-DSS requirement 3.3, PANs (Primary Account Numbers)
 * must be masked when displayed, showing only the first 6 and last 4
 * digits at most. GDPR requires minimizing exposure of personal data.
 *
 * @since 1.2033.2107
 */
class Diagnostic_Data_Masking_In_UI extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2107
	 * @var   string
	 */
	protected static $slug = 'data-masking-in-ui';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2107
	 * @var   string
	 */
	protected static $title = 'Data Masking in UI';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2107
	 * @var   string
	 */
	protected static $description = 'Verifies sensitive data is properly masked in user interfaces';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2107
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans admin pages and templates for unmasked sensitive data.
	 *
	 * @since  1.2033.2107
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Look for password fields using type="text".
		$text_passwords = self::scan_for_text_password_fields();
		if ( ! empty( $text_passwords ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d password field using type="text" instead of type="password"',
					'Found %d password fields using type="text" instead of type="password"',
					count( $text_passwords ),
					'wpshadow'
				),
				count( $text_passwords )
			);
		}

		// Check 2: Check for unmasked API keys in settings.
		$visible_keys = self::check_settings_pages_for_visible_keys();
		if ( ! empty( $visible_keys ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d settings pages displaying API keys without masking', 'wpshadow' ),
				count( $visible_keys )
			);
		}

		// Check 3: Look for credit card patterns in rendered HTML.
		$cc_in_html = self::scan_for_credit_cards_in_html();
		if ( $cc_in_html ) {
			$issues[] = __( 'Credit card numbers may be displayed without masking in HTML', 'wpshadow' );
		}

		// Check 4: Check for sensitive data in HTML comments.
		$data_in_comments = self::scan_for_data_in_comments();
		if ( ! empty( $data_in_comments ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d files with sensitive data in HTML comments', 'wpshadow' ),
				count( $data_in_comments )
			);
		}

		// Check 5: Check JavaScript for exposed sensitive data.
		$js_exposure = self::scan_javascript_for_sensitive_data();
		if ( ! empty( $js_exposure ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d JavaScript files with potentially exposed sensitive data', 'wpshadow' ),
				count( $js_exposure )
			);
		}

		// Check 6: Check for SSN patterns in templates.
		$ssn_exposure = self::scan_for_ssn_display();
		if ( $ssn_exposure ) {
			$issues[] = __( 'Social Security Numbers may be displayed without masking', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d data masking issue detected',
						'%d data masking issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-masking',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Unmasked sensitive data in UI creates multiple security and compliance risks. ' .
						'Shoulder surfing, screen recording malware, screenshot leaks, and support sessions can expose data. ' .
						'PCI-DSS requirement 3.3 mandates masking credit card numbers (showing max first 6 and last 4 digits). ' .
						'GDPR Article 32 requires appropriate security measures including data minimization in displays. ' .
						'Visible API keys in settings pages can be captured by browser extensions or compromised admin accounts. ' .
						'According to Verizon DBIR, 30% of breaches involve social engineering, where visible data aids attackers.',
						'wpshadow'
					),
					'recommendation' => __(
						'Always use type="password" for password fields. Mask credit cards showing only last 4 digits (•••• 1234). ' .
						'Display API keys partially (sk_live_••••••••1234) with "Show" toggle. Remove sensitive data from HTML comments. ' .
						'Avoid storing secrets in JavaScript variables. Implement click-to-reveal for sensitive fields in admin. ' .
						'Use CSS masking: content: "••••" with data-masked attribute. Log access when unmasking data.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}

	/**
	 * Scan for password fields using type="text".
	 *
	 * @since  1.2033.2107
	 * @return array Files with issue.
	 */
	private static function scan_for_text_password_fields() {
		$found = array();
		$theme_dir = get_stylesheet_directory();

		// Pattern: input field with "password" in name but type="text".
		$pattern = '/<input[^>]*name=["\'][^"\']*password[^"\']*["\'][^>]*type=["\']text["\']/i';

		$php_files = self::get_php_files( $theme_dir, 30 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				$found[] = str_replace( ABSPATH, '', $file );
			}
		}

		return $found;
	}

	/**
	 * Check settings pages for visible API keys.
	 *
	 * @since  1.2033.2107
	 * @return array Files with visible keys.
	 */
	private static function check_settings_pages_for_visible_keys() {
		$found = array();
		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Pattern: input fields with "api_key" or "secret" showing full value.
		$pattern = '/<input[^>]*name=["\'][^"\']*(?:api_key|secret|token)[^"\']*["\'][^>]*value=["\'][a-zA-Z0-9_-]{20,}["\']/i';

		// Scan theme.
		$theme_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $theme_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				$found[] = str_replace( ABSPATH, '', $file );
			}
		}

		// Scan top 5 active plugins.
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_path = $plugin_dir . '/' . dirname( $plugin );
			if ( ! is_dir( $plugin_path ) ) {
				continue;
			}

			$plugin_files = self::get_php_files( $plugin_path, 10 );
			foreach ( $plugin_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				if ( preg_match( $pattern, $content ) ) {
					$found[] = str_replace( ABSPATH, '', $file );
				}
			}
		}

		return $found;
	}

	/**
	 * Scan for credit card patterns in HTML.
	 *
	 * @since  1.2033.2107
	 * @return bool True if found.
	 */
	private static function scan_for_credit_cards_in_html() {
		$theme_dir = get_stylesheet_directory();
		
		// Pattern: 16 digit sequences that look like credit cards.
		$pattern = '/\b[0-9]{4}[\s-]?[0-9]{4}[\s-]?[0-9]{4}[\s-]?[0-9]{4}\b/';

		$php_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			
			// Skip if it's test/demo data.
			if ( str_contains( $content, '4111' ) || str_contains( $content, 'xxxx' ) ) {
				continue;
			}

			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Scan for sensitive data in HTML comments.
	 *
	 * @since  1.2033.2107
	 * @return array Files with issue.
	 */
	private static function scan_for_data_in_comments() {
		$found = array();
		$theme_dir = get_stylesheet_directory();

		// Pattern: HTML comments with sensitive keywords.
		$pattern = '/<!--[^>]*(?:password|api[_-]?key|secret|token|credit[_-]?card)[^>]*-->/i';

		$php_files = self::get_php_files( $theme_dir, 30 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				$found[] = str_replace( ABSPATH, '', $file );
			}
		}

		return $found;
	}

	/**
	 * Scan JavaScript for sensitive data.
	 *
	 * @since  1.2033.2107
	 * @return array Files with issue.
	 */
	private static function scan_javascript_for_sensitive_data() {
		$found = array();
		$theme_dir = get_stylesheet_directory();

		$js_dirs = array( $theme_dir . '/js', $theme_dir . '/assets/js' );
		$pattern = '/(?:apiKey|secretKey|password|token)\s*[:=]\s*["\'][^"\']{20,}["\']/';

		foreach ( $js_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$js_files = glob( $dir . '/*.js' );
			foreach ( $js_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				if ( preg_match( $pattern, $content ) ) {
					$found[] = str_replace( ABSPATH, '', $file );
				}
			}
		}

		return $found;
	}

	/**
	 * Scan for SSN display patterns.
	 *
	 * @since  1.2033.2107
	 * @return bool True if found.
	 */
	private static function scan_for_ssn_display() {
		$theme_dir = get_stylesheet_directory();
		
		// Pattern: SSN format XXX-XX-XXXX in display context.
		$pattern = '/echo\s+[^;]*\d{3}-\d{2}-\d{4}/';

		$php_files = self::get_php_files( $theme_dir, 20 );
		foreach ( $php_files as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since  1.2033.2107
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files.
	 * @return array File paths.
	 */
	private static function get_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}
}
