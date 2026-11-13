<?php

$nonce = base64_encode(random_bytes(16));

header(
    "Content-Security-Policy: " .
    "default-src 'none'; " .
    "connect-src 'self'; " .
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HashBurst Block Explorer</title>
<style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
  :root {
    --bg: #0D1117;
    --surface: #161B22;
    --surface2: #1C2128;
    --border: #30363D;
    --blue: #2E86C1;
    --blue-light: #58A6FF;
    --green: #3FB950;
    --yellow: #D29922;
    --red: #F85149;
    --amber: #D9822B;
    --text: #E6EDF3;
    --text-muted: #8B949E;
    --mono: ui-monospace, 'Courier New', monospace;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: var(--bg); color: var(--text); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 100vh; }

  
  .hidden { display: none; }

  
  header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text); cursor: pointer; }
  .logo-icon { width: 32px; height: 32px; background: linear-gradient(135deg, var(--blue) 0%, #1A5276 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; color: white; }
  .logo-text { font-weight: 700; font-size: 18px; }
  .logo-sub { font-size: 12px; color: var(--text-muted); font-weight: 400; }
  nav { display: flex; gap: 4px; align-items: center; }
  nav button { background: none; border: none; color: var(--text-muted); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.15s; font-family: inherit; }
  nav button:hover { background: var(--surface2); color: var(--text); }
  nav button.active { background: var(--blue); color: white; }
  nav a.navlink { color: var(--text-muted); text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 14px; }
  nav a.navlink:hover { background: var(--surface2); color: var(--text); }
  .header-status { font-size: 13px; color: var(--text-muted); }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--text-muted); display: inline-block; margin-right: 6px; }
  .status-dot.online { background: var(--green); animation: pulse 2s infinite; }
  .status-dot.offline { background: var(--red); }
  @keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.4} }

  
  main { max-width: 1200px; margin: 0 auto; padding: 24px; }

  
  .notice-bar { border-radius: 10px; padding: 14px 18px; font-size: 13px; margin-bottom: 20px; line-height: 1.6; }
  .notice-bar.warn { background: rgba(217,130,43,.12); border: 1px solid rgba(217,130,43,.35); color: #E8B47A; }
  .notice-bar.info { background: rgba(46,134,193,.10); border: 1px solid rgba(46,134,193,.30); color: var(--blue-light); }
  .notice-bar strong { font-weight: 700; }

  
  .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .stats-grid-pad { padding: 20px; margin-bottom: 0; }
  .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
  .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
  .stat-value { font-size: 28px; font-weight: 700; color: var(--blue-light); }
  .stat-value.small { font-size: 12px; font-family: var(--mono); padding-top: 8px; word-break: break-all; }
  .stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

  
  .search-bar { display: flex; gap: 8px; margin-bottom: 8px; }
  .search-bar input { flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 10px 16px; color: var(--text); font-size: 14px; outline: none; transition: border-color 0.15s; font-family: inherit; }
  .search-bar input:focus { border-color: var(--blue); }
  .search-bar input::placeholder { color: var(--text-muted); }
  .search-hint { font-size: 11px; color: var(--text-muted); margin-bottom: 24px; }
  .search-msg { font-size: 12px; color: var(--yellow); margin-bottom: 16px; }
  .btn { background: var(--blue); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-size: 14px; cursor: pointer; font-weight: 600; transition: opacity 0.15s; font-family: inherit; }
  .btn:hover { opacity: 0.85; }
  .btn-sm { padding: 6px 12px; font-size: 12px; }
  .btn-ghost { background: var(--surface2); color: var(--text-muted); }

  
  .section { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 24px; overflow: hidden; }
  .section-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  .section-title { font-weight: 600; font-size: 15px; }
  .section-sub { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
  .section-badge { background: var(--surface2); color: var(--text-muted); font-size: 11px; padding: 2px 8px; border-radius: 20px; white-space: nowrap; }
  .section-header-meta { display: flex; align-items: center; gap: 12px; }

  
  table { width: 100%; border-collapse: collapse; }
  th { padding: 10px 16px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border); }
  td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--surface2); }
  td.mono { font-family: var(--mono); font-size: 12px; color: var(--text-muted); }
  td.dim { color: var(--text-muted); }
  .hash { font-family: var(--mono); font-size: 12px; color: var(--blue-light); cursor: pointer; }
  .hash:hover { text-decoration: underline; }
  .empty-row td { text-align: center; color: var(--text-muted); padding: 32px 16px; }

  
  .badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
  .badge-green { background: rgba(63,185,80,0.15); color: var(--green); }
  .badge-blue { background: rgba(46,134,193,0.15); color: var(--blue-light); }
  .badge-yellow { background: rgba(210,153,34,0.15); color: var(--yellow); }
  .badge-red { background: rgba(248,81,73,0.15); color: var(--red); }
  .badge-muted { background: var(--surface2); color: var(--text-muted); }

  
  .view { display: none; }
  .view.active { display: block; }

  
  .detail-card { background: var(--surface); border: 1px solid var(--blue); border-radius: 12px; padding: 24px; margin-bottom: 24px; }
  .detail-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
  .detail-title { font-weight: 700; font-size: 15px; }
  .detail-row { display: flex; padding: 12px 0; border-bottom: 1px solid var(--border); align-items: flex-start; gap: 16px; }
  .detail-row:last-child { border-bottom: none; }
  .detail-key { width: 180px; flex-shrink: 0; font-size: 13px; color: var(--text-muted); font-weight: 500; padding-top: 2px; }
  .detail-val { font-size: 13px; word-break: break-all; }
  .detail-val.mono { font-family: var(--mono); font-size: 12px; color: var(--blue-light); }

  
  .node-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px; padding: 20px; }
  .node-card { background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; padding: 20px; }
  .node-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 8px; }
  .node-id { font-weight: 700; font-size: 15px; word-break: break-all; }
  .node-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
  .node-field-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
  .node-field-value { font-size: 12px; font-family: var(--mono); color: var(--blue-light); word-break: break-all; }
  .node-field-value.plain { font-family: inherit; color: var(--text); }
  .grid-empty { padding: 40px; color: var(--text-muted); grid-column: 1/-1; text-align: center; line-height: 1.6; }

  
  .tep-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; padding: 20px; }
  .tep-stat { text-align: center; }
  .tep-stat-val { font-size: 32px; font-weight: 700; color: var(--blue-light); }
  .tep-stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
  .tep-peers-wrap { padding: 0 20px 20px; }
  .tep-peers-title { font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-muted); }

  
  .cap-bar-svg { width: 100%; height: 28px; border-radius: 8px; border: 1px solid var(--border); display: block; margin-bottom: 12px; overflow: hidden; }
  .cap-bar-svg rect.cap-reserved { fill: var(--blue-light); }
  .cap-bar-svg rect.cap-sold { fill: var(--amber); }
  .cap-bar-svg rect.cap-free { fill: var(--green); }
  .cap-legend { display: flex; gap: 16px; font-size: 12px; color: var(--text-muted); }
  .cap-legend-item { display: flex; align-items: center; gap: 6px; }
  .cap-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
  .cap-dot.cap-reserved { background: var(--blue-light); }
  .cap-dot.cap-sold { background: var(--amber); }
  .cap-dot.cap-free { background: var(--green); }

  
  .loading { text-align: center; padding: 40px; color: var(--text-muted); }
  .loading.error { color: var(--red); }
  .spinner { display: inline-block; width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--blue); border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 8px; vertical-align: middle; }
  @keyframes spin { to { transform: rotate(360deg); } }

  .refresh-info { font-size: 11px; color: var(--text-muted); }

  @media (max-width: 640px) {
    header { padding: 0 12px; height: auto; flex-wrap: wrap; gap: 8px; padding-top: 8px; padding-bottom: 8px; }
    main { padding: 12px; }
    .stat-value { font-size: 22px; }
    .detail-row { flex-direction: column; gap: 4px; }
    .detail-key { width: auto; }
  }
