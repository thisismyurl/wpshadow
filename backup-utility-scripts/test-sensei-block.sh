#!/bin/bash
# Test WPShadow Sensei Course Block Integration

echo "🔍 WPShadow Sensei Course Block Installation Verification"
echo "=========================================================="
echo ""

# Check block files
echo "✅ Block Files:"
echo "   - Block class: $(test -f /workspaces/wpshadow/includes/blocks/class-sensei-course-block.php && echo 'EXISTS' || echo 'MISSING')"
echo "   - block.json: $(test -f /workspaces/wpshadow/blocks/sensei-course/block.json && echo 'EXISTS' || echo 'MISSING')"
echo "   - index.js: $(test -f /workspaces/wpshadow/blocks/sensei-course/index.js && echo 'EXISTS' || echo 'MISSING')"
echo "   - render.php: $(test -f /workspaces/wpshadow/blocks/sensei-course/render.php && echo 'EXISTS' || echo 'MISSING')"
echo "   - editor.css: $(test -f /workspaces/wpshadow/blocks/sensei-course/editor.css && echo 'EXISTS' || echo 'MISSING')"
echo "   - style.css: $(test -f /workspaces/wpshadow/blocks/sensei-course/style.css && echo 'EXISTS' || echo 'MISSING')"
echo "   - view.js: $(test -f /workspaces/wpshadow/blocks/sensei-course/view.js && echo 'EXISTS' || echo 'MISSING')"
echo ""

# Check CSS files
echo "✅ CSS Files:"
echo "   - sensei-course-block.css: $(test -f /workspaces/wpshadow/assets/css/sensei-course-block.css && echo 'EXISTS' || echo 'MISSING')"
echo "   - safety-warnings.css: $(test -f /workspaces/wpshadow/assets/css/safety-warnings.css && echo 'EXISTS' || echo 'MISSING')"
echo ""

# Check PHP syntax
echo "✅ PHP Syntax Check:"
cd /workspaces/wpshadow
php -l includes/blocks/class-sensei-course-block.php 2>&1 | grep -q "No syntax errors" && echo "   - class-sensei-course-block.php: ✅ VALID" || echo "   - class-sensei-course-block.php: ❌ INVALID"
php -l blocks/sensei-course/render.php 2>&1 | grep -q "No syntax errors" && echo "   - render.php: ✅ VALID" || echo "   - render.php: ❌ INVALID"
php -l wpshadow.php 2>&1 | grep -q "No syntax errors" && echo "   - wpshadow.php: ✅ VALID" || echo "   - wpshadow.php: ❌ INVALID"
echo ""

# Check wpshadow.php integration
echo "✅ Integration Check:"
grep -q "blocks/sensei-course" /workspaces/wpshadow/wpshadow.php && echo "   - Block registration in wpshadow.php: ✅ FOUND" || echo "   - Block registration in wpshadow.php: ❌ NOT FOUND"
grep -q "register_block_type" /workspaces/wpshadow/wpshadow.php && echo "   - register_block_type call: ✅ FOUND" || echo "   - register_block_type call: ❌ NOT FOUND"
grep -q "sensei-course-block" /workspaces/wpshadow/wpshadow.php && echo "   - CSS enqueue: ✅ FOUND" || echo "   - CSS enqueue: ❌ NOT FOUND"
echo ""

# Check Sensei course exists
echo "✅ Sensei Course Database Check:"
docker exec wpshadow-test curl -s "http://localhost/index.php?rest_route=/wp/v2/course" 2>/dev/null | grep -q '"id":227' && echo "   - Course ID 227: ✅ EXISTS" || echo "   - Course ID 227: ⚠️  CHECK MANUALLY"
echo ""

echo "=========================================================="
echo "Installation verification complete!"
echo ""
echo "Next steps:"
echo "1. Visit http://localhost:9000/wp-admin/post-new.php?post_type=post"
echo "2. Search for 'WPShadow Sensei Course' in the block inserter"
echo "3. Add the block and select 'Plugin Management Essentials' course"
echo "4. Preview the block on the frontend"
