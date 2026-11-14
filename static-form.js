/* Enquiry form for the static (Vercel) build.
   Sends through Web3Forms when an access key was provided at build time,
   otherwise opens the visitor's email app with the details filled in. */
(function () {
  document.querySelectorAll('.gpc-static-form').forEach(function (form) {
    var status = form.querySelector('.gpc-form-status');
    var button = form.querySelector('button[type="submit"]');
    var q = new URLSearchParams(location.search);
    var req = q.get('request');
    form.elements.request.value = req === 'assessment' || req === 'project' ? req : form.dataset.defaultRequest || 'project';
    var svc = q.get('service');
    if (svc && Array.prototype.some.call(form.elements.service.options, function (o) { return o.value === svc; })) {
      form.elements.service.value = svc;
      var d = form.querySelector('[data-book-details]'); if (d) d.open = true;
    }
    if (form.elements.request.value === 'assessment') { var det = form.querySelector('[data-book-details]'); if (det) det.open = true; }
    function say(msg, ok) { status.textContent = msg; status.style.color = ok ? '#0a7a4a' : '#b42318'; status.focus && status.focus(); }
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.elements.botcheck && form.elements.botcheck.checked) return;
      var data = new FormData(form);
      var key = form.elements.access_key.value;
      if (!key) {
        var lines = ['Request: ' + data.get('request'), 'Name: ' + data.get('name'), 'Email: ' + data.get('email'), 'Service: ' + (data.get('service') || ''), 'Book: ' + (data.get('book') || ''), 'Link: ' + (data.get('book_url') || ''), '', data.get('message')];
        location.href = 'mailto:' + form.dataset.contactEmail + '?subject=' + encodeURIComponent('Website enquiry from ' + data.get('name')) + '&body=' + encodeURIComponent(lines.join('\n'));
        say('Your email app should open with your message ready to send.', true);
        return;
      }
      button.disabled = true; var label = button.textContent; button.textContent = 'Sending…';
      fetch(form.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (r) {
          if (r.success) { form.reset(); say('Thank you. Your enquiry has been sent and we will reply about the next step.', true); }
          else { say(r.message || 'Something went wrong. Please email us directly.', false); }
        })
        .catch(function () { say('We could not send your enquiry. Please email us directly.', false); })
        .then(function () { button.disabled = false; button.textContent = label; });
    });
  });
})();
