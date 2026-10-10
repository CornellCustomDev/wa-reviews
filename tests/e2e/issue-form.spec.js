import { test, expect } from '@playwright/test';
import { chooseFirstOption, collectBrowserErrors, field, firstLinkPath } from './helpers.js';

/**
 * Smoke check for the Add Issue form: every field accepts input, dropdown
 * options are laid out and selectable, and the page logs no errors. It never
 * submits, so the local database is left unchanged.
 *
 * Uses the first project on /projects.
 */
test('add issue form accepts input in every field', async ({ page }) => {
    const errors = collectBrowserErrors(page);

    // Locally, /login signs in as the REMOTE_USER override.
    await page.goto('/login');
    await page.goto(await issueFormPath(page));
    const form = page.locator('form[wire\\:submit="save"]');
    await expect(form).toBeVisible();

    const scope = field(form, 'Scope');
    if (await scope.count()) {
        await chooseFirstOption(scope);
        await page.waitForLoadState('networkidle');
    }

    const target = field(form, 'Target').getByRole('textbox');
    await target.fill('Main navigation menu');
    await field(form, 'Description').locator('[contenteditable="true"]').fill('Menu items cannot be reached by keyboard.');
    const guideline = await chooseFirstOption(field(form, 'Guideline'));

    const warning = form.getByRole('radiogroup', { name: 'Assessment' }).getByRole('radio', { name: 'Warning' });
    await warning.check();
    const moderate = form.getByRole('radiogroup', { name: 'User impact level' }).getByRole('radio', { name: 'Moderate' });
    await moderate.check();

    await field(form, 'Recommendations').locator('[contenteditable="true"]').fill('Make each menu item focusable.');
    const testingMethod = await chooseFirstOption(field(form, 'Testing method'));
    await form.getByRole('checkbox', { name: 'Content entry issue' }).check();
    await page.waitForLoadState('networkidle');

    // Values must survive any Livewire round trips the inputs triggered.
    await expect(target).toHaveValue('Main navigation menu');
    await expect(guideline.input).toHaveValue(guideline.value);
    await expect(testingMethod.input).toHaveValue(testingMethod.value);
    await expect(warning).toBeChecked();
    await expect(moderate).toBeChecked();
    await expect(form.getByRole('button', { name: 'Add Issue' })).toBeEnabled();

    expect(errors, 'browser errors').toEqual([]);
});

async function issueFormPath(page) {
    return `${await firstLinkPath(page, '/projects', /\/project\/\d+$/)}/issue/create`;
}
