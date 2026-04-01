<?php
/**
 * Directory Listing Enabled Diagnostic
 *
 * Checks for directory indexes on upload directories, verifies .htaccess blocks
 * directory browsing, and tests for sensitive file exposure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Enabled Diagnostic Class
 *
 * Detects directory listing vulnerabilities that could expose sensitive
 * files and directory structures to attackers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Directory_Listing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'blocks_directory_listing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Directory Listing Enabled';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies directory browsing is disabled to prevent file exposure';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for index.php in wp-content (30 points).
		$wp_content_index = WP_CONTENT_DIR . '/index.php';
		if ( file_exists( $wp_content_index ) ) {
			$earned_points += 30;
			$stats['wp_content_index'] = 'exists';
		} else {
			$issues[] = 'Missing index.php in wp-content directory';
		}

		// Check for index.php in uploads directory (25 points).
		$upload_dir   = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];
		$uploads_index = $uploads_path . '/index.php';

		if ( file_exists( $uploads_index ) ) {
			$earned_points += 25;
			$stats['uploads_index'] = 'exists';
		} else {
			$issues[] = 'Missing index.php in uploads directory - directory listing may be enabled';
		}

		// Check for .htaccess with Options -Indexes (25 points).
		$htaccess_locations = array(
			ABSPATH . '.htaccess',
			$uploads_path . '/.htaccess',
		);

		$htaccess_protected = 0;
		foreach ( $htaccess_locations as $htaccess_path ) {
			if ( file_exists( $htaccess_path ) && is_readable( $htaccess_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$htaccess_content = file_get_contents( $htaccess_path );

				if ( preg_match( '/Options\s+(-|\+)?Indexes/i', $htaccess_content, $matches ) ) {
					// Check if it's disabling indexes.
					if ( strpos( $matches[0], '-Indexes' ) !== false ) {
						$htaccess_protected++;
					} else {
						$warnings[] = sprintf(
							/* translators: %s: .htaccess path */
							__( 'Directory indexing may be enabled in %s', 'wpshadow' ),
							basename( dirname( $htaccess_path ) )
						);
					}
				}
			}
		}

		if ( $htaccess_protected > 0 ) {
			$earned_points += 25;
			$stats['htaccess_protected_locations'] = $htaccess_protected;
		} else {
			$issues[] = 'No .htaccess files found with Options -Indexes directive';
		}

		// Check for security plugins with directory protection (15 points).
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
				$earned_points    += 5; // Up to 15 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		} else {
			$warnings[] = 'No security plugins detected for directory protection';
		}

		// Check for sensitive files in uploads (5 points penalty if found).
		$sensitive_extensions = array( '.php', '.sql', '.sh', '.bash', '.config', '.env' );
		$sensitive_found      = array();

		foreach ( $sensitive_extensions as $ext ) {
			$files = glob( $uploads_path . '/*' . $ext );
			if ( ! empty( $files ) ) {
				$sensitive_found[] = $ext;
			}
		}

		if ( empty( $sensitive_found ) ) {
			$earned_points += 5;
		} else {
			$warnings[] = sprintf(
				/* translators: %s: File extensions */
				__( 'Sensitive file types found in uploads: %s', 'wpshadow' ),
				implode( ', ', $sensitive_found )
			);
			$stats['sensitive_file_types'] = $sensitive_found;
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 65%.
		if ( $score < 65 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 75 : 65;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your directory listing protection scored %s. When directory listing is enabled, attackers can browse your file structure and discover sensitive files like backups, configs, or uploads. This information can be used to plan further attacks.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/directory-listing-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
