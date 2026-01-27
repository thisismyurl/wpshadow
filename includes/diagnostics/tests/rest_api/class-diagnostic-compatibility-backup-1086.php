<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility Backup 1086 Diagnostic
 *
 * Checks for compatibility backup 1086.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_CompatibilityBackup1086 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'compatibility-backup-1086';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Compatibility Backup 1086';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Compatibility Backup 1086';

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
		// TODO: Implement detection logic for compatibility-backup-1086
		return null;
	}
}
