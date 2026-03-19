<?php
<?php
/**
 * File Permission Security Treatment
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
 * @subpackage Treatments\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_File_Permission_Security Class
 *
 * Checks for unsafe permissions (777, world-writable) in uploads directory.\n * Implements permission validation to prevent file injection attacks.\n *
 * **Detection Pattern:**
 * 1. Get uploads directory path (wp_upload_dir())\n * 2. Check directory permissions (fileperms())\n * 3. Test if world-writable (0o777 or contains 0o007)\n * 4. Check individual file permissions\n * 5. Validate .htaccess in uploads (prevents PHP execution)\n * 6. Return severity if insecure permissions found\n *
 * **Real-World Scenario:**
 * Developer deploys WordPress to shared hosting. Hosting support says:\n * \"Set permissions to 777 for uploads to work\". Developer follows advice.\n * Later: site compromised. Attacker uploaded PHP shell via comment form.\n * PHP executed = full compromise. Database + files accessed. Attacker demands\n * ransom. Site down for 1 week during recovery.\n *
 * **Implementation Notes:**
 * - Uses fileperms() and decoct() for permission checking\n * - Validates 755 (rwxr-xr-x) or more restrictive\n * - Checks for 777 (rwxrwxrwx) or world-writable\n * - Severity: critical (world-writable), high (too open)\n * - Treatment: chmod uploads to 755, files to 644\n *
 * @since 1.6093.1200
 */
class Treatment_File_Permission_Security extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'file-permission-security';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Security';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates file and directory permissions for uploads';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Uploads directory permissions
	 * - World-writable files
	 * - WordPress file permission constants
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_File_Permission_Security' );
	}
}
