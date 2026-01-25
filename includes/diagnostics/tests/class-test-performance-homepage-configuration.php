<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Homepage Configuration (Performance)
 *
 * Checks if homepage is properly configured
 * Philosophy: Show value (#9) - proper setup improves initial impressions
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_HomepageConfiguration extends Diagnostic_Base {


	public static function check(): ?array {
		$front_page_type = get_option( 'show_on_front' );

		// Check if homepage is set to latest posts (less professional)
		if ( $front_page_type === 'posts' ) {
			return array(
				'id'           => 'homepage-configuration',
				'title'        => __( 'Homepage shows latest posts', 'wpshadow' ),
				'description'  => __( 'Set a static homepage for better branding. Go to Settings > Reading and set Front Page to a static page.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 10,
			);
		}

		return null;
	}

	public static function test_live_homepage_configuration(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Homepage is properly configured', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
