<?php
/**
 * Media Direct File Access Security Diagnostic
 *
 * Tests if direct access to PHP files in uploads is blocked.
 * Validates .htaccess rules prevent direct PHP execution.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
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
 * Diagnostic_Media_Direct_File_Access_Security Class
 *
 * Checks if direct PHP file execution is blocked in the uploads directory.
 * This is a critical security control that prevents attackers from executing
 * malicious PHP scripts if they manage to upload them.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Direct_File_Access_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-direct-file-access-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Direct File Access Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if direct PHP file execution is blocked in uploads directory';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		if ( empty( $uploads_path ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Could not determine uploads directory path.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'The uploads directory is a common target for attackers because it is writable by design and exposed publicly. If PHP execution is not explicitly blocked, a single malicious file upload can become remote code execution (RCE). Attackers often disguise scripts as images or exploit vulnerable upload forms; once a PHP file is in uploads, visiting it can grant full control over the site. OWASP Top 10 2021 ranks Injection #3 and Security Misconfiguration #5, both of which are implicated when executable files are allowed in public directories. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern against internet‑facing systems; file upload abuse is a common follow‑on technique once credentials are stolen or a plugin vulnerability is found. The business impact includes immediate site takeover, malware distribution, SEO blacklisting, and incident response costs. For ecommerce, RCE can lead to payment skimming, fraudulent orders, or data exfiltration. Blocking PHP execution in uploads is a foundational control that limits blast radius even if a vulnerability exists elsewhere. It is easy to verify, low maintenance, and expected by security auditors and insurers. Without it, a single mistake in any plugin or theme upload handler can lead to catastrophic compromise.', 'wpshadow' ),
					'recommendation' => __( '1. Add .htaccess rules to disable PHP execution in wp-content/uploads.
2. For Nginx, add location rules to deny *.php under uploads.
3. For IIS, configure web.config to deny script handlers in uploads.
4. Set uploads directory permissions to 755 and files to 644.
5. Enforce MIME type and file extension checks on all uploads.
6. Disable dangerous file types via upload_mimes filter.
7. Scan uploads for executable content and suspicious patterns.
8. Use WAF rules to block requests to /uploads/*.php.
9. Store sensitive uploads outside the web root when possible.
10. Re‑test after server or CDN changes to ensure protections persist.', 'wpshadow' ),
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
		}

		// Check for .htaccess file
		$htaccess_path = $uploads_path . '/.htaccess';
		$htaccess_exists = file_exists( $htaccess_path );

		// Check for web.config file (IIS)
		$web_config_path = $uploads_path . '/web.config';
		$web_config_exists = file_exists( $web_config_path );

		// Check if running on Windows/IIS
		$is_windows = 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) );

		// Determine which file we should check
		if ( $is_windows ) {
			// On Windows/IIS, web.config is primary
			if ( ! $web_config_exists && ! $htaccess_exists ) {
				$finding = array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'No .htaccess or web.config file found in uploads directory. Direct PHP file execution is not blocked.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'       => array(
						'why'            => __( 'Allowing PHP execution inside the uploads directory creates a direct path to remote code execution. Attackers frequently target upload endpoints or vulnerable plugins to place a web shell in uploads, then execute it by visiting the file. This bypasses normal authentication and can lead to full site takeover. OWASP Top 10 2021 ranks Injection #3 and Security Misconfiguration #5; an uploads directory without execution controls is both. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern against public‑facing systems; once an attacker has a foothold, uploading a script is a common escalation step. The business consequences are severe: malware distribution, SEO blacklisting, data theft, and payment skimming. Even short‑lived exposure can trigger Google Safe Browsing warnings that crater traffic and revenue. Blocking PHP execution in uploads is a low‑cost control that dramatically reduces risk because it turns an uploaded script into inert data. It also reduces insurer risk ratings and speeds incident response by limiting attacker persistence. This should be treated as a mandatory baseline control for any production WordPress site.', 'wpshadow' ),
						'recommendation' => __( '1. Create .htaccess (Apache) or web.config (IIS) rules that deny PHP execution in uploads.
2. Add Nginx location blocks to return 403 for /uploads/*.php.
3. Verify server config after migrations to ensure rules persist.
4. Disable PHP handlers for common extensions (.php, .phtml, .phar).
5. Restrict file permissions and ownership to the web server user.
6. Validate and sanitize all uploads server‑side, not just client‑side.
7. Add malware scanning to uploads directory.
8. Block direct access to sensitive file types (e.g., .zip, .sql) if not needed.
9. Monitor access logs for requests to /uploads/*.php.
10. Document and test the control during quarterly security reviews.', 'wpshadow' ),
					),
				);

				return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
			}
		} else {
			// On Unix/Apache, check .htaccess
			if ( ! $htaccess_exists ) {
				// Web server might not support .htaccess but might have other protections
				$finding = array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'No .htaccess file found in uploads directory. Direct PHP file execution may not be blocked.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'       => array(
						'why'            => __( 'Uploads directories are writable and publicly accessible, making them a frequent landing zone for malicious scripts. If Apache execution is not blocked via .htaccess (or an equivalent server rule), an attacker can upload a PHP shell and execute it directly, bypassing WordPress authentication. OWASP Top 10 2021 ranks Injection #3 and Security Misconfiguration #5, and this issue is both: executable uploads plus missing controls. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern; attackers often combine phishing or credential stuffing with an upload or plugin exploit, then execute code from uploads. The business impact is catastrophic: attackers can steal data, inject skimmers, or host malware. Even a short compromise can result in browser warnings and SEO penalties, leading to a sustained drop in traffic and revenue. This control is simple, inexpensive, and highly effective because it neutralizes an entire class of attacks. For compliance and insurance, it is a visible, auditable safeguard that reduces risk exposure.', 'wpshadow' ),
						'recommendation' => __( '1. Add a deny‑PHP .htaccess rule in wp-content/uploads.
2. Configure Nginx to block PHP execution in uploads.
3. Ensure .user.ini or similar handlers cannot override restrictions.
4. Limit allowed upload types via upload_mimes filter.
5. Verify file permissions and ownership on uploads.
6. Add security headers or WAF rules to block /uploads/*.php.
7. Scan uploads for web shells and suspicious strings.
8. Restrict directory listing in uploads.
9. Store sensitive media outside web root when feasible.
10. Re‑test after hosting changes, CDN updates, or migrations.', 'wpshadow' ),
					),
				);

				return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
			}

			// Check .htaccess content for PHP execution prevention
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( false === $htaccess_content ) {
				$finding = array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Could not read .htaccess file in uploads directory.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 70,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'       => array(
						'why'            => __( 'If the uploads .htaccess file cannot be read, you cannot confirm whether PHP execution is blocked. This creates a blind spot for one of the most critical controls in WordPress security. Upload directories are writable and exposed, and a single malicious file upload can lead to remote code execution if execution controls are missing or misconfigured. OWASP Top 10 2021 ranks Security Misconfiguration #5 and Injection #3; both categories are common in file upload attacks. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern. Attackers often exploit weak upload handling after initial access, making this control a key safety net. The business impact includes possible data theft, malware distribution, and reputational damage that can persist even after cleanup. Restoring visibility into configuration and enforcing a deny‑execution rule is necessary to reduce risk and to provide auditable evidence of security posture.', 'wpshadow' ),
						'recommendation' => __( '1. Fix permissions so the web server can read .htaccess.
2. Verify .htaccess ownership and file mode are correct.
3. Add explicit rules to block PHP execution in uploads.
4. Use server‑level config if .htaccess is not supported.
5. Validate rules by requesting a test .php file (should be blocked).
6. Monitor access logs for /uploads/*.php attempts.
7. Add malware scanning for uploads directory.
8. Limit upload types and enforce MIME checks.
9. Keep server and PHP updated to reduce exploit paths.
10. Document the control for compliance audits and insurer reviews.', 'wpshadow' ),
					),
				);

				return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
			}

			// Look for directives that prevent PHP execution
			$has_php_handler_block = false;

			// Common .htaccess directives that block PHP
			$php_block_patterns = array(
				'<FilesMatch \.php',
				'AddType text/plain',
				'php_flag engine off',
				'RemoveHandler .php',
				'RemoveType .php',
			);

			foreach ( $php_block_patterns as $pattern ) {
				if ( stripos( $htaccess_content, $pattern ) !== false ) {
					$has_php_handler_block = true;
					break;
				}
			}

			if ( ! $has_php_handler_block ) {
				$finding = array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( '.htaccess file found but does not contain rules to block PHP execution. Direct PHP files can be executed.', 'wpshadow' ),
					'severity'      => 'critical',
					'threat_level'  => 80,
					'auto_fixable'  => true,
					'kb_link'       => 'https://wpshadow.com/kb/media-direct-file-access-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'       => array(
						'why'            => __( 'Having an uploads .htaccess file without PHP execution blocking is functionally equivalent to having no protection. Attackers routinely upload PHP shells or backdoors through vulnerable forms or plugins; if those files can execute, they gain full control of the site. OWASP Top 10 2021 ranks Injection #3 and Security Misconfiguration #5, and this issue reflects both categories. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern; file upload abuse is a common escalation step once access is obtained. The business impact includes malware distribution, data theft, payment skimming, and brand damage from blacklisting. This control is straightforward and low cost: a few lines in .htaccess or server configuration can disable execution and reduce risk dramatically. It is also a clear, auditable safeguard that improves cyber insurance posture and shortens incident response time because the attack surface is reduced.', 'wpshadow' ),
						'recommendation' => __( '1. Add explicit PHP‑blocking rules to .htaccess (FilesMatch + Deny).
2. Disable PHP handlers (RemoveHandler/RemoveType) for uploads.
3. Mirror the rule in Nginx or IIS if not using Apache.
4. Validate protections by attempting to access a test .php file in uploads.
5. Block execution of other script types (.phtml, .phar, .cgi).
6. Restrict uploads to safe MIME types and validate server‑side.
7. Enable malware scanning for uploads.
8. Monitor logs for attempted access to executable files.
9. Keep plugins and themes updated to reduce upload vulnerabilities.
10. Document and re‑verify after migrations or hosting changes.', 'wpshadow' ),
					),
				);

				return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
			}
		}

		// All checks passed
		return null;
	}
}
