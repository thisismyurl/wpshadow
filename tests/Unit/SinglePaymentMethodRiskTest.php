<?php
/**
 * Tests for Single Payment Method Risk Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_Single_Payment_Method_Risk;
use WP_Mock\Tools\TestCase;

/**
 * Test Single Payment Method Risk Diagnostic
 */
class SinglePaymentMethodRiskTest extends TestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test diagnostic passes when no ecommerce plugin
	 */
	public function test_passes_when_no_ecommerce() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic passes when multiple payment methods available
	 */
	public function test_passes_when_multiple_methods() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
					'paypal' => (object) array( 'id' => 'paypal' ),
					'bacs'   => (object) array( 'id' => 'bacs' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertNull( $result ); // 3 methods is good.
	}

	/**
	 * Test diagnostic flags single payment method
	 */
	public function test_flags_single_payment_method() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'single-payment-method-risk', $result['id'] );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertArrayHasKey( 'payment_count', $result['meta'] );
		$this->assertEquals( 1, $result['meta']['payment_count'] );
	}

	/**
	 * Test diagnostic flags no payment methods
	 */
	public function test_flags_no_payment_methods() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn( array() );
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 0, $result['meta']['payment_count'] );
	}

	/**
	 * Test diagnostic structure
	 */
	public function test_diagnostic_structure() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes payment types
	 */
	public function test_meta_includes_payment_types() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertArrayHasKey( 'payment_types', $result['meta'] );
		$this->assertIsArray( $result['meta']['payment_types'] );
		$this->assertArrayHasKey( 'credit_card', $result['meta']['payment_types'] );
		$this->assertTrue( $result['meta']['payment_types']['credit_card'] );
	}

	/**
	 * Test details populated
	 */
	public function test_details_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
	}

	/**
	 * Test recommendations populated
	 */
	public function test_recommendations_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
		$this->assertGreaterThanOrEqual( 4, count( $result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'single-payment-method-risk' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level appropriate for risk
	 */
	public function test_threat_level_appropriate() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'WooCommerce' )->andReturn( true );
		
		$mock_wc = $this->createMock( \stdClass::class );
		$mock_payment_gateways = $this->createMock( \stdClass::class );
		
		$mock_payment_gateways->method( 'get_available_payment_gateways' )
			->willReturn(
				array(
					'stripe' => (object) array( 'id' => 'stripe' ),
				)
			);
		
		$mock_wc->payment_gateways = $mock_payment_gateways;
		
		WP_Mock::userFunction( 'WC' )->andReturn( $mock_wc );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Single_Payment_Method_Risk::check();

		$this->assertEquals( 55, $result['threat_level'] );
	}
}
