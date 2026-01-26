# Playwright E2E Testing - Quick Start

## ✅ Setup Complete!

Playwright has been installed and configured for WPShadow E2E testing.

## 📁 What Was Created

```
/workspaces/wpshadow/
├── playwright.config.js                    # Playwright configuration
├── package.json                            # Updated with test scripts
└── tests/e2e/
    ├── helpers/
    │   ├── global-setup.js                 # Pre-test WordPress check
    │   └── wordpress-helpers.js            # Login, navigation, AJAX helpers
    ├── 01-plugin-activation.spec.js        # Plugin activation tests
    ├── 02-dashboard.spec.js                # Dashboard UI tests
    ├── 03-diagnostics.spec.js              # Diagnostic scanning tests
    ├── 04-treatments.spec.js               # Treatment application tests
    ├── 05-kanban-board.spec.js             # Kanban drag-and-drop tests
    ├── 06-workflow-builder.spec.js         # Workflow wizard tests
    ├── .env.example                        # Environment variables template
    └── README.md                           # Full documentation
```

## 🚀 Quick Usage

### 1. Configure WordPress Connection

```bash
cd /workspaces/wpshadow
cp tests/e2e/.env.example tests/e2e/.env
```

Edit `tests/e2e/.env`:
```env
WP_BASE_URL=http://your-wordpress-site.com
WP_ADMIN_USER=admin
WP_ADMIN_PASS=password
```

### 2. Run Tests

**Option A: All Tests**
```bash
npm run test:e2e
```

**Option B: Watch Tests Run (Headed Mode)**
```bash
npm run test:e2e:headed
```

**Option C: Interactive UI (Best for debugging)**
```bash
npm run test:e2e:ui
```

**Option D: Step-by-Step Debug**
```bash
npm run test:e2e:debug
```

### 3. View Results

After tests run:
```bash
npm run test:e2e:report
```

Opens HTML report in browser with:
- ✅ Pass/Fail status
- 📸 Screenshots of failures
- 🎥 Videos of failed tests
- 🌐 Network requests
- 📝 Console logs

## 🎯 What Gets Tested

### Plugin Activation (4 tests)
- Plugin appears in plugins page
- Activates successfully
- Adds admin menu
- Creates submenu items

### Dashboard (6 tests)
- Page loads correctly
- Health summary displays
- Scan button exists
- Stats/KPIs visible
- No JavaScript errors
- Accessible navigation

### Diagnostics (4 tests)
- Quick scan executes
- Finding details display
- Auto-fix buttons appear
- KB links present

### Treatments (4 tests)
- Confirmation modal shows
- Treatment applies successfully
- Undo button appears
- Error handling works

### Kanban Board (6 tests)
- Board loads
- Columns display
- Cards render
- Drag-and-drop works
- Position persists

### Workflow Builder (7 tests)
- Builder loads
- Create button works
- Workflows list
- Wizard opens
- Trigger selection
- Action selection
- Save workflow

## 💡 Example Test Run

```bash
# Run just the dashboard tests
npx playwright test 02-dashboard --headed

# Run with slower speed to watch
npx playwright test --headed --slow-mo 500

# Run specific test by name
npx playwright test --grep "should run quick scan"

# Generate trace for debugging
npx playwright test --trace on
```

## 🐛 Debugging Tips

**See browser during test:**
```bash
npm run test:e2e:headed
```

**Interactive mode (best for writing tests):**
```bash
npm run test:e2e:ui
```

**Pause test at any point:**
```javascript
await page.pause(); // Debugger opens here
```

**Take screenshot for inspection:**
```javascript
await page.screenshot({ path: 'debug.png', fullPage: true });
```

**Console output during test:**
```javascript
console.log(await page.textContent('.finding-title'));
```

## 📝 Available Helper Functions

```javascript
const { 
  loginToWordPress,      // Login to wp-admin
  navigateToWPShadow,    // Go to WPShadow page
  waitForAjaxAction,     // Wait for AJAX call
  waitForNotice,         // Wait for success/error message
  isWPShadowActive,      // Check if plugin active
  activateWPShadow,      // Activate the plugin
  takeScreenshot,        // Save screenshot
} = require('./helpers/wordpress-helpers');
```

## 🔧 Common Issues

### "Cannot reach WordPress site"
- Make sure WordPress is running
- Check WP_BASE_URL in .env file
- Try: `curl http://localhost:9000/wp-admin/`

### "Login failed"
- Verify WP_ADMIN_USER and WP_ADMIN_PASS
- Try logging in manually first

### Tests are slow
- Tests run sequentially to avoid WordPress conflicts
- Use `--headed` to watch progress
- Normal: 30-60 seconds for all tests

### Element not found
- Selectors may need adjustment for your theme/setup
- Use `--debug` mode to inspect elements
- Update selectors in test files

## 📚 Next Steps

1. **Run your first test:**
   ```bash
   npm run test:e2e:headed
   ```

2. **Customize for your setup:**
   - Update selectors in test files
   - Add more test cases
   - Adjust timeouts if needed

3. **Add to CI/CD:**
   - See `tests/e2e/README.md` for GitHub Actions example
   - Run on every PR
   - Catch issues before deployment

4. **Write custom tests:**
   - Copy existing test file
   - Use helper functions
   - Follow Playwright docs: https://playwright.dev

## 🎉 You're Ready!

Your E2E testing framework is fully set up. Run your first test:

```bash
npm run test:e2e:ui
```

This opens an interactive UI where you can:
- ✅ Run tests with one click
- 👀 Watch tests execute
- 🔍 Inspect each step
- 🐛 Debug failures

Happy testing! 🚀
