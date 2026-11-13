# HashBurst Web Platform

Web presentation and deployment code for HashBurst public sites.

This repository does not implement the HVM/HBT protocol, consensus, ledger, validator operation, APoW, genesis, chain economics, or private-key management. Those functions belong to the blockchain and related runtime repositories.

## Repository model

A component is a bounded reusable web unit. It can be embedded in a site without owning that site's application structure. Components may consume public APIs and must treat returned network state as external data. The HVM network status panel used by blockchainapi.one is a component.

A module is a native application unit owned by a HashBurst site. A module can define site navigation, page flow, application state, and integration with other native modules. Modules intended for hashburst.io belong under modules.

Third-party or separately operated sites such as blockchainapi.one integrate HashBurst components through site-specific deployment code under deploy. The host site remains responsible for its web server, routing, TLS, CSP, and surrounding application.

HashBurst-native sites such as hashburst.io may use both components and native modules. Site modules must not be copied into the blockchain protocol repository.

## Layout

    components/    Reusable web components
    modules/       Native HashBurst site modules
    deploy/        Site-specific deployment and web-server integration
    tests/         Cross-component or repository-level tests

## Boundaries

Public API clients and presentation logic belong here.

Protocol implementation and node runtime code do not belong here.

Web-server routes that expose an existing public observer or gateway may be maintained here when they exist only to support a web property. The underlying observer, RPC, EVM gateway, consensus service, and network configuration remain outside this repository.

No private key, validator credential, founder credential, administrative RPC secret, or signing operation is permitted in this repository.
