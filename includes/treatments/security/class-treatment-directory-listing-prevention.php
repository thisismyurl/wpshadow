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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Directory_Listing_Prevention' );
	}
}