#network-state{display:block}
#service-availability{display:flex;flex-wrap:wrap;gap:8px 24px}
#service-availability p{margin:0}
#service-availability small{flex-basis:100%;margin-top:6px}
.hvm-wallet-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}
.hvm-wallet-link{display:inline-block;padding:10px 16px;border:1px solid #537b98;border-radius:5px;color:#bce4ff;text-decoration:none;font-size:13px;font-weight:600}
a.hvm-wallet-link:hover{background:#1b3249}
a.hvm-wallet-link:focus-visible{outline:2px solid #bce4ff;outline-offset:3px}
.hvm-wallet-link.is-disabled{color:#9caabc;border-color:#3d4b5e;cursor:not-allowed}
.hvm-table{border-collapse:collapse;width:100%}
.hvm-table tr{border-top:1px solid #637082}
.hvm-table tr:last-child{border-bottom:1px solid #637082}
.hvm-table th,.hvm-table td{background:transparent;vertical-align:top}
.hvm-observer-status{font-weight:700;color:#fff}
.hvm-observer-status.is-online{color:#8cd6ab}
.hvm-observer-status.is-unavailable{color:#f29a9a}
.hvm-wallet-note{font-size:12px}
.hvm-network-switch{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0 10px}
.hvm-wallet-controls{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin:0 0 18px}
.hvm-wallet-controls .section-sub{margin:0}
#service-availability{display:block}
#service-availability small{display:block;margin-top:10px}
.tep-stack{margin-top:28px}
.tep-stack pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:300px;overflow:auto}
.node-field-value{overflow-wrap:anywhere}
</style>
</head>
<body>

<header>
  <div class="logo" id="logo">
    <div class="logo-icon">HB</div>
    <div>
      <div class="logo-text">HashBurst</div>
      <div class="logo-sub">Network Explorer</div>
    </div>
  </div>
  <nav>
    <button class="active" data-view="explorer" id="nav-explorer">Explorer</button>
    <button data-view="nodes" id="nav-nodes">DNS Nodes</button>
    <button data-view="tep" id="nav-tep">HB-TEP</button>
    <button data-view="storage" id="nav-storage">Sovereign Storage</button>
    <button data-view="hvm" id="nav-hvm">HVM Network</button>
    <a class="navlink" href="/hashburst/mining.php">Mining Operations</a>
  </nav>
  <div class="header-status">
    <span class="status-dot" id="status-dot"></span>
    <span id="status-label">Connecting...</span>
  </div>
</header>

<main>

  
  <div id="network-state" class="notice-bar info">
    <div id="mainnet-operational">Mainnet 4735489 (mining, P2P and spending) status: checking.</div>
    <div id="testnet-operational">Testnet 4735490 (mining, P2P and spending) status: checking.</div>
    <div id="legacy-operational">Chain legacy mode 1337 (mining, P2P and spending) status: checking.</div>
    <p><strong>Ecosystem API results:</strong></p>
    <div id="service-availability" role="status"></div>
  </div>

  
  <div class="view active" id="view-explorer">

    <div class="search-bar">
      <input id="search-input" placeholder="Block number, block hash, or transaction hash">
      <button class="btn" id="search-btn">Search</button>
    </div>
    <div class="search-hint" id="search-hint">
      Searches the blocks and transactions currently loaded on this page.
    </div>
    <div id="search-msg"></div>

    <div id="detail-container"></div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Block Height</div>
        <div class="stat-value" id="stat-height">-</div>
        <div class="stat-sub">Latest block</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Chain ID</div>
        <div class="stat-value" id="stat-chain">-</div>
        <div class="stat-sub">Explorer data source</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Peer ID</div>
        <div class="stat-value small" id="stat-peer">-</div>
        <div class="stat-sub">Stable P2P identity</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Storage</div>
        <div class="stat-value" id="stat-storage">-</div>
        <div class="stat-sub">Ledger on disk</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">P2P Peers</div>
        <div class="stat-value" id="stat-peers">-</div>
        <div class="stat-sub">Connected nodes</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">TEP Peers</div>
        <div class="stat-value" id="stat-tep-peers">-</div>
        <div class="stat-sub">Encrypted tunnel, online</div>
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <div class="section-title">Latest Blocks</div>
        <div class="section-header-meta">
          <span class="refresh-info" id="last-refresh"></span>
          <span class="section-badge" id="block-count">- blocks</span>
        </div>
      </div>
      <div id="blocks-loading" class="loading"><span class="spinner"></span>Loading blocks...</div>
      <table id="blocks-table" class="hidden">
        <thead>
          <tr>
            <th>#</th>
            <th>Hash</th>
            <th>Parent Hash</th>
            <th>Transactions</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="blocks-tbody"></tbody>
      </table>
    </div>

    <div class="section">
      <div class="section-header">
        <div class="section-title">Transactions</div>
        <span class="section-badge" id="tx-count">- txs</span>
      </div>
      <div id="txs-loading" class="loading"><span class="spinner"></span>Loading transactions...</div>
      <table id="txs-table" class="hidden">
        <thead>
          <tr>
            <th>Hash</th>
            <th>From</th>
            <th>To</th>
            <th>Value</th>
            <th>Type</th>
          </tr>
        </thead>
        <tbody id="txs-tbody"></tbody>
      </table>
    </div>

  </div>

  
  <div class="view" id="view-nodes">
    <div class="section">
      <div class="section-header">
        <div>
          <div class="section-title">HB-TEP Node Discovery</div>
          <div class="section-sub">Live node identities and peer discovery reported by the HB-TEP registry.</div>
        </div>
        <span class="section-badge" id="nodes-count">- nodes</span>
      </div>
      <div id="nodes-loading" class="loading"><span class="spinner"></span>Loading nodes...</div>
      <div id="nodes-grid" class="node-grid hidden"></div>
      <div class="tep-stack">
        <h3>HB-TEP transport stack</h3>
        <table><tbody>
          <tr><th>Application overlay</th><td>HB-TEP-APP/1, authenticated identities and service messages above UDP.</td></tr>
          <tr><th>OSI layer 4</th><td>Tunnel over UDP. Default carrier port 47777.</td></tr>
          <tr><th>OSI layer 3</th><td>IP routing carries UDP datagrams in this deployment.</td></tr>
          <tr><th>OSI layers 1 and 2</th><td>Provided by the host and its underlying network interface.</td></tr>
          <tr><th>TEP framing</th><td>36-byte header: HBT02 magic, version, packet type, node identifier, nonce and encrypted payload length. AES-256-GCM payload in the configured encrypted mode.</td></tr>
        </tbody></table>
        <p>Peer IDs identify nodes; UDP endpoints carry packets. Packet capture shows the encapsulation, not decrypted application content or proof of authentication.</p>
        <button id="tep-monitor-refresh" class="btn" type="button">Inspect transport monitor</button>
        <a href="/api/hashburst/tep-monitor/pcap" class="btn">Download available capture</a>
        <pre id="tep-monitor-result">Monitor not sampled.</pre>
      </div>
    </div>
  </div>

  
  <div class="view" id="view-tep">
    <div class="section">
      <div class="section-header">
        <div>
          <div class="section-title">HashBurst Transport Encrypted Protocol (HB-TEP)</div>
          <div class="section-sub">Live HB-TEP transport telemetry: X25519 ECDH key agreement, AES-256-GCM authenticated encryption and peer discovery. Status and peer information are loaded from the public TEP API.</div>
        </div>
        <span id="tep-dns-source" class="badge badge-muted">Loading...</span>
      </div>
      <div id="tep-loading" class="loading"><span class="spinner"></span>Loading TEP status...</div>
      <div id="tep-content" class="hidden">
        <div class="tep-grid" id="tep-stats-grid"></div>
        <div class="tep-peers-wrap">
          <div class="tep-peers-title">PEER LIST</div>
          <table>
            <thead>
              <tr><th>Node ID</th><th>IP</th><th>Port</th><th>Status</th><th>Last Seen</th><th>Latency</th></tr>
            </thead>
            <tbody id="tep-peers-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <div class="section-title">TEP Node Identity</div>
      </div>
      <div id="tep-identity" class="tep-peers-wrap"></div>
    </div>
  </div>

  
  <div class="view" id="view-storage">
    <div class="section">
      <div class="section-header">
        <div>
          <div class="section-title">Sovereign Storage - HB-Files / IPFS</div>
          <div class="section-sub">Client-side encrypted storage across a private IPFS network. Files are encrypted in the browser; nodes hold only ciphertext. Figures are network aggregates - no per-stakeholder data, file identifiers, or client identities are exposed.</div>
        </div>
        <span class="section-badge" id="storage-src">-</span>
      </div>
      <div id="storage-loading" class="loading"><span class="spinner"></span>Loading storage network status...</div>
      <div id="storage-content" class="hidden">
        <div class="stats-grid stats-grid-pad">
          <div class="stat-card">
            <div class="stat-label">Storage Nodes</div>
            <div class="stat-value" id="st-nodes">-</div>
            <div class="stat-sub">Online / total</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Best-effort Capacity</div>
            <div class="stat-value" id="st-best-effort">-</div>
            <div class="stat-sub">Best-effort replica capacity</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Committable Capacity</div>
            <div class="stat-value" id="st-committable">-</div>
            <div class="stat-sub">Sovereign storage pool</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Reserved - Stakeholders</div>
            <div class="stat-value" id="st-reserved">-</div>
            <div class="stat-sub">Free quota to all stakeholders</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Sold - Active Clients</div>
            <div class="stat-value" id="st-sold">-</div>
            <div class="stat-sub">Capacity assigned to paying clients</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Free - Sellable</div>
            <div class="stat-value" id="st-free">-</div>
            <div class="stat-sub">Available surplus capacity</div>
          </div>
        </div>
        <div class="tep-peers-wrap">
          <div class="tep-peers-title">CAPACITY ALLOCATION</div>
          <div id="st-bar-wrap"></div>
        </div>
        <div class="tep-peers-wrap">
          <div class="tep-peers-title">STORAGE NODES</div>
          <table>
            <thead>
              <tr><th>Node</th><th>Role</th><th>Status</th><th>Capacity</th><th>Used</th><th>Source</th></tr>
            </thead>
            <tbody id="st-nodes-table"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>



  <div class="view" id="view-hvm">
    <style>
      #view-hvm .hvm-network-switch{display:flex;gap:10px;flex-wrap:wrap;margin:14px 0 18px}
      #view-hvm .hvm-network-switch .btn{min-width:132px;padding:10px 16px}
      #view-hvm .hvm-network-switch .btn.active{font-weight:700}
      #view-hvm .hvm-table-wrap{overflow-x:auto;margin:14px 0 18px}
      #view-hvm table{width:100%;border-collapse:separate;border-spacing:0}
      #view-hvm th,#view-hvm td{padding:11px 14px;vertical-align:top}
      #view-hvm .section>p{margin:12px 0;line-height:1.55}
      #view-hvm code{overflow-wrap:anywhere}
    </style>
    <div class="section">
      <div class="section-header"><div><div class="section-title">HVM Network</div><div class="section-sub">Live read-only Testnet and Mainnet observer telemetry. Select a network below; refreshes every 30 seconds while visible.</div></div>
      <div><button class="btn" id="hvm-refresh" type="button">Refresh</button></div></div>
      <div class="hvm-network-switch" role="group" aria-label="HVM telemetry network">
        <button class="btn active" id="hvm-connect-mainnet" type="button" data-hvm-network="mainnet">Mainnet</button>
        <button class="btn" id="hvm-connect-testnet" type="button" data-hvm-network="testnet">Testnet</button>
      </div>
      <div class="hvm-wallet-controls">
        <button class="btn" id="hvm-wallet-add" type="button">Add / Switch in MetaMask</button>
        <span id="hvm-wallet-status" class="section-sub" role="status">Wallet not connected.</span>
      </div>
      <p id="hvm-status" role="status"></p><p id="hvm-time"></p>
      <div class="hvm-table-wrap"><table><tbody>
        <tr><th>Network</th><td id="hvm-network"></td></tr>
        <tr><th>Status</th><td id="hvm-live"></td></tr>
        <tr><th>Chain ID</th><td id="hvm-chain"></td></tr>
        <tr><th>Block height</th><td id="hvm-height"></td></tr>
        <tr><th>Finalized height</th><td id="hvm-finalized"></td></tr>
        <tr><th>Progress</th><td id="hvm-progress"></td></tr>
        <tr><th>P2P peers</th><td><span id="hvm-peers"></span> <span class="section-sub">Observer connected peers</span></td></tr>
        <tr><th>Validator federation</th><td id="hvm-federation"></td></tr>
        <tr><th>Consensus reactor</th><td id="hvm-reactor"></td></tr>
        <tr><th>APoW / mining</th><td id="hvm-apow"></td></tr>
        <tr><th>EVM activation</th><td id="hvm-activation"></td></tr>
        <tr><th>JSON-RPC</th><td><code id="hvm-rpc-url"></code><br><span id="hvm-rpc"></span></td></tr>
        <tr><th>WebSocket</th><td><code id="hvm-ws-url"></code></td></tr>
      </tbody></table></div>
      <p id="hvm-network-note"></p>
      <p class="section-sub">P2P peers is the selected observer's live connected-peer count. Observer telemetry is read-only and exposes no validator or founder private keys. This panel sends no transactions.</p>
    </div>
  </div>

</main>

<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
'use strict';

const API = window.location.origin + '/api/hashburst';
const TEP_API = window.location.origin + '/api/tep';
const state = {
  view: 'explorer',
  blocks: [],
  txs: [],
  p2pPeers: null,
  tepPeers: null,
};

const $ = (id) => document.getElementById(id);

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = String(text);
  return node;
}
function showEl(node, visible) {
  if (!node) return;
  if (visible) node.classList.remove('hidden');
  else node.classList.add('hidden');
}
function svgEl(tag, attrs) {
  const e = document.createElementNS('http://www.w3.org/2000/svg', tag);
  for (const k in attrs) e.setAttribute(k, attrs[k]);
  return e;
}

function clear(node) {
  while (node.firstChild) node.removeChild(node.firstChild);
}

function td(text, className) {
  return el('td', className, text);
}

function tdNode(child, className) {
  const cell = el('td', className);
  cell.appendChild(child);
  return cell;
}

function detailRow(key, value, mono) {
  const row = el('div', 'detail-row');
  row.appendChild(el('div', 'detail-key', key));
  row.appendChild(el('div', 'detail-val' + (mono ? ' mono' : ''), value));
  return row;
}

function detailRowNode(key, child) {
  const row = el('div', 'detail-row');
  row.appendChild(el('div', 'detail-key', key));
  const val = el('div', 'detail-val');
  val.appendChild(child);
  row.appendChild(val);
  return row;
}

function shortHash(h, n = 16) {
  if (!h) return '-';
  const s = String(h);
  return s.length <= n ? s : s.slice(0, n) + '...';
}

function timeAgo(ts) {
  if (!ts) return '-';
  const d = Date.now() - ts * 1000;
  if (d < 60000) return Math.floor(d / 1000) + 's ago';
  if (d < 3600000) return Math.floor(d / 60000) + 'm ago';
  return new Date(ts * 1000).toLocaleTimeString();
}

async function apiFetch(url) {
  const r = await fetch(url, { cache: 'no-store', signal: AbortSignal.timeout(8000) });
  if (!r.ok) throw new Error('HTTP ' + r.status);
  return r.json();
}

function showView(name) {
  document.querySelectorAll('.view').forEach((v) => v.classList.remove('active'));
  document.querySelectorAll('nav button').forEach((b) => b.classList.remove('active'));
  const view = $('view-' + name);
  const nav = $('nav-' + name);
  if (!view || !nav) return;
  view.classList.add('active');
  nav.classList.add('active');
  state.view = name;
  loadView(name);
}

function loadView(name) {
  if (name === 'explorer') { loadStatus(); loadBlocks(); loadTxs(); loadStorage(); loadTEPStatus(); }
  else if (name === 'nodes') loadNodes();
  else if (name === 'tep') loadTEPDetail();
  else if (name === 'storage') loadStorageSummary();
}

function renderNetworkState() {}

async function loadStatus() {
  try {
    const health = await apiFetch('/api/hashburst/hvm/mainnet/network');
    if (health.ok !== true || health.chain_id !== 4735489 || health.network !== 'mainnet') throw Error('Mainnet identity mismatch');
    $('stat-height').textContent = health.finalized_height ?? health.height ?? 'Not reported';
    $('stat-chain').textContent = health.chain_id;
    $('stat-peer').textContent = health.peer_id || 'Not reported';
    $('stat-peers').textContent = health.transport?.connected_peers ?? health.peer_count ?? 'Not reported';
    state.p2pPeers = health.transport?.connected_peers ?? health.peer_count ?? null;
    $('status-dot').className = 'status-dot online';
    $('status-label').textContent = health.reactor_running === true ? 'Online' : 'Degraded';
    $('last-refresh').textContent = 'Checked ' + new Date().toISOString();
    renderNetworkState();
  } catch (e) {
    $('status-dot').className = 'status-dot offline';
    $('status-label').textContent = 'Unavailable';
    for (const id of ['stat-height','stat-chain','stat-peer','stat-peers']) $(id).textContent = 'Unavailable';
    $('last-refresh').textContent = 'Check failed: ' + e.message;
  }
}

async function loadStorage() {
  try {
    const storage = await apiFetch('/api/network/storage');
    if (storage.available !== true) throw Error('Storage unavailable');
    const n = storage.network || {};
    const used = Number(n.used_committable_gb || 0) + Number(n.used_best_effort_gb || 0);
    $('stat-storage').textContent = Number.isFinite(used) ? used.toFixed(2) + ' GB' : 'Not reported';
  } catch (e) {
    $('stat-storage').textContent = 'Unavailable';
  }
}
async function loadTEPStatus() {
  try {
    const t = await apiFetch(TEP_API + '/');
    const online = t.stats?.peers_online ?? null;
    $('stat-tep-peers').textContent = online ?? '-';
    state.tepPeers = online;
    renderNetworkState();
  } catch (e) { $('stat-tep-peers').textContent = 'Unavailable'; state.tepPeers = null; }
}

function blockRow(b) {
  const tr = el('tr');

  tr.appendChild(tdNode(el('span', 'badge badge-blue', '#' + b.number)));

  const link = el('span', 'hash', shortHash(b.hash));
  link.addEventListener('click', () => showBlock(b));
  tr.appendChild(tdNode(link));

  tr.appendChild(tdNode(el('span', 'hash', shortHash(b.parentHash))));

  const txBadge = el('span', 'badge ' + (b.transactions > 0 ? 'badge-green' : 'badge-blue'),
    b.transactions + ' txs');
  tr.appendChild(tdNode(txBadge));

  tr.appendChild(td(timeAgo(b.timestamp), 'dim'));
  return tr;
}

async function loadBlocks() {
  showEl($('blocks-loading'), true);
  $('blocks-loading').className = 'loading';
  showEl($('blocks-table'), false);
  try {
    const blocks = await apiFetch(API + '/blocks');
    state.blocks = Array.isArray(blocks) ? blocks : [];
    $('block-count').textContent = state.blocks.length + ' blocks';

    const tbody = $('blocks-tbody');
    clear(tbody);
    [...state.blocks].reverse().forEach((b) => tbody.appendChild(blockRow(b)));

    showEl($('blocks-loading'), false);
    showEl($('blocks-table'), true);
    updateSearchHint();
  } catch (e) {
    const box = $('blocks-loading');
    clear(box);
    box.className = 'loading error';
    box.textContent = 'Failed to load blocks: ' + e.message;
    showEl(box, true);
  }
}

function txTypeBadge(tx) {
  if (tx.tx_type === 'NODE_REGISTRATION') {
    return el('span', 'badge badge-yellow', 'NODE_REG');
  }

  if (tx.from === 'System') {
    return el('span', 'badge badge-blue', 'REWARD');
  }

  if (!tx.tx_type || tx.tx_type === 'TRANSFER') {
    return el('span', 'badge badge-green', 'TRANSFER');
  }

  return el('span', 'badge badge-muted', String(tx.tx_type));
}

function txRow(tx) {
  const tr = el('tr');
  const isReg = tx.tx_type === 'NODE_REGISTRATION';

  const link = el('span', 'hash', shortHash(tx.hash));
  link.title = tx.hash || '';
  link.addEventListener('click', () => showTx(tx));
  tr.appendChild(tdNode(link));

  tr.appendChild(td(shortHash(tx.from, 12), 'mono'));
  tr.appendChild(td(shortHash(tx.to, 12), 'mono'));
  tr.appendChild(td(isReg ? '-' : (Number(tx.value) || 0).toFixed(2) + ' HBT'));
  tr.appendChild(tdNode(txTypeBadge(tx)));
  return tr;
}

async function loadTxs() {
  showEl($('txs-loading'), true);
  $('txs-loading').className = 'loading';
  showEl($('txs-table'), false);
  try {
    const txs = await apiFetch(API + '/transactions');
    state.txs = Array.isArray(txs) ? txs : [];
    $('tx-count').textContent = state.txs.length + ' txs';

    const tbody = $('txs-tbody');
    clear(tbody);
    [...state.txs].reverse().forEach((tx) => tbody.appendChild(txRow(tx)));

    showEl($('txs-loading'), false);
    showEl($('txs-table'), true);
    updateSearchHint();
  } catch (e) {
    const box = $('txs-loading');
    clear(box);
    box.className = 'loading error';
    box.textContent = 'Failed to load transactions: ' + e.message;
    showEl(box, true);
  }
}

function detailCard(title, rows) {
  const card = el('div', 'detail-card');
  const head = el('div', 'detail-head');
  head.appendChild(el('div', 'detail-title', title));
  const close = el('button', 'btn btn-sm btn-ghost', 'Close');
  close.addEventListener('click', () => clear($('detail-container')));
  head.appendChild(close);
  card.appendChild(head);
  rows.forEach((r) => card.appendChild(r));
  return card;
}

function showBlock(b) {
  const box = $('detail-container');
  clear(box);
  box.appendChild(detailCard('Block #' + b.number, [
    detailRow('Hash', b.hash || '-', true),
    detailRow('Parent Hash', b.parentHash || '-', true),
    detailRow('Transactions', b.transactions ?? '-'),
    detailRow('Timestamp', b.timestamp ? new Date(b.timestamp * 1000).toLocaleString() : '-'),
    detailRow('Miner', b.miner || '-', true),
  ]));
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showTx(tx) {
  const box = $('detail-container');
  clear(box);
  box.appendChild(detailCard('Transaction', [
    detailRow('Hash', tx.hash || '-', true),
    detailRow('From', tx.from || '-', true),
    detailRow('To', tx.to || '-', true),
    detailRow('Value', tx.tx_type === 'NODE_REGISTRATION'
      ? '-' : (Number(tx.value) || 0).toFixed(2) + ' HBT'),
    detailRowNode('Type', txTypeBadge(tx)),
    detailRow('Timestamp', tx.timestamp ? new Date(tx.timestamp * 1000).toLocaleString() : '-'),
  ]));
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function updateSearchHint() {
  $('search-hint').textContent =
    'Searches the ' + state.blocks.length + ' block(s) and ' + state.txs.length +
    ' transaction(s) currently loaded on this page.';
}

function doSearch() {
  const q = $('search-input').value.trim();
  const msg = $('search-msg');
  clear(msg);
  clear($('detail-container'));
  if (!q) return;

  const lower = q.toLowerCase();

  if (/^\d+$/.test(q)) {
    const b = state.blocks.find((x) => String(x.number) === q);
    if (b) { showBlock(b); return; }
  }

  const block = state.blocks.find((x) => String(x.hash || '').toLowerCase().startsWith(lower));
  if (block) { showBlock(block); return; }

  const tx = state.txs.find((x) => String(x.hash || '').toLowerCase().startsWith(lower));
  if (tx) { showTx(tx); return; }

  msg.appendChild(el('div', 'search-msg',
    'No match among the ' + state.blocks.length + ' block(s) and ' + state.txs.length +
    ' transaction(s) loaded here. Older records are not fetched by this page.'));
}

function nodeField(label, value, plain) {
  const field = el('div', 'node-field');
  field.appendChild(el('div', 'node-field-label', label));
  field.appendChild(el('div', 'node-field-value' + (plain ? ' plain' : ''), value));
  return field;
}

function nodeCard(n) {
  const card = el('div', 'node-card');

  const head = el('div', 'node-card-header');
  head.appendChild(el('div', 'node-id', n.node_id || '-'));
  head.appendChild(el('span', 'badge badge-green', 'Chain ' + (n.chain_id ?? '-')));
  card.appendChild(head);

  card.appendChild(nodeField('Peer ID', n.peer_id || '-'));
  card.appendChild(nodeField('External IP',
    (n.external_ip || '-') + ' · TEP :' + (n.tep_port ?? '-'), true));
  card.appendChild(nodeField('TEP Pubkey (X25519)',
    n.tep_pubkey ? String(n.tep_pubkey).slice(0, 32) + '...' : '-'));
  card.appendChild(nodeField('RPC Endpoint', n.rpc_endpoint || '-', true));

  const addrField = el('div', 'node-field');
  addrField.appendChild(el('div', 'node-field-label', 'Multiaddrs'));
  const addrs = Array.isArray(n.multiaddrs) ? n.multiaddrs : [];
  if (addrs.length) {
    addrs.forEach((a) => addrField.appendChild(el('div', 'node-field-value', a)));
  } else {
    addrField.appendChild(el('div', 'node-field-value plain', '-'));
  }
  card.appendChild(addrField);

  card.appendChild(nodeField('Registered',
    n.timestamp ? new Date(n.timestamp * 1000).toLocaleString() : '-', true));
  card.appendChild(nodeField('Version', n.version || '-', true));
  return card;
}

async function loadNodes() {
  showEl($('nodes-loading'), true);
  showEl($('nodes-grid'), false);
  try {
    const t = await apiFetch(TEP_API + '/');
    if (!Array.isArray(t.peers)) throw Error('Peer inventory not returned');
    $('nodes-count').textContent = t.peers.length + ' discovered peers';
    const grid = $('nodes-grid'); clear(grid);
    for (const p of t.peers) {
      const card = el('div', 'node-card');
      card.appendChild(el('h3', null, p.id || p.node_id || 'Node'));
      card.appendChild(nodeField('P2P Peer ID', p.peer_id || 'Not reported'));
      card.appendChild(nodeField('TEP public identity (X25519)', p.pubkey || p.tep_pubkey || 'Not reported'));
      card.appendChild(nodeField('Transport', 'Tunnel over UDP', true));
      card.appendChild(nodeField('Status', p.online === true ? 'Online' : p.online === false ? 'Offline' : 'Not reported', true));
      card.appendChild(nodeField('Last seen', p.last_seen ? new Date(p.last_seen * 1000).toISOString() : 'Not reported', true));
      grid.appendChild(card);
    }
    if (!t.peers.length) grid.appendChild(el('p', null, 'No peers returned.'));
    $('nodes-loading').textContent = 'Registry: ' + (t.dns_source === 'blockchain' ? 'blockchain' : t.dns_source === 'static' ? 'local TEP registry' : 'source not reported') + '. Updated ' + new Date().toISOString();
    $('nodes-loading').className = 'section-sub';
    showEl(grid, true);
  } catch(e) {
    $('nodes-count').textContent = 'Unavailable';
    $('nodes-loading').textContent = 'Node discovery unavailable: ' + e.message;
    $('nodes-loading').className = 'loading error';
  }
}

function tepPeerRow(p) {
  const tr = el('tr');
  tr.appendChild(td(p.id ?? '-'));
  tr.appendChild(td(p.ip ?? '-', 'mono'));
  tr.appendChild(td(p.port ?? '-'));
  tr.appendChild(tdNode(el('span', 'badge ' + (p.online ? 'badge-green' : 'badge-red'),
    p.online ? 'Online' : 'Offline')));
  tr.appendChild(td(p.last_seen ? timeAgo(p.last_seen) : 'Never', 'dim'));
  tr.appendChild(td(p.latency_ms != null ? p.latency_ms.toFixed(1) + 'ms' : '-', 'dim'));
  return tr;
}

async function loadTEPDetail() {
  showEl($('tep-loading'), true);
  $('tep-loading').className = 'loading';
  showEl($('tep-content'), false);
  try {
    const t = await apiFetch(TEP_API + '/');
    const stats = t.stats || {};
    const onChain = t.dns_source === 'blockchain';

    const src = $('tep-dns-source');
    src.textContent = onChain ? 'Reported source: Blockchain DNS' : 'Reported source: ' + (t.dns_source === 'static' ? 'Static peer registry' : (t.dns_source || 'unspecified'));
    src.className = 'badge ' + (onChain ? 'badge-green' : 'badge-yellow');

    const peers = Array.isArray(t.peers) ? t.peers : [];

    const grid = $('tep-stats-grid');
    clear(grid);
    [
      ['Peers Online', stats.peers_online ?? 0],
      ['Peers Configured', stats.peers_total ?? peers.length],
      ['Pkts Sent', stats.pkts_sent ?? 0],
      ['Pkts Recv', stats.pkts_recv ?? 0],
      ['Pkts Dropped', stats.pkts_dropped ?? 0],
      ['Uptime', Math.round((stats.uptime_sec || 0) / 60) + 'm'],
    ].forEach(([label, value]) => {
      const cell = el('div', 'tep-stat');
      cell.appendChild(el('div', 'tep-stat-val', value));
      cell.appendChild(el('div', 'tep-stat-label', label));
      grid.appendChild(cell);
    });

    const tbody = $('tep-peers-tbody');
    clear(tbody);
    if (!peers.length) {
      const tr = el('tr', 'empty-row');
      const cell = el('td', null, 'No peers configured.');
      cell.colSpan = 6;
      tr.appendChild(cell);
      tbody.appendChild(tr);
    } else {
      peers.forEach((p) => tbody.appendChild(tepPeerRow(p)));
    }

    state.tepPeers = stats.peers_online ?? null;
    renderNetworkState();

    const ident = $('tep-identity');
    clear(ident);
    ident.appendChild(detailRow('Node ID', t.node_id || '-'));
    ident.appendChild(detailRow('TEP Pubkey (X25519)', t.pubkey || '-', true));
    ident.appendChild(detailRowNode('Crypto Mode',
      el('span', 'badge badge-green', t.crypto_mode || '-')));
    ident.appendChild(detailRowNode('DNS Source',
      el('span', 'badge ' + (onChain ? 'badge-green' : 'badge-yellow'), t.dns_source === 'static' ? 'Static peer registry' : (t.dns_source || 'Not reported'))));
    ident.appendChild(detailRow('Listen',
      (stats.listen_ip ?? '-') + ':' + (stats.listen_port ?? '-') + ' (UDP)', true));
    ident.appendChild(detailRow('Cryptographic scheme',
      'X25519 ECDH key agreement + AES-256-GCM AEAD. ' +
      'Runtime identity and transport status are reported by the live HB-TEP API.'));

    showEl($('tep-loading'), false);
    showEl($('tep-content'), true);
  } catch (e) {
    const box = $('tep-loading');
    clear(box);
    box.className = 'loading error';
    box.textContent = 'TEP status unavailable: ' + e.message;
    showEl(box, true);
  }
}

function fmtGB(gb) {
  if (gb === null || gb === undefined) return '-';
  const n = Number(gb);
  if (n >= 1024) return (n / 1024).toFixed(2) + ' TB';
  return n.toFixed(0) + ' GB';
}

function capacityBar(reserved, sold, free) {
  const total = (Number(reserved) || 0) + (Number(sold) || 0) + (Number(free) || 0);
  const W = 1000, H = 28;
  const svg = svgEl('svg', {
    viewBox: '0 0 ' + W + ' ' + H,
    preserveAspectRatio: 'none',
    class: 'cap-bar-svg',
    role: 'img',
  });

  let x = 0;
  const seg = (val, cls, label) => {
    if (total <= 0) return;
    const w = (Number(val) || 0) / total * W;
    if (w <= 0) return;
    const rect = svgEl('rect', {
      x: x.toFixed(2), y: 0, width: w.toFixed(2), height: H, class: cls,
    });
    const title = svgEl('title', {});
    title.textContent = label + ': ' + fmtGB(val);
    rect.appendChild(title);
    svg.appendChild(rect);
    x += w;
  };
  seg(reserved, 'cap-reserved', 'Reserved (stakeholders)');
  seg(sold, 'cap-sold', 'Sold (active clients)');
  seg(free, 'cap-free', 'Free (sellable)');

  const legend = el('div', 'cap-legend');
  const item = (cls, text) => {
    const i = el('div', 'cap-legend-item');
    i.appendChild(el('span', 'cap-dot ' + cls));
    i.appendChild(document.createTextNode(text));
    return i;
  };
  legend.appendChild(item('cap-reserved', 'Reserved'));
  legend.appendChild(item('cap-sold', 'Sold'));
  legend.appendChild(item('cap-free', 'Free'));

  const container = el('div');
  container.appendChild(svg);
  container.appendChild(legend);
  return container;
}

function storageNodeRow(n) {
  const tr = el('tr');
  tr.appendChild(td(n.name || '-'));
  tr.appendChild(tdNode(el('span', 'badge ' + (n.role === 'primary' ? 'badge-blue' : 'badge-muted'),
    n.role || '?')));
  tr.appendChild(tdNode(el('span', 'badge ' + (n.online ? 'badge-green' : 'badge-red'),
    n.online ? 'Online' : 'Offline')));
  tr.appendChild(td(n.capacity_gb != null ? fmtGB(n.capacity_gb) : '-'));
  tr.appendChild(td(n.used_gb != null ? fmtGB(n.used_gb) : '-', 'dim'));
  tr.appendChild(td(n.source || '-', 'dim'));
  return tr;
}

async function loadStorageSummary() {
  showEl($('storage-loading'), true);
  $('storage-loading').className = 'loading';
  showEl($('storage-content'), false);
  try {
    const resp = await apiFetch(window.location.origin + '/api/network/storage');

    if (resp.available !== true) {
      const box = $('storage-loading');
      clear(box);
      box.className = 'loading';
      box.textContent = 'Storage network summary not available yet.';
      showEl(box, true);
      return;
    }

    const s = resp.network || {};

    $('st-nodes').textContent = (s.storage_nodes_online ?? '-') +
      (s.storage_nodes_total ? ' / ' + s.storage_nodes_total : '');
    $('st-best-effort').textContent = fmtGB(s.capacity_best_effort_gb);
    $('st-committable').textContent = fmtGB(s.capacity_committable_gb);
    $('st-reserved').textContent = fmtGB(s.reserved_stakeholders_gb);
    $('st-sold').textContent = fmtGB(s.sold_active_gb);
    $('st-free').textContent = fmtGB(s.free_sellable_gb);

    const src = $('storage-src');
    const util = s.utilization_pct;
    src.textContent = (util !== null && util !== undefined) ? (util + '% used') : 'live';
    src.className = 'section-badge badge ' + (s.oversubscribed ? 'badge-red' : 'badge-green');

    const barWrap = $('st-bar-wrap');
    clear(barWrap);
    barWrap.appendChild(capacityBar(
      s.reserved_stakeholders_gb, s.sold_active_gb, s.free_sellable_gb));

    const nodesBox = $('st-nodes-table');
    if (nodesBox && Array.isArray(resp.nodes)) {
      clear(nodesBox);
      resp.nodes.forEach((n) => nodesBox.appendChild(storageNodeRow(n)));
    }

    showEl($('storage-loading'), false);
    showEl($('storage-content'), true);
  } catch (e) {
    const box = $('storage-loading');
    clear(box);
    box.className = 'loading error';
    box.textContent = 'Failed to load storage network status: ' + e.message;
    showEl(box, true);
  }
}

document.querySelectorAll('nav button[data-view]').forEach((btn) => {
  btn.addEventListener('click', () => showView(btn.dataset.view));
});
$('logo').addEventListener('click', () => showView('explorer'));
$('search-btn').addEventListener('click', doSearch);
$('search-input').addEventListener('keydown', (e) => { if (e.key === 'Enter') doSearch(); });

setInterval(() => {
  if (!document.hidden) loadView(state.view);
}, 30000);

const networkSamples = {};
function networkState(d, chain, now) {
  if (!d || d.ok !== true || d.chain_id !== chain || d.role !== 'observer') return 'unavailable';
  const peers = Number(d.connected_peers ?? d.transport?.connected_peers ?? d.peer_count);
  if (d.reactor_running !== true || peers < 1) return 'degraded';
  const prior = networkSamples[chain];
  networkSamples[chain] = {height:d.finalized_height, at:now};
  const capabilitiesReported =
    typeof d.p2p_enabled === 'boolean' &&
    typeof d.spending_enabled === 'boolean' &&
    typeof d.mining_active === 'boolean';
  if (capabilitiesReported && d.p2p_enabled && d.spending_enabled && d.mining_active && peers > 0) return 'active';
  if (prior && now - prior.at <= 90000 && Number.isSafeInteger(d.finalized_height) && d.finalized_height < prior.height) return 'degraded';
  return 'online';
}
async function serviceAvailability() {
  const endpoints = [
    ['HVM mainnet observer', '/api/hashburst/hvm/mainnet/network', d => d.ok === true && d.network === 'mainnet' && d.chain_id === 4735489 && d.role === 'observer' && d.reactor_running === true && Number(d.peer_count) > 0],
    ['HVM testnet observer', '/api/hashburst/hvm/testnet/network', d => d.ok === true && d.network === 'testnet' && d.chain_id === 4735490 && d.role === 'observer' && d.reactor_running === true && Number(d.peer_count) > 0],
    ['DePIN master', '/api/hashburst/master/status', d => d.master_live === true],
    ['HB-TEP', '/api/tep/', d => d.app_ready === true && !!d.stats && Number(d.stats.peers_online) > 0],
    ['Sovereign Storage', '/api/network/storage', d => d.available === true && !!d.network && Number(d.network.storage_nodes_online) > 0]
  ];
  const legacyPromise = apiFetch('/api/hashburst/health').then(d => ({online:d.status === 'ok' && d.chainId === 1337,data:d})).catch(e => ({online:false,error:e.message}));
  const results = await Promise.all(endpoints.map(async ([name,path,valid]) => {
    try { const d = await apiFetch(path); return {name,online:valid(d),data:d}; }
    catch(e) { return {name,online:false,error:e.message}; }
  }));
  const legacy = await legacyPromise;
  const box = $('service-availability');
  clear(box);
  results.forEach(r => {
    let value = r.online ? 'Online' : 'Unavailable';
    if (r.name === 'HB-TEP' && r.online) value = 'Online, ' + r.data.stats.peers_online + ' / ' + r.data.stats.peers_total + ' peers';
    if (r.name === 'Sovereign Storage' && r.online) value = 'Online, ' + r.data.network.storage_nodes_online + ' / ' + r.data.network.storage_nodes_total + ' nodes';
    if (r.name === 'DePIN master' && r.online) value = 'Online, ' + Number(r.data.active_workers || 0) + ' workers';
    box.appendChild(el('div', null, r.name + ': ' + value));
  });
  for (const [id,index,chain,label] of [['mainnet',0,4735489,'Mainnet'],['testnet',1,4735490,'Testnet']]) {
    const r=results[index];
    const status=r.online ? networkState(r.data,chain,Date.now()) : 'unavailable';
    $(id+'-operational').textContent=label+' '+chain+' (mining, P2P and spending) status: '+status+'.';
    const d=r.data || {};
    const details=[];
    details.push('P2P: '+(typeof d.p2p_enabled==='boolean'?(d.p2p_enabled?'enabled':'disabled'):'not reported'));
    details.push('Mining: '+(typeof d.mining_active==='boolean'?(d.mining_active?'active':'inactive'):'not reported'));
    details.push('Spending: '+(typeof d.spending_enabled==='boolean'?(d.spending_enabled?'enabled':'disabled'):'not reported'));
    $(id+'-operational').title=details.join(', ');
  }
  let legacyStatus='unavailable';
  if (legacy.online) {
    const d=legacy.data;
    legacyStatus=(d.mining_enabled===true || d.p2p_enabled===true || d.spendable===true) ? 'active' : 'inactive';
  }
  $('legacy-operational').textContent='Chain legacy mode 1337 (mining, P2P and spending) status: '+legacyStatus+'.';
  box.appendChild(el('small', null, 'Checked ' + new Date().toISOString()));
}
$('tep-monitor-refresh').addEventListener('click',async()=>{
  const box=$('tep-monitor-result');box.textContent='Loading monitor...';
  try{box.textContent=JSON.stringify(await apiFetch('/api/hashburst/tep-monitor/status'),null,2)}
  catch(e){box.textContent='Transport monitor under maintenance: '+e.message}
});
serviceAvailability();
setInterval(() => { if (!document.hidden) serviceAvailability(); }, 30000);
(() => {
  'use strict';
  const root=document.getElementById('view-hvm'), el=id=>document.getElementById('hvm-'+id);
  const wsOrigin =
    (window.location.protocol === 'https:' ? 'wss://' : 'ws://') +
    window.location.host;
  const networks={
    mainnet:{chain:4735489,hex:'0x484201',name:'HashBurst HVM Mainnet',node:'hvm-mainnet-observer-01',network:'/api/hashburst/hvm/mainnet/network',rpc:'/api/hashburst/hvm/mainnet/evm',rpcPublic:'/api/hashburst/hvm/mainnet/evm',ws:wsOrigin+'/api/hashburst/hvm/mainnet/evm/ws',federation:'4 validators',apow:'Reported dynamically by network telemetry',note:'Mainnet network telemetry and RPC parameters are read live from the public services.'},
    testnet:{chain:4735490,hex:'0x484202',name:'HashBurst HVM Testnet',node:'hvm-testnet-ingress',network:'/api/hashburst/hvm/testnet/network',rpc:'/api/hashburst/hvm/testnet/evm',rpcPublic:'/api/hashburst/hvm/testnet/evm',ws:wsOrigin+'/api/hashburst/hvm/testnet/evm/ws',federation:'Separate testnet validator set',apow:'Reported dynamically by network telemetry',note:'Testnet is a separate signing domain and remains available for testing.'}
  };
  let selected='mainnet',previous={mainnet:null,testnet:null},sample=null,progress='first',busy=false,error='',rpcOK=false,sampledAt='';
  const words={unknown:'Not checked',first:'First sample; refresh to compare',advanced:'Advanced since previous sample',same:'No advancement since previous sample',decreased:'Height decreased: investigate',unavailable:'Unavailable',running:'Running',stopped:'Stopped',stale:'Detailed status unavailable',activated:'Activation height finalized',pending:'Pending',time:'Last sample',active:'ACTIVE'};
  function cfg(){return networks[selected]}

  async function addOrSwitchWalletNetwork(){
    const status=el('wallet-status');
    const c=cfg();

    if(!window.ethereum || typeof window.ethereum.request !== 'function'){
      status.textContent='MetaMask / EIP-1193 wallet not detected.';
      return;
    }

    const rpcUrl=new URL(c.rpcPublic,window.location.origin).href;

    const params={
      chainId:c.hex,
      chainName:c.name,
      nativeCurrency:{
        name:'HashBurst Token',
        symbol:'HBT',
        decimals:18
      },
      rpcUrls:[rpcUrl]
    };

    status.textContent='Waiting for wallet confirmation...';

    try{
      try{
        await window.ethereum.request({
          method:'wallet_switchEthereumChain',
          params:[{chainId:c.hex}]
        });
      }catch(switchError){
        if(
          switchError &&
          (switchError.code===4902 ||
           switchError.code===-32603)
        ){
          await window.ethereum.request({
            method:'wallet_addEthereumChain',
            params:[params]
          });
        }else{
          throw switchError;
        }
      }

      status.textContent=c.name+' selected in wallet.';
    }catch(e){
      status.textContent=
        'Wallet request not completed: '+
        (e && e.message ? e.message : 'unknown error');
    }
  }

  function render(){
    const c=cfg();
    for(const n of ['mainnet','testnet']){const b=el('connect-'+n),on=selected===n;b.className='btn'+(on?' active':'');}
    el('network').textContent=c.name;el('chain').textContent=c.chain+' ('+c.hex+')';el('federation').textContent=c.federation;el('apow').textContent=c.apow;
    el('rpc-url').textContent=c.rpcPublic;el('ws-url').textContent=c.ws;el('network-note').textContent=c.note;
    el('status').textContent=error?words.unavailable+': '+error:sample?(selected==='mainnet'?'Mainnet':'Testnet')+' observer responding':words.unknown;
    el('time').textContent=sampledAt?words.time+': '+sampledAt:'';
    for(const id of ['live','height','finalized','progress','peers','reactor','activation','rpc']) el(id).textContent=error?words.unavailable:words.unknown;
    if(!sample||error)return;
    el('live').textContent=sample.ok===true&&sample.reactor_running===true?words.active:words.unavailable;
    el('height').textContent=String(sample.height);el('finalized').textContent=String(sample.finalized_height);el('peers').textContent=String(sample.connected_peers ?? sample.transport?.connected_peers ?? sample.peer_count);el('progress').textContent=words[progress];
    el('apow').textContent=typeof sample.mining_active==='boolean'?(sample.mining_active?'Active':'Inactive'):(sample.apow_enabled===true?'Enabled, activity not reported':'Not reported');
    el('reactor').textContent=sample.reactor_status_fresh&&sample.reactor?(sample.reactor_running?words.running:words.stopped)+' / '+sample.reactor.height+' / '+sample.reactor.round+' / '+sample.reactor.step:words.stale;
    el('activation').textContent=sample.evm_activation_height?(sample.finalized_height>=sample.evm_activation_height?words.activated:words.pending)+' ('+sample.evm_activation_height+')':words.unknown;
    el('rpc').textContent=rpcOK?(selected==='mainnet'?'Mainnet':'Testnet')+' RPC responding':words.unavailable;
  }
  async function refresh(){
    if(busy)return;busy=true;el('refresh').disabled=true;const c=cfg(),requested=selected;
    try{
      const r=await fetch(c.network,{cache:'no-store',signal:AbortSignal.timeout(15000)});if(!r.ok)throw Error('HTTP '+r.status);const d=await r.json();
      if(d.ok!==true||d.chain_id!==c.chain||d.network!==requested||d.role!=='observer'||d.node_id!==c.node||!Number.isSafeInteger(d.height)||!Number.isSafeInteger(d.finalized_height)||!Number.isSafeInteger(d.peer_count)||d.peer_count<0)throw Error('network/identity');
      progress=previous[requested]===null?'first':d.finalized_height>previous[requested]?'advanced':d.finalized_height===previous[requested]?'same':'decreased';
      previous[requested]=d.finalized_height;if(selected!==requested)return;sample=d;error='';sampledAt=new Date().toISOString();rpcOK=false;
      try{const rr=await fetch(c.rpc,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({jsonrpc:'2.0',id:1,method:'eth_chainId',params:[]}),signal:AbortSignal.timeout(8000)});const v=rr.ok?await rr.json():null;rpcOK=!!v&&!v.error&&v.result===c.hex;}catch(_){}
    }catch(e){if(selected===requested){error=e.message;sample=null;previous[requested]=null;}}
    finally{busy=false;el('refresh').disabled=false;render();}
  }
  function select(n){if(!networks[n]||busy)return;selected=n;sample=null;error='';sampledAt='';rpcOK=false;render();refresh();}
  document.getElementById('nav-hvm').addEventListener('click',refresh);el('refresh').addEventListener('click',refresh);
  el('connect-mainnet').addEventListener('click',()=>select('mainnet'));el('connect-testnet').addEventListener('click',()=>select('testnet'));
  el('wallet-add').addEventListener('click',addOrSwitchWalletNetwork);
  setInterval(()=>{if(root.classList.contains('active')&&!document.hidden)refresh();},30000);render();
})();
if (location.hash && document.getElementById('nav-' + location.hash.slice(1))) document.getElementById('nav-' + location.hash.slice(1)).click(); else loadView('explorer');
</script>
</body>
</html>
