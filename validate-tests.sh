#!/bin/bash

##
# WPShadow Test Structure Validator
#
# Validates test files are properly structured without requiring PHP.
# This checks syntax, structure, and completeness of the test suite.
##

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$SCRIPT_DIR"

echo "======================================================================"
echo -e "${BLUE}WPShadow Test Suite Validation${NC}"
echo "======================================================================"
echo ""
echo "Note: PHP not available in current environment."
echo "Validating test structure and completeness..."
echo ""

TOTAL_CHECKS=0
PASSED_CHECKS=0
FAILED_CHECKS=0

# Function to run check
run_check() {
    local NAME=$1
    local COMMAND=$2
    
    ((TOTAL_CHECKS++))
    
    if eval "$COMMAND" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} $NAME"
        ((PASSED_CHECKS++))
        return 0
    else
        echo -e "${RED}✗${NC} $NAME"
        ((FAILED_CHECKS++))
        return 1
    fi
}

echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test File Structure${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check test directories exist
run_check "tests/ directory exists" "test -d $PROJECT_ROOT/tests"
run_check "tests/Unit/ directory exists" "test -d $PROJECT_ROOT/tests/Unit"
run_check "tests/Integration/ directory exists" "test -d $PROJECT_ROOT/tests/Integration"
run_check "tests/Accessibility/ directory exists" "test -d $PROJECT_ROOT/tests/Accessibility"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test Configuration Files${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check configuration files
run_check "phpunit.xml exists" "test -f $PROJECT_ROOT/phpunit.xml"
run_check "tests/bootstrap.php exists" "test -f $PROJECT_ROOT/tests/bootstrap.php"
run_check "tests/TestCase.php exists" "test -f $PROJECT_ROOT/tests/TestCase.php"
run_check "run-tests.sh exists and is executable" "test -x $PROJECT_ROOT/run-tests.sh"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test Files${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check test files exist
run_check "DiagnosticBaseTest.php exists" "test -f $PROJECT_ROOT/tests/Unit/DiagnosticBaseTest.php"
run_check "TreatmentBaseTest.php exists" "test -f $PROJECT_ROOT/tests/Unit/TreatmentBaseTest.php"
run_check "FeatureIntegrationTest.php exists" "test -f $PROJECT_ROOT/tests/Integration/FeatureIntegrationTest.php"
run_check "WCAGComplianceTest.php exists" "test -f $PROJECT_ROOT/tests/Accessibility/WCAGComplianceTest.php"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test File Content Validation${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Count test methods in each file
DIAGNOSTIC_TESTS=$(grep -c "public function test" $PROJECT_ROOT/tests/Unit/DiagnosticBaseTest.php 2>/dev/null || echo "0")
TREATMENT_TESTS=$(grep -c "public function test" $PROJECT_ROOT/tests/Unit/TreatmentBaseTest.php 2>/dev/null || echo "0")
INTEGRATION_TESTS=$(grep -c "public function test" $PROJECT_ROOT/tests/Integration/FeatureIntegrationTest.php 2>/dev/null || echo "0")
ACCESSIBILITY_TESTS=$(grep -c "public function test" $PROJECT_ROOT/tests/Accessibility/WCAGComplianceTest.php 2>/dev/null || echo "0")

TOTAL_TESTS=$((DIAGNOSTIC_TESTS + TREATMENT_TESTS + INTEGRATION_TESTS + ACCESSIBILITY_TESTS))

echo -e "DiagnosticBaseTest:       ${GREEN}${DIAGNOSTIC_TESTS} tests${NC}"
echo -e "TreatmentBaseTest:        ${GREEN}${TREATMENT_TESTS} tests${NC}"
echo -e "FeatureIntegrationTest:   ${GREEN}${INTEGRATION_TESTS} tests${NC}"
echo -e "WCAGComplianceTest:       ${GREEN}${ACCESSIBILITY_TESTS} tests${NC}"
echo ""
echo -e "Total test methods:       ${BLUE}${TOTAL_TESTS} tests${NC}"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test Class Structure${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Verify test classes extend TestCase
run_check "DiagnosticBaseTest extends TestCase" "grep -q 'extends TestCase' $PROJECT_ROOT/tests/Unit/DiagnosticBaseTest.php"
run_check "TreatmentBaseTest extends TestCase" "grep -q 'extends TestCase' $PROJECT_ROOT/tests/Unit/TreatmentBaseTest.php"
run_check "FeatureIntegrationTest extends TestCase" "grep -q 'extends TestCase' $PROJECT_ROOT/tests/Integration/FeatureIntegrationTest.php"
run_check "WCAGComplianceTest extends TestCase" "grep -q 'extends TestCase' $PROJECT_ROOT/tests/Accessibility/WCAGComplianceTest.php"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Test Namespaces${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Verify proper namespaces
run_check "DiagnosticBaseTest uses WPShadow\\Tests\\Unit" "grep -q 'namespace WPShadow\\\\Tests\\\\Unit' $PROJECT_ROOT/tests/Unit/DiagnosticBaseTest.php"
run_check "TreatmentBaseTest uses WPShadow\\Tests\\Unit" "grep -q 'namespace WPShadow\\\\Tests\\\\Unit' $PROJECT_ROOT/tests/Unit/TreatmentBaseTest.php"
run_check "FeatureIntegrationTest uses WPShadow\\Tests\\Integration" "grep -q 'namespace WPShadow\\\\Tests\\\\Integration' $PROJECT_ROOT/tests/Integration/FeatureIntegrationTest.php"
run_check "WCAGComplianceTest uses WPShadow\\Tests\\Accessibility" "grep -q 'namespace WPShadow\\\\Tests\\\\Accessibility' $PROJECT_ROOT/tests/Accessibility/WCAGComplianceTest.php"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}PHPUnit Configuration${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check phpunit.xml structure
run_check "phpunit.xml has bootstrap path" "grep -q 'bootstrap=\"tests/bootstrap.php\"' $PROJECT_ROOT/phpunit.xml"
run_check "phpunit.xml defines Unit testsuite" "grep -q '<testsuite name=\"Unit\">' $PROJECT_ROOT/phpunit.xml"
run_check "phpunit.xml defines Integration testsuite" "grep -q '<testsuite name=\"Integration\">' $PROJECT_ROOT/phpunit.xml"
run_check "phpunit.xml defines Accessibility testsuite" "grep -q '<testsuite name=\"Accessibility\">' $PROJECT_ROOT/phpunit.xml"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Documentation${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check documentation
run_check "AUTOMATED_TESTING.md exists" "test -f $PROJECT_ROOT/docs/AUTOMATED_TESTING.md"
run_check "TEST_SUITE_IMPLEMENTATION_SUMMARY.md exists" "test -f $PROJECT_ROOT/TEST_SUITE_IMPLEMENTATION_SUMMARY.md"

echo ""
echo "----------------------------------------------------------------------"
echo -e "${BLUE}Composer Dependencies${NC}"
echo "----------------------------------------------------------------------"
echo ""

# Check vendor directory
run_check "vendor/ directory exists" "test -d $PROJECT_ROOT/vendor"
run_check "vendor/bin/phpunit exists" "test -f $PROJECT_ROOT/vendor/bin/phpunit"
run_check "vendor/autoload.php exists" "test -f $PROJECT_ROOT/vendor/autoload.php"

echo ""
echo "======================================================================"
echo -e "${BLUE}Validation Summary${NC}"
echo "======================================================================"
echo ""
echo -e "Total Checks:    ${BLUE}$TOTAL_CHECKS${NC}"
echo -e "Checks Passed:   ${GREEN}$PASSED_CHECKS${NC}"
echo -e "Checks Failed:   ${RED}$FAILED_CHECKS${NC}"
echo ""
echo -e "Test Methods:    ${BLUE}$TOTAL_TESTS${NC}"
echo "  - Unit:        $DIAGNOSTIC_TESTS + $TREATMENT_TESTS = $((DIAGNOSTIC_TESTS + TREATMENT_TESTS))"
echo "  - Integration: $INTEGRATION_TESTS"
echo "  - Accessibility: $ACCESSIBILITY_TESTS"
echo ""

if [ $FAILED_CHECKS -eq 0 ]; then
    echo -e "${GREEN}✓ Test suite structure is valid and complete${NC}"
    echo ""
    echo "To run tests, use one of these methods:"
    echo ""
    echo "  Method 1 (Recommended):"
    echo "    ./run-tests.sh"
    echo ""
    echo "  Method 2 (Direct PHPUnit):"
    echo "    php vendor/bin/phpunit"
    echo ""
    echo "  Method 3 (Composer):"
    echo "    composer test"
    echo ""
    echo "Note: Requires PHP 8.1+ environment with composer dependencies installed."
    echo ""
    exit 0
else
    echo -e "${RED}✗ Test suite has structural issues${NC}"
    echo ""
    echo "Please review failed checks above."
    echo ""
    exit 1
fi
