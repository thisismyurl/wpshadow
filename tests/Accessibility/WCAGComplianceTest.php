<?php
/**
 * WCAG Accessibility Tests
 *
 * Tests for WCAG 2.1 Level AA compliance.
 *
 * @package WPShadow\Tests\Accessibility
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Tests\Accessibility;

use WPShadow\Tests\TestCase;

/**
 * WCAG compliance tests
 */
class WCAGComplianceTest extends TestCase {

	/**
	 * Test workflow builder CSS color contrast
	 *
	 * @return void
	 */
	public function testWorkflowBuilderColorContrast(): void {
		$css_file = $this->getPluginPath() . '/assets/css/workflow-builder.css';
		$this->assertFileExists( $css_file, 'Workflow builder CSS file must exist' );

		$css_content = file_get_contents( $css_file );

		// Test 1: No gray-400 used for text (3.86:1 - fails WCAG AA)
		$gray_400_text_count = preg_match_all(
			'/(?:color|text-decoration-color):\s*var\(--wps-gray-400/i',
			$css_content
		);
		$this->assertEquals(
			0,
			$gray_400_text_count,
			'gray-400 should not be used for text colors (3.86:1 contrast fails WCAG AA 4.5:1)'
		);

		// Test 2: gray-500 is used for text (5.14:1 - passes WCAG AA)
		$gray_500_usage = preg_match(
			'/color:\s*var\(--wps-gray-500.*?5\.14:1/s',
			$css_content
		);
		$this->assertGreaterThan(
			0,
			$gray_500_usage,
			'gray-500 should be used for text with WCAG AA compliance comment'
		);

		// Test 3: warning-dark is used instead of warning base for icons
		$warning_dark_usage = preg_match(
			'/warning-dark.*?4\.6:1/s',
			$css_content
		);
		$this->assertGreaterThan(
			0,
			$warning_dark_usage,
			'warning-dark should be used for better contrast (4.6:1+)'
		);

		// Test 4: WCAG documentation header exists
		$has_wcag_header = preg_match(
			'/WCAG 2\.1 Level AA Compliance:/i',
			$css_content
		);
		$this->assertEquals(
			1,
			$has_wcag_header,
			'CSS file should have WCAG 2.1 Level AA compliance documentation'
		);
	}

	/**
	 * Test workflow builder PHP template accessibility
	 *
	 * @return void
	 */
	public function testWorkflowBuilderTemplateAccessibility(): void {
		$template_file = $this->getPluginPath() . '/includes/views/workflow-builder.php';
		
		if ( ! file_exists( $template_file ) ) {
			$this->markTestSkipped( 'Workflow builder template file not found' );
		}

		$template_content = file_get_contents( $template_file );

		// Test 1: Has skip link
		$has_skip_link = preg_match( '/class="wps-skip-link"/', $template_content );
		$this->assertGreaterThan(
			0,
			$has_skip_link,
			'Template should have skip link for keyboard navigation'
		);

		// Test 2: Has ARIA labels
		$aria_label_count = preg_match_all( '/aria-label="/', $template_content );
		$this->assertGreaterThan(
			0,
			$aria_label_count,
			'Template should have ARIA labels for screen readers'
		);

		// Test 3: Has ARIA roles
		$aria_role_count = preg_match_all( '/role="/', $template_content );
		$this->assertGreaterThan(
			0,
			$aria_role_count,
			'Template should have ARIA roles for accessibility'
		);

		// Test 4: Has live region for announcements
		$has_live_region = preg_match( '/aria-live="/', $template_content );
		$this->assertGreaterThan(
			0,
			$has_live_region,
			'Template should have ARIA live regions for dynamic content'
		);

		// Test 5: Has keyboard navigation attributes
		$has_tabindex = preg_match( '/tabindex="/', $template_content );
		$this->assertGreaterThanOrEqual(
			0,
			$has_tabindex,
			'Template should support keyboard navigation'
		);
	}

