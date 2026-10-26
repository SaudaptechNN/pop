(function () {
  var body = document.body;

  // Mobile sidebar
  var toggle = document.querySelector('.menu-toggle');
  function setSidebar(open) {
    body.classList.toggle('sidebar-open', open);
    if (toggle) toggle.setAttribute('aria-expanded', String(open));
  }
  if (toggle) toggle.addEventListener('click', function () { setSidebar(!body.classList.contains('sidebar-open')); });
  document.querySelectorAll('[data-close-sidebar]').forEach(function (el) {
    el.addEventListener('click', function () { setSidebar(false); });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setSidebar(false); });

  // Confirm before destructive actions
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
  var bulkForm = document.getElementById('bulk-form');
  if (bulkForm) {
    bulkForm.addEventListener('submit', function (e) {
      var action = bulkForm.querySelector('[name="bulk_action"]').value;
      var picked = bulkForm.querySelectorAll('input[name="ids[]"]:checked').length;
      if (!action) { e.preventDefault(); return; }
      if (action === 'delete' && !window.confirm('Delete ' + picked + ' selected inquiry(s)? This cannot be undone.')) e.preventDefault();
    });
  }

  // Select-all checkbox
  document.querySelectorAll('[data-check-all]').forEach(function (master) {
    master.addEventListener('change', function () {
      master.closest('table').querySelectorAll('tbody input[type="checkbox"]').forEach(function (cb) { cb.checked = master.checked; });
    });
  });

  // Live preview for image inputs
  document.querySelectorAll('img[data-preview]').forEach(function (img) {
    var input = document.querySelector('input[name="' + img.getAttribute('data-preview') + '"]');
    if (!input) return;
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file || !/^image\//.test(file.type)) return;
      img.src = URL.createObjectURL(file);
      img.classList.remove('is-empty');
    });
  });

  // Gallery dropzone
  document.querySelectorAll('[data-dropzone]').forEach(function (input) {
    var zone = input.closest('.dropzone');
    var label = zone.querySelector('[data-dropzone-label]');
    var original = label.textContent;
    input.addEventListener('change', function () {
      var n = input.files.length;
      label.textContent = n ? n + ' photo' + (n === 1 ? '' : 's') + ' selected' : original;
    });
    ['dragenter', 'dragover'].forEach(function (t) { zone.addEventListener(t, function () { zone.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (t) { zone.addEventListener(t, function () { zone.classList.remove('is-over'); }); });
  });

  // Show / hide password
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentElement.querySelector('input');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Hide' : 'Show';
    });
  });
})();
