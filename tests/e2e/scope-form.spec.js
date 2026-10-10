import { test, expect } from '@playwright/test';
import { collectBrowserErrors, field, firstLinkPath } from './helpers.js';

/**
 * Smoke check for the Add Scope form: every field accepts input and the page
 * logs no errors. It never submits, so the local database is left unchanged.
 *
 * Uses the first project on /projects.
 */
test('add scope form accepts input in every field', async ({ page }) => {
    const errors = collectBrowserErrors(page);

    // Locally, /login signs in as the REMOTE_USER override.
    await page.goto('/login');
    await page.goto(await scopeFormPath(page));
    const form = page.locator('form[wire\\:submit="save"]');
    await expect(form).toBeVisible();

    const title = field(form, 'Title').getByRole('textbox');
    const url = field(form, 'URL').getByRole('textbox');
    const notes = field(form, 'Notes').locator('[contenteditable="true"]');
    await title.fill('Smoke check scope');
    await url.fill('https://example.cornell.edu/smoke-check');
    await notes.fill('Entered by the Playwright smoke check.');
    await page.waitForLoadState('networkidle');

    // Values must survive any Livewire round trips the inputs triggered.
    await expect(title).toHaveValue('Smoke check scope');
    await expect(url).toHaveValue('https://example.cornell.edu/smoke-check');
    await expect(notes).toHaveText('Entered by the Playwright smoke check.');
    await expect(form.getByRole('button', { name: 'Add Scope' })).toBeEnabled();

    expect(errors, 'browser errors').toEqual([]);
});

async function scopeFormPath(page) {
    return `${await firstLinkPath(page, '/projects', /\/project\/\d+$/)}/scope/create`;
}
