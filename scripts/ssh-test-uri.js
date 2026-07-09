const { Client } = require('ssh2');

const conn = new Client();
const php = `<?php
\\$_SERVER['REQUEST_URI']='/s/ak-salon';
\\$_SERVER['SCRIPT_NAME']='/admin/public/index.php';
require 'domains/easygrox.com/public_html/admin/bootstrap/request.php';
vellor_normalize_request_uri();
echo \\$_SERVER['REQUEST_URI'];
`;

const cmd = [
  'cd domains/easygrox.com/public_html/admin',
  'php -r \'$_SERVER["REQUEST_URI"]="/s/ak-salon"; $_SERVER["SCRIPT_NAME"]="/admin/public/index.php"; require "bootstrap/request.php"; vellor_normalize_request_uri(); echo $_SERVER["REQUEST_URI"], PHP_EOL;\'',
  'php -r \'$_SERVER["REQUEST_URI"]="/admin/s/ak-salon"; $_SERVER["SCRIPT_NAME"]="/admin/public/index.php"; require "bootstrap/request.php"; vellor_normalize_request_uri(); echo $_SERVER["REQUEST_URI"], PHP_EOL;\'',
  'php -r \'$_SERVER["REQUEST_URI"]="/admin/public/index.php"; $_SERVER["SCRIPT_NAME"]="/admin/public/index.php"; $_SERVER["REDIRECT_URL"]="/s/ak-salon"; require "bootstrap/request.php"; vellor_normalize_request_uri(); echo $_SERVER["REQUEST_URI"], PHP_EOL;\'',
].join(' && ');

conn.on('ready', () => {
  conn.exec(cmd, (err, stream) => {
    if (err) { console.error(err); process.exit(1); }
    stream.on('data', (d) => process.stdout.write(d));
    stream.stderr.on('data', (d) => process.stderr.write(d));
    stream.on('close', () => conn.end());
  });
}).connect({
  host: 'srv1317.hstgr.io', port: 65002, username: 'u320650417',
  password: 'qPhWrR$F$%^&H7*&^@',
});
