// Placeholder server so the app deploys and responds until the real site is built.
const http = require('http');

const port = process.env.PORT || 3000;

const page = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lodgingeo</title>
  <style>
    body { margin: 0; min-height: 100vh; display: grid; place-items: center;
           font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; }
    main { text-align: center; padding: 16px; }
    p { color: #94a3b8; }
  </style>
</head>
<body>
  <main>
    <h1>Lodgingeo</h1>
    <p>Hotel data, coming soon.</p>
  </main>
</body>
</html>`;

http
  .createServer((req, res) => {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(page);
  })
  .listen(port, () => {
    console.log(`Lodgingeo listening on port ${port}`);
  });
