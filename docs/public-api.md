# HashBurst Public API

**Base URL:** `https://blockchainapi.one`
**Version:** experimental — no stability guarantees
**Last reviewed:** 2026-07-17

---

## Read this before you build on it

HashBurst is experimental software under active development. This API is served
by **a single node**. It is not a distributed network endpoint, there is no
redundancy, no uptime target, and no failover. If this node is down, the API is
down.

Endpoints, field names and response shapes **can change without notice or
deprecation period**. Do not build anything you cannot afford to have break.

Figures returned by these endpoints describe the state of this node, not the
state of a network. Where an endpoint reports peer counts, read the numbers
rather than the labels: a field named `peers_total` counts configured entries,
not established connections.

---

## Conventions

| | |
|---|---|
| Transport | HTTPS only. HTTP requests are redirected. |
| Method | `GET` for every endpoint below. |
| Content type | `application/json` |
| Authentication | None, except `/api/hashburst/workers/search`. |
| CORS | `Access-Control-Allow-Origin: *` on the mining endpoints. Verify per endpoint before relying on it from a browser. |
| Timestamps | Unix epoch seconds, UTC. |
| Amounts | Decimal strings or numbers depending on the upstream source. Parse defensively; do not assume a numeric type. |

### Rate limits

Enforced by the gateway, per client IP:

| Scope | Limit | Burst | On excess |
|---|---|---|---|
| Most endpoints | 30 requests/minute | 20 | `503` |
| `/api/hashburst/workers/search` | 10 requests/minute | 5 | `503` |

`/api/hashburst/workers/search` additionally enforces, per authenticated apikey,
20 requests/minute (`429`), and blocks an IP after 10 failed authentication
attempts in 5 minutes (`429`).

A `503` is a rate limit, not an outage. Back off and retry.

### Errors

Errors are returned as JSON with an `error` field:

```json
{ "error": "invalid apikey or signature" }
```

| Code | Meaning |
|---|---|
| `400` | Malformed request |
| `401` | Missing authentication headers |
| `403` | Invalid apikey or signature |
| `429` | Rate limit (application level) |
| `503` | Rate limit (gateway level) |

---

## Chain endpoints

### `GET /api/hashburst/health`

Node liveness and chain position.

```bash
curl -s "/api/hashburst/health"
```

Fields: `blockHeight`, `chainId`, `peerID`, `node`.

### `GET /api/hashburst/status`

Node status, including the number of connected P2P peers.

```bash
curl -s "/api/hashburst/status"
```

Fields: `peers`, `peerID`.

> `peers` is the count of **established** connections. At the time of writing it
> is `0`: this node has no P2P peers.

### `GET /api/hashburst/blocks`

Recent blocks, oldest first. Returns an array.

```bash
curl -s "/api/hashburst/blocks" | python3 -m json.tool
```

Per element: `number`, `hash`, `parentHash`, `transactions` (count), `timestamp`, `miner`.

> This endpoint returns a recent window, not the full chain, and takes no
> pagination parameters. There is no way to page back through history.

### `GET /api/hashburst/transactions`

Recent transactions. Returns an array.

```bash
curl -s "/api/hashburst/transactions" | python3 -m json.tool
```

Per element: `hash`, `from`, `to`, `value`, `tx_type`.

`tx_type` values seen so far: `NODE_REGISTRATION`, and transfers. A `from` of
`System` indicates a block reward.

### `GET /api/hashburst/nodes`

Nodes registered on-chain via `NODE_REGISTRATION` transactions. This is the
"blockchain DNS" registry: address records published to the ledger instead of an
external DNS server.

```bash
curl -s "/api/hashburst/nodes" | python3 -m json.tool
```

Per element: `node_id`, `peer_id`, `chain_id`, `external_ip`, `tep_port`,
`tep_pubkey`, `rpc_endpoint`, `multiaddrs[]`, `timestamp`, `version`.

> A registration record means a node announced itself. It does **not** mean the
> node is reachable, running, or has ever connected to anything.

### `GET /api/hashburst/storage`

Ledger size on disk.

```bash
curl -s "/api/hashburst/storage"
```

Fields: `size_bytes`, `size_mb`.

### `GET /api/tep/`

Status of HB-TEP, the encrypted transport layer: X25519 ECDH key agreement,
AES-256-GCM authenticated encryption, peer addresses resolved on-chain.

```bash
curl -s "/api/tep/" | python3 -m json.tool
```

Fields: `node_id`, `pubkey`, `crypto_mode`, `dns_source`, `peers[]`, `stats{}`.

- `peers[]`: `id`, `ip`, `port`, `online`, `last_seen`, `latency_ms`
- `stats{}`: `peers_online`, `peers_total`, `pkts_sent`, `pkts_recv`, `pkts_dropped`, `uptime_sec`, `listen_ip`, `listen_port`

> `peers_total` counts entries in the peer configuration. `peers_online` counts
> peers that have actually answered. These are different numbers and the gap
> between them is meaningful.
>
> The cryptographic construction uses standard, publicly specified primitives.
> The implementation is experimental and has not been independently audited. Do
> not rely on it to protect anything that matters.

