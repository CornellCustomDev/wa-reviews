import { test, expect } from '@playwright/test';
import { collectBrowserErrors, field, firstLinkPath } from './helpers.js';

/**
 * Smoke check for the Create Project form: every field accepts input, the
 * team dropdown (shown to site admins) is selectable, and the page logs no
 * errors. It never submits, so the local database is left unchanged.
 *
 * Uses the first team on /teams.
 */
test('create project form accepts input in every field', async ({ page }) => {
    const errors = collectBrowserErrors(page);

    // Locally, /login signs in as the REMOTE_USER override.
    await page.goto('/login');
    await page.goto(await projectFormPath(page));
    const form = page.locator('form[wire\\:submit="save"]');
    await expect(form).toBeVisible();

    // Site admins get a native team dropdown; others see the team name only.
    const team = field(form, 'Team').getByRole('combobox');
    const teamChoice = await team.count() ? await team.locator('option:not([disabled])').first().textContent() : null;
    if (teamChoice) {
        await team.selectOption({ label: teamChoice });
    }

    const editors = {
        'Description': 'Entered by the Playwright smoke check.',
        'What is the purpose of the site?': 'Smoke checking the form.',
    };
    const textboxes = {
        'Project Name': 'Smoke check project',
        'Site URL': 'https://example.cornell.edu',
        'Siteimprove Report URL': 'https://my2.siteimprove.com/Accessibility/1/NextGen/Overview',
        'Responsible unit at Cornell': 'Example Unit',
        'Name': 'Pat Example',
        'NetID': 'pe123',
        'Who is the audience?': 'Students, Staff',
    };
    for (const [label, value] of Object.entries(textboxes)) {
        await field(form, label).getByRole('textbox').fill(value);
    }
    for (const [label, value] of Object.entries(editors)) {
        await field(form, label).locator('[contenteditable="true"]').fill(value);
    }
    await page.waitForLoadState('networkidle');

    // Values must survive any Livewire round trips the inputs triggered.
    for (const [label, value] of Object.entries(textboxes)) {
        await expect(field(form, label).getByRole('textbox'), label).toHaveValue(value);
    }
    for (const [label, value] of Object.entries(editors)) {
        await expect(field(form, label).locator('[contenteditable="true"]'), label).toHaveText(value);
    }
    if (teamChoice) {
        await expect(team.locator('option:checked')).toHaveText(teamChoice);
    }
    await expect(field(form, 'Siteimprove ID').getByRole('textbox')).toBeDisabled();
    await expect(form.getByRole('button', { name: 'Create Project' })).toBeEnabled();

    expect(errors, 'browser errors').toEqual([]);
});

async function projectFormPath(page) {
    return `${await firstLinkPath(page, '/teams', /\/teams\/\d+$/)}/project/create`;
}
