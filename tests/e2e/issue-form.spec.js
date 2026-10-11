import { fileURLToPath } from 'node:url';
import { test, expect } from '@playwright/test';
import { chooseFirstOption, chooseOptionByTyping, collectBrowserErrors, field, firstLinkPath, refreshLivewire } from './helpers.js';

/**
 * Smoke check for the Add Issue form: every field accepts input, dropdown
 * options are laid out and selectable, and the page logs no errors. It never
 * submits, so the local database is left unchanged; the image upload only
 * lands in Livewire's temporary upload directory, which Livewire cleans up.
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
    const description = field(form, 'Description').locator('[contenteditable="true"]');
    await description.fill('Menu items cannot be reached by keyboard.');

    // #174: the guideline list must open, and typing a guideline number must filter it.
    const guidelineField = field(form, 'Guideline');
    await chooseFirstOption(guidelineField);
    const guideline = await chooseOptionByTyping(guidelineField, '14.');

    const warning = form.getByRole('radiogroup', { name: 'Assessment' }).getByRole('radio', { name: 'Warning' });
    await warning.check();
    const moderate = form.getByRole('radiogroup', { name: 'User impact level' }).getByRole('radio', { name: 'Moderate' });
    await moderate.check();

    const recommendations = field(form, 'Recommendations').locator('[contenteditable="true"]');
    await recommendations.fill('Make each menu item focusable.');
    const testingMethod = await chooseFirstOption(field(form, 'Testing method'));
    const contentIssue = form.getByRole('checkbox', { name: 'Content entry issue' });
    await contentIssue.check();

    // Each upload adds to the previews; the file input's label counts them all.
    const imageInput = form.getByLabel('Image examples');
    const imageLabel = form.locator('[x-ref="name"]');
    const previews = form.getByRole('img', { name: /^Preview of image: / });
    await imageInput.setInputFiles(imageFixture('unit_signature_sample.png'));
    await expect(previews).toHaveCount(1);
    await expect(imageLabel).toHaveText('1 file');
    await imageInput.setInputFiles(imageFixture('cuseal_full_white480_dimmed.png'));
    await expect(previews).toHaveCount(2);
    await expect(imageLabel).toHaveText('2 files');
    for (const preview of await previews.all()) {
        await expect.poll(() => preview.evaluate((image) => image.complete && image.naturalWidth), 'preview image loads').toBeGreaterThan(0);
    }

    await refreshLivewire(form);

    // Values must survive a Livewire round trip.
    await expect(target).toHaveValue('Main navigation menu');
    await expect(description).toHaveText('Menu items cannot be reached by keyboard.');
    await expect(guideline.input).toHaveValue(guideline.value);
    await expect(testingMethod.input).toHaveValue(testingMethod.value);
    await expect(warning).toBeChecked();
    await expect(moderate).toBeChecked();
    await expect(recommendations).toHaveText('Make each menu item focusable.');
    await expect(contentIssue).toBeChecked();
    await expect(previews).toHaveCount(2);
    await expect(imageLabel).toHaveText('2 files');
    await expect(form.getByRole('button', { name: 'Add Issue' })).toBeEnabled();

    await form.getByRole('button', { name: 'Remove unit_signature_sample.png' }).click();
    await expect(previews).toHaveCount(1);
    await expect(imageLabel).toHaveText('1 file');
    await form.getByRole('button', { name: 'Remove cuseal_full_white480_dimmed.png' }).click();
    await expect(previews).toHaveCount(0);
    await expect(imageLabel).toHaveText('No file chosen');

    expect(errors, 'browser errors').toEqual([]);
});

function imageFixture(name) {
    return fileURLToPath(new URL(`../../public/cwd-framework/images/cornell/${name}`, import.meta.url));
}

async function issueFormPath(page) {
    return `${await firstLinkPath(page, '/projects', /\/project\/\d+$/)}/issue/create`;
}
