<?php
declare(strict_types=1);

namespace WPShadow\Tests\Diagnostics;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use WPShadow\Diagnostics\Diagnostic_Active_Login_Attacks;

/**
 * Test case for Active Login Attacks diagnostic
 */
class DiagnosticActiveLoginAttacksTest extends TestCase {
    
    /**
     * Setup test environment before each test
     */
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }
    
    /**
     * Teardown test environment after each test
     */
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }
    
    /**
     * Test that diagnostic returns null when no attack signals present
     */
    public function test_check_returns_null_when_no_attacks_detected() {
        // Mock WordPress transient functions to return no attack data
        Functions\when('get_transient')->justReturn(false);
        
        $result = Diagnostic_Active_Login_Attacks::check();
        
        $this->assertNull($result, 'Expected null when no attacks detected');
    }
    
    /**
     * Test that diagnostic returns finding when attacks detected
     */
    public function test_check_returns_finding_when_attacks_detected() {
        // Mock WordPress transient functions to simulate active attacks
        Functions\when('get_transient')
            ->alias(function($key) {
                if ($key === 'wpshadow_failed_logins_24h') {
                    return 150; // Above threshold
                }
                if ($key === 'wpshadow_suspicious_ips') {
                    return ['192.168.1.1', '192.168.1.2', '192.168.1.3'];
                }
                return false;
            });
        
        $result = Diagnostic_Active_Login_Attacks::check();
        
        $this->assertIsArray($result, 'Expected array result when attacks detected');
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('severity', $result);
        $this->assertEquals('critical', $result['severity']);
        $this->assertEquals('active-login-attacks', $result['id']);
    }
}
