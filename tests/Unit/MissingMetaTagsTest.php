<?php
/**
 * Tests for Missing Meta Tags Audit Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6028.1635
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Missing_Meta_Tags;
use WP_Mock\Tools\TestCase;

/**
 * Missing Meta Tags Diagnostic Test Class
 *
 * @since 1.6028.1635
 */
class MissingMetaTagsTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes when tags complete
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_passes_when_tags_complete(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 0,
				'overall_completeness' => 100,
				'issues'               => array(),
			),
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags low completeness
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_flags_low_completeness(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 4,
				'overall_completeness' => 45,
				'missing_tags'         => array( 'description', 'og_image' ),
				'issues'               => array(
					'Only 45% of SEO meta tags are complete',
					'Meta descriptions are missing',
				),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 2,
					'og_title'        => 5,
					'og_description'  => 3,
					'og_image'        => 1,
					'og_url'          => 4,
					'twitter_card'    => 2,
					'structured_data' => 0,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 2 issues',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'missing-meta-tags', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 45, $result['meta']['overall_completeness'] );
	}

	/**
	 * Test diagnostic flags missing descriptions
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_flags_missing_descriptions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 6,
				'overall_completeness' => 75,
				'missing_tags'         => array( 'description' ),
				'issues'               => array( 'Meta descriptions are missing on all checked pages' ),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 0,
					'og_title'        => 6,
					'og_description'  => 6,
					'og_image'        => 6,
					'og_url'          => 6,
					'twitter_card'    => 6,
					'structured_data' => 6,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertContains( 'description', $result['meta']['missing_tag_types'] );
	}

	/**
	 * Test diagnostic flags missing OG tags
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_flags_missing_og_tags(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 6,
				'overall_completeness' => 65,
				'missing_tags'         => array( 'og_image', 'og_url' ),
				'issues'               => array(
					'OG image tags are missing on all checked pages',
					'OG URL tags are only 33% complete',
				),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 6,
					'og_title'        => 6,
					'og_description'  => 6,
					'og_image'        => 0,
					'og_url'          => 2,
					'twitter_card'    => 4,
					'structured_data' => 5,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 2 issues',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertContains( 'og_image', $result['meta']['missing_tag_types'] );
		$this->assertContains( 'og_url', $result['meta']['missing_tag_types'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 3,
				'overall_completeness' => 55,
				'missing_tags'         => array( 'structured_data' ),
				'issues'               => array( 'Structured data missing' ),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 5,
					'og_title'        => 6,
					'og_description'  => 5,
					'og_image'        => 4,
					'og_url'          => 6,
					'twitter_card'    => 3,
					'structured_data' => 0,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'family', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );

		$this->assertEquals( 'missing-meta-tags', $result['id'] );
		$this->assertEquals( 'seo', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes completeness data
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_meta_includes_completeness_data(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 2,
				'overall_completeness' => 75,
				'missing_tags'         => array(),
				'issues'               => array( 'Test' ),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 6,
					'og_title'        => 6,
					'og_description'  => 6,
					'og_image'        => 6,
					'og_url'          => 6,
					'twitter_card'    => 0,
					'structured_data' => 0,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'overall_completeness', $result['meta'] );
		$this->assertArrayHasKey( 'pages_checked', $result['meta'] );
		$this->assertArrayHasKey( 'seo_impact', $result['meta'] );
		$this->assertEquals( 75, $result['meta']['overall_completeness'] );
	}

	/**
	 * Test details include recommended plugins
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_details_include_recommended_plugins(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 1,
				'overall_completeness' => 70,
				'missing_tags'         => array(),
				'issues'               => array( 'Test' ),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 6,
					'og_title'        => 6,
					'og_description'  => 6,
					'og_image'        => 6,
					'og_url'          => 6,
					'twitter_card'    => 6,
					'structured_data' => 0,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommended_plugins', $result['details'] );
		$this->assertArrayHasKey( 'Yoast SEO', $result['details']['recommended_plugins'] );
	}

	/**
	 * Test threat level calculation
	 *
	 * @since 1.6028.1635
	 * @return void
	 */
	public function test_threat_level_calculation(): void {
		// Low completeness = higher threat.
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'pages_checked'        => 6,
				'pages_with_issues'    => 6,
				'overall_completeness' => 15,
				'missing_tags'         => array( 'description', 'og_image', 'structured_data' ),
				'issues'               => array( 'Test1', 'Test2', 'Test3' ),
				'tag_scores'           => array(
					'title'           => 6,
					'description'     => 0,
					'og_title'        => 0,
					'og_description'  => 0,
					'og_image'        => 0,
					'og_url'          => 0,
					'twitter_card'    => 0,
					'structured_data' => 0,
				),
				'pages_analyzed'       => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_Missing_Meta_Tags::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 50, $result['threat_level'] );
		$this->assertEquals( 'high', $result['severity'] );
	}
}
