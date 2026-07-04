# 低端影视 API PHPWind 扩展

[English](README.md) | 简体中文

[低端影视](https://ddys.io/) API 的官方 PHPWind 扩展。安装后，站点可以通过前台应用页、帖子短代码、WindEditor 插入按钮和本地 JSON 代理展示低端影视内容，并支持缓存、诊断和服务端求片表单。

- GitHub 仓库：[ddysiodev/ddys-phpwind-plugin](https://github.com/ddysiodev/ddys-phpwind-plugin)
- GitHub Release：[v0.1.0](https://github.com/ddysiodev/ddys-phpwind-plugin/releases/tag/v0.1.0)
- 下载压缩包：[ddys-phpwind-plugin-v0.1.0.zip](https://github.com/ddysiodev/ddys-phpwind-plugin/releases/download/v0.1.0/ddys-phpwind-plugin-v0.1.0.zip)
- 扩展目录：`ddys_open`
- 兼容目标：PHPWind 9 AppCenter 扩展体系
- 分发方式：GitHub Release ZIP

## 功能

- AppCenter Manifest：包含 PHPWind 9 元数据、资源目录、安装服务、主导航注册、后台菜单、编辑器应用、UBB 转换钩子和帖子阅读页兜底钩子。
- 后台配置：API Base URL、源站 URL、API Key、请求超时、缓存 TTL、默认数量、主题、布局、导航片段和求片表单。
- 后台诊断：连接测试、缓存状态、缓存清理、入口 URL 检查，以及短代码、前台页面和代理 URL 生成器。
- 前台页面：最新、热门、搜索、日历、影片详情、片单、片单详情和求片列表。
- 帖子短代码：通过 PHPWind UBB 转换和阅读页兜底渲染 `[ddys_*]` 标签。
- 编辑器按钮：在 WindEditor 工具栏插入常用 DDYS 短代码。
- 本地 JSON 代理：浏览器请求站点本地扩展入口，API Key 只保存在服务端。
- 服务端求片：带 nonce、限流、字段校验和清晰错误提示。
- 缓存：按接口和参数生成文件缓存，区分字典、新鲜内容、列表、详情和社区数据 TTL。
- 安全边界：PHPWind 入口保护、路由白名单、参数白名单、输出转义、媒体 URL 校验、请求超时、缓存隔离和敏感配置保护。

## 安装

1. 下载 Release 中的 `ddys-phpwind-plugin-v0.1.0.zip`。
2. 在 PHPWind 后台打开应用中心或本地应用安装入口，上传 ZIP。
3. 也可以手动解压后，将 `ddys_open` 上传到 `src/extensions/ddys_open`，再从应用中心安装。
4. 确认 PHPWind 已将扩展资源复制到 `themes/extres/ddys_open`。
5. 在后台 AppCenter 菜单打开“低端影视 API”，填写 API Base URL、缓存时间、展示样式和求片表单配置。
6. 如果要启用求片提交，填写 API Key，并在后台执行连接测试。

Release ZIP 必须包含顶层 `ddys_open/` 目录，因为 PHPWind 安装器会把压缩包根目录中的应用目录作为扩展入口。

## 前台入口

默认动态入口：

```text
index.php?m=app&app=ddys_open
index.php?m=app&app=ddys_open&c=index&a=run&view=hot
index.php?m=app&app=ddys_open&c=index&a=run&view=search
index.php?m=app&app=ddys_open&c=index&a=run&view=calendar
index.php?m=app&app=ddys_open&c=index&a=run&view=movie&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=run&view=collections
index.php?m=app&app=ddys_open&c=index&a=run&view=collection&slug=editor-choice
index.php?m=app&app=ddys_open&c=index&a=run&view=requests
```

本地代理示例：

```text
index.php?m=app&app=ddys_open&c=index&a=api&route=latest&limit=6
index.php?m=app&app=ddys_open&c=index&a=api&route=movie&slug=this-tempting-madness
index.php?m=app&app=ddys_open&c=index&a=api&route=collections&page=1
```

求片提交入口：

```text
index.php?m=app&app=ddys_open&c=index&a=request
```

后台设置入口：

```text
admin.php?m=app&app=ddys_open&c=manage&a=run
```

## 短代码

```text
[ddys_latest limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_suggest q="星际" limit="8"]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_related slug="this-tempting-madness"]
[ddys_comments slug="this-tempting-madness"]
[ddys_collections page="1"]
[ddys_collection slug="editor-choice"]
[ddys_shares page="1"]
[ddys_share id="1"]
[ddys_requests page="1"]
[ddys_activities page="1"]
[ddys_user username="demo"]
[ddys_types]
[ddys_genres]
[ddys_regions]
[ddys_request_form]
```

完整影片列表示例：

```text
[ddys_movies type="movie" genre="drama" region="us" year="2026" sort="latest" page="1" per_page="12"]
```

## 缓存

运行时缓存文件位于：

```text
src/extensions/ddys_open/cache
```

扩展会写入 `.htaccess` 和 `index.html`，减少缓存文件被直接访问的风险。后台可以随时清理缓存。

## 开发检查

在仓库根目录运行：

```powershell
node tools/check.mjs
```

检查覆盖 PHPWind 扩展结构、Manifest 元数据、注入服务、控制器、后台页面、前台页面、短代码覆盖、代理、求片、缓存、安全边界、UTF-8 编码、图标尺寸和敏感文件。
