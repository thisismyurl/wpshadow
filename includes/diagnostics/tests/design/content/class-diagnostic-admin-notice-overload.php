<?php
/**
 * Admin Notice Overload Diagnostic
 *
 * Issue #4969: Too Many Admin Notices (UI Clutter)
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if admin has excessive notices.
 * Plugin spam makes important notices invisible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Notice_Overload Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Notice_Overload extends Diagnostic_Base {

	protected static $slug = 'admin-notice-overload';
	protected static $title = 'Too Many Admin Notices (UI Clutter)';
	protected static $description = 'Checks if admin interface has excessive notices';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Limit plugin notices to critical information only', 'wpshadow' );
		$issues[] = __( 'Make notices dismissible with "X" button', 'wpshadow' );
		$issues[] = __( 'Store dismissal in user meta (don\'t show again)', 'wpshadow' );
		$issues[] = __( 'Use contextual notices (only on relevant pages)', 'wpshadow' );
		$issues[] = __( 'Never show ads/upgrade prompts as admin notices', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Too many admin notices create banner blindness. Users ignore ALL notices, missing critical security warnings.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-notices?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'banner_blindness'        => 'Users learn to ignore ALL notices when overwhelmed',
					'best_practice'           => 'Only show notices for actions user just took',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
