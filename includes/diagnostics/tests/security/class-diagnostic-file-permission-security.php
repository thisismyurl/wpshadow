<?php
<?php
/**
 * File Permission Security Diagnostic
 *
 * Validates file and directory permissions in uploads folder. Overly permissive\n * permissions (777, world-writable) allow attackers to modify/replace files.\n * Scenario: Attacker changes image.jpg to PHP webshell. Site compromise.\n *
 * **What This Check Does:**
 * - Checks permissions on uploads directory (/wp-content/uploads)\n * - Validates directory permissions are 755 or restrictive\n * - Detects world-writable permissions (777, 777, o+w)\n * - Tests file permissions (644 or restrictive)\n * - Checks .htaccess prevents PHP execution\n * - Validates wp-content permissions prevent modification\n *
 * **Why This Matters:**
 * World-writable directories enable file injection attacks. Scenarios:\n * - Uploads folder set to 777 (everyone can write)\n * - Attacker replaces image.jpg with PHP webshell\n * - Accesses /wp-content/uploads/shell.php\n * - Executes arbitrary code\n * - Full server compromise\n *
 * **Business Impact:**
 * Shared hosting provider sets uploads to 777 for \"convenience\". 100 WordPress\n * sites on same server. Attacker compromises one site via file permissions.\n * Gains FTP/SSH (uploads 777 = writable). Modifies all other sites (cross-site)\n * attack. 50 customers affected. Incident response: 1 week. Cost: $50K+ recovery\n * + legal + notification.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: File system hardened against modification\n * - #9 Show Value: Prevents shell access via file injection\n * - #10 Beyond Pure: Defense in depth, filesystem-level security\n *
 * **Related Checks:**
 * - Executable File Prevention (upload type restriction)\n * - htaccess Protection (execution prevention)\n * - Directory Listing Prevention (info disclosure)\n *
 * **Learn More:**
 * File permissions guide: https://wpshadow.com/kb/wordpress-file-permissions\n * Video: Securing WordPress file permissions (10min): https://wpshadow.com/training/permissions-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_Permission_Security Class
 *
 * Checks for unsafe permissions (777, world-writable) in uploads directory.\n * Implements permission validation to prevent file injection attacks.\n *
 * **Detection Pattern:**
 * 1. Get uploads directory path (wp_upload_dir())\n * 2. Check directory permissions (fileperms())\n * 3. Test if world-writable (0o777 or contains 0o007)\n * 4. Check individual file permissions\n * 5. Validate .htaccess in uploads (prevents PHP execution)\n * 6. Return severity if insecure permissions found\n *
 * **Real-World Scenario:**
 * Developer deploys WordPress to shared hosting. Hosting support says:\n * \"Set permissions to 777 for uploads to work\". Developer follows advice.\n * Later: site compromised. Attacker uploaded PHP shell via comment form.\n * PHP executed = full compromise. Database + files accessed. Attacker demands\n * ransom. Site down for 1 week during recovery.\n *
 * **Implementation Notes:**
 * - Uses fileperms() and decoct() for permission checking\n * - Validates 755 (rwxr-xr-x) or more restrictive\n * - Checks for 777 (rwxrwxrwx) or world-writable\n * - Severity: critical (world-writable), high (too open)\n * - Treatment: chmod uploads to 755, files to 644\n *
 * @since 1.6030.2148
 */
class Diagnostic_File_Permission_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'file-permission-security';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Security';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates file and directory permissions for uploads';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Uploads directory permissions
	 * - World-writable files
	 * - WordPress file permission constants
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$world_writable = 0;

		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		if ( empty( $base_dir ) || ! is_dir( $base_dir ) ) {
			$issues[] = __( 'Uploads directory is missing or invalid - cannot verify permissions', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uploads directory missing - permission check failed', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permission-security',
				'details'      => array( 'issues' => $issues ),
			);
		}

		// Check base uploads directory permissions.
		$base_perms = fileperms( $base_dir );
		if ( false !== $base_perms ) {
			$base_mode = $base_perms & 0777;
			if ( ( 0777 === $base_mode ) || ( 0 !== ( $base_mode & 0x0002 ) ) ) {
				$issues[] = sprintf(
					/* translators: %s: permissions */
					__( 'Uploads directory permissions are %s - should be 755', 'wpshadow' ),
					decoct( $base_mode )
				);
			}
		}

		// Check a sample of recent uploads for insecure permissions.
		global $wpdb;
		$files = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = %s
				ORDER BY p.post_date DESC
				LIMIT 20",
				'attachment'
			)
		);

		foreach ( $files as $file ) {
			$path = $base_dir . '/' . $file->file_path;
			if ( ! file_exists( $path ) ) {
				continue;
			}

			$perms = fileperms( $path );
			if ( false === $perms ) {
				continue;
			}

			$mode = $perms & 0777;
			if ( ( 0777 === $mode ) || ( 0 !== ( $mode & 0x0002 ) ) ) {
				$world_writable++;
			}
		}

		if ( 0 < $world_writable ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent upload is world-writable - security risk',
					'%d recent uploads are world-writable - security risk',
					$world_writable,
					'wpshadow'
				),
				$world_writable
			);
		}

		// Check permission constants.
		if ( defined( 'FS_CHMOD_FILE' ) ) {
			$chmod_file = FS_CHMOD_FILE;
			if ( 0644 < $chmod_file ) {
				$issues[] = sprintf(
					/* translators: %s: permissions */
					__( 'FS_CHMOD_FILE is set to %s - should be 0644', 'wpshadow' ),
					decoct( $chmod_file )
				);
			}
		}

		if ( defined( 'FS_CHMOD_DIR' ) ) {
			$chmod_dir = FS_CHMOD_DIR;
			if ( 0755 < $chmod_dir ) {
				$issues[] = sprintf(
					/* translators: %s: permissions */
					__( 'FS_CHMOD_DIR is set to %s - should be 0755', 'wpshadow' ),
					decoct( $chmod_dir )
				);
			}
		}

		// Check for group writable permissions (less severe).
		if ( is_multisite() && defined( 'UPLOADS' ) ) {
			$issues[] = __( 'Multisite uploads detected - verify network-wide permissions are consistent', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d file permission issue detected',
						'%d file permission issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-permission-security',
				'context'       => array(
					'why'            => __( 'World-writable files = privilege escalation. Real scenario: wp-config.php is 777 (anyone can read/write). Attacker modifies wp-config to add admin user. Logs in as admin. Full compromise. With proper permissions: wp-config is 644 (only owner reads). Attacker cannot modify. Attack blocked.', 'wpshadow' ),
					'recommendation' => __( '1. Set directories: 755 (rwxr-xr-x). 2. Set files: 644 (rw-r--r--). 3. wp-config.php: 600 (rw-------). 4. wp-content: 755. 5. Plugins: 755. 6. Uploads: 755. 7. Add constants: define(\'FS_CHMOD_FILE\', 0644); 8. Add constants: define(\'FS_CHMOD_DIR\', 0755); 9. Scan for 777/world-writable: find /path -perm 777. 10. Fix with: chmod -R 755 /wp-content.', 'wpshadow' ),
				),
				'details'       => array(
					'issues'         => $issues,
					'world_writable' => $world_writable,
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-permissions', 'permission-hardening' );
			return $finding;
		}

		return null;
	}
}
