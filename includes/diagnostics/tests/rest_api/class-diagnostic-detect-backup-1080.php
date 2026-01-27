<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Backup 1080 Diagnostic
 *
 * Checks for detect backup 1080.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_DetectBackup1080 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'detect-backup-1080';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Detect Backup 1080';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect Backup 1080';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest_api';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null
	 */
	public static function check() {
		// TODO: Implement detection logic for detect-backup-1080
		return null;
	}
}
