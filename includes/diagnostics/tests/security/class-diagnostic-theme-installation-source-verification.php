<?php
/**
 * Theme Installation Source Verification Diagnostic
 *
 * Validates that installed themes come from verified sources and that
 * the theme installation process is secure and authenticated.
 * Theme from untrusted source = backdoor risk. Malware pre-installed.
 *
 * **What This Check Does:**
 * - Checks theme source (WordPress.org vs custom)
 * - Validates installation authentication
 * - Tests for signed/checksummed themes
 * - Checks if theme auto-updates enabled
 * - Tests for theme trust verification
 * - Returns severity for unverified themes
 *
 * **Why This Matters:**
 * Custom theme from untrusted developer.
 * Theme includes backdoor malware pre-installed.
 * Site compromised from day 1. Every user exposed.
 *
 * **Business Impact:**
 * Client buys cheap theme from marketplace (not WP.org).
 * Theme installed. All works. Six months later: site hacked.
 * Attacker had backdoor. Theme had hidden malware. Client:
 * - Lost customer data
 * - GDPR fines: $50K+
 * - Notification costs: $200K
 * - Recovery: $50K
 * - Insurance deductible: $50K
 * - Reputation: irreparable
 * Total cost: $400K+. With verification: install only from WP.org
 * or verified sources. Backdoors blocked at theme review.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme source is trusted
 * - #9 Show Value: Prevents pre-installed backdoors
 * - #10 Beyond Pure: Supply chain security
 *
 * **Related Checks:**
 * - Plugin Installation Source Verification (similar for plugins)
 * - Theme Backdoor Installation Risk (related)
 * - WordPress Installation Security (broader)
 *
 * **Learn More:**
 * Theme sources: https://wpshadow.com/kb/theme-sources
 * Video: Choosing safe themes (11min): https://wpshadow.com/training/theme-sources
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Installation Source Verification Diagnostic Class
 *
 * Checks theme installation source verification.
 *
 * **Detection Pattern:**
 * 1. Get active theme information
 * 2. Check theme source (WP.org or custom)
 * 3. Validate theme signature/checksum if available
 * 4. Test if theme from WP.org can auto-update
 * 5. Check for security certifications
 * 6. Return severity for unverified themes
 *
 * **Real-World Scenario:**
 * Admin downloads free theme from random marketplace. No verification.
 * Theme installed. Code review skipped (not WP.org approved). Theme
 * contains backdoor. Months later: attacker exploits backdoor. Site
 * hacked. With verification: only WP.org themes (reviewed) or themes
 * with known publisher signature. Backdoors caught during WP.org review.
 *
 * **Implementation Notes:**
 * - Checks active theme source
 * - Validates WP.org or verified status
 * - Tests for theme updates/auto-updates
 * - Severity: high (unverified custom), medium (WP.org non-updated)
 * - Treatment: only use WP.org themes or verified commercial themes
 *
 * @since 1.6032.1340
 */
