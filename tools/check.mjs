import { promises as fs } from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const failures = [];

const requiredFiles = [
  'README.md',
  'README.zh-CN.md',
  'LICENSE',
  '.gitignore',
  'ddys_open/Manifest.xml',
  'ddys_open/conf',
  'ddys_open/controller/IndexController.php',
  'ddys_open/admin/ManageController.php',
  'ddys_open/template/index_run.htm',
  'ddys_open/template/admin/manage_run.htm',
  'ddys_open/source/bootstrap.php',
  'ddys_open/source/security.php',
  'ddys_open/source/cache.php',
  'ddys_open/source/client.php',
  'ddys_open/source/render.php',
  'ddys_open/source/shortcode.php',
  'ddys_open/service/srv/App_DdysOpen_AdminMenuDo.php',
  'ddys_open/service/srv/App_DdysOpen_EditorAppDo.php',
  'ddys_open/service/srv/App_DdysOpen_UbbCodeDo.php',
  'ddys_open/service/srv/App_DdysOpen_ThreadDisplayInjector.php',
  'ddys_open/service/srv/App_DdysOpen_ThreadDisplayDo.php',
  'ddys_open/service/srv/App_DdysOpen_InstallDo.php',
  'ddys_open/resource/editorApp.js',
  'ddys_open/resource/images/icon.png',
  'ddys_open/resource/images/logo.png',
  'ddys_open/resource/static/css/frontend.css',
  'ddys_open/resource/static/css/admin.css',
  'ddys_open/resource/static/js/frontend.js',
  'ddys_open/resource/static/js/admin.js',
  'ddys_open/resource/static/images/icon-16.png',
  'ddys_open/resource/static/images/icon-32.png',
  'ddys_open/resource/static/images/icon-192.png',
  'ddys_open/resource/static/images/icon-512.png',
  'ddys_open/resource/static/images/logo.png'
];

const shortcodes = [
  'ddys_movies',
  'ddys_latest',
  'ddys_hot',
  'ddys_search',
  'ddys_suggest',
  'ddys_calendar',
  'ddys_movie',
  'ddys_sources',
  'ddys_related',
  'ddys_comments',
  'ddys_collections',
  'ddys_collection',
  'ddys_shares',
  'ddys_share',
  'ddys_requests',
  'ddys_activities',
  'ddys_user',
  'ddys_types',
  'ddys_genres',
  'ddys_regions',
  'ddys_request_form'
];

const pageViews = [
  'movies',
  'latest',
  'hot',
  'search',
  'suggest',
  'calendar',
  'movie',
  'sources',
  'related',
  'comments',
  'collections',
  'collection',
  'shares',
  'share',
  'requests',
  'activities',
  'user',
  'types',
  'genres',
  'regions'
];

for (const file of requiredFiles) {
  await mustExist(file);
}

await checkEncoding();
await checkManifest();
await checkControllers();
await checkAdmin();
await checkServices();
await checkSource();
await checkDocs();
await checkAssets();
await checkForbiddenFiles();
await checkForbiddenText();

if (failures.length > 0) {
  console.error(failures.map((failure) => `- ${failure}`).join('\n'));
  process.exit(1);
}

console.log(JSON.stringify({ ok: true, files: (await listFiles(root)).length, shortcodes: shortcodes.length }, null, 2));

async function checkEncoding() {
  const files = await listFiles(root);
  for (const full of files) {
    const rel = slash(path.relative(root, full));
    if (!isTextFile(rel)) continue;
    const buffer = await fs.readFile(full);
    assert(!(buffer[0] === 0xef && buffer[1] === 0xbb && buffer[2] === 0xbf), `${rel} must not contain UTF-8 BOM.`);
    const text = buffer.toString('utf8');
    assert(!text.includes('\uFFFD'), `${rel} contains replacement characters.`);
    if (rel.endsWith('.php') || rel === 'ddys_open/conf') {
      assert(!/\?>\s*$/.test(text), `${rel} should omit the closing PHP tag.`);
    }
  }
}

