<?php
/**
 * Empty State Design Diagnostic
 *
 * Issue #4919: Empty States Show Nothing (No Guidance)
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if empty states provide helpful guidance.
 * Empty lists should explain what to do next.
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
 * Diagnostic_Empty_State_Design Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Empty_State_Design extends Diagnostic_Base {

	protected static $slug = 'empty-state-design';
	protected static $title = 'Empty States Show Nothing (No Guidance)';
	protected static $description = 'Checks if empty states provide helpful next steps';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show illustration or icon for empty states', 'wpshadow' );
		$issues[] = __( 'Explain what goes here: "No posts yet"', 'wpshadow' );
		$issues[] = __( 'Provide action button: "Create your first post"', 'wpshadow' );
		$issues[] = __( 'Add link to documentation: "Learn how to..."', 'wpshadow' );
		$issues[] = __( 'Never show just blank space (users think it\'s broken)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Empty states are opportunities to guide users. Show them what to do next instead of leaving them staring at blank space.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/empty-states',
				'details'      => array(
					'recommendations'         => $issues,
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
					'pattern'                 => 'Icon + Explanation + Action Button + Help Link',
					'examples'                => 'Mailchimp, Slack, Stripe (excellent empty states)',
				),
			);
		}

		return null;
	}
}
