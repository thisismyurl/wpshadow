<?php
/**
 * Hash Verification For Updates Not Configured Diagnostic
 *
 * Checks if hash verification is configured.
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
 * Hash Verification For Updates Not Configured Diagnostic Class
 *
 * Detects missing update hash verification.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Hash_Verification_For_Updates_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hash-verification-for-updates-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hash Verification For Updates Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hash verification is configured';

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
		// Check if hash verification filter is active
		if ( ! has_filter( 'upgrader_pre_download', 'verify_update_hash' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Hash verification for updates is not configured. Verify SHA hashes of downloaded updates to prevent man-in-the-middle attacks.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hash-verification-for-updates-not-configured',
			);
		}

		return null;
	}
}
