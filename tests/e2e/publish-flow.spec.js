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
  await expect(page.locator('.markdown-body').getByText('Shipped from e2e test.', { exact: true })).toBeVisible();

  const copyArticleButton = page.locator('[data-copy-target="article-markdown"]');
  await copyArticleButton.click();
  await expect(copyArticleButton).toHaveText('Copied');
  const copiedMarkdown = await page.evaluate(() => navigator.clipboard.readText());
  expect(copiedMarkdown).toBe(markdown);

  const copyBodyButton = page.locator('[data-copy-target="article-body"]');
  await copyBodyButton.click();
  await expect(copyBodyButton).toHaveText('Copied');
  const copiedBody = await page.evaluate(() => navigator.clipboard.readText());
  expect(copiedBody).toBe(`${title}\n\nShipped from e2e test.`);
});
