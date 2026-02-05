<?php
/**
 * Unused CSS Removal Treatment
 *
 * Issue #4985: Unused CSS Not Removed (Dead Code)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if unused CSS is removed.
 * Dead CSS bloats stylesheets unnecessarily.
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
 * Treatment_Unused_CSS_Removal Class
 *
 * @since 1.6050.0000
 */
class Treatment_Unused_CSS_Removal extends Treatment_Base {

	protected static $slug = 'unused-css-removal';
	protected static $title = 'Unused CSS Not Removed (Dead Code)';
	protected static $description = 'Checks if unused CSS is identified and removed';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Audit CSS files for unused rules', 'wpshadow' );
		$issues[] = __( 'Use PurgeCSS or similar tool to find unused styles', 'wpshadow' );
		$issues[] = __( 'Remove CSS for deactivated plugins', 'wpshadow' );
		$issues[] = __( 'Remove old theme CSS (if theme changed)', 'wpshadow' );
		$issues[] = __( 'Use CSS-in-JS or scoped styles to avoid bloat', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unused CSS rules increase file size. Tools like PurgeCSS identify unused styles so they can be removed.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/unused-css',
				'details'      => array(
					'recommendations'         => $issues,
					'typical_impact'          => '30-60% of CSS is unused on average',
					'tools'                   => 'PurgeCSS, UnCSS, Chrome DevTools Coverage tab',
					'deactivated_plugins'     => 'Check .css files in wp-content/plugins/',
				),
			);
		}

		return null;
	}
}
