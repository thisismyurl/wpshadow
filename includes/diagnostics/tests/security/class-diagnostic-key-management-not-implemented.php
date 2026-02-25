<?php
/**
 * Key Management Not Implemented Diagnostic
 *
 * Checks key management.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Key_Management_Not_Implemented Class
 *
 * Performs diagnostic check for Key Management Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Key_Management_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'key-management-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Key Management Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks key management';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'manage_encryption_keys' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Key management not implemented. Rotate encryption keys regularly and store them securely outside code.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/key-management-not-implemented',
			);
		}

		return null;
	}
}
