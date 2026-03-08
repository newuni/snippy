import { test, expect } from '@playwright/test';

test('draft autosaves, publishes, and appears on explore', async ({ page }) => {
  await page.goto('/new');
  await expect(page).toHaveURL(/\/manage\//);

  const unique = Date.now();
  const title = `Release ${unique}`;
  const markdown = `# ${title}\n\nShipped from e2e test.`;

  await page.getByLabel('Title').fill(title);
  await page.getByLabel('Tags').fill('release,e2e');
  await page.getByLabel('Markdown').fill(markdown);

  await expect(page.getByText(/Saving|Saved/)).toBeVisible();
  await expect(page.locator('[data-preview] h1')).toHaveText(title);

  await page.getByRole('button', { name: /Publish/ }).first().click();
  await expect(page.getByText('Post published.')).toBeVisible();

  const publicLink = await page.locator('#public-link').inputValue();
  await expect(publicLink).toContain('/p/');

  await page.goto('/explore?tag=release');
  await expect(page.getByRole('link', { name: title })).toBeVisible();

  await page.goto(publicLink);
  await expect(page.getByRole('heading', { name: title }).first()).toBeVisible();
  await expect(page.getByText('Shipped from e2e test.')).toBeVisible();
});
