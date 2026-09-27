// Split .ai/*_TASKS.md into one plain-text file per prompt, so the user can
// copy a whole file (Ctrl+A / Ctrl+C) instead of guessing fence boundaries.
//
// Section boundary  : "## প্রম্পট N — ..."  (also "## ⚠️" / "## 🔴" end a run)
// Prompt body       : between the FIRST and LAST ``` inside that section
//                     (inner fences are code examples and must be preserved)

const fs = require('fs');
const path = require('path');

const ROOT = 'C:\\laragon\\www\\ecommercego';
const OUT = path.join(ROOT, '.ai', 'prompts');

// execution order → [source file, prompt number in that file, task id, HAND?]
// HAND = the .txt is hand-written; keep its slot for numbering, skip generation.
const HAND = 'hand-written';
const ORDER = [
  ['GEMINI', 1, 'GEMINI-1'],
  ['GEMINI', 2, 'GEMINI-2'],
  ['BULK', 1, 'BULK-1'],
  ['CLAUDE', 1, 'CLAUDE-1'],
  ['CLAUDE', 2, 'CLAUDE-2'],
  ['CLAUDE', 3, 'CLAUDE-3'],
  ['CLAUDE', 4, 'CLAUDE-4'],
  ['GEMINI', 3, 'GEMINI-3'],
  ['CLAUDE', 5, 'CLAUDE-5'],
  // BULK-2 keeps its slot so the numbering stays stable, but HAND marks it as
  // hand-written: 10-BULK-2.txt is not generated. Its generated version told the
  // model to take "the first 20 files of class A" from script-inventory.md, and
  // 60 of those 300 blocks sit in packages/workdo/LandingPage, a forbidden
  // folder. The hand-written file names the 20 allowed files outright.
  ['BULK', 2, 'BULK-2', HAND],
  ['BULK', 3, 'BULK-3'],
  // BULK-3 and BULK-4 keep their slots but are hand-written for the same reason
  // as BULK-2: the inventory mixes in packages/workdo files, and 13 standalone
  // payment pages have no helper stub, so wrapping them breaks the page.
  ['BULK', 4, 'BULK-4', HAND],
  ['CLAUDE', 6, 'CLAUDE-6'],
  ['CLAUDE', 7, 'CLAUDE-7'],
  ['CLAUDE', 8, 'CLAUDE-8'],
  ['CLAUDE', 9, 'CLAUDE-9'],
  ['CLAUDE', 10, 'CLAUDE-10'],
  ['CLAUDE', 11, 'CLAUDE-11'],
  ['BULK', 5, 'BULK-5'],
  ['CLAUDE', 12, 'CLAUDE-12'],
  ['GEMINI', 4, 'GEMINI-4'],
  ['BULK', 6, 'BULK-6'],
  ['BULK', 7, 'BULK-7'],
  ['CLAUDE', 13, 'CLAUDE-13'],
  ['CLAUDE', 14, 'CLAUDE-14'],
  ['CLAUDE', 15, 'CLAUDE-15'],
  ['CLAUDE', 16, 'CLAUDE-16'],
  ['BULK', 8, 'BULK-8'],
  ['GEMINI', 5, 'GEMINI-5'],
];

const BOOT = `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ধাপ ১ / ২ — আগে নিজের অবস্থান বুঝে নাও
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

তুমি C:\\laragon\\www\\ecommercego প্রজেক্টে কাজ করছ।
ধরে নাও তোমার কোনো পূর্ব স্মৃতি নেই। কাজ শুরুর আগে এই ৪টি ধাপ করো:

  ১. পড়ো:  .ai/PROJECT_CONTEXT.md
  ২. পড়ো:  .ai/PROGRESS.md
  ৩. চালাও: git log --oneline -20
  ৪. চালাও: git status --short

নিচের টাস্কটি PROGRESS.md-এ কোন অবস্থায় আছে দেখো:

  ⬜ PENDING      → নতুন করে শুরু করো
  🟨 IN PROGRESS  → আগের সেশনে অসম্পূর্ণ রেখে থেমেছিলে। সেই টাস্কের
                    .ai/reports/ ফাইলটি পড়ো, কোন ফাইল পর্যন্ত শেষ হয়েছে
                    দেখো, তার পরের ফাইল থেকে শুরু করো।
                    ⛔ শেষ হওয়া ফাইল আবার করবে না — দুইবার পরিবর্তন
                       করলে কোড ভেঙে যাবে।
  ✅ DONE         → এই টাস্ক শেষ। ব্যবহারকারীকে জানাও, নতুন কিছু করবে না।

git log আর PROGRESS.md না মিললে git-কে সত্য ধরবে। কমিট মিথ্যা বলে না।

⛔ git সম্পর্কে দুইটি কঠোর নিয়ম:

  • \`git push\` কখনো চালাবে না। remote (github.com/arraasheed/ecommercego)
    একটি PUBLIC repo। push শুধু ব্যবহারকারী নিজে সিদ্ধান্ত নেবেন।
    তোমার কাজ শুধু লোকাল \`git commit\`।
  • \`git reset --hard\`, \`git clean\`, \`git checkout <file>\`,
    \`git push --force\` — এগুলোও কখনো নয়। এতে কাজ মুছে যায়।

শুরু করার আগে আমাকে এক লাইনে জানাও: কোন টাস্কে আছ, কোথা থেকে শুরু করছ।

`;

