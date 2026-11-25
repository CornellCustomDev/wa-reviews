import { defineConfig } from '@playwright/test';
import { loadEnv } from 'vite';

/**
 * Browser checks run against the local site at APP_URL (from .env, or the environment)
 * and sign in through /login with the REMOTE_USER override. They use the installed
 * Google Chrome, so no Playwright browser download is needed.
 */
const env = loadEnv('', process.cwd(), '');

export default defineConfig({
    testDir: './tests/e2e',
    reporter: 'list',
    use: {
        baseURL: env.APP_URL,
        channel: 'chrome',
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
});
