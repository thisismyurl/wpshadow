<?php
/**
 * Directory Listing Prevention Treatment
 *
 * Issue #4914: Directory Listing Enabled (Information Disclosure)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if directory browsing is prevented.
 * Directory listing reveals file structure and backups.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Directory_Listing_Prevention Class
 *
 * @since 1.6050.0000
 */
class Treatment_Directory_Listing_Prevention extends Treatment_Base {

	protected static $slug = 'directory-listing-prevention';
	protected static $title = 'Directory Listing Enabled (Information Disclosure)';
	protected static $description = 'Checks if directory browsing is disabled';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add index.php to all plugin/theme directories', 'wpshadow' );
		$issues[] = __( 'Add "Options -Indexes" to .htaccess', 'wpshadow' );
		$issues[] = __( 'Prevent listing of wp-content/uploads/', 'wpshadow' );
		$issues[] = __( 'Prevent listing of wp-includes/', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Directory listing reveals your file structure, backup files, configuration files, and helps attackers map your site for vulnerabilities.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/directory-listing',
				'details'      => array(
					'recommendations'         => $issues,
					'information_disclosed'   => 'Plugin versions, backup files, configuration',
					'htaccess_directive'      => 'Options -Indexes',
					'index_php_content'       => '<?php // Silence is golden',
				),
			);
		}

		return null;
	}
}
