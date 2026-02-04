<?php
/**
 * Code Review Process Diagnostic
 *
 * Tests if code changes are reviewed before deployment.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Code Review Process Diagnostic Class
 *
 * Verifies that code reviews are required before deployment.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Implements_Code_Review extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-code-review';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Code Review Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if code changes are reviewed before deployment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_code_review_required' );
		if ( $manual_flag ) {
			return null;
		}

		if ( self::has_code_review_files() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No code review process detected. Require code reviews to reduce defects and security risks before deployment.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/code-review-process',
			'persona'      => 'developer',
		);
	}

	/**
	 * Check for repository indicators of code review practices.
	 *
	 * @since  1.6050.0000
	 * @return bool True if files exist.
	 */
	private static function has_code_review_files() {
		$paths = array(
			ABSPATH . '.github/CODEOWNERS',
			ABSPATH . 'CODEOWNERS',
			ABSPATH . 'CONTRIBUTING.md',
			ABSPATH . 'docs/code-review.md',
			ABSPATH . 'docs/engineering/code-review.md',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return false;
	}
}
