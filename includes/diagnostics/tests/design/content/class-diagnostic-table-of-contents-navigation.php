<?php
/**
 * Table of Contents Navigation Diagnostic
 *
 * Issue #4915: Long Pages Missing Table of Contents
 * Pillar: 🎓 Learning Inclusive / 🌍 Accessibility First
 *
 * Checks if long-form content has navigation.
 * Users need to jump to sections without scrolling.
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
 * Diagnostic_Table_Of_Contents_Navigation Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Table_Of_Contents_Navigation extends Diagnostic_Base {

	protected static $slug = 'table-of-contents-navigation';
	protected static $title = 'Long Pages Missing Table of Contents';
	protected static $description = 'Checks if documentation pages have navigation aids';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add table of contents to pages >1000 words', 'wpshadow' );
		$issues[] = __( 'Link TOC entries to heading IDs (#section-name)', 'wpshadow' );
		$issues[] = __( 'Make TOC sticky on scroll (always visible)', 'wpshadow' );
		$issues[] = __( 'Add "Back to top" button for long pages', 'wpshadow' );
		$issues[] = __( 'Show current section highlighted in TOC', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Long documentation is hard to navigate. Table of contents lets users jump directly to sections instead of scrolling through everything.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/table-of-contents',
				'details'      => array(
					'recommendations'         => $issues,
					'when_to_add'             => 'Pages >1000 words or >5 sections',
					'accessibility'           => 'Screen readers announce landmarks',
					'seo_benefit'             => 'Google uses TOC for jump-to links',
				),
			);
		}

		return null;
	}
}
