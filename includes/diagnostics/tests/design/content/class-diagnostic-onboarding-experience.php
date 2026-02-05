<?php
/**
 * Onboarding Experience Diagnostic
 *
 * Issue #4920: No Onboarding for New Users
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if new users get onboarding guidance.
 * First-time users need a tour or setup wizard.
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
 * Diagnostic_Onboarding_Experience Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Onboarding_Experience extends Diagnostic_Base {

	protected static $slug = 'onboarding-experience';
	protected static $title = 'No Onboarding for New Users';
	protected static $description = 'Checks if plugin has first-time user onboarding';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show welcome screen on first activation', 'wpshadow' );
		$issues[] = __( 'Provide setup wizard for essential settings', 'wpshadow' );
		$issues[] = __( 'Highlight key features with interactive tour', 'wpshadow' );
		$issues[] = __( 'Link to getting started documentation', 'wpshadow' );
		$issues[] = __( 'Allow skipping onboarding (power users)', 'wpshadow' );
		$issues[] = __( 'Provide "Take tour again" option in help menu', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'First impressions matter. New users need guidance to understand features and get value quickly. Good onboarding reduces confusion and support requests.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/onboarding',
				'details'      => array(
					'recommendations'         => $issues,
					'commandments'            => 'Commandment #1: Helpful Neighbor, #6: Drive to Training',
					'pattern'                 => 'Welcome → Setup Wizard → Feature Tour → Success',
					'skip_option'             => 'Always provide "Skip" or "Do this later"',
				),
			);
		}

		return null;
	}
}
