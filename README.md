# Military-Grade Encryption 🔐 | GIUCTF 2025 | Crypto Challenge 

> *“Absolutely unbreakable. Trust us.”*  
> — Anonymous dev, minutes before the CTF went live

[![status](https://img.shields.io/badge/CTF-archive-blue)]() 
[![difficulty](https://img.shields.io/badge/difficulty-Hard-red)]() 
[![points](https://img.shields.io/badge/points-300-ff69b4)]()

---

## Story
*This challenge was originally devised by **Ali Sherif (https://www.linkedin.com/in/ali-sherif-13812b276/)** for the
official **GIUCTF 2025** competition.*

During a red-team audit, investigators stumbled across an internal SSO portal
boasting **traditional military-grade en|crypt|ion**.  
A forgotten backup-file in the webroot told a different story…

Players had to:

1. **Forge an admin cookie** to read the first flag.
2. **Extract the master encryption key** baked into every cookie block for the second flag.

---

## Repository Layout

```text
web/
├── index.php          ← live endpoint
├── index.php.bak      ← leaked backup (intentional)
├── helper.php         ← server-side crypto helpers
├── config.php         ← flags + secret key
└── README.txt         ← rules shown in-game
Dockerfile             ← PHP-8.1-apache image
```
## Vulnerabilities at a Glance 🔍
| Issue                                                | CWE | Why it matters                                         |
|------------------------------------------------------|-----|--------------------------------------------------------|
| Same 2-byte **DES salt** reused per cookie           | 330 | identical blocks ⇒ identical hashes                   |
| **crypt()** hashes only the **first 8 bytes**        | 327 | padding/oracle lets you target 1 byte at a time       |
| Backup code disclosure (`*.bak`)                     | 538 | reveals payload layout and crypto comments            |
| User-controlled **User-Agent** in payload            | 807 | precise alignment of unknown key bytes                |

---

## Flags
| Phase | Flag                                   |
|-------|----------------------------------------|
| 1     | `GIUCTF{N0T_S0_S3CUR3_AFT3RALL}`        |
| 2     | `GIUCTF{1_byt3_brut3_f0rc3_ftw}`       |

---

<details>
<summary>🛠️ <b>Solution Outline (SPOILERS)</b></summary>

### Leak the code  
`GET /index.php.bak` → shows payload `{$session}::{$ua}::{$key}` and comment that DES-crypt looks at 8 bytes.

### Understand the chunk layout  
Cookie = `crypt(chunk₀)‖crypt(chunk₁)…` (each 13 chars).  
Salt = first 2 chars of the cookie.

### Phase 1 – 1-byte collision  
* Control first 8-byte block (`guest::X`).  
* Brute printable `c` with same salt so `crypt("admin::c") == chunk₀`.  
* Splice new chunk₀ + old chunks → log in as **admin** → flag 1.

### Phase 2 – recover 24-byte `ENC_KEY`  
For each byte *i* (0 .. 23):  
1. Pad UA with `"A"*(7 − i mod 8)` so `key[i]` is at offset 7.  
2. Grab cookie (captures salt & target chunk).  
3. Test 95 printable chars until hash matches ⇒ `key[i]`.  

Total work: 24 × 95 = 2 280 DES hashes – <1 s script.

### Forge final admin cookie  
Use recovered key with chosen salt, recalc all chunks, send cookie ⇒ proof of key ⇒ flag 2.

</details>

---

## Run Locally 🖥️
```bash
git clone https://github.com/alimezar/military-grade-enc.git
cd military-grade-enc
docker build -t mge .
docker run -p 8080:80 mge
# → browse http://localhost:8080
