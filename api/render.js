import chromium from 'chrome-aws-lambda';
import puppeteer from 'puppeteer-core';

export default async function handler(req, res) {
  const url = req.query.url;
  if (!url) return res.status(400).send('Missing url query parameter');

  // Basic whitelist (optional) — edit or remove as needed
  const allowedHosts = ['example.com', 'quotes.toscrape.com', 'news.ycombinator.com'];
  try {
    const parsed = new URL(url);
    if (!allowedHosts.includes(parsed.hostname) && process.env.NODE_ENV === 'production') {
      return res.status(403).send('Hostname not allowed');
    }
  } catch (e) {
    return res.status(400).send('Invalid URL');
  }

  try {
    const executablePath = await chromium.executablePath;
    const browser = await puppeteer.launch({
      args: chromium.args,
      executablePath: executablePath || undefined,
      headless: chromium.headless,
    });

    const page = await browser.newPage();
    await page.setUserAgent(
      (req.headers['user-agent']) ? req.headers['user-agent'] : 'Mozilla/5.0 (compatible; ProtegoTest/1.0)'
    );
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });

    const content = await page.content();
    await browser.close();

    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.status(200).send(content);
  } catch (err) {
    console.error('Render error:', err);
    res.status(500).send(`Error: ${err.message}`);
  }
}