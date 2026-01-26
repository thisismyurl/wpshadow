# GitHub Actions E2E Testing - Quick Reference

## 🔐 Step 1: Add Secrets (Required First!)

Go to: **Repository → Settings → Secrets and variables → Actions**

Add these 3 secrets:

| Name | Value |
|------|-------|
| `WP_BASE_URL` | Your WordPress URL (e.g., `https://staging.wpshadow.com`) |
| `WP_ADMIN_USER` | WordPress admin username (e.g., `admin`) |
| `WP_ADMIN_PASS` | WordPress admin password |

## 🚀 Step 2: Push Code to GitHub

```bash
cd /workspaces/wpshadow
git add .github/ tests/e2e/ playwright.config.js package.json
git commit -m "Add automated E2E testing with GitHub Actions"
git push origin main
```

## 📊 Step 3: Watch Tests Run

1. Go to **Actions** tab in GitHub
2. See "E2E Tests" workflow running
3. Click on it to watch live
4. Wait ~2-5 minutes for completion

## 📥 Step 4: View Results

**In GitHub:**
- Scroll to bottom of workflow run
- See **Artifacts** section
- Download **"playwright-report"**

**On Your Computer:**
```bash
unzip playwright-report.zip
open index.html  # Opens in browser
```

**You'll see:**
- ✅ Pass/fail for all 31 tests
- 📸 Screenshots of failures
- 🎥 Videos of failed tests
- 📊 Detailed test execution logs

## 🎯 What Gets Tested Automatically

Every time you push code, GitHub Actions will:

1. ✅ **Plugin Activation** (4 tests)
   - Verify plugin appears
   - Test activation
   - Check menu items

2. ✅ **Dashboard** (6 tests)
   - Page loads correctly
   - Health summary displays
   - No JavaScript errors

3. ✅ **Diagnostics** (4 tests)
   - Scan button works
   - Findings display
   - KB links present

4. ✅ **Treatments** (4 tests)
   - Apply button works
   - Confirmation modal shows
   - Success messages appear

5. ✅ **Kanban Board** (6 tests)
   - Drag and drop works
   - Cards persist
   - Columns display

6. ✅ **Workflow Builder** (7 tests)
   - Wizard opens
   - Triggers select
   - Save functionality

**Total: 31 automated tests** checking buttons, modals, AJAX, and UI interactions!

## 🔄 When Tests Run

**Automatically:**
- ✅ Every push to `main` or `develop`
- ✅ Every pull request
- ✅ Daily at 2 AM UTC (scheduled check)

**Manually:**
- Actions tab → E2E Tests → Run workflow button

## ⚠️ If Tests Fail

1. **Download the report** (Artifacts section)
2. **Open index.html** to see details
3. **Check screenshots** of what failed
4. **Watch videos** of the failure
5. **Fix the issue**
6. **Push again** - tests re-run automatically

## 💡 Pro Tips

**Test Faster:**
```bash
# Run locally before pushing
npm run test:e2e:ui  # Interactive mode
```

**Debug Failed Tests:**
- Download artifact
- Look at screenshots
- Watch failure videos
- Check console logs

**Customize Schedule:**
Edit `.github/workflows/e2e-tests-scheduled.yml`:
```yaml
schedule:
  - cron: '0 */6 * * *'  # Every 6 hours
  - cron: '0 9 * * 1'    # Every Monday at 9 AM
```

**Add Slack Notifications:**
Add to workflow:
```yaml
- name: Notify Slack
  if: failure()
  uses: slackapi/slack-github-action@v1
  with:
    webhook-url: ${{ secrets.SLACK_WEBHOOK }}
```

## 🎉 That's It!

Once you add the secrets and push:
- ✅ Tests run automatically on every commit
- ✅ Every PR gets tested before merge
- ✅ Daily health checks ensure stability
- ✅ Detailed reports for debugging

**No more manual testing of buttons and modals!** 🚀

---

**Full documentation:** [GITHUB_ACTIONS_SETUP.md](.github/GITHUB_ACTIONS_SETUP.md)
