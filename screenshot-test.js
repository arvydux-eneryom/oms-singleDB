import puppeteer from 'puppeteer';

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();

  // Set viewport size
  await page.setViewport({ width: 1280, height: 720 });

  // Navigate to a website
  await page.goto('https://example.com', { waitUntil: 'networkidle2' });

  // Take screenshot
  await page.screenshot({ path: 'screenshot-test.png' });

  console.log('Screenshot saved as screenshot-test.png');

  await browser.close();
})();
