<?php
// Update if you change your Vercel project domain
$vercelApiBase = 'https://protego-test.vercel.app/api/render?url=';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $target = trim($_POST['url']);
    if (!preg_match('/^https?:\\/\\//i', $target)) {
        $target = 'http://' . $target;
    }

    if (!filter_var($target, FILTER_VALIDATE_URL)) {
        $error = "Invalid URL format.";
    } else {
        $vercelApi = $vercelApiBase . urlencode($target);
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: PHP-Proxy\r\n",
                "timeout" => 60
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($vercelApi, false, $context);
        if ($response === false) {
            $error = "Error fetching rendered page from Vercel API.";
        } else {
            $rendered = $response;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Protego Test - Puppeteer via Vercel</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 1000px; margin: auto; }
    input[type="text"] { width: 70%; padding: 8px; }
    button { padding: 8px 12px; }
    .error { color: #b00020; }
    .frame { border: 1px solid #ddd; padding: 12px; margin-top: 16px; background:#fff; }
  </style>
</head>
<body>
  <h2>Protego Test: Enter a URL to Render via Vercel API</h2>

  <form method="POST" style="margin-bottom:12px">
    <input type="text" name="url" placeholder="https://example.com" required value="<?php echo isset($_POST['url'])?htmlspecialchars($_POST['url']):''; ?>">
    <button type="submit">Render</button>
  </form>

  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($rendered)): ?>
    <h3>Rendered Output of: <?php echo htmlspecialchars($target); ?></h3>
    <div class="frame"><?php echo $rendered; ?></div>
  <?php endif; ?>
</body>
</html>