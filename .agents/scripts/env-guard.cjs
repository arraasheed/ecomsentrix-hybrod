#!/usr/bin/env node
/*
 * .agents/scripts/env-guard.cjs — ADMIN-NAV-FIX .env guard
 *
 * WHY THIS EXISTS
 * The .env cache/session drivers kept ending up as `file` — either never set
 * (CLAUDE-9 only edited .env.example) or silently reverted by a parallel
 * model session. The `file` driver was the root cause of the ~500-800 ms TTFB
 * on every admin navigation: every settings lookup became a disk read.
 *
 * USAGE (any model, before/after touching .env):
 *   node .agents/scripts/env-guard.cjs          report + verify
 *   node .agents/scripts/env-guard.cjs --fix    set cache/session drivers to redis
 *   node .agents/scripts/env-guard.cjs --file   KILL-SWITCH: revert drivers to file
 *
 * Always exits 0 — a guard must never block work, only warn loudly.
 * QUEUE_CONNECTION is intentionally left alone (stays "sync": no queue worker
 * runs on this machine, and a redis queue with no worker would strand jobs).
 */

const fs = require('fs');
const path = require('path');
const net = require('net');

const ENV_PATH = path.resolve(__dirname, '..', '..', '.env');

const REDIS_DRIVERS = { CACHE_DRIVER: 'redis', CACHE_STORE: 'redis', SESSION_DRIVER: 'redis' };
const FILE_DRIVERS = { CACHE_DRIVER: 'file', CACHE_STORE: 'file', SESSION_DRIVER: 'file' };

function readEnv() {
  if (!fs.existsSync(ENV_PATH)) return { text: '', lines: [] };
  const text = fs.readFileSync(ENV_PATH, 'utf8');
  return { text, lines: text.split(/\r?\n/) };
}

function parseDrivers(lines) {
  const out = {};
  for (const line of lines) {
    const m = line.match(/^([A-Z_]+)\s*=\s*(.*)$/);
    if (m) out[m[1]] = m[2].trim().replace(/^["']|["']$/g, '');
  }
  return out;
}

function writeDrivers(target) {
  const { text } = readEnv();
  if (!text) {
    console.error('[env-guard] .env not found — nothing written');
    return;
  }
  let changed = 0;
  let next = text;
  for (const [key, value] of Object.entries(target)) {
    const present = new RegExp(`^${key}=.*$`, 'm');
    const already = new RegExp(`^${key}=${value}$`, 'm');
    if (present.test(next)) {
      if (!already.test(next)) {
        next = next.replace(present, `${key}=${value}`);
        changed++;
      }
    } else {
      next += `\n${key}=${value}`;
      changed++;
    }
  }
  if (changed > 0) {
    fs.writeFileSync(ENV_PATH, next);
    console.log(`[env-guard] updated ${changed} driver(s) -> ${Object.values(target).join(', ')}`);
    console.log('[env-guard] NOTE: no config:cache in this project, so .env is read at runtime.');
  } else {
    console.log('[env-guard] drivers already correct, nothing written');
  }
}

function redisReachable(timeoutMs = 600) {
  return new Promise((resolve) => {
    const socket = net.connect({ host: '127.0.0.1', port: 6379 });
    const done = (ok) => { socket.destroy(); resolve(ok); };
    socket.setTimeout(timeoutMs);
    socket.on('connect', () => done(true));
    socket.on('timeout', () => done(false));
    socket.on('error', () => done(false));
  });
}

(async () => {
  const arg = process.argv[2];

  if (arg === '--fix') { writeDrivers(REDIS_DRIVERS); return; }
  if (arg === '--file') { writeDrivers(FILE_DRIVERS); return; }

  const { lines } = readEnv();
  const drivers = parseDrivers(lines);
  const reachable = await redisReachable();

  console.log('[env-guard] .env driver state:');
  for (const key of Object.keys(REDIS_DRIVERS)) {
    const actual = drivers[key] ?? '(missing)';
    const want = REDIS_DRIVERS[key];
    console.log(`  ${key}=${actual}  (want ${want})  ${actual === want ? 'OK' : 'MISMATCH'}`);
  }
  console.log(`  redis 127.0.0.1:6379: ${reachable ? 'reachable' : 'NOT reachable'}`);

  const cacheOk = drivers.CACHE_DRIVER === 'redis' && drivers.CACHE_STORE === 'redis';
  const sessionOk = drivers.SESSION_DRIVER === 'redis';

  if (cacheOk && sessionOk && reachable) {
    console.log('[env-guard] GUARD OK - redis drivers active and redis running.');
  } else if ((cacheOk || sessionOk) && !reachable) {
    console.log('[env-guard] DANGER - drivers say redis but redis is DOWN. App will error.');
    console.log('           Fix: start redis, or kill-switch: node .agents/scripts/env-guard.cjs --file');
  } else {
    console.log('[env-guard] drivers are on file (or missing) - TTFB stays ~500-800ms.');
    console.log('           Fix: node .agents/scripts/env-guard.cjs --fix');
  }
  console.log('[env-guard] note: QUEUE_CONNECTION stays "sync" on purpose (no queue worker runs).');
})();
