import { test, expect } from '@playwright/test';

/**
 * Smoke check for the Add Issue form: every field accepts input, dropdown
 * options are laid out and selectable, and the page logs no errors. It never
 * submits, so the local database is left unchanged.
 *
 * Uses the first project on /projects; set E2E_ISSUE_FORM_PATH to check a
 * different form, e.g. /project/1/issue/create.
 */
test('add issue form accepts input in every field', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(message.text());
        }
    });

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
    if (process.env.E2E_ISSUE_FORM_PATH) {
        return process.env.E2E_ISSUE_FORM_PATH;
    }

    await page.goto('/projects');
    const projectUrl = await page.locator('a[href*="/project/"]').evaluateAll((links) => links
        .map((link) => link.href)
        .find((href) => /\/project\/\d+$/.test(href)));
    expect(projectUrl, 'a project link on /projects').toBeTruthy();

    return `${new URL(projectUrl).pathname}/issue/create`;
}

function field(form, label) {
    return form.locator('[data-flux-field]').filter({
        has: form.page().locator(':scope > [data-flux-label]', { hasText: new RegExp(`^\\s*${label}`) }),
    });
}

/**
 * Opens a combobox, checks its options render properly, and picks the first.
 */
async function chooseFirstOption(selectField) {
    const input = selectField.getByRole('combobox');
    await input.click();
    const options = selectField.locator('ui-option:visible');
    await expect(options.first()).toBeVisible();

    const layout = await options.evaluateAll((elements) => elements.slice(0, 10).map((option) => ({
        text: option.textContent.trim().replace(/\s+/g, ' '),
        squeezed: [option, ...option.querySelectorAll('*')].some((element) => element instanceof HTMLElement
            && getComputedStyle(element).display !== 'inline'
            && element.scrollWidth > element.clientWidth + 1),
    })));
    const description = (await selectField.locator(':scope > [data-flux-description]').textContent())?.trim().replace(/\s+/g, ' ');

    for (const option of layout) {
        expect(option.squeezed, `"${option.text}" fits its option box`).toBe(false);
        if (description) {
            expect(option.text, 'option text excludes the field description').not.toContain(description);
        }
    }

    await options.first().click();
    await expect(input).toHaveValue(layout[0].text);

    return { input, value: layout[0].text };
}
