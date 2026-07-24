---
name: web3-crypto-integration
description: Use when integrating with blockchain/crypto APIs or building Web3 features — wallet connections, reading on-chain data, and token/market data integration. Not financial advice; technical integration only.
---

# Web3 / Crypto Integration

## Wallet connections

- Use an established wallet-connection library (RainbowKit, Web3Modal, wagmi) rather than hand-rolling provider detection and connection logic — wallet behavior across MetaMask, WalletConnect, Coinbase Wallet, etc. has enough edge cases that a maintained abstraction is worth the dependency.
- Always let the user explicitly initiate the connection (a button click) — never auto-connect to a wallet without user action, both for UX and because some wallets/browsers block silent connection attempts.
- Handle network/chain mismatches explicitly: detect the connected chain and prompt a network switch rather than silently failing transactions built for the wrong chain.

## Reading on-chain / market data

- Use a dedicated RPC provider (Alchemy, Infura, or a public node with documented rate limits) rather than assuming an arbitrary public endpoint has production-grade uptime.
- Cache read-heavy on-chain queries (token balances, prices) with a sensible TTL — blockchain state doesn't need to be re-queried on every render, and public RPC/API endpoints often rate-limit aggressively.
- When surfacing price or market-cap data, cite the data source and timestamp in the UI — crypto prices are volatile enough that unlabeled, stale data misleads users.

## Transactions

- Simulate or estimate gas before submitting a transaction and surface the estimated cost to the user — an unexpected gas fee is one of the most common sources of user complaints in Web3 UX.
- Show clear transaction status (pending, confirmed, failed) tied to the actual transaction hash, and let users track it on a block explorer — don't just show a generic spinner with no way to verify what's happening on-chain.
- Never construct or sign a transaction with a hardcoded private key in application code; production signing belongs in the user's own wallet or a properly secured signing service.

## Security

- Treat any contract-interaction code as security-critical: verify contract addresses against an authoritative source (official docs, verified block-explorer listing) before hardcoding them, since a wrong address can send funds to an attacker's contract.
- If the project handles real user funds, get a professional audit before mainnet deployment — this domain has an unusually high cost for undiscovered bugs.

## Scope note

This skill covers technical integration only — it is not investment guidance, and nothing here should be read as a recommendation to buy, sell, or hold any asset.
