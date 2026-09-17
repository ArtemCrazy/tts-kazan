// Форма заявки. Проверка в браузере — по п.11.2 ТЗ, серверная проверка живёт
// в теме WordPress (inc/lead.php). Если у формы нет адреса отправки
// (статическая версия сайта), показываем подтверждение без отправки.
(function () {
  var form = document.getElementById('leadForm');
  if (!form) return;

  var done = document.getElementById('leadDone');
  var again = document.getElementById('leadAgain');
  var endpoint = form.getAttribute('data-endpoint');
  var button = form.querySelector('.form__submit');
  var formError = form.querySelector('[data-error-form]');
  var opened = Date.now();

  var messages = {
    name: 'Укажите имя',
    phone: 'Оставьте телефон',
    consent: 'Нужно согласие'
  };

  function fieldOf(control) {
    return control.closest('.field') || control.closest('.consent');
  }

  function showError(control, text) {
    var box = fieldOf(control);
    if (!box) return;
    var slot = box.querySelector('[data-error]');
    if (slot) slot.textContent = text;
    box.classList.toggle('is-invalid', Boolean(text));
    control.setAttribute('aria-invalid', text ? 'true' : 'false');
  }

  function validate(control) {
    var name = control.name;
    var value = control.type === 'checkbox' ? control.checked : control.value.trim();
    var text = '';

    if (!value && messages[name]) {
      text = messages[name];
    } else if (name === 'phone' && value) {
      // цифр должно хватать на реальный номер — маску не навязываем,
      // человек может писать с кодом страны и без него
      var digits = value.replace(/\D/g, '');
      if (digits.length < 10) text = 'Проверьте номер';
    }

    showError(control, text);
    return !text;
  }

  var checked = ['name', 'phone', 'consent'].map(function (n) {
    return form.elements[n];
  }).filter(Boolean);

  function track(event, payload) {
    window.dataLayer = window.dataLayer || [];
    var data = { event: event, page_url: location.href, page_title: document.title };
    for (var key in payload) data[key] = payload[key];
    window.dataLayer.push(data);
  }

  // form_start по п.13 ТЗ — один раз за визит, на первом осмысленном вводе
  var started = false;
  form.addEventListener('input', function () {
    if (started) return;
    started = true;
    track('form_start', { form_id: form.id });
  });

  checked.forEach(function (control) {
    control.addEventListener('blur', function () { validate(control); });
    control.addEventListener('input', function () {
      if (fieldOf(control).classList.contains('is-invalid')) validate(control);
    });
    control.addEventListener('change', function () {
      if (control.type === 'checkbox') validate(control);
    });
  });

  // Рекламные метки и gclid: сохраняем на время визита и прикладываем к заявке
  // (п. 13 ТЗ). Личных данных в этом хранилище нет, только параметры ссылки.
  var MARKS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid'];

  function marks() {
    var found = {};
    try {
      var saved = JSON.parse(sessionStorage.getItem('ttsMarks') || '{}');
      MARKS.forEach(function (key) { if (saved[key]) found[key] = saved[key]; });
    } catch (error) { /* хранилище недоступно — работаем без него */ }

    var query = new URLSearchParams(location.search);
    MARKS.forEach(function (key) {
      var value = query.get(key);
      if (value) found[key] = value;
    });

    try { sessionStorage.setItem('ttsMarks', JSON.stringify(found)); } catch (error) { /* не страшно */ }
    return found;
  }

  function payload() {
    var found = marks();
    var utm = MARKS.filter(function (key) { return key !== 'gclid' && found[key]; })
      .map(function (key) { return key + '=' + found[key]; }).join('&');

    return {
      name: form.elements.name.value.trim(),
      phone: form.elements.phone.value.trim(),
      comment: form.elements.comment ? form.elements.comment.value.trim() : '',
      direction: form.elements.direction ? form.elements.direction.value : '',
      model: form.elements.model ? form.elements.model.value : '',
      source: form.getAttribute('data-source') || '',
      page: location.href,
      utm: utm,
      gclid: found.gclid || '',
      consent: Boolean(form.elements.consent && form.elements.consent.checked),
      company: form.elements.company ? form.elements.company.value : '',
      spent: Math.round((Date.now() - opened) / 1000)
    };
  }

  function showDone() {
    form.hidden = true;
    done.hidden = false;
    done.focus && done.focus();
  }

  function eventData() {
    return {
      form_id: form.id,
      form_type: form.getAttribute('data-source') || '',
      direction: form.elements.direction ? form.elements.direction.value : '',
      model: form.elements.model ? form.elements.model.value : ''
    };
  }

  var sending = false;

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (sending) return; // защита от второго клика по кнопке

    var ok = true;
    var first = null;
    checked.forEach(function (control) {
      if (!validate(control)) {
        ok = false;
        if (!first) first = control;
      }
    });

    if (!ok) {
      first.focus();
      return;
    }

    if (formError) formError.textContent = '';

    // Статическая версия: отправлять некуда, просто подтверждаем.
    if (!endpoint) {
      track('form_submit_success', eventData());
      showDone();
      return;
    }

    sending = true;
    if (button) button.disabled = true;

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload())
    }).then(function (response) {
      return response.json().then(function (data) { return { status: response.status, data: data }; });
    }).then(function (result) {
      if (result.data && result.data.ok) {
        // Событие успеха только после подтверждения сервером (п. 13 ТЗ).
        track('form_submit_success', eventData());
        showDone();
        return;
      }

      var errors = (result.data && result.data.errors) || {};
      Object.keys(errors).forEach(function (name) {
        var control = form.elements[name];
        if (control) showError(control, errors[name]);
      });
      if (errors.form && formError) formError.textContent = errors.form;
      track('form_submit_error', eventData());
    }).catch(function () {
      if (formError) formError.textContent = 'Не удалось отправить. Попробуйте ещё раз или позвоните нам.';
      track('form_submit_error', eventData());
    }).finally(function () {
      sending = false;
      if (button) button.disabled = false;
    });
  });

  if (again) {
    again.addEventListener('click', function () {
      form.reset();
      checked.forEach(function (control) { showError(control, ''); });
      done.hidden = true;
      form.hidden = false;
      started = false;
      opened = Date.now();
      if (formError) formError.textContent = '';
      form.elements.name.focus();
    });
  }
})();
