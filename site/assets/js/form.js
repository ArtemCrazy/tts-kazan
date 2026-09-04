// Форма заявки. Проверка на стороне браузера — обязательная часть по п.11.2 ТЗ;
// серверная появится вместе с WordPress, сейчас отправки никуда не происходит.
(function () {
  var form = document.getElementById('leadForm');
  if (!form) return;

  var done = document.getElementById('leadDone');
  var again = document.getElementById('leadAgain');

  var messages = {
    name: 'Укажите, как к вам обращаться',
    phone: 'Оставьте телефон для связи',
    consent: 'Без согласия мы не сможем обработать заявку'
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
      if (digits.length < 10) text = 'Похоже, в номере не хватает цифр';
    }

    showError(control, text);
    return !text;
  }

  var checked = ['name', 'phone', 'consent'].map(function (n) {
    return form.elements[n];
  }).filter(Boolean);

  checked.forEach(function (control) {
    control.addEventListener('blur', function () { validate(control); });
    control.addEventListener('input', function () {
      if (fieldOf(control).classList.contains('is-invalid')) validate(control);
    });
    control.addEventListener('change', function () {
      if (control.type === 'checkbox') validate(control);
    });
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();

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

    if (window.dataLayer) {
      window.dataLayer.push({
        event: 'form_submit_success',
        direction: form.elements.direction ? form.elements.direction.value : ''
      });
    }

    form.hidden = true;
    done.hidden = false;
    done.focus && done.focus();
  });

  if (again) {
    again.addEventListener('click', function () {
      form.reset();
      checked.forEach(function (control) { showError(control, ''); });
      done.hidden = true;
      form.hidden = false;
      form.elements.name.focus();
    });
  }
})();
