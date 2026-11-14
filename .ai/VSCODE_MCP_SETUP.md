# 🔌 VS Code Automation MCP Configuration (Ready & Active)

আপনার লোকাল মেশিনে ইনস্টল থাকা ৩টি কোর অটোমেশন MCP সার্ভার:
1. **`chrome-devtools`** (Chrome DevTools Automation)
2. **`playwright`** (Playwright Browser Test & Vision MCP)
3. **`rea`** (Reverse Engineer Anything & Bundle Inspector)

---

### ✅ স্বয়ংক্রিয়ভাবে যে ফাইলগুলোতে কনফিগার করা হয়েছে:
1. **Workspace লেভেল:** [c:\laragon\www\ecommercego\.vscode\mcp.json](file:///c:/laragon/www/ecommercego/.vscode/mcp.json)
2. **VS Code User প্রোফাইল লেভেল:** `C:\Users\arraa\AppData\Roaming\Code\User\mcp.json`
3. **Claude Dev / Cline এক্সটেনশন লেভেল:** `C:\Users\arraa\AppData\Roaming\Code\User\globalStorage\saoudrizwan.claude-dev\settings\cline_mcp_settings.json`

---

### ⚙️ কনফিগারেশন কোড (JSON):

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "command": "C:\\Program Files\\nodejs\\node.exe",
      "args": [
        "C:\\Users\\arraa\\AppData\\Local\\npm-cache\\_npx\\15c61037b1978c83\\node_modules\\chrome-devtools-mcp\\build\\src\\bin\\chrome-devtools-mcp.js",
        "--headless",
        "--isolated"
      ]
    },
    "playwright": {
      "command": "C:\\Program Files\\nodejs\\node.exe",
      "args": [
        "C:\\Users\\arraa\\AppData\\Local\\npm-cache\\_npx\\9833c18b2d85bc59\\node_modules\\@playwright\\mcp\\cli.js",
        "--caps=vision,pdf",
        "--snapshot-boxes",
        "--output-dir",
        "C:\\Users\\arraa\\AppData\\Local\\Temp\\opencode\\pw"
      ]
    },
    "rea": {
      "command": "C:\\Program Files\\nodejs\\node.exe",
      "args": [
        "C:\\Users\\arraa\\AppData\\Roaming\\npm\\node_modules\\rea-agents\\scripts\\rea.mjs",
        "mcp"
      ]
    }
  }
}
```

---

### 🚀 অটোমেশন যেভাবে কাজ করবে:
* **Playwright & Chrome DevTools:** কোনো UI টেস্টিং, বাটন ক্লিক ভেরিফিকেশন, কিংবা স্ক্রিনশট নেওয়ার প্রয়োজন হলে মডেল স্বয়ংক্রিয়ভাবে ব্রাউজার ব্যাকগ্রাউন্ডে ওপেন করে কাজ সেরে ফেলবে।
* **REA (Reverse Engineer Anything):** কোনো বান্ডেল, স্ক্রিপ্ট বা সোর্স ডিপেন্ডেন্সি ট্রেস করার প্রয়োজন হলে এটি ব্যাকগ্রাউন্ডে কল হবে।
