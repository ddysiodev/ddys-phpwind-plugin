(function () {
  function $(selector, root) {
    return (root || document).querySelector(selector);
  }

  function setStatus(form, message, failed) {
    var node = $('.ddys-phpwind-admin-status', form) || $('.ddys-phpwind-admin-status', form.parentNode || form);
    if (!node) return;
    node.textContent = message || '';
    node.classList.toggle('is-error', !!failed);
  }

  function bindAjaxForms() {
    var forms = document.querySelectorAll('[data-ddys-phpwind-form], [data-ddys-phpwind-tool]');
    Array.prototype.forEach.call(forms, function (form) {
      form.addEventListener('submit', function (event) {
        if (!window.fetch || !window.FormData) return;
        event.preventDefault();
        var button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;
        setStatus(form, '处理中...');
        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin'
        }).then(function (response) {
          return response.json().catch(function () {
            return { code: 1, msg: '服务器返回格式无效。' };
          });
        }).then(function (json) {
          var ok = json && json.code === 0;
          setStatus(form, ok ? (json.data || json.msg || '完成。') : (json.msg || '操作失败。'), !ok);
        }).catch(function () {
          setStatus(form, '网络请求失败。', true);
        }).finally(function () {
          if (button) button.disabled = false;
        });
      });
    });
  }

  var routeMap = {
    ddys_movies: 'movies',
    ddys_latest: 'latest',
    ddys_hot: 'hot',
    ddys_search: 'search',
    ddys_suggest: 'suggest',
    ddys_calendar: 'calendar',
    ddys_movie: 'movie',
    ddys_sources: 'sources',
    ddys_related: 'related',
    ddys_comments: 'comments',
    ddys_collections: 'collections',
    ddys_collection: 'collection',
    ddys_shares: 'shares',
    ddys_share: 'share',
    ddys_requests: 'requests',
    ddys_activities: 'activities',
    ddys_user: 'user',
    ddys_types: 'types',
    ddys_genres: 'genres',
    ddys_regions: 'regions'
  };

  function addParam(params, name, value) {
    if (value !== null && value !== undefined && String(value) !== '') {
      params.set(name, value);
    }
  }

  function buildShortcode(tag, limit, page, value) {
    var attrs = [];
    if (tag === 'ddys_movie' || tag === 'ddys_sources' || tag === 'ddys_related' || tag === 'ddys_comments' || tag === 'ddys_collection') {
      attrs.push('slug="' + (value || 'this-tempting-madness') + '"');
    } else if (tag === 'ddys_share') {
      attrs.push('id="' + (value || '1') + '"');
    } else if (tag === 'ddys_user') {
      attrs.push('username="' + (value || 'demo') + '"');
    } else if (tag === 'ddys_search' || tag === 'ddys_suggest') {
      attrs.push('q="' + (value || 'movie') + '"');
    }
    if (/^(ddys_movies|ddys_latest|ddys_hot|ddys_suggest)$/.test(tag)) {
      attrs.push('limit="' + (limit || '12') + '"');
    }
    if (/^(ddys_movies|ddys_collections|ddys_shares|ddys_requests|ddys_activities)$/.test(tag)) {
      attrs.push('page="' + (page || '1') + '"');
    }
    return '[' + tag + (attrs.length ? ' ' + attrs.join(' ') : '') + ']';
  }

  function buildPage(root, tag, limit, page, value) {
    var view = 'latest';
    if (tag === 'ddys_hot') view = 'hot';
    if (tag === 'ddys_search') view = 'search';
    if (tag === 'ddys_calendar') view = 'calendar';
    if (tag === 'ddys_movie' || tag === 'ddys_sources' || tag === 'ddys_related' || tag === 'ddys_comments') view = 'movie';
    if (tag === 'ddys_collections') view = 'collections';
    if (tag === 'ddys_collection') view = 'collection';
    if (tag === 'ddys_requests' || tag === 'ddys_request_form') view = 'requests';
    var url = new URL(root, window.location.href);
    url.searchParams.set('app', 'ddys_open');
    if (view !== 'latest') url.searchParams.set('view', view);
    if (view === 'movie' || view === 'collection') addParam(url.searchParams, 'slug', value || 'this-tempting-madness');
    if (view === 'search') addParam(url.searchParams, 'q', value || '');
    if (limit) addParam(url.searchParams, 'limit', limit);
    if (page && /^(collections|requests)$/.test(view)) addParam(url.searchParams, 'page', page);
    return url.toString();
  }

  function buildProxy(apiUrl, tag, limit, page, value) {
    var route = routeMap[tag] || 'latest';
    var url = new URL(apiUrl, window.location.href);
    url.searchParams.set('route', route);
    if (/^(movie|sources|related|comments|collection)$/.test(route)) addParam(url.searchParams, 'slug', value || 'this-tempting-madness');
    if (route === 'share') addParam(url.searchParams, 'id', value || '1');
    if (route === 'user') addParam(url.searchParams, 'username', value || 'demo');
    if (route === 'search' || route === 'suggest') addParam(url.searchParams, 'q', value || '');
    if (limit && /^(latest|hot|suggest)$/.test(route)) addParam(url.searchParams, 'limit', limit);
    if (page && /^(movies|collections|shares|requests|activities|comments)$/.test(route)) addParam(url.searchParams, 'page', page);
    return url.toString();
  }

  function bindGenerator() {
    var box = $('.ddys-phpwind-generator');
    if (!box) return;
    var output = $('[data-ddys-generator-output]', box);
    function refresh() {
      var kind = $('[data-ddys-generator-kind]', box).value;
      var tag = $('[data-ddys-generator-type]', box).value;
      var limit = $('[data-ddys-generator-limit]', box).value;
      var page = $('[data-ddys-generator-page]', box).value;
      var value = $('[data-ddys-generator-value]', box).value;
      if (kind === 'page') {
        output.value = buildPage(box.getAttribute('data-ddys-page-url'), tag, limit, page, value);
      } else if (kind === 'proxy') {
        output.value = buildProxy(box.getAttribute('data-ddys-api-url'), tag, limit, page, value);
      } else {
        output.value = buildShortcode(tag, limit, page, value);
      }
    }
    Array.prototype.forEach.call(box.querySelectorAll('select,input'), function (node) {
      node.addEventListener('input', refresh);
      node.addEventListener('change', refresh);
    });
    var copy = $('[data-ddys-copy]', box);
    if (copy) {
      copy.addEventListener('click', function () {
        output.select();
        document.execCommand('copy');
      });
    }
    refresh();
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindAjaxForms();
    bindGenerator();
  });
})();

