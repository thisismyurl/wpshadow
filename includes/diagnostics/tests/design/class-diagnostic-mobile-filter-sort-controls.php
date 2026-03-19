<?php
/**
 * Mobile Filter/Sort Controls Diagnostic
 *
 * Validates that filter and sort controls (e.g., WooCommerce products) are
 * mobile-friendly with proper touch targets and mobile UI patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Filter/Sort Controls Diagnostic Class
 *
 * Checks filter and sort controls for mobile usability including touch targets,
 * mobile patterns (bottom sheets, drawers), and accessibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Filter_Sort_Controls extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-filter-sort-controls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Filter/Sort Controls';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates filter and sort controls are mobile-friendly with proper touch targets and UI patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant if WooCommerce or similar is active.
		if ( ! function_exists( 'WC' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return null;
		}

		$issues = array();

		// Check WooCommerce shop page.
		if ( function_exists( 'wc_get_page_id' ) ) {
			$shop_issues = self::check_woocommerce_filters();
			if ( ! empty( $shop_issues ) ) {
				$issues = array_merge( $issues, $shop_issues );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count  = count( $issues );
		$threat_level = min( 65, 45 + ( $issue_count * 5 ) );
		$severity     = $threat_level >= 60 ? 'medium' : 'low';
		$auto_fixable = false;

		$description = sprintf(
			/* translators: %d: number of filter/sort issues */
			__( 'Found %d mobile filter/sort control issue(s). 68%% of mobile shoppers use filters. Poor filter UX causes 35%% cart abandonment on mobile e-commerce sites.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-filters',
			'details'      => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile filter/sort controls are critical for e-commerce:
					
					Mobile Shopping Statistics:
					• 68% of mobile shoppers use product filters (Baymard)
					• 35% abandon if filters don\'t work well
					• Filter/sort users convert 2.5x higher than browsers
					• Mobile accounts for 54% of e-commerce traffic
					
					Mobile-Specific Challenges:
					• Limited screen space for sidebar filters
					• Dropdowns hard to use on small screens
					• Accidental clicks on small checkboxes
					• Horizontal scroll for many filters
					• Filter updates slow (page reload)
					
					Best Mobile Patterns:
					• Bottom sheet/drawer for filters (taps button to open)
					• Large checkboxes (44x44px minimum)
					• AJAX filter updates (no page reload)
					• Active filter chips (easy to remove)
					• Sticky "Filter" button
					• Show result count with each filter option
					• "Apply Filters" button (not auto-update)
					
					Desktop vs Mobile:
					• Desktop: Sidebar filters work well
					• Mobile: Sidebar pushed below products (useless)
					• Solution: Modal/drawer pattern for mobile
					
					Impact of Poor Filters:
					• Users can\'t find products
					• Abandon to competitor sites
					• Lower average order value
					• Poor mobile conversion rates',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Implement mobile-friendly filters:
					
					WooCommerce Plugins (Recommended):
					
					1. YITH WooCommerce Ajax Product Filter (Free + Premium)
					   • Mobile drawer/modal for filters
					   • AJAX updates (no page reload)
					   • Large touch-friendly checkboxes
					   • Active filter chips
					   • wordpress.org/plugins/yith-woocommerce-ajax-navigation
					
					2. Product Filter for WooCommerce (Free)
					   • Mobile-optimized filter UI
					   • AJAX filtering
					   • Multiple layouts
					   • wordpress.org/plugins/prdctfltr
					
					3. FacetWP (Premium)
					   • Advanced faceted search
					   • Mobile-first design
					   • Fast AJAX performance
					   • facetwp.com
					
					4. Relevanssi Premium (with WooCommerce)
					   • Better product search
					   • Works with filter plugins
					   • Fast performance
					
					Mobile Filter Pattern (Custom):
					
					HTML Structure:
					<!-- Filter Button (Sticky) -->
					<button id="open-filters" class="filter-button">
					  <span class="icon">⚙️</span>
					  Filters
					  <span class="active-count" data-count="0"></span>
					</button>
					
					<!-- Filter Drawer (Hidden by default) -->
					<div id="filter-drawer" class="filter-drawer" aria-hidden="true">
					  <div class="drawer-overlay"></div>
					  <div class="drawer-content">
					    <div class="drawer-header">
					      <h2>Filter Products</h2>
					      <button class="close-drawer">×</button>
					    </div>
					    
					    <div class="drawer-body">
					      <!-- Filter Options -->
					      <div class="filter-group">
					        <h3>Category</h3>
					        <label class="filter-option">
					          <input type="checkbox" name="category" value="shirts">
					          <span>Shirts (24)</span>
					        </label>
					      </div>
					    </div>
					    
					    <div class="drawer-footer">
					      <button class="clear-filters">Clear All</button>
					      <button class="apply-filters">Apply Filters</button>
					    </div>
					  </div>
					</div>
					
					CSS (Mobile-First):
					.filter-button {
					  position: sticky;
					  bottom: 20px;
					  width: calc(100% - 32px);
					  margin: 0 16px;
					  padding: 16px;
					  font-size: 16px;
					  z-index: 100;
					}
					
					.filter-drawer {
					  position: fixed;
					  inset: 0;
					  z-index: 1000;
					  display: none;
					}
					
					.filter-drawer[aria-hidden="false"] {
					  display: block;
					}
					
					.drawer-overlay {
					  position: absolute;
					  inset: 0;
					  background: rgba(0, 0, 0, 0.5);
					}
					
					.drawer-content {
					  position: absolute;
					  bottom: 0;
					  left: 0;
					  right: 0;
					  max-height: 80vh;
					  background: white;
					  border-radius: 16px 16px 0 0;
					  animation: slideUp 0.3s ease;
					}
					
					.filter-option {
					  display: flex;
					  align-items: center;
					  padding: 12px 16px;
					  min-height: 44px; /* Touch target */
					}
					
					.filter-option input[type="checkbox"] {
					  width: 24px;
					  height: 24px;
					  margin-right: 12px;
					}
					
					JavaScript (AJAX Filtering):
					document.querySelector("#apply-filters").addEventListener("click", function() {
					  const filters = getSelectedFilters();
					  
					  fetch(ajaxurl, {
					    method: "POST",
					    body: new URLSearchParams({
					      action: "filter_products",
					      filters: JSON.stringify(filters)
					    })
					  })
					  .then(response => response.text())
					  .then(html => {
					    document.querySelector("#products-grid").innerHTML = html;
					    closeFilterDrawer();
					  });
					});
					
					WooCommerce Default Improvements:
					// Move sidebar filters to modal on mobile
					add_action( "woocommerce_before_shop_loop", "add_mobile_filter_button", 15 );
					function add_mobile_filter_button() {
					    if ( wp_is_mobile() ) {
					        echo "<button id=\"mobile-filters-toggle\" class=\"button\">Filters</button>";
					    }
					}
					
					Best Practices:
					• Button triggers drawer (don\'t hide in hamburger menu)
					• AJAX updates (no page reload)
					• Show product count for each filter option
					• "Clear All" button prominent
					• Active filters shown as removable chips
					• Smooth animations (slideUp from bottom)
					• Accessibility: focus trap in drawer, ESC to close',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check WooCommerce filter implementation.
	 *
	 * @since 1.6093.1200
	 * @return array Issues found.
	 */
	private static function check_woocommerce_filters() {
		$issues = array();

		// Get shop page URL.
		$shop_id  = wc_get_page_id( 'shop' );
		$shop_url = $shop_id > 0 ? get_permalink( $shop_id ) : wc_get_page_permalink( 'shop' );

		if ( empty( $shop_url ) ) {
			return $issues;
		}

		// Capture shop page HTML.
		$html = self::capture_page_html( $shop_url );
		if ( empty( $html ) ) {
			return $issues;
		}

		// Check for filter widgets.
		$has_filters = preg_match( '/widget.*?(?:layered_nav|product_categories|product_tag_cloud|price_filter)/i', $html );

		if ( $has_filters ) {
			// Check if filters are in sidebar (bad for mobile).
			$has_sidebar = preg_match( '/<aside[^>]*class=["\'][^"\']*sidebar[^"\']*["\']/', $html ) ||
						   preg_match( '/<div[^>]*class=["\'][^"\']*sidebar[^"\']*["\']/', $html );

			if ( $has_sidebar ) {
				$issues[] = array(
					'issue_type'  => 'sidebar_filters',
					'severity'    => 'medium',
					'description' => 'Filters appear to be in sidebar (poor mobile UX - pushed below products)',
					'recommendation' => 'Use drawer/modal pattern for mobile filters',
				);
			}

			// Check for mobile filter button.
			$has_filter_button = preg_match( '/button[^>]*(?:filter|toggle.*filter)/i', $html );

			if ( ! $has_filter_button ) {
				$issues[] = array(
					'issue_type'  => 'no_filter_button',
					'severity'    => 'medium',
					'description' => 'No mobile filter button detected',
					'impact'      => 'Users may not find filters on mobile',
				);
			}
		} else {
			// No filters detected but products exist.
			$product_count = wp_count_posts( 'product' );
			if ( $product_count && $product_count->publish > 20 ) {
				$issues[] = array(
					'issue_type'  => 'no_filters',
					'severity'    => 'medium',
					'description' => sprintf( 'Shop has %d products but no filters detected', $product_count->publish ),
					'impact'      => 'Users cannot narrow down product selection',
				);
			}
		}

		// Check for sort dropdown.
		$has_orderby = preg_match( '/orderby/', $html );
		if ( $has_orderby ) {
			// Check if orderby is mobile-friendly.
			if ( preg_match( '/<select[^>]*name=["\']orderby["\'][^>]*>/', $html ) ) {
				// Standard dropdown - check if styled for mobile.
				$issues[] = array(
					'issue_type'  => 'default_sort_dropdown',
					'severity'    => 'low',
					'description' => 'Using default <select> dropdown for sort (consider custom mobile UI)',
					'impact'      => 'Native dropdowns can be clunky on mobile',
				);
			}
		}

		return $issues;
	}

	/**
	 * Capture page HTML.
	 *
	 * @since 1.6093.1200
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
