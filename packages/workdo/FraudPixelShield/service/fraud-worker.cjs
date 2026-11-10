/**
 * Ecom Sentrix — 3-Source Real-Time Courier Fraud Worker
 * Listens on 127.0.0.1:3005
 * 
 * Sources:
 * 1. BD Courier API (with key rotation)
 * 2. EliteMart Delivery Scraper
 * 3. FraudChecker.link Delivery Scraper
 * 
 * Uses Promise.allSettled with maximum-per-courier aggregation and 4.0s hard timeout.
 */

const http = require('http');
const url = require('url');

const PORT = process.env.FRAUD_WORKER_PORT || 3005;

// Mock / Default fallback key pool if none supplied by request
const DEFAULT_BD_COURIER_KEYS = [
    'mpWPZ6FM0uhC5yrRmjiEbqbEOCpKyA9LGLSLacoiodnEIzTixUSubj4f6ISj',
    'mduxdnwfnliaqramouibwypsyoxla8s4'
];

/**
 * Clean phone number to standard 11 digits (01XXXXXXXXX)
 */
function normalizePhone(phone) {
    if (!phone) return '';
    let p = String(phone).replace(/[^0-9]/g, '');
    if (p.startsWith('880')) p = p.substring(2);
    if (p.startsWith('88')) p = p.substring(2);
    return p;
}

/**
 * 1. BD Courier API Request (Fastest & authoritative)
 */
async function queryBdCourierApi(phone, apiKey) {
    const key = apiKey || DEFAULT_BD_COURIER_KEYS[Math.floor(Math.random() * DEFAULT_BD_COURIER_KEYS.length)];
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    try {
        const res = await fetch('https://api.bdcourier.com/courier-check', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${key}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ phone: phone }),
            signal: controller.signal
        });
        clearTimeout(timeout);

        if (!res.ok) return [];

        const json = await res.json();
        const rows = [];
        if (json && json.status === 'success' && json.data) {
            for (const [k, stat] of Object.entries(json.data)) {
                if (k === 'summary') continue;
                rows.push({
                    courier: stat.name || k,
                    orders: parseInt(stat.total_parcel) || 0,
                    delivery: parseInt(stat.success_parcel) || 0,
                    cancel: parseInt(stat.cancelled_parcel) || 0
                });
            }
        }
        return rows;
    } catch (e) {
        clearTimeout(timeout);
        return [];
    }
}

/**
 * 2. EliteMart Scraper (HTTP endpoint fallback)
 */
async function queryEliteMart(phone) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    try {
        const res = await fetch(`https://elitemart.com.bd/api/fraud-check?phone=${phone}`, {
            method: 'GET',
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            },
            signal: controller.signal
        });
        clearTimeout(timeout);

        if (!res.ok) return [];
        const json = await res.json();
        if (Array.isArray(json.data)) {
            return json.data.map(r => ({
                courier: r.courier || 'EliteMart',
                orders: parseInt(r.orders) || 0,
                delivery: parseInt(r.delivery) || 0,
                cancel: parseInt(r.cancel) || 0
            }));
        }
        return [];
    } catch (e) {
        clearTimeout(timeout);
        return [];
    }
}

/**
 * 3. FraudChecker.link Scraper
 */
async function queryFraudCheckerLink(phone) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    try {
        const res = await fetch(`https://fraudchecker.link/api/check?phone=${phone}`, {
            method: 'GET',
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            },
            signal: controller.signal
        });
        clearTimeout(timeout);

        if (!res.ok) return [];
        const json = await res.json();
        if (Array.isArray(json.data)) {
            return json.data.map(r => ({
                courier: r.courier || 'FraudChecker',
                orders: parseInt(r.orders) || 0,
                delivery: parseInt(r.delivery) || 0,
                cancel: parseInt(r.cancel) || 0
            }));
        }
        return [];
    } catch (e) {
        clearTimeout(timeout);
        return [];
    }
}

/**
 * Combine all results with Maximum-per-courier logic
 */
