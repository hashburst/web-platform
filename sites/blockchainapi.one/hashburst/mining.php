<?php
// public/hashburst/mining.php
// Separate page from the network explorer (index.php). Shows external-pool
// mining infrastructure (PoW on third-party coins), not HashBurst consensus.
//
// SICUREZZA:
//
// 1. Nessun dato proveniente dalle pool esterne (nomi worker, coin, note) viene
//    salvato. I nomi worker sono stringhe scelte dall'utente e registrate su 
//    servizi di terze parti.
//
// 2. Content-Security-Policy con nonce per richiesta: anche in caso di
//    injection, nessuno script inline non autorizzato viene eseguito.
//
// La credenziale (signature) NON persistente di default: vedi il blocco
// "credential handling" nel JS.

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
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>HashBurst — Mining Operations</title>
<style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
:root{--bg:#0D1117;--surface:#161B22;--border:#30363D;--amber:#D9822B;
--text:#E6EDF3;--muted:#8B949E;--danger:#e85149}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:-apple-system,sans-serif}
header{background:var(--surface);border-bottom:1px solid var(--border);
padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
nav a{color:var(--muted);text-decoration:none;margin-left:16px;font-size:13px}
nav a.active{color:var(--amber);font-weight:600}
main{max-width:900px;margin:0 auto;padding:24px}
.warn{background:rgba(217,130,43,.12);border:1px solid rgba(217,130,43,.35);
border-radius:10px;padding:14px 18px;font-size:13px;color:#E8B47A;margin-bottom:16px}
.warn a{color:#E8B47A}
.warn p{margin-bottom:8px}
.warn p:last-child{margin-bottom:0}
.card{background:var(--surface);border:1px solid var(--border);border-radius:12px;
padding:18px;margin-bottom:16px}
.pool-name{font-weight:700;font-size:15px;text-transform:uppercase;color:var(--amber)}
.row{display:flex;justify-content:space-between;gap:16px;padding:6px 0;font-size:13px;
border-bottom:1px solid var(--border)}
.row:last-child{border-bottom:none}
.row span:last-child{text-align:right;word-break:break-all}
.muted{color:var(--muted)}
.sites{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}
.site-tag{background:var(--surface);border:1px solid var(--border);border-radius:20px;
padding:4px 12px;font-size:11px;color:var(--muted)}
.search-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;
padding:18px;margin-top:24px}
.search-card h3{font-size:14px;margin-bottom:12px;color:var(--text)}
.search-card input[type=text],.search-card input[type=password]{width:100%;background:#0D1117;
border:1px solid var(--border);border-radius:8px;padding:8px 10px;color:var(--text);
font-size:13px;margin-bottom:8px;font-family:ui-monospace,monospace}
.search-card button{background:var(--amber);border:none;border-radius:8px;
padding:8px 16px;font-size:13px;font-weight:600;color:#1a1206;cursor:pointer}
.search-card button.secondary{background:transparent;border:1px solid var(--border);
color:var(--muted);font-weight:400;margin-left:8px}
.search-card button:hover{opacity:.9}
.search-hint{font-size:11px;color:var(--muted);margin-bottom:12px;line-height:1.5}
.remember{display:flex;align-items:center;gap:8px;font-size:11px;color:var(--muted);
margin:4px 0 12px}
.worker-row{display:flex;justify-content:space-between;gap:16px;padding:6px 0;
font-size:12px;border-bottom:1px solid var(--border)}
.worker-row:last-child{border-bottom:none}
.worker-row .wname{font-family:ui-monospace,monospace;word-break:break-all}
.search-error{color:var(--danger);font-size:12px;margin-top:8px}
.search-meta{font-size:11px;color:var(--muted);margin:8px 0}
.notice{font-size:11px;color:#E8B47A;margin-top:8px}
</style>
</head>
<body>
<header>
  <div style="font-weight:700">HashBurst</div>
  <nav>
    <a href="/hashburst/">Network (P2P/TEP)</a>
    <a href="/hashburst/mining.php" class="active">Mining Operations</a>
  </nav>
</header>
<main>
  <div class="warn">
    <p>This section shows <strong>external-pool mining infrastructure
    (PoW on third-party coins)</strong> operated by the node operator. It is
    not part of HashBurst's P2P/TEP consensus network (PoH), shown instead
    on the <a href="/hashburst/">Network</a> page.</p>
    <p id="apiDisclaimer" class="muted"></p>
  </div>

  <div id="content">Loading…</div>

  <div class="sites" id="sites"></div>

  <div class="search-card">
    <h3>Your workers (owner access)</h3>
    <div class="search-hint">
      Enter the apikey and signature assigned to your node/stakeholder record.
      This returns <strong>only the workers registered under your own apikey</strong> —
      worker names are user identifiers, so no other operator's workers are ever
      returned, with or without a filter.
      <br><br>
      Credentials are sent only to the aggregator endpoint and are never stored
      on this server. By default they are not stored in your browser either.
    </div>
    <input type="text" id="wsApiKey" placeholder="Your apikey" autocomplete="off" spellcheck="false">
    <input type="password" id="wsSignature" placeholder="Your signature" autocomplete="off" spellcheck="false">
    <input type="text" id="wsQuery" placeholder="Optional filter on your own workers (leave empty for all)" autocomplete="off" spellcheck="false">
    <label class="remember">
      <input type="checkbox" id="wsRemember">
      Keep credentials for this browser tab only (cleared when the tab closes)
    </label>
    <button id="wsSearchBtn">Show my workers</button>
    <button id="wsClearBtn" class="secondary">Clear credentials</button>
    <div id="wsNotice"></div>
    <div id="wsResults"></div>
  </div>
</main>
<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
'use strict';

const $ = (id) => document.getElementById(id);

// --- helper di rendering ------------------------------------------------
// Unico punto in cui si costruisce DOM. Nessuna interpolazione in innerHTML:
// nomi worker, coin e note arrivano da API di pool esterne.

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = String(text);
  return node;
}

function clear(node) {
  while (node.firstChild) node.removeChild(node.firstChild);
}

function addRow(parent, label, value) {
  const row = el('div', 'row');
  row.appendChild(el('span', 'muted', label));
  row.appendChild(el('span', null, value));
  parent.appendChild(row);
}

function fmtHashrate(v) {
  if (v === null || v === undefined) return 'n/a';
  const n = Number(v);
  return Number.isFinite(n) ? n.toLocaleString() + ' H/s' : 'n/a';
}

// --- dashboard aggregata ------------------------------------------------

function poolCard(p) {
  const card = el('div', 'card');
  card.appendChild(el('div', 'pool-name', p.pool || '—'));
  addRow(card, 'Coin', p.coin || '—');

  // Zpool non espone hashrate per questo wallet: la metrica disponibile sono le share. 
  const hasHashrate = p.hashrate_hs !== null && p.hashrate_hs !== undefined;
  const hasShares = p.accepted_shares !== null && p.accepted_shares !== undefined;

  if (hasHashrate) addRow(card, 'Hashrate', fmtHashrate(p.hashrate_hs));

  if (hasShares) {
    const accepted = Number(p.accepted_shares) || 0;
    const rejected = Number(p.rejected_shares) || 0;
    addRow(card, 'Shares accepted', accepted.toLocaleString());
    addRow(card, 'Shares rejected', rejected.toLocaleString());
    const submitted = accepted + rejected;
    if (submitted > 0) {
      addRow(card, 'Reject rate', (rejected / submitted * 100).toFixed(2) + '%');
    }
  }

  if (!hasHashrate && !hasShares) addRow(card, 'Hashrate', 'n/a');

  if (p.unpaid !== undefined && p.unpaid !== null) addRow(card, 'Unpaid', p.unpaid);
  if (p.balance !== undefined && p.balance !== null) addRow(card, 'Balance', p.balance);
  if (p.active_workers !== undefined && p.active_workers !== null) {
    const w = (p.total_workers !== undefined && p.total_workers !== null)
      ? p.active_workers + ' / ' + p.total_workers
      : String(p.active_workers);
    addRow(card, 'Workers', w);
  }
  if (p.note) {
    const note = el('div', 'row muted');
    note.appendChild(el('span', null, p.note));
    card.appendChild(note);
  }
  return card;
}

async function load() {
  const c = $('content');
  try {
    const r = await fetch('/api/hashburst/mining/summary');
    const d = await r.json();

    // API disclaimer
    $('apiDisclaimer').textContent = d.disclaimer || '';

    clear(c);
    if (!d.pools || !d.pools.length) {
      c.appendChild(el('div', 'card muted', 'No mining data available at the moment.'));
      return;
    }
    d.pools.forEach((p) => c.appendChild(poolCard(p)));

    const sites = $('sites');
    clear(sites);
    (d.sites_declared || []).forEach((s) => sites.appendChild(el('span', 'site-tag', s)));
  } catch (e) {
    clear(c);
    c.appendChild(el('div', 'card muted', 'Loading error: ' + e.message));
  }
}

load();
setInterval(load, 60000);

// --- credential handling --------------------------------------------------------------------------------
// Signature: credenziale di autenticazione non persistente nel localStorage per evitare esposizione a XSS. 
// Default: nessuna persistenza. Opt-in esplicito: sessionStorage, si distrugge alla chiusura della tab.

const CRED_KEYS = { apikey: 'hb_ws_apikey', signature: 'hb_ws_signature' };

(function purgeLegacyLocalStorage() {
  // Rimozione dal browser di apikey e signature in localStorage se salvate usando le precedenti versioni. 
  const hadLegacy = localStorage.getItem(CRED_KEYS.apikey) || localStorage.getItem(CRED_KEYS.signature);
  const legacyApikey = localStorage.getItem(CRED_KEYS.apikey);
  localStorage.removeItem(CRED_KEYS.apikey);
  localStorage.removeItem(CRED_KEYS.signature);
  if (hadLegacy) {
    if (legacyApikey) $('wsApiKey').value = legacyApikey;
    $('wsNotice').appendChild(el('div', 'notice',
      'Credentials previously saved by this page have been removed from persistent ' +
      'browser storage. Re-enter your signature; it is no longer stored unless you opt in.'));
  }
})();

(function restoreSessionCreds() {
  const k = sessionStorage.getItem(CRED_KEYS.apikey);
  const s = sessionStorage.getItem(CRED_KEYS.signature);
  if (k) $('wsApiKey').value = k;
  if (s) { $('wsSignature').value = s; $('wsRemember').checked = true; }
})();

function persistCreds(apiKey, signature) {
  if ($('wsRemember').checked) {
    sessionStorage.setItem(CRED_KEYS.apikey, apiKey);
    sessionStorage.setItem(CRED_KEYS.signature, signature);
  } else {
    sessionStorage.removeItem(CRED_KEYS.apikey);
    sessionStorage.removeItem(CRED_KEYS.signature);
  }
}

function clearCreds() {
  sessionStorage.removeItem(CRED_KEYS.apikey);
  sessionStorage.removeItem(CRED_KEYS.signature);
  localStorage.removeItem(CRED_KEYS.apikey);
  localStorage.removeItem(CRED_KEYS.signature);
  $('wsApiKey').value = '';
  $('wsSignature').value = '';
  $('wsRemember').checked = false;
  clear($('wsResults'));
  clear($('wsNotice'));
  $('wsNotice').appendChild(el('div', 'notice', 'Credentials cleared from this browser.'));
}

// --- ricerca worker -----------------------------------------------------

function workerRow(w) {
  const row = el('div', 'worker-row');

  const left = el('span', null);
  left.appendChild(el('span', 'wname', w.name));
  const meta = ' (' + (w.pool || '?') + '/' + (w.coin || '?') + ')';
  left.appendChild(el('span', 'muted', meta));
  row.appendChild(left);

  let right = '—';
  if (w.hashrate_hs !== null && w.hashrate_hs !== undefined) {
    right = fmtHashrate(w.hashrate_hs);
  } else if (w.accepted_shares !== null && w.accepted_shares !== undefined) {
    right = w.accepted_shares + ' shares';
  }
  row.appendChild(el('span', null, right));
  return row;
}

async function searchWorkers() {
  const apiKey = $('wsApiKey').value.trim();
  const signature = $('wsSignature').value.trim();
  const query = $('wsQuery').value.trim();
  const results = $('wsResults');

  clear($('wsNotice'));
  clear(results);

  // Senza filtro l'endpoint restituisce tutti e soli i worker del chiamante.
  if (!apiKey || !signature) {
    results.appendChild(el('div', 'search-error', 'apikey and signature are required.'));
    return;
  }

  persistCreds(apiKey, signature);
  results.appendChild(el('div', 'muted', 'Loading…'));

  try {
    const url = '/api/hashburst/workers/search' +
      (query ? '?q=' + encodeURIComponent(query) : '');
    const r = await fetch(url, {
      headers: { 'X-Api-Key': apiKey, 'X-Signature': signature }
    });

    let d;
    try {
      d = await r.json();
    } catch (parseErr) {
      clear(results);
      results.appendChild(el('div', 'search-error',
        r.status === 503
          ? 'Rate limited by the gateway (HTTP 503). Wait a few seconds and retry.'
          : 'Unexpected response from the server (HTTP ' + r.status + ').'));
      return;
    }

    clear(results);

    if (!r.ok) {
      results.appendChild(el('div', 'search-error', d.error || ('HTTP ' + r.status)));
      return;
    }

    const meta = query
      ? 'Showing ' + d.count + ' of your ' + d.owned_total + ' worker(s) matching the filter.'
      : 'You own ' + d.owned_total + ' worker(s).';
    results.appendChild(el('div', 'search-meta', meta));

    if (!d.workers || !d.workers.length) {
      results.appendChild(el('div', 'muted',
        query
          ? 'None of your workers match that filter.'
          : 'No workers are currently registered under your apikey.'));
      return;
    }

    d.workers.forEach((w) => results.appendChild(workerRow(w)));
  } catch (e) {
    clear(results);
    results.appendChild(el('div', 'search-error', 'Request failed: ' + e.message));
  }
}

// Listener registrati e non con onclick inline: con la CSP attiva gli handler inline non verrebbero eseguiti.
$('wsSearchBtn').addEventListener('click', searchWorkers);
$('wsClearBtn').addEventListener('click', clearCreds);
$('wsQuery').addEventListener('keydown', (e) => { if (e.key === 'Enter') searchWorkers(); });
$('wsSignature').addEventListener('keydown', (e) => { if (e.key === 'Enter') searchWorkers(); });
</script>
</body>
</html>
