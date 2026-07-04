(function () {
  function text(node, value) {
    if (node) node.textContent = value;
  }

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.matches || !form.matches('[data-ddys-phpwind-request-form]')) {
      return;
    }
    if (!window.fetch || !window.FormData) {
      return;
    }
    event.preventDefault();
    var status = form.querySelector('.ddys-phpwind-status');
    var button = form.querySelector('button[type="submit"]');
    text(status, '提交中...');
    if (button) button.disabled = true;
    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin'
    }).then(function (response) {
      return response.json().catch(function () {
        return { success: false, message: '服务器返回格式无效。' };
      });
    }).then(function (json) {
      var ok = json && (json.success === true || json.code === 0);
      text(status, ok ? '提交成功。' : (json.message || json.msg || '提交失败。'));
      if (ok) form.reset();
    }).catch(function () {
      text(status, '网络请求失败，请稍后重试。');
    }).finally(function () {
      if (button) button.disabled = false;
    });
  });
})();


