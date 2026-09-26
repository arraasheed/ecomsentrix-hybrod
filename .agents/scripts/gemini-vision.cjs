#!/usr/bin/env node
/**
 * Gemini Vision Helper CLI
 * Reads any image, sends it to Gemini Flash API, and outputs detailed description and OCR text.
 * 
 * Usage:
 *   node .agents/scripts/gemini-vision.cjs <image-path> [optional prompt]
 *   node .agents/scripts/gemini-vision.cjs path/to/screenshot.png "Extract all error messages"
 * 
 * API Key resolution:
 *   1. Argument: --key=AIzaSy...
 *   2. Environment: GEMINI_API_KEY
 *   3. .env file: GEMINI_API_KEY=...
 */

const fs = require('fs');
const path = require('path');
const https = require('https');

// 1. Resolve API Key
function getApiKey() {
  const keyArg = process.argv.find(arg => arg.startsWith('--key='));
  if (keyArg) return keyArg.split('=')[1];
  if (process.env.GEMINI_API_KEY) return process.env.GEMINI_API_KEY;

  // Try reading .env in current directory or root
  const envPaths = [
    path.resolve(process.cwd(), '.env'),
    path.resolve(__dirname, '../../.env')
  ];

  for (const envPath of envPaths) {
    if (fs.existsSync(envPath)) {
      const content = fs.readFileSync(envPath, 'utf8');
      const match = content.match(/^GEMINI_API_KEY\s*=\s*["']?([^"'\r\n]+)["']?/m);
      if (match && match[1]) return match[1].trim();
    }
  }

  return null;
}

// 2. Parse arguments
const args = process.argv.slice(2).filter(arg => !arg.startsWith('--key='));
if (args.length === 0) {
  console.log(`
Usage:
  node .agents/scripts/gemini-vision.cjs <image-path> [prompt] [--key=YOUR_GEMINI_KEY]

Examples:
  node .agents/scripts/gemini-vision.cjs avatar.png
  node .agents/scripts/gemini-vision.cjs screenshot.png "Extract all table data and buttons"
`);
  process.exit(1);
}

const imagePath = path.resolve(process.cwd(), args[0]);
const customPrompt = args.slice(1).join(' ') || "Describe everything in this image in detail. Transcribe all visible text, UI elements, buttons, errors, or forms accurately.";

if (!fs.existsSync(imagePath)) {
  console.error(`Error: File not found at ${imagePath}`);
  process.exit(1);
}

const apiKey = getApiKey();
if (!apiKey) {
  console.error(`Error: GEMINI_API_KEY is not set.`);
  console.error(`Please set it in your .env file: GEMINI_API_KEY=your_key_here`);
  console.error(`Or pass it as a flag: node .agents/scripts/gemini-vision.cjs "${imagePath}" --key=AIzaSy...`);
  process.exit(1);
}

// 3. MIME type detection
const ext = path.extname(imagePath).toLowerCase();
const mimeTypes = {
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.gif': 'image/gif',
  '.bmp': 'image/bmp',
  '.svg': 'image/svg+xml'
};
const mimeType = mimeTypes[ext] || 'image/jpeg';

// 4. Read image & Base64 encode
const base64Data = fs.readFileSync(imagePath).toString('base64');

// 5. Send to Gemini API (using Gemini 2.5 Flash / 1.5 Flash)
const payload = JSON.stringify({
  contents: [{
    parts: [
      { text: customPrompt },
      {
        inlineData: {
          mimeType: mimeType,
          data: base64Data
        }
      }
    ]
  }]
});

const model = 'gemini-2.5-flash';
const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

const req = https.request(url, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(payload)
  }
}, (res) => {
  let responseData = '';
  res.on('data', chunk => responseData += chunk);
  res.on('end', () => {
    try {
      const json = JSON.parse(responseData);
      if (json.error) {
        console.error(`Gemini API Error: ${json.error.message || JSON.stringify(json.error)}`);
        process.exit(1);
      }
      const text = json.candidates?.[0]?.content?.parts?.[0]?.text;
      if (text) {
        console.log(text);
      } else {
        console.log('No text generated. Full response:', responseData);
      }
    } catch (e) {
      console.error('Failed to parse API response:', responseData);
    }
  });
});

req.on('error', (e) => {
  console.error(`Network Error: ${e.message}`);
  process.exit(1);
});

req.write(payload);
req.end();
