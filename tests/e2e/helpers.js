import { expect } from '@playwright/test';

/**
 * Collects page errors and console errors so a test can assert none occurred.
 */
export function collectBrowserErrors(page) {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    page.on('console', (message) => {
        if (message.type() === 'error') {
            errors.push(message.text());
        }
    });

    return errors;
}

/**
 * Returns the pathname of the first link on `listPath` whose href matches `pattern`.
 */
export async function firstLinkPath(page, listPath, pattern) {
    await page.goto(listPath);
    const url = await page.locator('a[href]').evaluateAll((links, source) => links
        .map((link) => link.href)
        .find((href) => new RegExp(source).test(href)), pattern.source);
    expect(url, `a link matching ${pattern} on ${listPath}`).toBeTruthy();

    return new URL(url).pathname;
}

export function field(form, label) {
    return form.locator('[data-flux-field]').filter({
        has: form.page().locator(':scope > [data-flux-label]', { hasText: new RegExp(`^\\s*${label}`) }),
    });
}

/**
 * Opens a combobox, checks its options render properly, and picks the first.
 */
export async function chooseFirstOption(selectField) {
    const input = selectField.getByRole('combobox');
    await input.click();

    return chooseFirstVisibleOption(selectField, input);
}

/**
 * Types into a combobox, checks the filtered options render properly and
 * contain the search text, and picks the first.
 */
export async function chooseOptionByTyping(selectField, search) {
    const input = selectField.getByRole('combobox');
    await input.clear();
    await input.pressSequentially(search);
    const choice = await chooseFirstVisibleOption(selectField, input);
    expect(choice.value, `option found by typing "${search}"`).toContain(search);

    return choice;
}

/**
 * Sends any deferred wire:model updates and re-renders the form's Livewire
 * component, so assertions afterwards cover a full server round trip.
 */
export async function refreshLivewire(form) {
    const update = form.page().waitForResponse((response) => response.request().method() === 'POST'
        && response.url().includes('livewire'));
    await form.evaluate((element) => {
        const componentId = element.closest('[wire\\:id]').getAttribute('wire:id');

        return window.Livewire.find(componentId).$refresh();
    });
    expect((await update).ok(), 'Livewire update succeeded').toBe(true);
}

async function chooseFirstVisibleOption(selectField, input) {
    const options = selectField.locator('ui-option:visible');
    await expect(options.first()).toBeVisible();

    const layout = await options.evaluateAll((elements) => elements.map((option) => ({
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