async function checkManifest() {
  const manifest = await read('ddys_open/Manifest.xml');
  for (const fragment of [
    '<name>低端影视 API</name>',
    '<alias>ddys_open</alias>',
    '<version>0.1.0</version>',
    '<pw-version>9.0</pw-version>',
    '<type>app</type>',
    '<charset>utf-8</charset>',
    '<res>resource</res>',
    '<install>EXT:ddys_open.service.srv.App_DdysOpen_InstallDo</install>',
    '<installation-service>nav_main</installation-service>',
    '<s_admin_menu>',
    '<s_PwEditor_app>',
    '<s_PwUbbCode_convert>',
    '<c_read_run>',
    'https://github.com/ddysiodev/ddys-phpwind-plugin'
  ]) {
    assert(manifest.includes(fragment), `Manifest.xml missing ${fragment}`);
  }
  assert(!manifest.includes('<description></description>'), 'Manifest.xml description must not be empty.');
  assert(count(manifest, '<manifest>') === 1 && count(manifest, '</manifest>') === 1, 'Manifest.xml must have one manifest root.');
}

async function checkControllers() {
  const index = await read('ddys_open/controller/IndexController.php');
  for (const fragment of [
    'class IndexController extends PwBaseController',
    'beforeAction',
    'ddys_open_bootstrap',
    'public function run()',
    'setOutput(ddys_open_page_title($view), \'ddysTitle\')',
    'ddys_open_page_tabs($view)',
    'ddys_open_render_page($view, $params)',
    'public function apiAction()',
    'ddys_open_json_response(ddys_open_proxy_response())',
    'public function requestAction()',
    'ddys_open_json_response(ddys_open_handle_request_form())'
  ]) {
    assert(index.includes(fragment), `IndexController.php missing ${fragment}`);
  }
}

async function checkAdmin() {
  const controller = await read('ddys_open/admin/ManageController.php');
  const template = await read('ddys_open/template/admin/manage_run.htm');
  for (const fragment of [
    'class ManageController extends AdminBaseController',
    'doRunAction',
    'testAction',
    'clearAction',
    'ddys_open_verify_nonce',
    'ddys_open_save_settings',
    'ddys_open_api_get(\'/latest\'',
    'ddys_open_cache_clear',
    '_configChecks',
    'function_exists(\'curl_init\')',
    'json_decode'
  ]) {
    assert(controller.includes(fragment), `ManageController.php missing ${fragment}`);
  }
  for (const fragment of [
    '{$ddysAdminAssets|html}',
    'class="J_ajaxForm"',
    'ddys_admin_nonce',
    'data-ddys-page-url',
    'data-ddys-api-url',
    'data-ddys-generator-kind',
    'data-ddys-generator-type',
    'data-ddys-generator-output',
    'Pw::isSelected',
    'Pw::ifcheck',
    '启用短代码导航片段',
    '测试连接',
    '清理缓存'
  ]) {
    assert(template.includes(fragment), `manage_run.htm missing ${fragment}`);
  }
  assert(!template.includes('启用扩展导航'), 'Admin template should not imply it controls AppCenter nav installation.');
}

async function checkServices() {
  const serviceChecks = [
    ['ddys_open/service/srv/App_DdysOpen_AdminMenuDo.php', ['class App_DdysOpen_AdminMenuDo', 'app_ddys_open', 'app/manage/*?app=ddys_open']],
    ['ddys_open/service/srv/App_DdysOpen_EditorAppDo.php', ['class App_DdysOpen_EditorAppDo', "'name' => 'ddys_open'"]],
    ['ddys_open/service/srv/App_DdysOpen_UbbCodeDo.php', ['class App_DdysOpen_UbbCodeDo', 'ddys_open_parse_shortcodes($message)']],
    ['ddys_open/service/srv/App_DdysOpen_ThreadDisplayInjector.php', ['extends PwBaseHookInjector', 'App_DdysOpen_ThreadDisplayDo']],
    ['ddys_open/service/srv/App_DdysOpen_ThreadDisplayDo.php', ['extends PwThreadDisplayDoBase', 'bulidRead', "$read['content']", 'ddys_open_parse_shortcodes']],
    ['ddys_open/service/srv/App_DdysOpen_InstallDo.php', ['implements iPwInstall', 'install($install)', 'unInstall($install)', 'rollback($install)', 'ddys_open_ensure_runtime', 'ddys_open_cache_clear']]
  ];
  for (const [file, fragments] of serviceChecks) {
    const text = await read(file);
    for (const fragment of fragments) {
      assert(text.includes(fragment), `${file} missing ${fragment}`);
    }
  }
}

