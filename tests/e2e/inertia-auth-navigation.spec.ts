import { expect, test } from '@playwright/test';

test('user can register and access core Inertia pages', async ({ page }) => {
    const email = `e2e-${Date.now()}@example.com`;

    await page.goto('/register');

    await page.getByLabel('Name').fill('E2E User');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password', { exact: true }).fill('password');
    await page.getByLabel('Confirm Password').fill('password');

    await page.getByRole('button', { name: 'Register' }).click();

    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();

    await page.getByRole('link', { name: 'Family Tree' }).click();
    await expect(page).toHaveURL(/\/family-tree$/);
    await expect(page.getByRole('heading', { name: 'Family Tree' })).toBeVisible();

    await page.getByRole('link', { name: 'Profile Management' }).click();
    await expect(page).toHaveURL(/\/profile-management$/);
    await expect(page.getByRole('heading', { name: 'Profile Management' })).toBeVisible();
});
