import { test, expect } from '@playwright/test';

test('draft autosaves, publishes, and appears on explore', async ({ page }, testInfo) => {
  await page.goto('/');
  await page.getByRole('button', { name: 'New draft' }).click();
  await expect(page).toHaveURL(/\/manage\//);

  const manageLinkBox = await page.locator('#manage-link').boundingBox();
  const publicLinkBox = await page.locator('#public-link').boundingBox();
  expect(manageLinkBox).not.toBeNull();
  expect(publicLinkBox).not.toBeNull();
  expect(publicLinkBox.y).toBeGreaterThan(manageLinkBox.y + manageLinkBox.height);

  const unique = `${Date.now()}-${testInfo.project.name}`;
  const title = `Release ${unique}`;
  const markdown = `# ${title}\n\nShipped from e2e test.\n\n- Unordered item\n  - Nested unordered item\n- Another unordered item\n\n1. First ordered item\n   1. Nested ordered item\n2. Second ordered item`;

  await page.getByLabel('Title').fill(title);
  await page.getByLabel('Tags').fill('release,e2e');
  await page.getByLabel('Markdown').fill(markdown);

  await expect(page.getByText(/Saving|Saved/)).toBeVisible();
  await expect(page.locator('[data-preview] h1')).toHaveText(title);
  await expect(page.locator('[data-preview] ul').first()).toHaveCSS('list-style-type', 'disc');
  await expect(page.locator('[data-preview] ul ul')).toHaveCSS('list-style-type', 'circle');
  await expect(page.locator('[data-preview] ol').first()).toHaveCSS('list-style-type', 'decimal');
  await expect(page.locator('[data-preview] ol ol')).toHaveCSS('list-style-type', 'lower-alpha');

  await page.getByRole('button', { name: /Publish/ }).first().click();
  await expect(page.getByText('Post published.')).toBeVisible();

  const publicLink = await page.locator('#public-link').inputValue();
  await expect(publicLink).toContain('/p/');

  await page.goto('/explore?tag=release');
  await expect(page.getByRole('link', { name: title })).toBeVisible();

  await page.goto(publicLink);
  await expect(page.getByRole('heading', { name: title }).first()).toBeVisible();
  await expect(page.locator('.markdown-body').getByText('Shipped from e2e test.', { exact: true })).toBeVisible();
  await expect(page.locator('.markdown-body ul').first()).toHaveCSS('list-style-type', 'disc');
  await expect(page.locator('.markdown-body ol').first()).toHaveCSS('list-style-type', 'decimal');

  const copyArticleButton = page.locator('[data-copy-target="article-markdown"]');
  await copyArticleButton.click();
  await expect(copyArticleButton).toHaveText('Copied');
  const copiedMarkdown = await page.evaluate(() => navigator.clipboard.readText());
  expect(copiedMarkdown).toBe(markdown);

  const copyBodyButton = page.locator('[data-copy-target="article-body"]');
  await copyBodyButton.click();
  await expect(copyBodyButton).toHaveText('Copied');
  const copiedBody = await page.evaluate(() => navigator.clipboard.readText());
  expect(copiedBody).toContain(title);
  expect(copiedBody).toContain('Unordered item');
  expect(copiedBody).toContain('First ordered item');
  expect(copiedBody).not.toContain('- Unordered item');
});