async function checkFraudAllSources(phone, apiKey) {
    const startTime = Date.now();
    const cleanPhone = normalizePhone(phone);

    if (!cleanPhone || cleanPhone.length !== 11) {
        return {
            status: 'error',
            message: 'Invalid 11-digit Bangladesh phone number',
            phone: phone
        };
    }

    // Run all 3 concurrently with hard timeout
    const [bdResult, eliteResult, linkResult] = await Promise.allSettled([
        queryBdCourierApi(cleanPhone, apiKey),
        queryEliteMart(cleanPhone),
        queryFraudCheckerLink(cleanPhone)
    ]);

    const combinedCouriers = {};
    const sourcesResponded = [];

    const processRows = (rows, sourceName) => {
        if (!Array.isArray(rows) || rows.length === 0) return;
        sourcesResponded.push(sourceName);
        rows.forEach(r => {
            if (r.orders === 0 && r.delivery === 0 && r.cancel === 0) return;
            const key = (r.courier || 'other').toLowerCase().trim();
            if (!combinedCouriers[key]) {
                combinedCouriers[key] = {
                    courier: r.courier,
                    orders: r.orders || 0,
                    delivery: r.delivery || 0,
                    cancel: r.cancel || 0
                };
            } else {
                // Maximum logic: keep the higher counts
                if (r.orders > combinedCouriers[key].orders) {
                    combinedCouriers[key].orders = r.orders;
                    combinedCouriers[key].delivery = r.delivery;
                    combinedCouriers[key].cancel = r.cancel;
                }
            }
        });
    };

    if (bdResult.status === 'fulfilled') processRows(bdResult.value, 'BD Courier API');
    if (eliteResult.status === 'fulfilled') processRows(eliteResult.value, 'EliteMart');
    if (linkResult.status === 'fulfilled') processRows(linkResult.value, 'FraudChecker.link');

    const finalRows = Object.values(combinedCouriers);
    let totalOrders = 0;
    let totalDelivery = 0;
    let totalCancel = 0;
    let courierString = '-';

    if (finalRows.length > 0) {
        finalRows.forEach(r => {
            totalOrders += r.orders;
            totalDelivery += r.delivery;
            totalCancel += r.cancel;
        });
        courierString = finalRows.map(r => `${r.courier}-${r.orders}`).join(', ');
    }

    const successRate = totalOrders > 0 ? Math.round((totalDelivery / totalOrders) * 100) : 0;
    const durationMs = Date.now() - startTime;

    return {
        status: 'success',
        phone: cleanPhone,
        total_orders: totalOrders,
        total_delivery: totalDelivery,
        total_cancel: totalCancel,
        success_rate: successRate,
        courier_details: courierString,
        sources_responded: sourcesResponded,
        duration_ms: durationMs
    };
}

// Create HTTP server
const server = http.createServer(async (req, res) => {
    res.setHeader('Content-Type', 'application/json');
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const parsedUrl = url.parse(req.url, true);

    if (parsedUrl.pathname === '/health') {
        res.writeHead(200);
        res.end(JSON.stringify({ status: 'ok', worker: 'Ecom Sentrix Fraud Shield', uptime: process.uptime() }));
        return;
    }

    if (parsedUrl.pathname === '/check') {
        let phone = parsedUrl.query.phone;
        let apiKey = parsedUrl.query.api_key;

        if (req.method === 'POST') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', async () => {
                try {
                    const data = body ? JSON.parse(body) : {};
                    phone = data.phone || phone;
                    apiKey = data.api_key || apiKey;

                    const result = await checkFraudAllSources(phone, apiKey);
                    res.writeHead(200);
                    res.end(JSON.stringify(result));
                } catch (e) {
                    res.writeHead(400);
                    res.end(JSON.stringify({ status: 'error', message: e.message }));
                }
            });
            return;
        }

        const result = await checkFraudAllSources(phone, apiKey);
        res.writeHead(200);
        res.end(JSON.stringify(result));
        return;
    }

    res.writeHead(404);
    res.end(JSON.stringify({ status: 'error', message: 'Not found' }));
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`[FraudWorker] Ecom Sentrix 3-Source Fraud Worker running on http://127.0.0.1:${PORT}`);
});
