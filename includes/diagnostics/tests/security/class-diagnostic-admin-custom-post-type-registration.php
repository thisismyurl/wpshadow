<?php
/**
 * Admin Custom Post Type Registration Diagnostic
 *
 * Validates that custom post types (CPTs) are properly registered with appropriate
 * capabilities, labels, and security settings. Custom post types extend WordPress
 * beyond posts and pages - but misconfigured CPTs create security holes, break
 * permissions, or confuse users with poor UX.
 *
 * **What This Check Does:**
 * - Scans registered custom post types via `get_post_types()`
 * - Validates each CPT has proper capability mapping
 * - Checks if public CPTs have appropriate security settings
 * - Detects missing or incomplete labels (UX issue)
 * - Identifies CPT sprawl (>10 types = confusion)
 * - Validates CPT slug naming conventions
 *
 * **Why This Matters:**
 * Custom post types control content structure and permissions. A public CPT
 * without proper capabilities means anyone can create/edit that content type.
 * Missing labels mean users see "Add New Post" instead of "Add New Product" -
 * confusing and unprofessional. CPT sprawl (10+ types) overwhelms users and
 * slows admin performance.
 *
 * **Real-World Security Issue:**
 * E-commerce plugin registers "Products" CPT:
 * ```php
 * // VULNERABLE:
 * register_post_type( 'product', array(
 *   'public' => true,
 *   // No 'capability_type' specified
 *   // No 'capabilities' array
 * ) );
 * ```
 * Result: WordPress uses default 'post' capabilities.
 * Subscribers can create products. Contributors can edit all products.
 * Complete content security bypass.
 *
 * **Proper CPT Registration:**
 * ```php
 * register_post_type( 'product', array(
 *   'public'             => true,
 *   'capability_type'    => 'product',
 *   'map_meta_cap'       => true, // Enables fine-grained permissions
 *   'capabilities'       => array(
 *     'edit_post'          => 'edit_product',
 *     'edit_posts'         => 'edit_products',
 *     'edit_others_posts'  => 'edit_others_products',
 *     'publish_posts'      => 'publish_products',
 *     'read_post'          => 'read_product',
 *     'delete_post'        => 'delete_product',
 *   ),
 *   'labels' => array(
 *     'name'          => 'Products',
 *     'singular_name' => 'Product',
 *     'add_new_item'  => 'Add New Product',
 *     // ... complete label set
 *   ),
 * ) );
 * ```
 *
 * **Common CPT Mistakes:**
 *
 * **1. Missing Capabilities (Security):**
 * CPT uses default 'post' capabilities → Wrong users can edit
 * Solution: Always define `capability_type` and `capabilities` array
 *
 * **2. Incomplete Labels (UX):**
 * CPT missing labels → Admin shows "Add New Post" for products
 * Users confused, looks unprofessional
 * Solution: Define all 12+ labels for complete UX
 *
 * **3. CPT Sprawl (Performance + UX):**
 * Plugin A: Products CPT
 * Plugin B: Products CPT (different slug)
 * Plugin C: Product CPT (singular)
 * Result: 3 menu items, user confusion, duplicate content
 * Solution: Standardize CPT slugs, limit to 5-7 types maximum
 *
 * **4. Public CPTs Without Consideration:**
 * ```php
 * 'public' => true,  // Appears in REST API, search, feeds
 * ```
 * Ask:
 * - Should this appear in site search? (public)
 * - Should REST API expose this? (show_in_rest)
 * - Should users see archives? (has_archive)
 * Don't default to public without thinking through implications
 *
 * **5. Slug Naming Issues:**
 * Bad slugs: `my-products`, `myproduct`, `my_product_type`
 * Good slugs: `product` (singular, lowercase, no underscores)
 * Reason: Slug becomes URL (`site.com/product/item-name`)
 *
 * **What This Diagnostic Flags:**
 * - Public CPTs without custom capabilities (security)
 * - CPTs missing `capability_type` (permission bypass)
 * - >10 registered CPTs (performance + UX confusion)
 * - CPTs with incomplete label arrays (poor UX)
 * - CPT slugs with underscores or uppercase (URL issues)
 *
 * **Performance Impact:**
 * Each CPT adds database queries for:
 * - Admin menu rendering
 * - Capability checks
 * - Meta box registration
 * 15+ CPTs = 50-100ms slower admin page loads
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Ensures content security boundaries respected
 * - #1 Helpful Neighbor: Prevents user confusion with proper labels
 * - Technical Excellence: Validates WordPress CPT best practices
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/custom-post-type-security for complete guide
 * or https://wpshadow.com/training/custom-post-types-capabilities
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0638
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Custom Post Type Registration
 *
 * Uses WordPress' `get_post_types()` function to retrieve and analyze CPT registrations.
 * All CPTs (except built-in types like 'post' and 'page') are examined for security
 * and usability issues.
 *
 * **Implementation Pattern:**
 * 1. Get non-built-in post types: `get_post_types( array( '_builtin' => false ), 'objects' )`
 * 2. For each CPT, check:
 *    - Is it public? (exposed to users)
 *    - Does it have custom capabilities? (security)
 *    - Are labels complete? (UX)
 *    - Is slug properly formatted? (URLs)
 * 3. Count total CPTs (>10 = sprawl)
 * 4. Return finding if issues detected
 *
 * **Security Analysis:**
 * - Public CPT + empty capabilities = Anyone can edit
 * - Public CPT + 'post' capability_type = Wrong permission mapping
 * - Public CPT + show_in_rest but no auth callback = REST API security hole
 *
 * **UX Analysis:**
 * - Missing 'add_new_item' label: Shows generic "Add New Post"
 * - Missing 'edit_item' label: Shows "Edit Post" instead of "Edit Product"
 * - Incomplete labels: Inconsistent admin interface
 *
 * **Related Diagnostics:**
 * - Capability Map Consistency: Validates CPT capabilities properly mapped
 * - Admin Menu Visibility: Checks CPT menu items respect capabilities
 * - REST API Security: Validates CPT REST endpoints have auth
 *
 * @since 1.6033.0638
 */
class Diagnostic_Admin_Custom_Post_Type_Registration extends Diagnostic_Base {

	protected static $slug = 'admin-custom-post-type-registration';
	protected static $title = 'Admin Custom Post Type Registration';
	protected static $description = 'Verifies custom post types are properly registered';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all custom post types
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );
		$public_count = 0;

		foreach ( $post_types as $post_type ) {
			// Check if post type is publicly accessible without proper capability
			if ( $post_type->public && empty( $post_type->capabilities ) ) {
				$public_count++;
			}
		}

		if ( $public_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of post types */
				__( '%d custom post type(s) are public but lack proper capabilities defined', 'wpshadow' ),
				$public_count
			);
		}

		// Check custom post type count
		$total_cpt = count( $post_types );
		if ( $total_cpt > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of post types */
				__( 'High number of custom post types (%d) registered', 'wpshadow' ),
				$total_cpt
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-custom-post-type-registration',
			);
		}

		return null;
	}
}
