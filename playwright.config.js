import { defineConfig } from '@playwright/test';

/**
 * Browser checks run against the local Lando site and sign in through
 * /login with the REMOTE_USER override. They use the installed Google Chrome, so no
 * Playwright browser download is needed.
 */
export default defineConfig({
    testDir: './tests/e2e',
    reporter: 'list',
    use: {
        baseURL: 'https://wa-reviews.lndo.site',
        channel: 'chrome',
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
});