async function checkSource() {
  const bootstrap = await read('ddys_open/source/bootstrap.php');
  const security = await read('ddys_open/source/security.php');
  const cache = await read('ddys_open/source/cache.php');
  const client = await read('ddys_open/source/client.php');
  const render = await read('ddys_open/source/render.php');
  const shortcode = await read('ddys_open/source/shortcode.php');
  const frontendJs = await read('ddys_open/resource/static/js/frontend.js');
  const adminJs = await read('ddys_open/resource/static/js/admin.js');
  const editorJs = await read('ddys_open/resource/editorApp.js');

  for (const shortcodeName of shortcodes) {
    assert(shortcode.includes(`'${shortcodeName}'`), `shortcode.php missing ${shortcodeName}`);
    assert(shortcode.includes(`$tag === '${shortcodeName}'`) || shortcode.includes('ddys_open_shortcodes'), `shortcode.php may not render ${shortcodeName}`);
  }

  for (const fragment of [
    'DDYS_OPEN_PHPWIND_ID',
    'DDYS_OPEN_PHPWIND_VERSION',
    'ddys_open_ensure_runtime',
    '.htaccess',
    'index.html'
  ]) {
    assert(bootstrap.includes(fragment), `bootstrap.php missing ${fragment}`);
  }

  for (const fragment of [
    "Wind::getRealPath('EXT:'",
    'Wekit::url()',
    'WindUrlHelper::createUrl',
    '$request->getGet',
    '$request->getPost',
    'ddys_open_nonce',
    'ddys_open_verify_nonce',
    'hash_hmac',
    'ddys_open_hash_equals',
    'ddys_open_json_response',
    'JSON_UNESCAPED_UNICODE',
    'ddys_open_safe_media_url'
  ]) {
    assert(security.includes(fragment), `security.php missing ${fragment}`);
  }
  assert(security.includes('function ddys_open_page_views'), 'security.php missing unified page view allowlist.');
  for (const view of pageViews) {
    assert(security.includes(`'${view}'`), `security.php missing page view ${view}`);
  }

  for (const fragment of [
    'ddys_open_cache_key',
    'ksort($params)',
    'LOCK_EX',
    'request_*.lock',
    'ddys_open_prune_locks',
    'ddys_open_cache_stats',
    'is_writable'
  ]) {
    assert(cache.includes(fragment), `cache.php missing ${fragment}`);
  }

  for (const fragment of [
    'ddys_open_allowed_route',
    'ddys_open_proxy_path',
    'ddys_open_proxy_query',
    'ddys_open_proxy_response',
    'Authorization: Bearer',
    "allow_url_fopen",
    "!ini_get('open_basedir')",
    '年份格式无效',
    '豆瓣 ID 格式无效',
    'IMDb ID 格式无效',
    '备注不能超过 1000 个字符',
    'ddys_open_check_rate_limit'
  ]) {
    assert(client.includes(fragment), `client.php missing ${fragment}`);
  }

  for (const route of ['movies', 'latest', 'hot', 'search', 'suggest', 'calendar', 'movie', 'sources', 'related', 'comments', 'collections', 'collection', 'shares', 'share', 'requests', 'activities', 'user', 'types', 'genres', 'regions']) {
    assert(client.includes(`'${route}'`), `client.php missing proxy route ${route}`);
  }

  for (const fragment of [
    'ddys_open_frontend_assets() .',
    'ddys-phpwind-nav nav-link',
    'ddys_open_page_views()',
    'ddys_open_render_page',
    'ddys_open_page_tabs',
    'ddys_open_render_request_form',
    'data-ddys-phpwind-request-form',
    'name="m" value="app"',
    'name="c" value="index"',
    'name="a" value="run"',
    'name="app" value="',
    'magnet:',
    'ed2k:',
    'thunder:',
    'records',
    'is_string($resource)'
  ]) {
    assert(render.includes(fragment), `render.php missing ${fragment}`);
  }
  for (const view of pageViews) {
    assert(render.includes(`$view === '${view}'`) || render.includes(`'${view}' =>`), `render.php missing frontend view ${view}`);
  }

  assert(!shortcode.includes('log_content'), 'shortcode.php must not contain Emlog log_content leftovers.');
  assert(!shortcode.includes('article_content_echo'), 'shortcode.php must not contain Emlog article hook leftovers.');
  assert(frontendJs.includes('!window.fetch') && frontendJs.includes('FormData') && frontendJs.includes('credentials: \'same-origin\''), 'frontend.js request form handling is incomplete.');
  assert(adminJs.includes("url.searchParams.set('app', 'ddys_open')") && adminJs.includes("kind === 'page'") && adminJs.includes("kind === 'proxy'"), 'admin.js generator is incomplete.');
  for (const view of ['movies', 'suggest', 'sources', 'related', 'comments', 'shares', 'share', 'activities', 'user', 'types', 'genres', 'regions']) {
    assert(adminJs.includes(`view = '${view}'`), `admin.js page generator missing view ${view}`);
  }
  assert(editorJs.includes("var appName = 'ddys_open'") && editorJs.includes('WindEditor.initOpenApp[appName]') && editorJs.includes('[ddys_latest limit="12"]'), 'editorApp.js is incomplete.');

  for (const rel of [
    'ddys_open/conf',
    'ddys_open/controller/IndexController.php',
    'ddys_open/admin/ManageController.php',
    'ddys_open/source/bootstrap.php',
    'ddys_open/source/security.php',
    'ddys_open/source/cache.php',
    'ddys_open/source/client.php',
    'ddys_open/source/render.php',
    'ddys_open/source/shortcode.php',
    'ddys_open/service/srv/App_DdysOpen_AdminMenuDo.php',
    'ddys_open/service/srv/App_DdysOpen_EditorAppDo.php',
    'ddys_open/service/srv/App_DdysOpen_UbbCodeDo.php',
    'ddys_open/service/srv/App_DdysOpen_ThreadDisplayInjector.php',
    'ddys_open/service/srv/App_DdysOpen_ThreadDisplayDo.php',
    'ddys_open/service/srv/App_DdysOpen_InstallDo.php'
  ]) {
    await checkBalancedPhp(rel);
  }

  for (const rel of [
    'ddys_open/controller/IndexController.php',
    'ddys_open/admin/ManageController.php',
    'ddys_open/source/bootstrap.php',
    'ddys_open/source/security.php',
    'ddys_open/source/cache.php',
    'ddys_open/source/client.php',
    'ddys_open/source/render.php',
    'ddys_open/source/shortcode.php',
    'ddys_open/service/srv/App_DdysOpen_AdminMenuDo.php',
    'ddys_open/service/srv/App_DdysOpen_EditorAppDo.php',
    'ddys_open/service/srv/App_DdysOpen_UbbCodeDo.php',
    'ddys_open/service/srv/App_DdysOpen_ThreadDisplayInjector.php',
    'ddys_open/service/srv/App_DdysOpen_ThreadDisplayDo.php',
    'ddys_open/service/srv/App_DdysOpen_InstallDo.php'
  ]) {
    const text = await read(rel);
    assert(text.includes("defined('WEKIT_VERSION')"), `${rel} must guard direct access.`);
  }
}

