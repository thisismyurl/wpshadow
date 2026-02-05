<?php
/**
 * Theme Update Status Treatment
 *
 * Checks for outdated themes that require updates.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1440
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Theme_Update_Status Class
 *
 * Detects pending theme updates.
 *
 * @since 1.6035.1440
 */
class Treatment_Theme_Update_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-update-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Update Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for themes that require updates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1440
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_theme_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$updates = get_theme_updates();
		if ( ! empty( $updates ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme updates are available. Outdated themes can introduce security risks.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-update-status',
				'meta'         => array(
					'theme_updates' => count( $updates ),
				),
			);
		}

		return null;
	}
}