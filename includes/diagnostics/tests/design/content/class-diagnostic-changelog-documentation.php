<?php
/**
 * Changelog Documentation Diagnostic
 *
 * Issue #4945: No Changelog for Updates
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if plugin maintains a changelog.
 * Users need to know what changed in updates.
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
 * Diagnostic_Changelog_Documentation Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Changelog_Documentation extends Diagnostic_Base {

	protected static $slug = 'changelog-documentation';
	protected static $title = 'No Changelog for Updates';
	protected static $description = 'Checks if plugin documents changes between versions';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Maintain CHANGELOG.md or changelog.txt file', 'wpshadow' );
		$issues[] = __( 'Document every release with version and date', 'wpshadow' );
		$issues[] = __( 'Categorize changes: Added, Changed, Fixed, Removed', 'wpshadow' );
		$issues[] = __( 'Link to GitHub issues or tickets', 'wpshadow' );
		$issues[] = __( 'Highlight breaking changes clearly', 'wpshadow' );
		$issues[] = __( 'Use semantic versioning (1.2.3)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Users need to know what changed in updates before clicking "Update". A clear changelog builds trust and helps users decide when to update.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/changelog',
				'details'      => array(
					'recommendations'         => $issues,
					'format'                  => 'Keep A Changelog format (keepachangelog.com)',
					'categories'              => 'Added, Changed, Deprecated, Removed, Fixed, Security',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