async function checkDocs() {
  const zh = await read('README.zh-CN.md');
  const en = await read('README.md');
  assert(zh.includes('[English](README.md) | 简体中文'), 'Chinese README missing language link.');
  assert(en.includes('English | [简体中文](README.zh-CN.md)'), 'English README missing language link.');
  assert(zh.includes('[低端影视](https://ddys.io/) API'), 'Chinese README must link DDYS API text.');
  assert(en.includes('[DDYS](https://ddys.io/) API'), 'English README must link DDYS API text.');
  assert(zh.includes('ddys-phpwind-plugin-v0.1.0.zip') && en.includes('ddys-phpwind-plugin-v0.1.0.zip'), 'README files missing release zip.');
  assert(zh.includes('src/extensions/ddys_open') && en.includes('src/extensions/ddys_open'), 'README files missing PHPWind install path.');
  assert(zh.includes('themes/extres/ddys_open') && en.includes('themes/extres/ddys_open'), 'README files missing extension resource path.');
  assert(zh.includes('admin.php?m=app&app=ddys_open&c=manage&a=run') && en.includes('admin.php?m=app&app=ddys_open&c=manage&a=run'), 'README files missing admin route.');
  assert(zh.includes('index.php?m=app&app=ddys_open&c=index&a=api&route=latest') && en.includes('index.php?m=app&app=ddys_open&c=index&a=api&route=latest'), 'README files missing proxy route.');
  assert(zh.includes('view=movies&type=movie') && en.includes('view=movies&type=movie'), 'README files missing movies frontend route.');
  assert(zh.includes('view=sources&slug=this-tempting-madness') && en.includes('view=sources&slug=this-tempting-madness'), 'README files missing sources frontend route.');
  assert(zh.includes('view=share&id=1') && en.includes('view=share&id=1'), 'README files missing share frontend route.');
  assert(zh.includes('view=user&username=demo') && en.includes('view=user&username=demo'), 'README files missing user frontend route.');
  assert(zh.includes('view=types') && en.includes('view=types'), 'README files missing dictionary frontend routes.');
  assert(zh.includes('node tools/check.mjs') && en.includes('node tools/check.mjs'), 'README files missing development check command.');
  for (const shortcodeName of shortcodes) {
    assert(zh.includes(`[${shortcodeName}`), `Chinese README missing ${shortcodeName}`);
    assert(en.includes(`[${shortcodeName}`), `English README missing ${shortcodeName}`);
  }
  for (const bad of ['�', '绠', '浣庣', '鏂', '銆', '嘳', '歔']) {
    assert(!zh.includes(bad) && !en.includes(bad), `README files contain mojibake marker ${bad}`);
  }
  assert(!/npm/i.test(zh + en), 'README files should not mention npm for this extension.');
}

