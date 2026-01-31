<?php
/**
 * Content Encryption For Sensitive Data Not Implemented Diagnostic
 *
 * Checks if data encryption is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Encryption For Sensitive Data Not Implemented Diagnostic Class
 *
 * Detects missing data encryption.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Encryption_For_Sensitive_Data_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-encryption-for-sensitive-data-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Encryption For Sensitive Data Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data encryption is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if encryption plugin is active
		if ( ! is_plugin_active( 'wpsecure-db-encryption/plugin.php' ) && ! is_plugin_active( 'encryption/encryption.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Data encryption for sensitive content is not implemented. Encrypt sensitive data at rest to protect user information.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-encryption-for-sensitive-data-not-implemented',
			);
		}

		return null;
	}
}