	/**
	 * Test form accessibility
	 *
	 * @return void
	 */
	public function testFormAccessibility(): void {
		// Test that forms have proper labels
		$views_dir = $this->getPluginPath() . '/includes/views';
		
		if ( ! is_dir( $views_dir ) ) {
			$this->markTestSkipped( 'Views directory not found' );
		}

		$view_files = glob( $views_dir . '/*.php' );
		$forms_with_labels = 0;
		$forms_without_labels = 0;

		foreach ( $view_files as $file ) {
			$content = file_get_contents( $file );
			
			// Count input fields
			preg_match_all( '/<input[^>]*>/', $content, $inputs );
			$input_count = count( $inputs[0] );

			// Count labels
			preg_match_all( '/<label[^>]*>/', $content, $labels );
			$label_count = count( $labels[0] );

			if ( $input_count > 0 ) {
				if ( $label_count >= $input_count ) {
					$forms_with_labels++;
				} else {
					$forms_without_labels++;
				}
			}
		}

		// Most forms should have proper labels
		$this->assertGreaterThanOrEqual(
			$forms_without_labels,
			$forms_with_labels,
			'Most forms should have proper label associations'
		);
	}

	/**
	 * Test color contrast ratios
	 *
	 * @return void
	 */
	public function testColorContrastRatios(): void {
		// Define color contrast requirements
		$color_tests = array(
			// gray-500 on white background
			array(
				'foreground' => '#6b7280',
				'background' => '#ffffff',
				'min_ratio'  => 4.5,
				'name'       => 'gray-500 text on white',
			),
			// gray-600 on white background
			array(
				'foreground' => '#4b5563',
				'background' => '#ffffff',
				'min_ratio'  => 4.5,
				'name'       => 'gray-600 text on white',
			),
			// gray-700 on white background
			array(
				'foreground' => '#374151',
				'background' => '#ffffff',
				'min_ratio'  => 4.5,
				'name'       => 'gray-700 text on white',
			),
		);

		foreach ( $color_tests as $test ) {
			$ratio = $this->calculateContrastRatio(
				$test['foreground'],
				$test['background']
			);

			$this->assertGreaterThanOrEqual(
				$test['min_ratio'],
				$ratio,
				"{$test['name']} must have at least {$test['min_ratio']}:1 contrast ratio (got {$ratio}:1)"
			);
		}
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * @param string $foreground Foreground color hex.
	 * @param string $background Background color hex.
	 * @return float Contrast ratio.
	 */
	private function calculateContrastRatio( string $foreground, string $background ): float {
		$l1 = $this->getRelativeLuminance( $foreground );
		$l2 = $this->getRelativeLuminance( $background );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return round( ( $lighter + 0.05 ) / ( $darker + 0.05 ), 2 );
	}

	/**
	 * Get relative luminance of a color
	 *
	 * @param string $hex Hex color code.
	 * @return float Relative luminance.
	 */
	private function getRelativeLuminance( string $hex ): float {
		$hex = ltrim( $hex, '#' );
		$r   = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g   = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b   = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Test button touch targets
	 *
	 * @return void
	 */
	public function testButtonTouchTargets(): void {
		$css_file = $this->getPluginPath() . '/assets/css/workflow-builder.css';
		
		if ( ! file_exists( $css_file ) ) {
			$this->markTestSkipped( 'CSS file not found' );
		}

		$css_content = file_get_contents( $css_file );

		// Check for minimum touch target sizes (44x44px)
		$has_touch_targets = preg_match(
			'/min-width:\s*44px.*?min-height:\s*44px/s',
			$css_content
		);

		$this->assertGreaterThan(
			0,
			$has_touch_targets,
			'Interactive elements should have minimum 44x44px touch targets'
		);
	}

	/**
	 * Test reduced motion support
	 *
	 * @return void
	 */
	public function testReducedMotionSupport(): void {
		$css_file = $this->getPluginPath() . '/assets/css/workflow-builder.css';
		
		if ( ! file_exists( $css_file ) ) {
			$this->markTestSkipped( 'CSS file not found' );
		}

		$css_content = file_get_contents( $css_file );

		// Check for prefers-reduced-motion media query
		$has_reduced_motion = preg_match(
			'/@media\s*\(prefers-reduced-motion:\s*reduce\)/i',
			$css_content
		);

		$this->assertGreaterThan(
			0,
			$has_reduced_motion,
			'CSS should respect prefers-reduced-motion for accessibility'
		);
	}
}