async function checkAssets() {
  for (const [rel, width, height] of [
    ['ddys_open/resource/static/images/icon-16.png', 16, 16],
    ['ddys_open/resource/static/images/icon-32.png', 32, 32],
    ['ddys_open/resource/static/images/icon-192.png', 192, 192],
    ['ddys_open/resource/static/images/icon-512.png', 512, 512],
    ['ddys_open/resource/static/images/logo.png', 512, 512],
    ['ddys_open/resource/images/icon.png', 32, 32],
    ['ddys_open/resource/images/logo.png', 192, 192]
  ]) {
    const buffer = await fs.readFile(path.join(root, rel));
    const dim = pngDimensions(buffer);
    assert(dim && dim.width === width && dim.height === height, `${rel} must be ${width}x${height}.`);
  }
}

async function checkForbiddenFiles() {
  const files = await listFiles(root);
  for (const full of files) {
    const rel = slash(path.relative(root, full));
    assert(!/(^|\/)(\.env|node_modules|vendor|dist|build)(\/|$)/i.test(rel), `Forbidden file in repository: ${rel}`);
    assert(!/\.(zip|log|bak|tmp)$/i.test(rel), `Forbidden file in repository: ${rel}`);
    assert(!/^ddys_open\/cache\/.+\.php$/i.test(rel), `Runtime cache file must not be committed: ${rel}`);
    assert(!/^ddys_open\/cache\/request_.*\.lock$/i.test(rel), `Runtime lock file must not be committed: ${rel}`);
  }
}

