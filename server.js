const express = require('express');
const crypto = require('crypto');
const app = express();
const port = process.env.PORT || 10000; // Use PORT from environment or default to 3000

const keyStorage = {}; // Stores key data indexed by IP

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static('public'));

// Route to create a new key
app.post('/create-key', (req, res) => {
  const ip = req.ip;
  const now = Date.now();

  if (keyStorage[ip] && keyStorage[ip].expiration > now) {
    return res.status(403).json({ message: 'You can only create one key every 4 hours.' });
  }

  const key = crypto.randomBytes(16).toString('hex');
  const expiration = now + 4 * 60 * 60 * 1000; // 4 hours from now

  keyStorage[ip] = { key, expiration };

  res.json({
    key,
    ip,
    expiration: new Date(expiration).toLocaleString()
  });
});

// Route to validate the key
app.get('/mykey/:key', (req, res) => {
  const ip = req.ip;
  const { key } = req.params;
  const now = Date.now();

  if (keyStorage[ip] && keyStorage[ip].key === key && keyStorage[ip].expiration > now) {
    res.send('true');
  } else {
    res.send('false');
  }
});

// Route to display only the key
app.get('/mykey', (req, res) => {
  const ip = req.ip;

  if (keyStorage[ip]) {
    res.send(keyStorage[ip].key);
  } else {
    res.status(404).send('No key found.');
  }
});

app.listen(port, () => {
  console.log(`Server running on http://localhost:${port}`);
});
