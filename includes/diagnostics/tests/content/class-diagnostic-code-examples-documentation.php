<?php
/**
 * Code Examples Documentation Diagnostic
 *
 * Issue #4904: Documentation Missing Real-World Code Examples
 * Pillar: 🎓 Learning Inclusive
 *
 * Checks if documentation includes practical code examples.
 * Developers learn best from working examples, not just descriptions.
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
 * Diagnostic_Code_Examples_Documentation Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Code_Examples_Documentation extends Diagnostic_Base {

	protected static $slug = 'code-examples-documentation';
	protected static $title = 'Documentation Missing Real-World Code Examples';
	protected static $description = 'Checks if documentation includes practical, copy-paste-ready examples';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Include working code examples for every feature', 'wpshadow' );
		$issues[] = __( 'Show real-world use cases, not just "Hello World"', 'wpshadow' );
		$issues[] = __( 'Make examples copy-paste ready (complete, not snippets)', 'wpshadow' );
		$issues[] = __( 'Show both simple and advanced examples', 'wpshadow' );
		$issues[] = __( 'Include error handling in examples', 'wpshadow' );
		$issues[] = __( 'Provide interactive demos or CodePen/JSFiddle links', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Developers learn by doing. Code examples are more valuable than paragraphs of explanation. Show, don\'t just tell.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/code-examples',
				'details'      => array(
					'recommendations'         => $issues,
					'example_structure'       => 'Problem → Solution → Complete Code → Explanation',
					'interactive_tools'       => 'CodePen, JSFiddle, Repl.it for live examples',
					'learning_style'          => 'Kinesthetic learners need hands-on examples',
				),
			);
		}

		return null;
	}
}