async function checkForbiddenText() {
  const files = await listFiles(root);
  const patterns = ['ghp' + '_', 'github_pat_', 'npm' + '_', 'sk-', 'OpenAI', 'AI Agent', 'GPT', 'TODO', 'FIXME', 'var_dump', 'print_r', 'console.log'];
  const leftovers = ['EMLOG_ROOT', 'DDYS_OPEN_EMLOG', 'DDYS_OPEN_XIUNO', 'BLOG_URL', 'PLUGIN_URL', 'content/plugins', 'data-ddys-emlog', 'ddys-emlog', 'log_content'];
  for (const full of files) {
    const rel = slash(path.relative(root, full));
    if (rel === 'tools/check.mjs' || /\.(png|jpg|jpeg|webp|gif)$/i.test(rel)) continue;
    const text = await read(rel);
    for (const pattern of [...patterns, ...leftovers]) {
      if (text.includes(pattern)) {
        failures.push(`${rel} contains restricted text pattern ${pattern}`);
      }
    }
  }
}

async function checkBalancedPhp(file) {
  const text = await read(file);
  const pairs = { '}': '{', ')': '(', ']': '[' };
  const stack = [];
  let quote = '';
  let escaped = false;
  let blockComment = false;
  let lineComment = false;
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const next = text[i + 1];
    if (lineComment) {
      if (char === '\n') lineComment = false;
      continue;
    }
    if (blockComment) {
      if (char === '*' && next === '/') {
        blockComment = false;
        i++;
      }
      continue;
    }
    if (quote) {
      if (escaped) {
        escaped = false;
        continue;
      }
      if (char === '\\') {
        escaped = true;
        continue;
      }
      if (char === quote) quote = '';
      continue;
    }
    if (char === '/' && next === '/') {
      lineComment = true;
      i++;
      continue;
    }
    if (char === '#') {
      lineComment = true;
      continue;
    }
    if (char === '/' && next === '*') {
      blockComment = true;
      i++;
      continue;
    }
    if (char === '"' || char === "'") {
      quote = char;
      continue;
    }
    if (char === '{' || char === '(' || char === '[') stack.push(char);
    if (char === '}' || char === ')' || char === ']') {
      const opener = stack.pop();
      if (opener !== pairs[char]) {
        failures.push(`${file} has mismatched bracket near offset ${i}.`);
        return;
      }
    }
  }
  assert(stack.length === 0, `${file} has unclosed bracket(s).`);
  assert(quote === '', `${file} has unterminated string.`);
  assert(!blockComment, `${file} has unterminated block comment.`);
}

function pngDimensions(buffer) {
  if (buffer.toString('ascii', 1, 4) !== 'PNG') return null;
  return { width: buffer.readUInt32BE(16), height: buffer.readUInt32BE(20) };
}

async function mustExist(file) {
  try {
    await fs.access(path.join(root, file));
  } catch {
    failures.push(`Missing required file: ${file}`);
  }
}

async function read(file) {
  return fs.readFile(path.join(root, file), 'utf8');
}

async function listFiles(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    if (entry.name === '.git' || entry.name === 'node_modules' || entry.name === 'vendor') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...await listFiles(full));
    } else {
      files.push(full);
    }
  }
  return files;
}

function isTextFile(rel) {
  return /\.(php|xml|htm|html|js|css|md|json|txt)$/i.test(rel) || rel === 'ddys_open/conf' || rel === '.gitignore' || rel === 'LICENSE';
}

function slash(value) {
  return value.replace(/\\/g, '/');
}

function count(value, needle) {
  return value.split(needle).length - 1;
}

function assert(condition, message) {
  if (!condition) failures.push(message);
}
