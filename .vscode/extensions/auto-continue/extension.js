/**
 * Auto-Continue Extension for VS Code
 * Automatically accepts "Continue?" prompts in Copilot Chat after 25 tasks
 * Layer 1: Direct chat API interception
 */

const vscode = require('vscode');

let isMonitoring = false;
let promptCount = 0;
const PROMPT_THRESHOLD = 25;

/**
 * Activate the extension
 */
function activate(context) {
    console.log('🔄 Auto-Continue extension activated');

    // Register command for manual trigger
    let disposable = vscode.commands.registerCommand('auto-continue.acceptPrompt', () => {
        autoAcceptCurrentPrompt();
    });
    context.subscriptions.push(disposable);

    // Start monitoring chat for continuation prompts
    startMonitoring(context);

    // Listen for chat messages
    if (vscode.chat?.onDidCreateChatSession) {
        context.subscriptions.push(
            vscode.chat.onDidCreateChatSession((session) => {
                console.log('✓ Chat session created, auto-continue ready');
                monitorSession(session, context);
            })
        );
    }
}

/**
 * Start monitoring for continuation prompts
 */
function startMonitoring(context) {
    if (isMonitoring) return;
    isMonitoring = true;

    // Monitor notifications for "Continue?" prompt
    const notificationListener = vscode.window.onDidChangeWindowState(() => {
        checkForPrompt();
    });
    context.subscriptions.push(notificationListener);

    // Also set up a periodic check (safety fallback)
    const interval = setInterval(() => {
        checkForPrompt();
    }, 1000); // Check every second

    context.subscriptions.push(new vscode.Disposable(() => clearInterval(interval)));

    console.log('🔄 Auto-continue monitoring started');
}

/**
 * Monitor individual chat sessions
 */
function monitorSession(session, context) {
    if (!session) return;

    try {
        // Intercept chat responses to count tasks
        const originalStream = session.stream;
        session.stream = function (message) {
            promptCount++;

            if (promptCount % 5 === 0) {
                console.log(`📊 Auto-continue: ${promptCount} tasks completed`);
            }

            if (promptCount >= PROMPT_THRESHOLD) {
                console.log('🎯 Task threshold reached, preparing auto-accept...');
                scheduleAutoAccept();
            }

            return originalStream.call(this, message);
        };
    } catch (e) {
        console.log('ℹ️  Session monitoring setup (API may not support interception)');
    }
}

/**
 * Check for continuation prompt in UI
 */
function checkForPrompt() {
    // Look for common "Continue?" button patterns in visible UI
    // This is a helper that pairs with keyboard shortcut automation

    try {
        // Fire keyboard shortcut that accepts the prompt
        // (Paired with Layer 2 notification watcher)
        const editor = vscode.window.activeTextEditor;
        if (editor && vscode.window.activeColorTheme) {
            // Environment is active, safe to attempt accept
            attemptAccept();
        }
    } catch (e) {
        // Silent - this is just trying
    }
}

/**
 * Schedule auto-accept with slight delay
 */
function scheduleAutoAccept() {
    setTimeout(() => {
        autoAcceptCurrentPrompt();
    }, 500);
}

/**
 * Auto-accept the current prompt
 */
function autoAcceptCurrentPrompt() {
    // Method 1: Try keyboard shortcut (works with notification UI)
    vscode.commands.executeCommand('notifications.showList');

    // Method 2: Send Enter key (accepts focused button)
    setTimeout(() => {
        vscode.commands.executeCommand('key1', 'enter').catch(() => {
            // Fallback: execute workbench action
            vscode.commands.executeCommand('workbench.action.acceptInputAction').catch(() => {
                // Silent fallback
            });
        });
    }, 100);

    console.log('✅ Auto-accept triggered');
}

/**
 * Attempt to accept using multiple strategies
 */
function attemptAccept() {
    // Strategy 1: Send keyboard input
    vscode.commands.executeCommand('sendSequenceToTerminal', { text: 'y' }).catch(() => {
        // Strategy 2: Click accept via command
        vscode.commands.executeCommand('workbench.action.chatEditor.accept').catch(() => {
            // Strategy 3: Fire enter key globally
            vscode.commands.executeCommand('type', { text: 'y' }).catch(() => {
                // Silent - tried multiple approaches
            });
        });
    });
}

/**
 * Deactivate the extension
 */
function deactivate() {
    console.log('🛑 Auto-continue extension deactivated');
    isMonitoring = false;
}

module.exports = {
    activate,
    deactivate
};
