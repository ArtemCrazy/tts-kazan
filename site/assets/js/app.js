(function () {
  var burger = document.getElementById('burger');
  var nav = document.getElementById('nav');
  var notice = document.getElementById('notice');
  var groups = Array.prototype.slice.call(nav.querySelectorAll('.nav__group'));
  var desktop = window.matchMedia('(min-width: 901px)');
  var canHover = window.matchMedia('(hover: hover) and (pointer: fine)');
  var noticeTimer = null;

  function closeMenu() {
    nav.classList.remove('nav--open');
    burger.setAttribute('aria-expanded', 'false');
    burger.setAttribute('aria-label', 'Открыть меню');
  }

  burger.addEventListener('click', function () {
    var open = nav.classList.toggle('nav--open');
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
  });

  function setGroup(group, open) {
    var toggle = group.querySelector('.nav__toggle');
    var panel = group.querySelector('.dropdown');
    group.classList.toggle('nav__group--open', open);
    toggle.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
  }

  function closeGroups(except) {
    groups.forEach(function (group) {
      if (group !== except) setGroup(group, false);
    });
  }

  groups.forEach(function (group) {
    var toggle = group.querySelector('.nav__toggle');

    toggle.addEventListener('click', function () {
      var isOpen = toggle.getAttribute('aria-expanded') === 'true';
      closeGroups(group);
      if (!isOpen) {
        setGroup(group, true);
        return;
      }
      // On a hover device the menu is already open from mouseenter, so a click
      // must not close what the pointer is still sitting on. Touch has no hover:
      // there the second tap closes.
      if (!canHover.matches) setGroup(group, false);
    });

    group.addEventListener('mouseenter', function () {
      if (!canHover.matches || !desktop.matches) return;
      closeGroups(group);
      setGroup(group, true);
    });

    group.addEventListener('mouseleave', function () {
      if (!canHover.matches || !desktop.matches) return;
      setGroup(group, false);
    });

    group.addEventListener('focusout', function (event) {
      if (!group.contains(event.relatedTarget)) setGroup(group, false);
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    closeGroups(null);
    closeMenu();
  });

  document.addEventListener('click', function (event) {
    if (!nav.contains(event.target)) closeGroups(null);
  });

  nav.addEventListener('click', function (event) {
    if (!event.target.closest('a')) return;
    closeGroups(null);
    closeMenu();
  });

  // Реверс в светлую тему: показываем на варианте с рендером, выбор запоминаем.
  // Светлая тема рассчитана только на вариант с рендером: на фото- и видеофоне
  // она даёт нечитаемую смесь, поэтому там тема жёстко тёмная независимо от выбора.
  var themeToggle = document.getElementById('themeToggle');
  if (document.documentElement.dataset.hero !== 'render') {
    document.documentElement.setAttribute('data-theme', 'dark');
    themeToggle = null;
  }
  if (themeToggle) {
    var THEME = 'tts-theme';

    var applyTheme = function (name) {
      var light = name === 'light';
      document.documentElement.setAttribute('data-theme', light ? 'light' : 'dark');
      themeToggle.setAttribute('aria-pressed', String(light));
      themeToggle.setAttribute('aria-label', light ? 'Тёмная тема' : 'Светлая тема');
      try { localStorage.setItem(THEME, light ? 'light' : 'dark'); } catch (e) {}
    };

    var storedTheme = null;
    try { storedTheme = localStorage.getItem(THEME); } catch (e) {}
    applyTheme(storedTheme === 'light' ? 'light' : 'dark');

    themeToggle.addEventListener('click', function () {
      applyTheme(themeToggle.getAttribute('aria-pressed') === 'true' ? 'dark' : 'light');
    });
  }

  // Видеофон подключаем только в том варианте, где он нужен, и только если человек
  // не просил уменьшить анимацию — иначе остаётся кадр-постер.
  var heroVideo = document.querySelector('.hero__video');
  if (heroVideo && document.documentElement.dataset.hero === 'video') {
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!calm.matches) {
      heroVideo.src = heroVideo.dataset.src;
      heroVideo.play().catch(function () {});
    }
  }

  // Раскрытие вопросов. Открытие рисует CSS само, а закрытие приходится
  // придерживать: <details> прячет содержимое до того, как успеет пройти
  // переход, и блок схлопывается рывком.
  Array.prototype.forEach.call(document.querySelectorAll('.faq__item'), function (item) {
    var panel = item.querySelector('.faq__panel');
    var summary = item.querySelector('summary');
    if (!panel || !summary) return;

    summary.addEventListener('click', function (event) {
      if (!item.open) return;
      event.preventDefault();
      if (item.classList.contains('is-closing')) return;

      var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (calm) {
        item.open = false;
        return;
      }

      var timer = null;

      function finish() {
        clearTimeout(timer);
        panel.removeEventListener('transitionend', onEnd);
        item.classList.remove('is-closing');
        item.open = false;
      }

      function onEnd(e) {
        if (e.target === panel) finish();
      }

      item.classList.add('is-closing');
      panel.addEventListener('transitionend', onEnd);
      // страховка: если браузер не умеет анимировать строки сетки,
      // события не будет — вопрос обязан закрыться в любом случае
      timer = setTimeout(finish, 450);
    });
  });

  function showNotice(text) {
    notice.textContent = text;
    notice.hidden = false;
    requestAnimationFrame(function () {
      notice.classList.add('notice--shown');
    });
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(function () {
      notice.classList.remove('notice--shown');
      setTimeout(function () { notice.hidden = true; }, 240);
    }, 2600);
  }

  // Only the hero is built so far: every other target is announced instead of
  // sending the visitor to a page that does not exist yet.
  document.addEventListener('click', function (event) {
    var link = event.target.closest('[data-stage]');
    if (!link) return;
    event.preventDefault();

    // Якорь на раздел, который уже собран на этой странице, — не подсказка,
    // а рабочая ссылка. Прокручиваем сами: адреса записаны как "/#contact",
    // и браузер по ним ушёл бы в корень сайта, а не к блоку.
    var href = link.getAttribute('href') || '';
    var id = href.indexOf('#') > -1 ? href.slice(href.indexOf('#') + 1) : '';
    var target = id && document.getElementById(id);

    if (target && !link.hasAttribute('data-marquiz')) {
      var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      target.scrollIntoView({ behavior: calm ? 'auto' : 'smooth', block: 'start' });
      history.replaceState(null, '', '#' + id);
      return;
    }

    showNotice(link.hasAttribute('data-marquiz')
      ? 'Кнопка откроет квиз Marquiz — ждём ссылку от заказчика'
      : 'Раздел появится на следующем этапе сборки');
  });
})();
