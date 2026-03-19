<?php
/**
 * Encryption at Rest Diagnostic
 *
 * Checks whether data-at-rest encryption is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Encryption_At_Rest Class
 *
 * Validates that encryption at rest is enabled via configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Encryption_At_Rest extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'encryption-at-rest';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Encryption at Rest';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether data-at-rest encryption is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$enabled = (bool) get_option( 'wpshadow_encryption_at_rest_enabled', false );

		if ( ! $enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Encryption at rest is not configured. Enable database or disk encryption for compliance.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/encryption-at-rest',
			);
		}

		return null;
	}
}