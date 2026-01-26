#!/bin/bash

##
# WPShadow Test Runner
#
# Runs all tests and generates reports.
#
# Usage:
#   ./run-tests.sh [options]
#
# Options:
#   --unit           Run only unit tests
#   --integration    Run only integration tests
#   --accessibility  Run only accessibility tests
#   --coverage       Generate code coverage report
#   --verbose        Verbose output
#   --help           Show this help message
#
# Examples:
#   ./run-tests.sh                     # Run all tests
#   ./run-tests.sh --unit              # Run only unit tests
#   ./run-tests.sh --coverage          # Run with coverage
##

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"
PHPUNIT="$PROJECT_ROOT/vendor/bin/phpunit"
COVERAGE_DIR="$PROJECT_ROOT/tests/coverage"

# Parse arguments
RUN_UNIT=false
RUN_INTEGRATION=false
RUN_ACCESSIBILITY=false
GENERATE_COVERAGE=false
VERBOSE=false
SHOW_HELP=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --unit)
            RUN_UNIT=true
            shift
            ;;
        --integration)
            RUN_INTEGRATION=true
            shift
            ;;
        --accessibility)
            RUN_ACCESSIBILITY=true
            shift
            ;;
        --coverage)
            GENERATE_COVERAGE=true
            shift
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        --help)
            SHOW_HELP=true
            shift
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            SHOW_HELP=true
            shift
            ;;
    esac
done

# Show help
if [ "$SHOW_HELP" = true ]; then
    head -n 25 "$0" | tail -n +3 | sed 's/^##*//'
    exit 0
fi

# If no specific test type selected, run all
if [ "$RUN_UNIT" = false ] && [ "$RUN_INTEGRATION" = false ] && [ "$RUN_ACCESSIBILITY" = false ]; then
    RUN_UNIT=true
    RUN_INTEGRATION=true
    RUN_ACCESSIBILITY=true
fi

echo "======================================================================"
echo -e "${BLUE}WPShadow Test Suite${NC}"
echo "======================================================================"
echo ""

# Check if PHPUnit is installed
if [ ! -f "$PHPUNIT" ]; then
    echo -e "${RED}✗ PHPUnit not found${NC}"
    echo ""
    echo "Installing dependencies..."
    cd "$PROJECT_ROOT"
    composer install
    echo ""
fi

# Check if phpunit.xml exists
if [ ! -f "$PROJECT_ROOT/phpunit.xml" ]; then
    echo -e "${RED}✗ phpunit.xml not found${NC}"
    echo "Please ensure phpunit.xml is in the project root."
    exit 1
fi

# Change to project root
cd "$PROJECT_ROOT"

# Build PHPUnit command
PHPUNIT_CMD="$PHPUNIT"

if [ "$VERBOSE" = true ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --verbose"
fi

if [ "$GENERATE_COVERAGE" = true ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --coverage-html $COVERAGE_DIR --coverage-text"
    echo -e "${BLUE}Code coverage will be generated in: $COVERAGE_DIR${NC}"
    echo ""
fi

# Track test results
TESTS_PASSED=0
TESTS_FAILED=0
TOTAL_TESTS=0

# Function to run test suite
run_test_suite() {
    local SUITE_NAME=$1
    local SUITE_PATH=$2
    
    echo ""
    echo "----------------------------------------------------------------------"
    echo -e "${BLUE}Running ${SUITE_NAME} Tests${NC}"
    echo "----------------------------------------------------------------------"
    echo ""
    
    if [ ! -d "$SUITE_PATH" ]; then
        echo -e "${YELLOW}⚠ ${SUITE_NAME} test directory not found: $SUITE_PATH${NC}"
        return 1
    fi
    
    # Count test files
    TEST_FILES=$(find "$SUITE_PATH" -name "*Test.php" | wc -l)
    echo -e "Test files found: ${GREEN}$TEST_FILES${NC}"
    echo ""
    
    if [ $TEST_FILES -eq 0 ]; then
        echo -e "${YELLOW}⚠ No test files found in $SUITE_NAME${NC}"
        return 1
    fi
    
    # Run tests
    if $PHPUNIT_CMD --testsuite "$SUITE_NAME"; then
        echo ""
        echo -e "${GREEN}✓ ${SUITE_NAME} tests passed${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo ""
        echo -e "${RED}✗ ${SUITE_NAME} tests failed${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Run selected test suites
if [ "$RUN_UNIT" = true ]; then
    run_test_suite "Unit" "$PROJECT_ROOT/tests/Unit"
    ((TOTAL_TESTS++))
fi

if [ "$RUN_INTEGRATION" = true ]; then
    run_test_suite "Integration" "$PROJECT_ROOT/tests/Integration"
    ((TOTAL_TESTS++))
fi

if [ "$RUN_ACCESSIBILITY" = true ]; then
    run_test_suite "Accessibility" "$PROJECT_ROOT/tests/Accessibility"
    ((TOTAL_TESTS++))
fi

# Summary
echo ""
echo "======================================================================"
echo -e "${BLUE}Test Summary${NC}"
echo "======================================================================"
echo ""
echo -e "Test Suites Run:    ${BLUE}$TOTAL_TESTS${NC}"
echo -e "Test Suites Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Test Suites Failed: ${RED}$TESTS_FAILED${NC}"
echo ""

# Coverage report link
if [ "$GENERATE_COVERAGE" = true ] && [ -f "$COVERAGE_DIR/index.html" ]; then
    echo -e "${GREEN}✓ Code coverage report generated${NC}"
    echo -e "   Open: ${BLUE}$COVERAGE_DIR/index.html${NC}"
    echo ""
fi

# Exit status
if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Some tests failed${NC}"
    echo ""
    exit 1
fi
