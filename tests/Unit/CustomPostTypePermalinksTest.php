<?php
/**
 * Custom Post Type Permalinks Diagnostic Tests
 *
 * @package    WPShadow
 * @subpackage Tests
 * @since      1.6032.1402
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Custom_Post_Type_Permalinks;
use WP_Mock\Tools\TestCase;

/**
 * Custom Post Type Permalinks Diagnostic Test Class
 *
 * Tests the diagnostic that validates CPT permalink structures
 * and rewrite slug configurations.
 *
 * @since 1.6032.1402
 */
class CustomPostTypePermalinksTest extends TestCase {

	/**
	 * Test that diagnostic has correct slug
	 *
	 * @since 1.6032.1402
	 */
	public function test_has_correct_slug() {
		$this->assertEquals(
			'custom-post-type-permalinks',
			Diagnostic_Custom_Post_Type_Permalinks::get_slug()
		);
	}

	/**
	 * Test that diagnostic has correct title
	 *
	 * @since 1.6032.1402
	 */
	public function test_has_correct_title() {
		$this->assertEquals(
			'Custom Post Type Permalinks',
			Diagnostic_Custom_Post_Type_Permalinks::get_title()
		);
	}

	/**
	 * Test that diagnostic has correct description
	 *
	 * @since 1.6032.1402
	 */
	public function test_has_correct_description() {
		$this->assertEquals(
			'Validates CPT permalink structures and rewrite slug configuration',
			Diagnostic_Custom_Post_Type_Permalinks::get_description()
		);
	}

	/**
	 * Test that diagnostic belongs to seo family
	 *
	 * @since 1.6032.1402
	 */
	public function test_belongs_to_seo_family() {
		$this->assertEquals(
			'seo',
			Diagnostic_Custom_Post_Type_Permalinks::get_family()
		);
	}

