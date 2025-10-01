import puppeteer from 'puppeteer';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const screenshotsDir = join(__dirname, '../docs/user-guides/images/screenshots');

// Create screenshots directory if it doesn't exist
import { mkdirSync } from 'fs';
try {
    mkdirSync(screenshotsDir, { recursive: true });
} catch (err) {
    if (err.code !== 'EEXIST') throw err;
}

const screenshots = [
    {
        name: '1-central-dashboard',
        url: 'http://localhost:8000/dashboard',
        description: 'Central Application Dashboard',
        waitFor: 'body',
        viewport: { width: 1920, height: 1080 }
    },
    {
        name: '2-subdomains-list',
        url: 'http://localhost:8000/subdomains',
        description: 'Subdomains List View',
        waitFor: 'body',
        viewport: { width: 1920, height: 1080 }
    },
    {
        name: '3-create-subdomain',
        url: 'http://localhost:8000/subdomains/create',
        description: 'Create New Subdomain Form',
        waitFor: 'form',
        viewport: { width: 1920, height: 1080 }
    },
    {
        name: '4-tenant-login',
        url: 'http://localhost:8000/login',
        description: 'Tenant Login Page',
        waitFor: 'form',
        viewport: { width: 1920, height: 1080 }
    }
];

async function login(page) {
    console.log('🔐 Logging in...\n');

    // Navigate to login page
    await page.goto('http://localhost:8000/login', {
        waitUntil: 'networkidle0',
        timeout: 10000
    });

    // Fill in login form with seeded super admin credentials
    await page.type('input[name="email"]', 'super-admin@example.com');
    await page.type('input[name="password"]', 'password');

    // Click login button
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle0' }),
        page.click('button[type="submit"]')
    ]);

    console.log('   ✅ Logged in successfully\n');
}

async function takeScreenshots() {
    console.log('🚀 Starting screenshot capture...\n');

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();

        // Login first before taking screenshots
        try {
            await login(page);
        } catch (error) {
            console.error('❌ Login failed:', error.message);
            console.error('Please make sure:');
            console.error('  1. The application is running on http://localhost:8000');
            console.error('  2. You have run the database seeders (php artisan db:seed)');
            console.error('  3. The super-admin@example.com user exists with password: password\n');
            return;
        }

        for (const screenshot of screenshots) {
            console.log(`📸 Capturing: ${screenshot.description}`);
            console.log(`   URL: ${screenshot.url}`);

            // Set viewport
            await page.setViewport(screenshot.viewport);

            try {
                // Navigate to URL
                await page.goto(screenshot.url, {
                    waitUntil: 'networkidle0',
                    timeout: 10000
                });

                // Wait for specific element if specified
                if (screenshot.waitFor) {
                    await page.waitForSelector(screenshot.waitFor, { timeout: 5000 });
                }

                // Take screenshot
                const filepath = join(screenshotsDir, `${screenshot.name}.png`);
                await page.screenshot({
                    path: filepath,
                    fullPage: false
                });

                console.log(`   ✅ Saved: ${filepath}\n`);
            } catch (error) {
                console.error(`   ❌ Failed to capture ${screenshot.name}:`);
                console.error(`   ${error.message}\n`);
            }
        }

        console.log('✨ Screenshot capture complete!');
    } finally {
        await browser.close();
    }
}

takeScreenshots().catch(error => {
    console.error('💥 Error:', error);
    process.exit(1);
});