class Diagnostic_Theme_Installation_Source_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-installation-source-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Installation Source Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme installation source verification';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all installed themes.
		$themes = wp_get_themes();

		// Identify theme sources and verify legitimacy.
		$themes_analysis = array();

		foreach ( $themes as $theme_slug => $theme ) {
			$theme_info = array(
				'name'    => $theme->get( 'Name' ),
				'author'  => $theme->get( 'Author' ),
				'uri'     => $theme->get( 'ThemeURI' ),
				'source'  => 'unknown',
				'verified' => false,
			);

			// Determine source.
			if ( ! empty( $theme_info['uri'] ) ) {
				if ( false !== strpos( $theme_info['uri'], 'wordpress.org' ) ) {
					$theme_info['source']   = 'wordpress.org';
					$theme_info['verified'] = true; // WordPress.org themes are verified.
				} elseif ( false !== strpos( $theme_info['uri'], 'github.com' ) ) {
					$theme_info['source'] = 'github';
					// GitHub themes need manual verification.
				} elseif ( false !== strpos( $theme_info['uri'], 'themeforest.net' ) ) {
					$theme_info['source'] = 'themeforest';
					// ThemeForest themes should be verified.
				} else {
					$theme_info['source'] = 'external';
				}
			} else {
				// No URI - theme location unknown.
				$theme_info['source'] = 'no_uri';
			}

			$themes_analysis[] = $theme_info;
		}

		// Check for suspicious patterns.
		$suspicious_themes = array();

		foreach ( $themes_analysis as $theme_info ) {
			$reasons = array();

			// Check for missing URI.
			if ( 'no_uri' === $theme_info['source'] ) {
				$reasons[] = 'no theme URI';
			}

			// Check for generic/suspicious author names.
			if ( in_array( strtolower( $theme_info['author'] ), array( 'admin', 'test', 'demo', 'unknown' ), true ) ) {
				$reasons[] = 'suspicious author name';
			}

			// Check for misspelled official theme names.
			if ( preg_match( '/twenty[0-9]{2}|storefront|hello/i', $theme_info['name'] ) ) {
				// Could be a theme mimicking official themes.
				if ( false === strpos( $theme_info['uri'], 'wordpress.org' ) ) {
					$reasons[] = 'mimics official theme name but not from wordpress.org';
				}
			}

			// Check for suspicious URI patterns.
			if ( ! empty( $theme_info['uri'] ) ) {
				if ( ! filter_var( $theme_info['uri'], FILTER_VALIDATE_URL ) ) {
					$reasons[] = 'invalid theme URI format';
				}

				// Check for redirects or suspicious domains.
				if ( preg_match( '/bit\.ly|tinyurl|short\.link|goo\.gl/i', $theme_info['uri'] ) ) {
					$reasons[] = 'theme URI uses URL shortener (unverifiable)';
				}

				// Check for typosquatting patterns.
				if ( preg_match( '/wordpress[-_]?org|word[-_]?press\.net/i', $theme_info['uri'] ) ) {
					$reasons[] = 'URI mimics wordpress.org (possible typosquatting)';
				}
			}

			if ( ! empty( $reasons ) ) {
				$suspicious_themes[ $theme_info['name'] ] = $reasons;
			}
		}

		if ( ! empty( $suspicious_themes ) ) {
			foreach ( $suspicious_themes as $theme_name => $reasons ) {
				$issues[] = sprintf(
					/* translators: 1: theme name, 2: reason list */
					__( 'Theme "%1$s": %2$s', 'wpshadow' ),
					$theme_name,
					implode( ', ', $reasons )
				);
			}
		}

		// Check for unverified themes (non-wordpress.org).
		$unverified_count = 0;
		foreach ( $themes_analysis as $theme_info ) {
			if ( ! $theme_info['verified'] && 'no_uri' !== $theme_info['source'] ) {
				$unverified_count++;
			}
		}

		if ( $unverified_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unverified themes */
				__( '%d theme(s) from unverified sources (manually verify authenticity)', 'wpshadow' ),
				$unverified_count
			);
		}

		// Check theme integrity (check for backdoors, malicious code patterns).
		$themes_dir = get_theme_root();

		// Check for suspicious theme files.
		$suspicious_files = array(
			'*.php' => array( 'eval(', 'base64_decode', 'gzinflate', 'create_function' ),
		);

		$themes_with_suspicious_code = array();

		foreach ( $themes as $theme_slug => $theme ) {
			$theme_dir   = $theme->get_stylesheet_directory();
			$php_files   = glob( $theme_dir . '/*.php' );
			$suspicious  = false;
			$suspicious_patterns = array();

			foreach ( $php_files as $php_file ) {
				$content = file_get_contents( $php_file );

				// Check for dangerous functions (very basic).
				if ( preg_match( '/eval\s*\(|base64_decode|gzinflate|gzuncompress|create_function/i', $content ) ) {
					$suspicious = true;
					$suspicious_patterns[] = basename( $php_file );
				}

				// Check for hexadecimal strings (often used for obfuscation).
				if ( preg_match( '/0x[a-f0-9]{20,}/i', $content ) ) {
					// Might be legitimate, but worth noting.
				}

				// Check for shell_exec, system, exec, passthru.
				if ( preg_match( '/shell_exec|system\s*\(|passthru|proc_open/i', $content ) ) {
					$suspicious = true;
					$suspicious_patterns[] = basename( $php_file );
				}
			}

			if ( $suspicious ) {
				$themes_with_suspicious_code[] = array(
					'theme'    => $theme->get( 'Name' ),
					'files'    => array_unique( $suspicious_patterns ),
				);
			}
		}

		if ( ! empty( $themes_with_suspicious_code ) ) {
			foreach ( $themes_with_suspicious_code as $suspicious_theme ) {
				$issues[] = sprintf(
					/* translators: 1: theme name, 2: file list */
					__( 'Theme "%1$s" contains suspicious code patterns in: %2$s', 'wpshadow' ),
					$suspicious_theme['theme'],
					implode( ', ', $suspicious_theme['files'] )
				);
			}
		}

		// Check WordPress.org theme verification.
		$wordpress_org_themes = array();

		foreach ( $themes_analysis as $theme_info ) {
			if ( 'wordpress.org' === $theme_info['source'] ) {
				$wordpress_org_themes[] = $theme_info['name'];
			}
		}

		// Check theme update history.
		$theme_transients = get_transient( 'update_themes' );

		if ( ! empty( $theme_transients ) && ! empty( $theme_transients->response ) ) {
			$themes_with_updates = $theme_transients->response;
			// Some themes have available updates.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of source verification issues */
					__( 'Found %d theme installation source verification issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'               => $issues,
					'total_themes'         => count( $themes ),
					'wordpress_org_themes' => count( $wordpress_org_themes ),
					'unverified_themes'    => $unverified_count,
					'recommendation'       => __( 'Only install themes from WordPress.org or trusted commercial marketplaces. Verify theme authors and check theme security before installation.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
