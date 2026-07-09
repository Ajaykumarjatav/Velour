const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const localFile = path.join('c:', 'xampp', 'htdocs', 'vellor', 'admin', 'bootstrap', 'request.php');
const remoteFile = 'domains/easygrox.com/public_html/admin/bootstrap/request.php';

const conn = new Client();
conn.on('ready', () => {
  conn.sftp((err, sftp) => {
    if (err) { console.error(err); process.exit(1); }
    const rs = fs.createReadStream(localFile);
    const ws = sftp.createWriteStream(remoteFile);
    ws.on('close', () => {
      conn.exec([
        'cd domains/easygrox.com/public_html/admin',
        'php -r \'$_SERVER["REQUEST_URI"]="/admin/public/index.php"; $_SERVER["SCRIPT_NAME"]="/admin/public/index.php"; $_SERVER["REDIRECT_URL"]="/s/ak-salon"; require "bootstrap/request.php"; vellor_normalize_request_uri(); echo $_SERVER["REQUEST_URI"], PHP_EOL;\'',
        'curl -sI https://easygrox.com/s/ak-salon 2>&1 | head -5',
      ].join(' && '), (e, stream) => {
        stream.on('data', (d) => process.stdout.write(d));
        stream.stderr.on('data', (d) => process.stderr.write(d));
        stream.on('close', () => conn.end());
      });
    });
    rs.pipe(ws);
  });
}).on('error', (e) => { console.error(e.message); process.exit(1); })
.connect({ host: 'srv1317.hstgr.io', port: 65002, username: 'u320650417', password: 'qPhWrR$F$%^&H7*&^@' });