const HEAD2 = `
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ধাপ ২ / ২ — তোমার টাস্ক
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

`;

// Extra rules injected only into BULK prompts. These live in a standalone
// section of BULK_TASKS.md (outside any prompt fence), so the generic
// extractor would drop them.
const BULK_RULES = `
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ধাপ ২ / ৩ — কাজের নিয়ম (এগুলো ভাঙা যাবে না)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

তোমার কাজ = দেওয়া recipe হুবহু প্রয়োগ করা। নতুন কিছু ডিজাইন করা নয়,
সিদ্ধান্ত নেওয়া নয়।

▎নিয়ম ১ — "আরও ভালো করে দিই" ⛔ নিষিদ্ধ

  indentation বদলাবে না। variable-এর নাম বদলাবে না। let → const করবে না।
  অপ্রয়োজনীয় মনে হওয়া কোড মুছবে না।

  এটি একটি লাইসেন্সকৃত স্ক্রিপ্ট (WorkDo)। ব্যবহারকারীকে পরে upstream
  আপডেট merge করতে হবে। তুমি যত লাইন বদলাবে, তত merge conflict।

    আগে:                           পরে:
    $(document).ready(function(){   window.onTurboLoad(function(){
        $('.select2').select2();        $('.select2').select2();  ← হুবহু একই
    });                             });

  কোনো লাইন খারাপ মনে হলে রিপোর্টে লিখবে, ঠিক করবে না।

▎নিয়ম ২ — সন্দেহ হলে স্পর্শ করবে না

  নিশ্চিত না হলে ফাইলটি বাদ দাও, রিপোর্টে লিখো "Claude দেখবে"।
  একটি ফাইল বাদ দেওয়া নিরাপদ; ভুলভাবে বদলানো নয়।

  এগুলো পেলে থামবে, নিজে সিদ্ধান্ত নেবে না:
    • একই <script>-এ $(document).ready আর $(document).on(...) দুইটাই আছে
    • Blade কোড (@if, {{ }}) script-এর মধ্যে জড়ানো
    • ভেতরে nested function যার ভূমিকা অস্পষ্ট
    • ফাইলটি তালিকায় নেই

▎নিয়ম ৩ — পাইলট: প্রথমে ২টি ফাইল করে থামবে

  ১. শুধু ২টি ফাইল করো
  ২. git add -A
     git commit -m "wip(<scope>): pilot 2 files  [<টাস্ক আইডি>]"
  ৩. ব্যবহারকারীকে দেখাও ওই ২ ফাইলে হুবহু কী বদলেছ (before/after)
  ৪. থামো। অনুমতি ছাড়া ৩ নম্বর ফাইলে যাবে না।

  কারণ: ভুল recipe-এ ২০ ফাইল নষ্ট করার চেয়ে ২ ফাইলে ধরা পড়া ভালো।

▎নিয়ম ৪ — এরপর প্রতি ৩টি ফাইলে commit

  পাইলট পাস করার পর, প্রতি ৩টি ফাইল শেষ করার সাথে সাথে:
    ১. .ai/reports/<এই টাস্কের রিপোর্ট>.md এ ওই ৩টি ফাইলের সারি যোগ করো
    ২. .ai/PROGRESS.md এ অবস্থা 🟨 IN PROGRESS, নোটে "N/মোট ফাইল শেষ"
    ৩. git add -A
       git commit -m "wip(<scope>): files X-Y  [<টাস্ক আইডি>]"

  কারণ: টোকেন লিমিট মাঝপথে ফুরালে সর্বোচ্চ ২ ফাইলের কাজ হারাবে, ২০টার নয়।

▎নিয়ম ৫ — রিপোর্টে সৎ থাকবে

  তিনটি সংখ্যা আলাদা করে লিখতে হবে:
    পরিবর্তন করেছি        : N ফাইল
    স্পর্শ করিনি (কারণ সহ)  : N ফাইল
    সন্দেহ — Claude দেখবে   : N ফাইল

  "সব ঠিক আছে" লিখলে Claude যাচাই করবে, না মিললে কাজ বাতিল হবে।
  বাদ দেওয়া ফাইল স্বীকার করা ব্যর্থতা নয় — সেটাই সঠিক আচরণ।

▎নিয়ম ৬ — যা কখনো করবে না

  ❌ app/  routes/  database/  vendor/  packages/  — নিষিদ্ধ ফোল্ডার
  ❌ git push — remote একটি PUBLIC repo, push শুধু ব্যবহারকারীর সিদ্ধান্ত
  ❌ git reset --hard, git checkout <file>, git clean, git push --force
  ❌ npm install / composer install — কখনো নয়
  ❌ .env ফাইল পড়বে না, লিখবে না
  ❌ একই ফাইল দুইবার পরিবর্তন করবে না
  ❌ টাস্ক নম্বরের বাইরে কোনো কাজ নয় — "পাশে একটা bug দেখলাম" = রিপোর্টে লিখো

`;

