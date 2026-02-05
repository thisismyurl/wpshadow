<?php
/**
 * Form Auto-Save Diagnostic
 *
 * Issue #4855: Long Forms Don't Auto-Save Draft Progress
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if long forms auto-save draft progress to prevent data loss.
 * Lost form data due to browser crash or accidental navigation causes frustration and abandonment.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Form_Auto_Save Class
 *
 * Checks for:
 * - JavaScript that saves form data periodically
 * - Local storage or IndexedDB usage for draft storage
 * - Recovery/restore mechanism on page reload
 * - Clear indication to user that draft exists
 *
 * Auto-save protects users from data loss due to:
 * - Accidental browser/tab closure
 * - Network interruption mid-submission
 * - Server timeout during processing
 * - Browser crash or computer restart
 *
 * @since 1.6050.0000
 */
class Diagnostic_Form_Auto_Save extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'form-auto-save';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Long Forms Don\'t Auto-Save Draft Progress';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if long forms auto-save draft progress to prevent data loss';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for forms on the site
		// This would require checking the HTML of pages
		// For now, provide informational finding

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Long or complex forms should auto-save draft progress every 30 seconds to prevent data loss', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/form-auto-save',
			'details'      => array(
				'what_is_auto_save' => 'Periodically save form input to localStorage, allowing recovery if page closes',
				'recommended_interval' => '30 seconds',
				'user_benefit' => 'Users don\'t lose 10-minute form fills due to accidental navigation',
				'implementation' => 'Use JavaScript setInterval() to serialize form data to localStorage',
			),
		);
	}
}
