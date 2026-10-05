import { test, expect, Page } from '@playwright/test';

/**
 * The widget is sunset: the order confirm step must not load the Mondu widget
 * SDK, a Mondu order must redirect to the hosted checkout and come back to the
 * thank-you page, and other payment methods must still place orders normally.
 */

const PRODUCT_ID = process.env.E2E_PRODUCT_ID ?? 'd861ad687c60820255dbf8f88516f24d';

// Mondu sandbox authorizes this buyer automatically (company "Mondu GmbH", email ac.good.*).
const BUYER = {
  oxfname: 'Max',
  oxlname: 'Mustermann',
  oxcompany: process.env.E2E_BUYER_COMPANY ?? 'Mondu GmbH',
  oxstreet: 'Strassmannstr.',
  oxstreetnr: '45',
  oxzip: '10122',
  oxcity: 'Berlin',
  oxfon: '+493031196513',
};

async function goToOrderStep(page: Page, paymentId: string) {
  await page.goto(`/index.php?cl=details&anid=${PRODUCT_ID}`);
  await page.click('#toBasket');
  await page.waitForLoadState('networkidle');

  // guest checkout: "Ohne Registrierung bestellen"
  await page.goto('/index.php?cl=user');
  await page.getByRole('button', { name: 'Weiter' }).first().click();
  await page.waitForLoadState('networkidle');

  await page.fill('#userLoginName', `ac.good.${Date.now()}@example.com`);
  for (const [field, value] of Object.entries(BUYER)) {
    await page.fill(`[name="invadr[oxuser__${field}]"]`, value);
  }
  // the country select is wrapped by bootstrap-select, set the native value
  await page.$eval('[name="invadr[oxuser__oxcountryid]"]', (select: HTMLSelectElement) => {
    const germany = [...select.options].find(o => o.text.trim() === 'Deutschland');
    select.value = germany!.value;
    select.dispatchEvent(new Event('change', { bubbles: true }));
  });
  await page.click('#userNextStepBottom');
  await page.waitForURL(/cl=payment/);

  await page.check(`#payment_${paymentId}`);
  await page.click('#paymentNextStepBottom');
  await page.waitForURL(/cl=order/);

  const agb = page.locator('#checkAgbTop, #checkAgbBottom, input[name="ord_agb"]').first();
  if (await agb.isVisible().catch(() => false)) await agb.check();
}

async function submitOrder(page: Page) {
  await page.locator('#orderConfirmAgbBottom button[type="submit"]').click();
}

test('Mondu order goes through hosted checkout without loading the widget SDK', async ({ page }) => {
  const widgetRequests: string[] = [];
  page.on('request', (request) => {
    const url = new URL(request.url());
    if (url.hostname.includes('mondu') && url.pathname.endsWith('/widget.js')) widgetRequests.push(url.href);
  });

  await goToOrderStep(page, 'oxmondu_invoice');

  await expect(page.locator('#mondu-checkout-input')).toHaveCount(1);
  await expect(page.locator('#mondu-checkout-widget')).toHaveCount(0);
  expect(await page.evaluate(() => typeof (window as any).widgetUrl)).toBe('undefined');

  await submitOrder(page);
  await page.waitForURL('**mondu.ai/**', { timeout: 60_000 });

  const confirmButton = page.getByRole('button', { name: /zahlen mit|pay with|bestätigen|confirm|submit/i }).first();
  await confirmButton.waitFor({ state: 'visible', timeout: 30_000 });
  await confirmButton.click({ force: true });

  await page.waitForURL(/cl=thankyou/, { timeout: 90_000 });
  expect(widgetRequests).toEqual([]);
});

test('non-Mondu payment still places the order without Mondu interception', async ({ page }) => {
  const monduCalls: string[] = [];
  page.on('request', (request) => {
    if (request.url().includes('cl=oemonducheckout')) monduCalls.push(request.url());
  });

  await goToOrderStep(page, 'oxidpayadvance');

  await expect(page.locator('#mondu-checkout-input')).toHaveCount(0);

  await submitOrder(page);
  await page.waitForURL(/cl=thankyou/, { timeout: 60_000 });
  expect(monduCalls).toEqual([]);
});