const HEAD3 = `
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ধাপ ৩ / ৩ — তোমার টাস্ক
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

`;

const BN = '০১২৩৪৫৬৭৮৯';
const toBn = (n) => String(n).split('').map((d) => BN[+d]).join('');

function isSectionHeading(line) {
  if (!line.startsWith('## ')) return false;
  // Only a heading that STARTS with these ends a prompt section.
  // "## ২. 🔴 Dynamic Class" is a report-format heading *inside* a prompt,
  // so matching 🔴 anywhere would cut the section short.
  const t = line.slice(3).trim();
  return /^(প্রম্পট|⚠️|🔴)/.test(t);
}

/** Return the prompt body (array of lines) for "## প্রম্পট <bnNum>" in `file`. */
function extract(lines, promptNum) {
  const want = 'প্রম্পট ' + toBn(promptNum);
  let start = -1;
  for (let i = 0; i < lines.length; i++) {
    if (lines[i].startsWith('## ') && lines[i].includes(want + ' ')) {
      start = i;
      break;
    }
  }
  if (start === -1) throw new Error('heading not found: ' + want);

  let end = lines.length;
  for (let i = start + 1; i < lines.length; i++) {
    if (isSectionHeading(lines[i])) {
      end = i;
      break;
    }
  }

  const fences = [];
  for (let i = start; i < end; i++) {
    if (lines[i].trim() === '```') fences.push(i);
  }
  if (fences.length < 2) throw new Error(want + ': need >=2 fences, got ' + fences.length);
  if (fences.length % 2 !== 0) {
    console.log(`  ⚠️  ${want}: odd fence count (${fences.length}) — check output`);
  }

  return {
    body: lines.slice(fences[0] + 1, fences[fences.length - 1]),
    title: lines[start].replace(/^##\s*/, '').trim(),
  };
}

fs.mkdirSync(OUT, { recursive: true });
// Wipe generated files, but keep the hand-written ones. 10-BULK-2.txt has no
// source section in BULK_TASKS.md: its generated version told the model to take
// "the first 20 files of class A" from script-inventory.md, and 60 of those 300
// blocks sit in packages/workdo/LandingPage, a forbidden folder. The hand-written
// file names the 20 allowed files outright.
const KEEP = new Set(['10-BULK-2.txt', '12-BULK-4.txt']);
for (const f of fs.readdirSync(OUT)) {
  if (f.endsWith('.txt') && !KEEP.has(f)) fs.unlinkSync(path.join(OUT, f));
}

const cache = {};
const written = [];

const BOOT_HEAD = (of) => `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ধাপ ১ / ${of} — আগে নিজের অবস্থান বুঝে নাও
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
`;

ORDER.forEach(([src, num, taskId, hand], idx) => {
  const seqEarly = String(idx + 1).padStart(2, '0');
  if (hand === HAND) {
    // Slot reserved for numbering only; the .txt is maintained by hand.
    const name = `${seqEarly}-${taskId}.txt`;
    const p = path.join(OUT, name);
    written.push([name, fs.existsSync(p) ? -1 : -2, 'HAND-WRITTEN (not generated)']);
    return;
  }
  if (!cache[src]) {
    cache[src] = fs
      .readFileSync(path.join(ROOT, '.ai', src + '_TASKS.md'), 'utf8')
      .replace(/^\uFEFF/, '')
      .split(/\r?\n/);
  }
  const { body, title } = extract(cache[src], num);
  const seq = String(idx + 1).padStart(2, '0');
  const name = `${seq}-${taskId}.txt`;
  const isBulk = src === 'BULK';
  const steps = isBulk ? '৩' : '২';

  const header =
    `################################################################\n` +
    `#  ফাইল: ${name}\n` +
    `#  টাস্ক: ${taskId}\n` +
    `#  ${title}\n` +
    `#\n` +
    `#  এই ফাইলের পুরোটা কপি করে মডেলকে দিন (Ctrl+A → Ctrl+C)।\n` +
    `#  ভেতরে ${steps}টি ধাপ আছে — সবগুলো একসাথে পেস্ট করবেন।\n` +
    `################################################################\n\n`;

  const boot = BOOT_HEAD(steps) + BOOT.split('\n').slice(3).join('\n');

  const out =
    header +
    boot +
    (isBulk ? BULK_RULES + HEAD3 : HEAD2) +
    body.join('\n').trim() +
    '\n';

  fs.writeFileSync(path.join(OUT, name), out, 'utf8');
  written.push([name, body.length, title]);
});

console.log(`\nwrote ${written.length} files to .ai\\prompts\\\n`);
for (const [n, len, t] of written) {
  if (len === -1) {
    console.log(`  ${n.padEnd(20)}    — ${t}`);
  } else if (len === -2) {
    console.log(`  ${n.padEnd(20)}    ⚠️ MISSING — ${t}`);
  } else {
    console.log(`  ${n.padEnd(20)} ${String(len).padStart(4)} lines  ${t.slice(0, 52)}`);
  }
}