---

## Mining endpoints

These cover mining on **external pools** — proof-of-work on third-party coins,
performed by the node operator's hardware. This is not HashBurst consensus (PoH)
and is unrelated to the chain endpoints above.

### `GET /api/hashburst/mining/summary`

Public. Per-pool aggregates. No worker identifiers, no IP addresses, no
hostnames.

```bash
curl -s "/api/hashburst/mining/summary" | python3 -m json.tool
```

```json
{
  "generated_at": 1784246211,
  "disclaimer": "...",
  "sites_declared": [],
  "pools": [
    {
      "pool": "viabtc",
      "coin": "LTC",
      "hashrate_hs": 15730010.0,
      "active_workers": 771,
      "total_workers": 1044
    },
    {
      "pool": "zpool",
      "coin": "DOGE",
      "hashrate_hs": null,
      "active_workers": 12,
      "accepted_shares": 158234.0,
      "rejected_shares": 412.0,
      "unpaid": "1461.50739559",
      "balance": "1393.95057799"
    }
  ]
}
```

Field availability **differs per pool**, because the upstream APIs differ:

| Field | Meaning | Notes |
|---|---|---|
| `pool` | Pool identifier | `zpool`, `viabtc`, `nanopool`, `nicehash` |
| `coin` | Coin mined on that pool | |
| `hashrate_hs` | Aggregate hashrate, H/s | `null` where the pool does not publish it (zpool) |
| `active_workers` | Workers currently contributing | |
| `total_workers` | Workers known to the pool | Absent where the pool reports only active ones |
| `accepted_shares` | Accepted shares, cumulative | Only where the pool exposes shares instead of hashrate |
| `rejected_shares` | Rejected shares, cumulative | Pair with `accepted_shares` to compute a reject rate |
| `unpaid` | Pending balance | Decimal string |
| `balance` | Confirmed balance | Decimal string |
| `note` | Free-text note from the pool | Optional |

Handle missing fields rather than assuming them. `hashrate_hs: null` on zpool is
correct, not an error: that pool exposes share counts, which are provided
instead.

Cache: recomputed every 60 seconds. `generated_at` tells you how stale the
snapshot is. Polling faster than that returns the same data and burns your rate
limit.

### `GET /api/hashburst/workers/search`

**Authenticated. Returns only the workers owned by the caller.**

Worker names on the external pools are the stakeholder's own apikey — they are
stable user identifiers, not anonymous infrastructure labels. This endpoint
therefore has no global search: an authenticated caller sees their own workers
and nothing else. The `q` parameter filters within that set; it cannot widen it.

**Headers**

| Header | Value |
|---|---|
| `X-Api-Key` | Your apikey, as assigned in your stakeholder record |
| `X-Signature` | The signature paired with that apikey |

**Parameters**

| Name | Required | Description |
|---|---|---|
| `q` | No | Case-insensitive substring filter over your own worker names. Omit for all of them. |

```bash
# All of your workers
curl -s "/api/hashburst/workers/search" \
  -H "X-Api-Key: <your-apikey>" \
  -H "X-Signature: <your-signature>" | python3 -m json.tool

# Filtered
curl -s -G --data-urlencode "q=vm3" \
  "/api/hashburst/workers/search" \
  -H "X-Api-Key: <your-apikey>" \
  -H "X-Signature: <your-signature>" | python3 -m json.tool
```

```json
{
  "scope": "self",
  "scope_note": "Worker names are user identifiers (your API key). This endpoint returns only workers owned by the authenticated caller.",
  "generated_at": 1784246211,
  "query": null,
  "owned_total": 1,
  "count": 1,
  "workers": [
    {
      "name": "<your-apikey>",
      "pool": "viabtc",
      "coin": "LTC",
      "hashrate_hs": 13981.0,
      "active": true
    }
  ]
}
```

`owned_total` is how many workers you own; `count` is how many matched `q`.
Without `q` they are equal.

**Worker naming.** A worker is attributed to you when its name is your apikey,
optionally followed by a separator (`.` `_` `-` `:` `/` `@` `|`) and a machine
suffix:

```
<apikey>              -> yours
<apikey>_vm3          -> yours
<apikey>.rig01        -> yours
pgxsrv01              -> attributed to nobody, returned to nobody
```

A worker whose name does not start with a registered apikey is invisible to
every caller, including whoever runs it. Name your workers accordingly.

**Not an error:** an empty `workers` array with `owned_total: 0` means no worker
is currently registered under your apikey on any pool the aggregator polls.

---

## Known gaps

Documented because they affect what you get back, not because they are
acceptable:

- Worker-level coverage is currently incomplete for some pools. Aggregate counts
  in `/mining/summary` may exceed the number of workers actually retrievable via
  `/workers/search`. If you own workers on those pools, they may not appear yet.
- `/blocks` and `/transactions` return a recent window with no pagination.
- Single node: see the top of this document.

---

## Support

Issues: <https://github.com/hashburst>

This document describes the API as verified on the date at the top. If an
endpoint behaves differently from what is written here, the document is wrong —
report it.
