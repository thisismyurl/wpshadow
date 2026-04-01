<?php
/**
 * Mobile Search Input Optimization Diagnostic
 *
 * Validates that search inputs are optimized for mobile with proper keyboard,
 * autocomplete, and visual design.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Input Optimization Diagnostic Class
 *
 * Checks search forms for mobile-specific optimizations including input type,
 * autocomplete, button size, and keyboard appearance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Search_Input_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-input-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Input Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that search inputs are optimized for mobile with proper keyboard, autocomplete, and design';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Capture homepage HTML.
		$html = self::capture_page_html( home_url( '/' ) );
		if ( empty( $html ) ) {
			return null;
		}

		// Check search form patterns.
		$search_issues = self::check_search_forms( $html );
		if ( ! empty( $search_issues ) ) {
			$issues = array_merge( $issues, $search_issues );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count    = count( $issues );
		$threat_level   = min( 65, 40 + ( $issue_count * 8 ) );
		$severity       = $threat_level >= 60 ? 'medium' : 'low';
		$auto_fixable   = false;

		$description = sprintf(
			/* translators: %d: number of search optimization issues */
			__( 'Found %d mobile search optimization issue(s). Search is used by 50%% of mobile visitors - poor mobile search UX directly impacts findability and conversions.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'     => 'https://wpshadow.com/kb/mobile-search-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'     => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile search optimization affects user success:

					Mobile Search Statistics:
					• 50% of mobile users use search (Nielsen Norman)
					• 43% of mobile searches are abandoned if results take >3 seconds
					• 80% of users won\'t return after bad search experience
					• Search users convert 2-3x higher than browsers

					Mobile-Specific Challenges:
					• Tiny search buttons hard to tap
					• Wrong keyboard type (full QWERTY vs search keyboard)
					• No autocomplete = more typing required
					• Small results hard to read/tap
					• Slow search = immediate bounce

					Best Practices:
					• Use type="search" for search keyboard
					• Minimum 44x44px tap target for button
					• Autocomplete suggestions
					• Instant/AJAX search results
					• Clear button to reset search
					• Voice search option
					• Recent searches saved
					• Sticky search bar

					Business Impact:
					• E-commerce: 30% of traffic uses search
					• Search users spend 2-3x more
					• Bad mobile search = lost revenue
					• Google prioritizes sites with good search UX',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Optimize search forms for mobile:

					HTML Pattern:
					<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( \'/\' ) ); ?>">
					  <label for="mobile-search">
					    <span class="screen-reader-text">Search for:</span>
					  </label>
					  <input
					    type="search"
					    id="mobile-search"
					    name="s"
					    placeholder="Search..."
					    autocomplete="off"
					    aria-label="Search"
					  >
					  <button type="submit" aria-label="Submit search">
					    <span aria-hidden="true">🔍</span>
					  </button>
					</form>

					CSS for Mobile:
					@media (max-width: 768px) {
					  .search-form input[type="search"] {
					    font-size: 16px; /* Prevents zoom on iOS */
					    padding: 12px 16px;
					    border-radius: 24px;
					    width: 100%;
					  }
					  .search-form button {
					    min-width: 44px;
					    min-height: 44px;
					    padding: 12px;
					  }
					}

					JavaScript Enhancements:
					// Add instant search
					const searchInput = document.querySelector("#mobile-search");
					searchInput.addEventListener("input", debounce(function(e) {
					  // Fetch and display instant results
					  fetchSearchResults(e.target.value);
					}, 300));

					// Add clear button
					const clearBtn = document.createElement("button");
					clearBtn.type = "button";
					clearBtn.textContent = "×";
					clearBtn.onclick = () => searchInput.value = "";
					searchInput.parentNode.appendChild(clearBtn);

					WordPress Plugins:
					• SearchWP (better search relevance)
					• Relevanssi (instant search)
					• Ivory Search (advanced mobile search)
					• WP Search with Algolia (fast, typo-tolerant)

					Key Points:
					• ALWAYS use type="search" not type="text"
					• Make submit button 44x44px minimum
					• Font size 16px+ to prevent iOS zoom
					• Consider instant/AJAX results
					• Test on real mobile devices',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check search forms for mobile issues.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML to scan.
	 * @return array Issues found.
	 */
	private static function check_search_forms( $html ) {
		$issues = array();

		// Find search forms.
		if ( ! preg_match_all( '/<form[^>]*role=["\']search["\'][^>]*>(.*?)<\/form>/is', $html, $form_matches ) ) {
			// Try alternate pattern (name="s").
			if ( ! preg_match_all( '/<form[^>]*>(.*?name=["\']s["\'].*?)<\/form>/is', $html, $form_matches ) ) {
				// No search form found.
				$issues[] = array(
					'issue_type'  => 'no_search_form',
					'severity'    => 'low',
					'description' => 'No search form detected on homepage',
				);
				return $issues;
			}
		}

		foreach ( $form_matches[0] as $idx => $form_html ) {
			$form_content = $form_matches[1][ $idx ];

			// Check 1: Using type="search" (not type="text").
			if ( ! preg_match( '/type=["\']search["\']/', $form_content ) ) {
				if ( preg_match( '/type=["\']text["\'][^>]*name=["\']s["\']/', $form_content ) ||
					 preg_match( '/name=["\']s["\'][^>]*type=["\']text["\']/', $form_content ) ) {
					$issues[] = array(
						'issue_type'  => 'wrong_input_type',
						'severity'    => 'medium',
						'description' => 'Search input uses type="text" instead of type="search"',
						'impact'      => 'Mobile keyboard doesn\'t show search button',
					);
				}
			}

			// Check 2: Submit button size/tap target.
			if ( preg_match( '/<button[^>]*type=["\']submit["\'][^>]*>/i', $form_content, $button_match ) ) {
				// Can't reliably check size from HTML, but we can check if button has content.
				if ( strpos( $button_match[0], 'aria-label' ) === false && ! preg_match( '/>.*?<\/button>/', $form_content ) ) {
					$issues[] = array(
						'issue_type'  => 'button_no_label',
						'severity'    => 'medium',
						'description' => 'Search button has no aria-label or visible text',
						'impact'      => 'Accessibility issue for screen readers',
					);
				}
			}

			// Check 3: Font size (should be 16px+ to prevent iOS zoom).
			// This is difficult to check from HTML, but we can check for inline styles.
			if ( preg_match( '/style=["\'][^"\']*font-size:\s*([0-9]+)px/', $form_content, $size_match ) ) {
				$font_size = (int) $size_match[1];
				if ( $font_size < 16 ) {
					$issues[] = array(
						'issue_type'  => 'font_too_small',
						'severity'    => 'medium',
						'description' => sprintf( 'Search input font-size is %dpx (should be 16px+ for iOS)', $font_size ),
						'impact'      => 'iOS will zoom page when input focused',
					);
				}
			}

			// Check 4: Autocomplete attribute.
			if ( strpos( $form_content, 'autocomplete' ) === false ) {
				$issues[] = array(
					'issue_type'  => 'no_autocomplete',
					'severity'    => 'low',
					'description' => 'Search input missing autocomplete attribute',
					'impact'      => 'Browser won\'t suggest previous searches',
				);
			}

			// Check 5: Label for accessibility.
			if ( ! preg_match( '/<label[^>]*>/', $form_content ) && ! preg_match( '/aria-label=["\']/', $form_content ) ) {
				$issues[] = array(
					'issue_type'  => 'no_label',
					'severity'    => 'medium',
					'description' => 'Search input has no <label> or aria-label',
					'impact'      => 'Accessibility issue for screen readers',
				);
			}
		}

		return $issues;
	}

	/**
	 * Capture page HTML.
	 *
	 * @since 0.6093.1200
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