	/**
	 * Test passes when no custom post types are registered
	 *
	 * Verifies that the diagnostic returns null (no issues) when
	 * there are no custom post types registered on the site.
	 *
	 * @since 1.6032.1402
	 */
	public function test_passes_with_no_custom_post_types() {
		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array() );

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertNull( $result );
	}

	/**
	 * Test passes with properly configured custom post type
	 *
	 * Verifies that the diagnostic returns null when a CPT is
	 * properly configured with valid rewrite rules.
	 *
	 * @since 1.6032.1402
	 */
	public function test_passes_with_properly_configured_cpt() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug'         => 'portfolio',
				'with_front'   => true,
				'hierarchical' => false,
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'portfolio' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertNull( $result );
	}

	/**
	 * Test flags CPT with disabled rewrites
	 *
	 * Verifies that the diagnostic detects when a custom post type
	 * has rewrites explicitly disabled (rewrite => false).
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_cpt_with_disabled_rewrites() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertEquals( 'custom-post-type-permalinks', $result['id'] );
		$this->assertEquals( 60, $result['threat_level'] );
		$this->assertStringContainsString( 'Rewrites disabled', $result['description'] );
	}

	/**
	 * Test flags CPT with missing rewrite slug
	 *
	 * Verifies that the diagnostic detects when a custom post type
	 * has rewrite enabled but no slug defined.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_cpt_with_missing_rewrite_slug() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				// Missing 'slug' key
				'with_front' => true,
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Missing rewrite slug', $result['description'] );
	}

	/**
	 * Test flags CPT with reserved slug conflict
	 *
	 * Verifies that the diagnostic detects when a CPT uses a
	 * reserved WordPress slug that could cause conflicts.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_cpt_with_reserved_slug() {
		$mock_post_type = (object) array(
			'name'                => 'custom_admin',
			'label'               => 'Custom Admin',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'admin', // Reserved slug!
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'custom_admin' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'admin' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'conflicts with reserved WordPress slug', $result['description'] );
	}

	/**
	 * Test flags CPT with page slug conflict
	 *
	 * Verifies that the diagnostic detects when a CPT slug
	 * conflicts with an existing page.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_cpt_with_page_conflict() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'about', // Conflicts with existing page
			),
		);

		$mock_page = (object) array(
			'ID'         => 123,
			'post_title' => 'About',
			'post_name'  => 'about',
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'about' )
			->andReturn( $mock_page );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'conflicts with existing page', $result['description'] );
	}

	/**
	 * Test flags CPT with invalid characters in slug
	 *
	 * Verifies that the diagnostic detects invalid characters
	 * in rewrite slugs that could cause URL issues.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_cpt_with_invalid_slug_characters() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'my portfolio!', // Invalid characters
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'my portfolio!' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'contains invalid characters', $result['description'] );
	}

	/**
	 * Test flags hierarchical CPT missing hierarchical rewrite
	 *
	 * Verifies that the diagnostic detects when a hierarchical
	 * post type doesn't have hierarchical rewrite enabled.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_hierarchical_cpt_without_hierarchical_rewrite() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => true, // Hierarchical
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'portfolio',
				// Missing 'hierarchical' => true
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'portfolio' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Hierarchical post type missing hierarchical rewrite setting', $result['description'] );
	}

	/**
	 * Test flags public CPT without archive
	 *
	 * Verifies that the diagnostic detects when a public, queryable
	 * post type doesn't have archive pages enabled.
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_public_cpt_without_archive() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => false, // No archive
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'portfolio',
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'portfolio' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Archive disabled for public post type', $result['description'] );
	}

	/**
	 * Test flags when pretty permalinks are not enabled
	 *
	 * Verifies that the diagnostic detects when WordPress is using
	 * default permalinks (no pretty permalinks enabled).
	 *
	 * @since 1.6032.1402
	 */
	public function test_flags_when_pretty_permalinks_disabled() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'publicly_queryable'  => true,
			'hierarchical'        => false,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'rewrite'             => array(
				'slug' => 'portfolio',
			),
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_page_by_path' )
			->once()
			->with( 'portfolio' )
			->andReturn( null );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '' ); // Empty = default permalinks

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		\WP_Mock::userFunction( 'sprintf' )
			->andReturnUsing(
				function ( $format, ...$args ) {
					return vsprintf( $format, $args );
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Pretty permalinks are not enabled', $result['description'] );
	}

	/**
	 * Test skips non-public custom post types
	 *
	 * Verifies that the diagnostic ignores non-public CPTs
	 * as they don't need public permalink configurations.
	 *
	 * @since 1.6032.1402
	 */
	public function test_skips_non_public_post_types() {
		$mock_post_type = (object) array(
			'name'         => 'internal',
			'label'        => 'Internal',
			'public'       => false, // Not public
			'rewrite'      => false,
			'hierarchical' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'internal' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertNull( $result );
	}

	/**
	 * Test threat level is set to 60
	 *
	 * Verifies that the diagnostic correctly sets threat level to 60
	 * as specified in the requirements.
	 *
	 * @since 1.6032.1402
	 */
	public function test_threat_level_is_60() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertEquals( 60, $result['threat_level'] );
	}

	/**
	 * Test severity is medium
	 *
	 * Verifies that the diagnostic sets severity to medium.
	 *
	 * @since 1.6032.1402
	 */
	public function test_severity_is_medium() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertEquals( 'medium', $result['severity'] );
	}

	/**
	 * Test is not auto-fixable
	 *
	 * Verifies that the diagnostic is marked as not auto-fixable
	 * since permalink changes require manual configuration.
	 *
	 * @since 1.6032.1402
	 */
	public function test_is_not_auto_fixable() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test includes details in result
	 *
	 * Verifies that the diagnostic includes detailed information
	 * about problematic post types and configurations.
	 *
	 * @since 1.6032.1402
	 */
	public function test_includes_details_in_result() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'post_types', $result['details'] );
		$this->assertArrayHasKey( 'problematic_post_types', $result['details'] );
		$this->assertArrayHasKey( 'issues', $result['details'] );
		$this->assertArrayHasKey( 'permalink_structure', $result['details'] );
	}

	/**
	 * Test includes knowledge base link
	 *
	 * Verifies that the diagnostic includes a link to the
	 * relevant knowledge base article.
	 *
	 * @since 1.6032.1402
	 */
	public function test_includes_kb_link() {
		$mock_post_type = (object) array(
			'name'                => 'portfolio',
			'label'               => 'Portfolio',
			'public'              => true,
			'rewrite'             => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		);

		\WP_Mock::userFunction( 'get_post_types' )
			->once()
			->with( array( '_builtin' => false ), 'objects' )
			->andReturn( array( 'portfolio' => $mock_post_type ) );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'permalink_structure' )
			->andReturn( '/%postname%/' );

		\WP_Mock::userFunction( '__' )
			->andReturnUsing(
				function ( $text ) {
					return $text;
				}
			);

		$result = Diagnostic_Custom_Post_Type_Permalinks::check();
		$this->assertEquals(
			'https://wpshadow.com/kb/custom-post-type-permalinks',
			$result['kb_link']
		);
	}
}
