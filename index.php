<?php
session_start();

function generateKey() {
  return bin2hex(random_bytes(16));
}

if (isset($_POST['action']) && $_POST['action'] === 'create') {
  $ip = $_SERVER['REMOTE_ADDR'];
  $now = time();

  if (isset($_SESSION['keys'][$ip]) && $_SESSION['keys'][$ip]['expiration'] > $now) {
    echo json_encode(['message' => 'You can only create one key every 4 hours.']);
    exit;
  }

  $_SESSION['keys'][$ip] = [
    'key' => generateKey(),
    'expiration' => $now + 4 * 60 * 60
  ];

  echo json_encode([
    'key' => $_SESSION['keys'][$ip]['key'],
    'ip' => $ip,
    'expiration' => date('Y-m-d H:i:s', $_SESSION['keys'][$ip]['expiration'])
  ]);
  exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get-key') {
  $ip = $_SERVER['REMOTE_ADDR'];
  $now = time();

  if (isset($_SESSION['keys'][$ip])) {
    $keyData = $_SESSION['keys'][$ip];
    echo json_encode([
      'key' => $keyData['key'],
      'ip' => $ip,
      'expiration' => date('Y-m-d H:i:s', $keyData['expiration'])
    ]);
  } else {
    echo json_encode(['message' => 'No key found.']);
  }
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Key Generator</title>
  <script>
    async function createKey() {
      const response = await fetch('/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'create' })
      });
      const data = await response.json();
      if (response.ok) {
        document.getElementById('keyDisplay').innerText = `Your Key: ${data.key}\nYour IP: ${data.ip}\nExpires: ${data.expiration}`;
      } else {
        document.getElementById('keyDisplay').innerText = data.message;
      }
    }

    async function displayKey() {
      const response = await fetch('/?action=get-key');
      const data = await response.json();
      if (response.ok) {
        document.getElementById('keyDisplay').innerText = `Your Key: ${data.key}`;
      } else {
        document.getElementById('keyDisplay').innerText = data.message;
      }
    }

    window.onload = displayKey;
  </script>
</head>
<body>
  <h1>Key Generator</h1>
  <form method="POST" action="" onsubmit="event.preventDefault(); createKey();">
    <button type="submit">Create a Key</button>
  </form>
  <pre id="keyDisplay"></pre>
</body>
</html>
