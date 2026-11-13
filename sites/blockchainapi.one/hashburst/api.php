<?php
// public/hashburst/api.php — HashBurst Public API guide
//
// Pagina interamente statica: nessun dato esterno viene renderizzato, quindi
// non c'e' superficie di injection. La CSP con nonce resta per coerenza con
// index.php e mining.php e per non dover riesaminare il file se un domani
// qualcuno ci aggiunge una fetch.
//
// ACCURATEZZA: questa pagina descrive cio' che gli endpoint fanno davvero,
// inclusi i limiti. Ogni endpoint elencato qui deve essere stato verificato
// con verify_api.sh. Se un endpoint non risponde 200, va tolto da qui prima
// che qualcuno ci costruisca sopra.

$nonce = base64_encode(random_bytes(16));
$reviewed = '2026-08-13';

header(
    "Content-Security-Policy: " .
    "default-src 'none'; " .
    "script-src 'nonce-{$nonce}'; " .
    "style-src 'nonce-{$nonce}'; " .
    "base-uri 'none'; " .
    "form-action 'none'; " .
    "frame-ancestors 'none'"
);
header("Referrer-Policy: no-referrer");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>HashBurst — Public API</title>
<style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
  :root{--bg:#0D1117;--surface:#161B22;--surface2:#1C2128;--border:#30363D;
  --blue:#2E86C1;--blue-light:#58A6FF;--green:#3FB950;--yellow:#D29922;
  --red:#F85149;--amber:#D9822B;--text:#E6EDF3;--muted:#8B949E;
  --mono:ui-monospace,'Courier New',monospace}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;line-height:1.6}
  header{background:var(--surface);border-bottom:1px solid var(--border);padding:16px 24px;
  display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
  .brand{font-weight:700}
  nav a{color:var(--muted);text-decoration:none;margin-left:16px;font-size:13px}
  nav a.active{color:var(--blue-light);font-weight:600}
  main{max-width:900px;margin:0 auto;padding:24px}
  h1{font-size:26px;margin-bottom:4px}
  .subtitle{color:var(--muted);font-size:13px;margin-bottom:24px}
  h2{font-size:18px;margin:36px 0 12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
  h3{font-size:14px;margin:24px 0 8px;color:var(--text)}
  p{margin-bottom:12px;font-size:14px}
  ul{margin:0 0 12px 20px;font-size:14px}
  li{margin-bottom:4px}
  a{color:var(--blue-light)}
  code{font-family:var(--mono);font-size:12px;background:var(--surface2);
  padding:1px 5px;border-radius:4px;color:var(--blue-light)}
  .notice{border-radius:10px;padding:14px 18px;font-size:13px;margin-bottom:20px}
  .notice.warn{background:rgba(217,130,43,.12);border:1px solid rgba(217,130,43,.35);color:#E8B47A}
  .notice.info{background:rgba(46,134,193,.10);border:1px solid rgba(46,134,193,.30);color:var(--blue-light)}
  .notice p:last-child{margin-bottom:0}
  .ep{background:var(--surface);border:1px solid var(--border);border-radius:12px;
  padding:18px;margin-bottom:16px}
  .ep-head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px}
  .method{background:rgba(63,185,80,.15);color:var(--green);font-size:11px;font-weight:700;
  padding:2px 8px;border-radius:4px;letter-spacing:.5px}
  .path{font-family:var(--mono);font-size:13px;color:var(--text);word-break:break-all}
  .tag{font-size:10px;padding:2px 8px;border-radius:20px;font-weight:600}
  .tag.public{background:rgba(46,134,193,.15);color:var(--blue-light)}
  .tag.auth{background:rgba(217,130,43,.15);color:var(--amber)}
  .ep p{font-size:13px;color:var(--muted)}
  .ep p.lead{color:var(--text)}
  pre{background:#0A0D12;border:1px solid var(--border);border-radius:8px;padding:12px 14px;
  overflow-x:auto;font-family:var(--mono);font-size:12px;color:var(--text);margin:10px 0;line-height:1.5}
  .pre-wrap{position:relative}
  .copy{position:absolute;top:8px;right:8px;background:var(--surface2);color:var(--muted);
  border:1px solid var(--border);border-radius:6px;font-size:10px;padding:3px 8px;cursor:pointer;
  font-family:inherit}
  .copy:hover{color:var(--text)}
  table{width:100%;border-collapse:collapse;margin:12px 0;font-size:13px}
  th{text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;
  color:var(--muted);border-bottom:1px solid var(--border)}
  td{padding:8px 12px;border-bottom:1px solid var(--border);vertical-align:top}
  td:first-child{font-family:var(--mono);font-size:12px;color:var(--blue-light);white-space:nowrap}
  tr:last-child td{border-bottom:none}
  .caveat{border-left:2px solid var(--amber);padding:2px 0 2px 14px;margin:12px 0;
  font-size:13px;color:#E8B47A}
  footer{border-top:1px solid var(--border);margin-top:48px;padding:20px 0;
  font-size:12px;color:var(--muted)}
</style>
</head>
<body>
<header>
  <div class="brand">HashBurst</div>
  <nav>
    <a href="/hashburst/">Network (P2P/TEP)</a>
    <a href="/hashburst/mining.php">Mining Operations</a>
    <a href="/hashburst/api.php" class="active">API</a>
  </nav>
</header>
<main>

  <h1>HashBurst Public API</h1>
  <div class="subtitle">
    Base URL <code>/</code> · Experimental · Last verified <?= htmlspecialchars($reviewed, ENT_QUOTES, 'UTF-8') ?>
  </div>

  <div class="notice warn">
    <p><strong>Read this before you build on it.</strong> HashBurst is experimental
    software under active development. The HashBurst blockchain and HB-TEP operate
    as a multi-node network, while <code>blockchainapi.one</code> is currently the
    public API gateway. Availability of this hostname therefore depends on that
    gateway even when the underlying peer network remains operational.</p>
    <p>Endpoints, field names and response shapes can change without notice or
    deprecation period. Do not build anything you cannot afford to have break.</p>
    <p>Chain, Blockchain DNS and TEP endpoints expose live information observed by
    this gateway node. Peer counters are runtime values: <code>peers</code> counts
    established blockchain P2P connections, while <code>peers_total</code> in TEP
    counts configured/discovered peer entries and <code>peers_online</code> counts
    peers that have actually answered.</p>
  </div>

  <h2>Conventions</h2>
  <table>
    <tr><td>Transport</td><td>HTTPS only. HTTP is redirected.</td></tr>
    <tr><td>Method</td><td><code>GET</code> for every endpoint below.</td></tr>
    <tr><td>Content type</td><td><code>application/json</code></td></tr>
    <tr><td>Auth</td><td>None, except <code>/api/hashburst/workers/search</code>.</td></tr>
    <tr><td>CORS</td><td><code>Access-Control-Allow-Origin: *</code> on the mining endpoints.</td></tr>
    <tr><td>Timestamps</td><td>Unix epoch seconds, UTC.</td></tr>
    <tr><td>Amounts</td><td>Decimal strings or numbers depending on the upstream source. Parse defensively.</td></tr>
  </table>

  <h3>Rate limits</h3>
  <p>Enforced by the gateway, per client IP. A <code>503</code> is a rate limit, not an
  outage — back off and retry.</p>
  <table>
    <tr><th>Scope</th><th>Limit</th><th>Burst</th><th>On excess</th></tr>
    <tr><td>Most endpoints</td><td>30 req/min</td><td>20</td><td><code>503</code></td></tr>
    <tr><td>/workers/search</td><td>10 req/min</td><td>5</td><td><code>503</code></td></tr>
  </table>
  <p><code>/workers/search</code> additionally allows 20 req/min per authenticated apikey
  (<code>429</code>), and blocks an IP after 10 failed authentication attempts in 5 minutes
  (<code>429</code>).</p>

  <h3>Errors</h3>
  <p>JSON, with an <code>error</code> field.</p>
  <table>
    <tr><td>400</td><td>Malformed request</td></tr>
    <tr><td>401</td><td>Missing authentication headers</td></tr>
    <tr><td>403</td><td>Invalid apikey or signature</td></tr>
    <tr><td>429</td><td>Rate limit, application level</td></tr>
    <tr><td>503</td><td>Rate limit, gateway level</td></tr>
  </table>

  <h2>Chain endpoints</h2>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/health</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Node liveness and chain position.</p>
    <p>Fields: <code>blockHeight</code>, <code>chainId</code>, <code>peerID</code>, <code>node</code>.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/health"</pre></div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/status</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Node status, including connected P2P peers.</p>
    <p>Fields: <code>peers</code>, <code>peerID</code>.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/status"</pre></div>
    <div class="caveat"><code>peers</code> counts established blockchain P2P
    connections observed by this gateway node. The value is live and may change as
    peers connect or disconnect.</div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/blocks</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Recent blocks, oldest first. Returns an array.</p>
    <p>Per element: <code>number</code>, <code>hash</code>, <code>parentHash</code>,
    <code>transactions</code> (count), <code>timestamp</code>, <code>miner</code>.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/blocks" | python3 -m json.tool</pre></div>
    <div class="caveat">Returns a recent window, not the full chain, and takes no
    pagination parameters. There is no way to page back through history.</div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/transactions</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Recent transactions. Returns an array.</p>
    <p>Per element: <code>hash</code>, <code>from</code>, <code>to</code>, <code>value</code>,
    <code>tx_type</code>. A <code>from</code> of <code>System</code> indicates a block reward.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/transactions" | python3 -m json.tool</pre></div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/nodes</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Nodes registered on-chain via <code>NODE_REGISTRATION</code> transactions:
    address records published to the ledger instead of an external DNS server.</p>
    <p>Per element: <code>node_id</code>, <code>peer_id</code>, <code>chain_id</code>,
    <code>external_ip</code>, <code>tep_port</code>, <code>tep_pubkey</code>,
    <code>rpc_endpoint</code>, <code>multiaddrs[]</code>, <code>timestamp</code>, <code>version</code>.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/nodes" | python3 -m json.tool</pre></div>
    <div class="caveat">A registration record means a node announced itself. It does
    not mean the node is reachable, running, or has ever connected to anything.</div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/storage</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Ledger size on disk.</p>
    <p>Fields: <code>size_bytes</code>, <code>size_mb</code>.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/storage"</pre></div>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/tep/</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Status of HB-TEP, the encrypted transport layer: X25519 ECDH key
    agreement, AES-256-GCM authenticated encryption, peer addresses resolved on-chain.</p>
    <p>Fields: <code>node_id</code>, <code>pubkey</code>, <code>crypto_mode</code>,
    <code>dns_source</code>, <code>peers[]</code>, <code>stats{}</code>.</p>
    <ul>
      <li><code>peers[]</code>: <code>id</code>, <code>ip</code>, <code>port</code>, <code>online</code>, <code>last_seen</code>, <code>latency_ms</code></li>
      <li><code>stats{}</code>: <code>peers_online</code>, <code>peers_total</code>, <code>pkts_sent</code>, <code>pkts_recv</code>, <code>pkts_dropped</code>, <code>uptime_sec</code>, <code>listen_ip</code>, <code>listen_port</code></li>
    </ul>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/tep/" | python3 -m json.tool</pre></div>
    <div class="caveat">
      <p><code>peers_total</code> counts entries in the peer configuration.
      <code>peers_online</code> counts peers that have actually answered. These are
      different numbers and the gap between them is meaningful.</p>
      <p>The cryptographic construction uses standard, publicly specified primitives.
      The implementation is experimental and has not been independently audited. Do not
      rely on it to protect anything that matters.</p>
    </div>
  </div>

  <h2>Mining endpoints</h2>
  <div class="notice info">
    <p>These cover mining on <strong>external pools</strong> — proof-of-work on
    third-party coins, performed by the node operator's hardware. This is not HashBurst
    consensus (PoH) and is unrelated to the chain endpoints above.</p>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/mining/summary</span>
      <span class="tag public">public</span>
    </div>
    <p class="lead">Per-pool aggregates. No worker identifiers, no IP addresses, no hostnames.</p>
    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/mining/summary" | python3 -m json.tool</pre></div>

    <h3>Response fields</h3>
    <p>Availability differs per pool, because the upstream APIs differ. Handle missing
    fields rather than assuming them.</p>
    <table>
      <tr><th>Field</th><th>Meaning</th></tr>
      <tr><td>pool</td><td><code>zpool</code>, <code>viabtc</code>, <code>nanopool</code>, <code>nicehash</code></td></tr>
      <tr><td>coin</td><td>Coin mined on that pool</td></tr>
      <tr><td>hashrate_hs</td><td>Aggregate hashrate in H/s. <code>null</code> where the pool does not publish it</td></tr>
      <tr><td>active_workers</td><td>Workers currently contributing</td></tr>
      <tr><td>total_workers</td><td>Workers known to the pool. Absent where the pool reports only active ones</td></tr>
      <tr><td>accepted_shares</td><td>Cumulative accepted shares. Present where the pool exposes shares instead of hashrate</td></tr>
      <tr><td>rejected_shares</td><td>Cumulative rejected shares. Pair with the above to compute a reject rate</td></tr>
      <tr><td>unpaid</td><td>Pending balance, decimal string</td></tr>
      <tr><td>balance</td><td>Confirmed balance, decimal string</td></tr>
      <tr><td>note</td><td>Free-text note from the pool, optional</td></tr>
    </table>
    <div class="caveat"><code>hashrate_hs: null</code> on zpool is correct, not an error:
    that pool does not publish a hashrate for this wallet, so share counts are provided
    instead.</div>
    <p>Cache: recomputed every 60 seconds. <code>generated_at</code> tells you how stale
    the snapshot is. Polling faster returns the same data and burns your rate limit.</p>
  </div>

  <div class="ep">
    <div class="ep-head">
      <span class="method">GET</span>
      <span class="path">/api/hashburst/workers/search</span>
      <span class="tag auth">authenticated</span>
    </div>
    <p class="lead">Returns only the workers owned by the caller.</p>
    <p>Worker names on the external pools are the stakeholder's own apikey — they are
    stable user identifiers, not anonymous infrastructure labels. This endpoint therefore
    has no global search: an authenticated caller sees their own workers and nothing else.
    The <code>q</code> parameter filters within that set; it cannot widen it.</p>

    <table>
      <tr><th>Header</th><th>Value</th></tr>
      <tr><td>X-Api-Key</td><td>Your apikey, as assigned in your stakeholder record</td></tr>
      <tr><td>X-Signature</td><td>The signature paired with that apikey</td></tr>
    </table>
    <table>
      <tr><th>Parameter</th><th>Required</th><th>Description</th></tr>
      <tr><td>q</td><td>No</td><td>Case-insensitive substring filter over your own worker names. Omit for all of them.</td></tr>
    </table>

    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s "/api/hashburst/workers/search" \
  -H "X-Api-Key: &lt;your-apikey&gt;" \
  -H "X-Signature: &lt;your-signature&gt;" | python3 -m json.tool</pre></div>

    <div class="pre-wrap"><button class="copy">copy</button><pre>curl -s -G --data-urlencode "q=vm3" \
  "/api/hashburst/workers/search" \
  -H "X-Api-Key: &lt;your-apikey&gt;" \
  -H "X-Signature: &lt;your-signature&gt;" | python3 -m json.tool</pre></div>

    <pre>{
  "scope": "self",
  "generated_at": 1784246211,
  "query": null,
  "owned_total": 1,
  "count": 1,
  "workers": [
    { "name": "&lt;your-apikey&gt;", "pool": "viabtc", "coin": "LTC",
      "hashrate_hs": 13981.0, "active": true }
  ]
}</pre>
    <p><code>owned_total</code> is how many workers you own; <code>count</code> is how many
    matched <code>q</code>. Without <code>q</code> they are equal.</p>

    <h3>Worker naming</h3>
    <p>A worker is attributed to you when its name is your apikey, optionally followed by
    a separator (<code>.</code> <code>_</code> <code>-</code> <code>:</code> <code>/</code>
    <code>@</code> <code>|</code>) and a machine suffix.</p>
    <pre>&lt;apikey&gt;              -> yours
&lt;apikey&gt;_vm3          -> yours
&lt;apikey&gt;.rig01        -> yours
pgxsrv01              -> attributed to nobody, returned to nobody</pre>
    <div class="caveat">A worker whose name does not start with a registered apikey is
    invisible to every caller, including whoever runs it. Name your workers accordingly.</div>
    <p>An empty <code>workers</code> array with <code>owned_total: 0</code> is not an error:
    it means no worker is currently registered under your apikey on any pool the aggregator
    polls.</p>
  </div>

  <h2>Known gaps</h2>
  <div class="notice warn">
    <p>Documented because they affect what you get back, not because they are acceptable.</p>
    <ul>
      <li>Worker-level coverage is currently incomplete for some pools. Aggregate counts in
      <code>/mining/summary</code> may exceed the number of workers actually retrievable via
      <code>/workers/search</code>. If you own workers on those pools, they may not appear yet.</li>
      <li><code>/blocks</code> and <code>/transactions</code> return a recent window with no
      pagination.</li>
      <li>The public API currently has a single gateway hostname even though the
      underlying HashBurst blockchain and HB-TEP network are multi-node.</li>
    </ul>
  </div>

  <footer>
    This page describes the API as verified on <?= htmlspecialchars($reviewed, ENT_QUOTES, 'UTF-8') ?>.
    If an endpoint behaves differently from what is written here, this page is wrong —
    report it at <a href="https://github.com/hashburst">github.com/hashburst</a>.
  </footer>

</main>
<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
'use strict';
// Copia negli appunti. Legge da textContent del <pre> adiacente: nessun dato
// esterno, nessuna interpolazione.
document.querySelectorAll('.copy').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var pre = btn.parentElement.querySelector('pre');
    if (!pre) return;
    navigator.clipboard.writeText(pre.textContent).then(function () {
      btn.textContent = 'copied';
      setTimeout(function () { btn.textContent = 'copy'; }, 1200);
    }).catch(function () {
      btn.textContent = 'failed';
      setTimeout(function () { btn.textContent = 'copy'; }, 1200);
    });
  });
});
</script>
</body>
</html>
