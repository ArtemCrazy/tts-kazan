// Общий каталог оборудования: табы, поиск, два фильтра, модальное описание.
// Состав позиций перенесён из прототипа без изменений. При переезде на WordPress
// массив заменяется записями типа «Оборудование» — шаблон карточки и логика
// фильтров не меняются, поэтому данные описаны здесь один раз и никуда не дублируются.
(function () {
  var DIRECTION_PAGE = {
    zsss: '/catalog/smartdrymix/',
    beton: '/catalog/smartbeton/',
    vpi: '/catalog/vpi/',
    terminal: '/catalog/smartstock/',
    pkn: '/catalog/pkn/'
  };

  var CATEGORY_LABEL = {
    zsss: 'Заводы сухих смесей',
    beton: 'Бетонные заводы',
    vpi: 'Заводы ВПИ',
    terminal: 'Цементные терминалы',
    pkn: 'Пневмокамерные насосы'
  };

  var CATALOG = [
    { id: "zsss-5", cat: "zsss", purpose: "drymix", scale: "compact", code: "5 G-L",
      status: "Пример конфигурации", name: "SmartDryMix 5 G-L", capacity: "5 т/ч",
      text: "Компактный гипсовый завод горизонтальной компоновки для запуска собственного производства.",
      features: ["Гипсовые смеси", "Горизонтальная компоновка", "Модульный состав под продукт и площадку"] },
    { id: "zsss-20", cat: "zsss", purpose: "drymix", scale: "medium", code: "20 C-L",
      status: "Пример конфигурации", name: "SmartDryMix 20 C-L", capacity: "20 т/ч",
      text: "Горизонтальный цементно-песчаный завод среднего масштаба.",
      features: ["Цементно-песчаные смеси", "Горизонтальная компоновка", "Собственная АСУ SmartDryMix"] },
    { id: "zsss-50", cat: "zsss", purpose: "drymix", scale: "industrial", code: "50 GC-T",
      status: "Пример конфигурации", name: "SmartDryMix 50 GC-T", capacity: "50 т/ч",
      text: "Крупная универсальная башенная линия для гипсовых и цементно-песчаных смесей.",
      features: ["Гипсовые и цементные смеси", "Башенная компоновка", "Промышленная производительность"] },
    { id: "beton-60", cat: "beton", purpose: "ready", scale: "compact", code: "60",
      status: "Готовая конфигурация", name: "SmartBeton 60", capacity: "до 50 м³/ч",
      text: "Компактный завод для старта производства товарного бетона на ограниченной площадке.",
      features: ["Товарный бетон", "Компактная компоновка", "Стабильная работа в разных климатических условиях"] },
    { id: "beton-90", cat: "beton", purpose: "ready", scale: "medium", code: "90",
      status: "Готовая конфигурация", name: "SmartBeton 90", capacity: "до 75 м³/ч",
      text: "Завод средней производительности с гибкой комплектацией под тип смеси.",
      features: ["Гибкая комплектация", "Двухвальный смеситель CO-NELE", "АСУ SmartMix"] },
    { id: "beton-120", cat: "beton", purpose: "ready", scale: "industrial", code: "120",
      status: "Готовая конфигурация", name: "SmartBeton 120", capacity: "до 90 м³/ч",
      text: "Универсальный завод для товарного бетона и широкого спектра бетонных смесей.",
      features: ["Товарный бетон и ЖБИ", "Конвейерная или скиповая подача", "АСУ SmartMix"] },
    { id: "beton-135", cat: "beton", purpose: "ready", scale: "industrial", code: "135",
      status: "Готовая конфигурация", name: "SmartBeton 135", capacity: "до 100 м³/ч",
      text: "Высокопроизводительный завод для крупных объёмов и масштабных строительных задач.",
      features: ["Крупные объёмы", "Интенсивное смешивание", "Один оператор на весь завод"] },
    { id: "vpi-15", cat: "vpi", purpose: "vpi", scale: "compact", code: "15 S",
      status: "Типовая модель", name: "SmartBeton 15 S", capacity: "до 10 м³/ч",
      text: "Стартовая конфигурация для компактной линии ВПИ и небольших площадок.",
      features: ["До 16 изделий за цикл", "Под QUNFENG QF 400", "Точная жёсткая смесь"] },
    { id: "vpi-25", cat: "vpi", purpose: "vpi", scale: "compact", code: "25 S",
      status: "Типовая модель", name: "SmartBeton 25 S", capacity: "до 15 м³/ч",
      text: "Базовое решение для выпуска плитки, брусчатки, бордюров и блоков.",
      features: ["До 22 изделий за цикл", "Под QUNFENG QF 700–QS 1000", "Интеграция с вибропрессом"] },
    { id: "vpi-30", cat: "vpi", purpose: "vpi", scale: "medium", code: "30 S",
      status: "Типовая модель", name: "SmartBeton 30 S", capacity: "до 15 м³/ч",
      text: "Универсальная конфигурация для широкой номенклатуры одно- и двухслойных изделий.",
      features: ["До 30 изделий за цикл", "Основная и лицевая смесь", "Адресная подача бетона"] },
    { id: "vpi-50", cat: "vpi", purpose: "vpi", scale: "medium", code: "50 S",
      status: "Типовая модель", name: "SmartBeton 50 S", capacity: "до 20 м³/ч",
      text: "Завод среднего масштаба для интенсивной загрузки и расширенной продуктовой программы.",
      features: ["До 36 изделий за цикл", "Под QUNFENG QS 1200–1300", "Синхронизация с формовочным циклом"] },
    { id: "vpi-60", cat: "vpi", purpose: "vpi", scale: "industrial", code: "60 S",
      status: "Типовая модель", name: "SmartBeton 60 S", capacity: "до 30 м³/ч",
      text: "Промышленная конфигурация для высокопроизводительного вибропресса.",
      features: ["До 54 изделий за цикл", "Под QUNFENG QS 1500", "Непрерывная работа"] },
    { id: "vpi-70", cat: "vpi", purpose: "vpi", scale: "industrial", code: "70 S",
      status: "Типовая модель", name: "SmartBeton 70 S", capacity: "до 30 м³/ч",
      text: "Комплектация для крупного формовочного оборудования с точным дозированием.",
      features: ["До 54 изделий за цикл", "Под QUNFENG QS 1800", "Точное дозирование компонентов"] },
    { id: "vpi-90", cat: "vpi", purpose: "vpi", scale: "industrial", code: "90 S",
      status: "Типовая модель", name: "SmartBeton 90 S", capacity: "до 40 м³/ч",
      text: "Старшая типовая модель для крупных объёмов и широкой номенклатуры изделий.",
      features: ["До 66 изделий за цикл", "Под QUNFENG QS 2000", "Максимальная типовая мощность"] },
    { id: "stock-1000", cat: "terminal", purpose: "storage", scale: "compact", code: "1000",
      status: "Проектная конфигурация", name: "SmartStock 1000", capacity: "1000 тонн",
      text: "Компактный цементный терминал или интеграция с действующим производством.",
      features: ["Приём из вагонов и автотранспорта", "До 4 марок цемента", "Механическая или пневматическая подача"] },
    { id: "stock-2000", cat: "terminal", purpose: "storage", scale: "medium", code: "2000",
      status: "Проектная конфигурация", name: "SmartStock 2000", capacity: "2000 тонн",
      text: "Средний запас для производственной площадки или региональной дистрибуции.",
      features: ["Автоматизированная приёмка", "Контроль остатков", "Интеграция в действующую площадку"] },
    { id: "stock-3000", cat: "terminal", purpose: "storage", scale: "industrial", code: "3000",
      status: "Проектная конфигурация", name: "SmartStock 3000", capacity: "3000 тонн",
      text: "Промышленный склад с несколькими марками и гибкими маршрутами отгрузки.",
      features: ["До 4 марок цемента", "АСУ терминала", "Механическая или пневматическая ветка"] },
    { id: "stock-5000", cat: "terminal", purpose: "storage", scale: "industrial", code: "5000",
      status: "Проектная конфигурация", name: "SmartStock 5000", capacity: "5000 тонн",
      text: "Крупный распределительный комплекс для масштабной логистики цемента.",
      features: ["Железнодорожная и автомобильная приёмка", "Прозрачный учёт", "Производство комплекса — около 90 рабочих дней"] },
    { id: "pkn-10", cat: "pkn", purpose: "transport", scale: "compact", code: "Т10",
      status: "Пневмотранспорт", name: "ПКН Т10", capacity: "10 т/ч",
      text: "Модель для небольшого расхода материала и локальных производственных задач.",
      features: ["До 250 м по трассе", "До 30 м по высоте", "Шкаф управления в комплекте"] },
    { id: "pkn-20", cat: "pkn", purpose: "transport", scale: "compact", code: "Т20",
      status: "Пневмотранспорт", name: "ПКН Т20", capacity: "20 т/ч",
      text: "Универсальная модель для подачи цемента и других совместимых сыпучих материалов.",
      features: ["Вертикальное исполнение", "Разгонный участок 4 м", "Компоненты Camozzi и SMC"] },
    { id: "pkn-40", cat: "pkn", purpose: "transport", scale: "medium", code: "Т40",
      status: "Пневмотранспорт", name: "ПКН Т40", capacity: "40 т/ч",
      text: "Производительное исполнение для бетонных заводов и цементных терминалов.",
      features: ["Пневматический привод", "Дальность до 250 м", "Поставка в собранном виде"] },
    { id: "pkn-40n", cat: "pkn", purpose: "transport", scale: "medium", code: "Т40Н",
      status: "Модифицированный", name: "ПКН Т40Н", capacity: "40 т/ч",
      text: "Модифицированное исполнение с привязкой состава к точке установки.",
      features: ["Индивидуальная интеграция", "Управляемая подача", "Гарантия 2 года"] },
    { id: "pkn-60", cat: "pkn", purpose: "transport", scale: "industrial", code: "Т60",
      status: "Пневмотранспорт", name: "ПКН Т60", capacity: "60 т/ч",
      text: "Старшая модель линейки для высокой загрузки и промышленной производительности.",
      features: ["Для силосов, хопперов и вагонов", "0,6 МПа, 5–12 м³/мин", "Шкаф управления в комплекте"] }  ];

  var grid = document.getElementById('catalogGrid');
  if (!grid) return;

  var search = document.getElementById('catalogSearch');
  var purpose = document.getElementById('purposeFilter');
  var scale = document.getElementById('scaleFilter');
  var counter = document.getElementById('resultCount');
  var empty = document.getElementById('emptyState');
  var reset = document.getElementById('resetFilters');
  var emptyReset = document.getElementById('emptyReset');
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.tab'));

  var modal = document.getElementById('productModal');
  var modalDialog = modal.querySelector('.modal__dialog');
  var modalClose = document.getElementById('modalClose');

  var picked = document.getElementById('leadPicked');
  var pickedDirection = document.getElementById('leadDirection');
  var pickedFilters = document.getElementById('leadFilters');

  var category = 'all';
  var lastFocused = null;
  var filterTimer = null;

  function track(event, payload) {
    window.dataLayer = window.dataLayer || [];
    var data = { event: event, page_url: location.href, page_title: document.title };
    for (var key in payload) data[key] = payload[key];
    window.dataLayer.push(data);
  }

  // Регистр и «ё» не должны мешать поиску: человек ищет «шнек», а не точное написание
  function normalize(value) {
    return value.toLocaleLowerCase('ru-RU').split('ё').join('е');
  }

  function haystack(item) {
    return normalize([item.name, CATEGORY_LABEL[item.cat], item.capacity, item.text]
      .concat(item.features).join(' '));
  }

  function matches(item, query) {
    if (category !== 'all' && item.cat !== category) return false;
    if (purpose.value !== 'all' && item.purpose !== purpose.value) return false;
    if (scale.value !== 'all' && item.scale !== scale.value) return false;
    return !query || haystack(item).indexOf(query) > -1;
  }

  function filtered() {
    var query = normalize(search.value.trim());
    return CATALOG.filter(function (item) { return matches(item, query); });
  }

  function cardMarkup(item) {
    return '<article class="product">' +
      '<button class="product__media" type="button" data-cat="' + item.cat + '" data-open="' + item.id + '"' +
        ' aria-label="Характеристики: ' + item.name + '">' +
        '<span class="product__status">' + item.status + '</span>' +
        '<span class="product__code">' + item.code + '</span>' +
      '</button>' +
      '<div class="product__body">' +
        '<p class="product__category">' + CATEGORY_LABEL[item.cat] + '</p>' +
        '<h2 class="product__title">' + item.name + '</h2>' +
        '<p class="product__text">' + item.text + '</p>' +
        '<div class="product__capacity">' +
          '<span>Производительность / вместимость</span>' +
          '<strong>' + item.capacity + '</strong>' +
        '</div>' +
        '<div class="product__actions">' +
          '<button class="btn btn--solid product__quote" type="button" data-quote="' + item.id + '">Получить КП</button>' +
          '<button class="product__details" type="button" data-open="' + item.id + '">Характеристики и описание</button>' +
        '</div>' +
      '</div>' +
    '</article>';
  }

  function activeFilters() {
    var parts = [];
    if (category !== 'all') parts.push('категория: ' + CATEGORY_LABEL[category]);
    if (purpose.value !== 'all') parts.push('назначение: ' + purpose.options[purpose.selectedIndex].text);
    if (scale.value !== 'all') parts.push('масштаб: ' + scale.options[scale.selectedIndex].text);
    if (search.value.trim()) parts.push('поиск: ' + search.value.trim());
    return parts.join('; ');
  }

  // Состояние фильтров живёт в адресе страницы: подобранную выборку можно
  // сохранить в закладки и переслать инженеру ссылкой (п. 8.2 ТЗ).
  function syncUrl() {
    var params = new URLSearchParams();
    if (category !== 'all') params.set('cat', category);
    if (purpose.value !== 'all') params.set('purpose', purpose.value);
    if (scale.value !== 'all') params.set('scale', scale.value);
    if (search.value.trim()) params.set('q', search.value.trim());
    var query = params.toString();
    history.replaceState(null, '', query ? '?' + query : location.pathname);
  }

  function render() {
    var items = filtered();
    grid.innerHTML = items.map(cardMarkup).join('');
    grid.hidden = items.length === 0;
    empty.hidden = items.length > 0;
    counter.textContent = 'Найдено позиций: ' + items.length;
    if (pickedFilters) pickedFilters.value = activeFilters();
    syncUrl();
  }

  // Событие фильтра отправляем с паузой: иначе на каждую букву в поиске
  // в аналитику улетает отдельное срабатывание.
  function renderAndTrack() {
    render();
    clearTimeout(filterTimer);
    filterTimer = setTimeout(function () {
      track('catalog_filter', {
        catalog_category: category,
        catalog_purpose: purpose.value,
        catalog_scale: scale.value,
        catalog_query: search.value.trim(),
        catalog_results: filtered().length
      });
    }, 600);
  }

  function setCategory(next) {
    category = next;
    tabs.forEach(function (tab) {
      tab.setAttribute('aria-pressed', String(tab.dataset.category === next));
    });
  }

  function resetAll() {
    search.value = '';
    purpose.value = 'all';
    scale.value = 'all';
    setCategory('all');
    renderAndTrack();
    search.focus();
  }

  function find(id) {
    for (var i = 0; i < CATALOG.length; i++) if (CATALOG[i].id === id) return CATALOG[i];
    return null;
  }

  function openModal(item) {
    lastFocused = document.activeElement;
    modal.querySelector('[data-modal="media"]').dataset.cat = item.cat;
    modal.querySelector('[data-modal="status"]').textContent = item.status;
    modal.querySelector('[data-modal="code"]').textContent = item.code;
    modal.querySelector('[data-modal="category"]').textContent = CATEGORY_LABEL[item.cat];
    modal.querySelector('[data-modal="title"]').textContent = item.name;
    modal.querySelector('[data-modal="text"]').textContent = item.text;
    modal.querySelector('[data-modal="capacity"]').textContent = item.capacity;
    modal.querySelector('[data-modal="features"]').innerHTML =
      item.features.map(function (f) { return '<li class="modal__feature">' + f + '</li>'; }).join('');
    modal.querySelector('[data-modal="page"]').href = DIRECTION_PAGE[item.cat];
    modal.querySelector('[data-modal="quote"]').dataset.quote = item.id;
    modal.hidden = false;
    document.body.classList.add('is-locked');
    modalClose.focus();
    track('product_view', { item_id: item.id, item_name: item.name, item_category: CATEGORY_LABEL[item.cat] });
  }

  function closeModal() {
    if (modal.hidden) return;
    modal.hidden = true;
    document.body.classList.remove('is-locked');
    if (lastFocused) lastFocused.focus();
  }

  function requestQuote(item, source) {
    picked.value = item ? item.name : 'Подбор по задаче';
    if (pickedDirection) pickedDirection.value = item ? CATEGORY_LABEL[item.cat] : '';
    if (pickedFilters) pickedFilters.value = activeFilters();
    closeModal();
    track('quote_click', {
      item_id: item ? item.id : '',
      item_name: item ? item.name : '',
      cta_source: source
    });
    document.getElementById('contact').scrollIntoView({ block: 'start' });
    var name = document.getElementById('leadName');
    if (name) name.focus({ preventScroll: true });
  }

  grid.addEventListener('click', function (event) {
    var open = event.target.closest('[data-open]');
    if (open) { openModal(find(open.dataset.open)); return; }
    var quote = event.target.closest('[data-quote]');
    if (quote) requestQuote(find(quote.dataset.quote), 'card');
  });

  modal.addEventListener('click', function (event) {
    if (event.target === modal) { closeModal(); return; }
    var quote = event.target.closest('[data-quote]');
    if (quote) requestQuote(find(quote.dataset.quote), 'modal');
  });

  modalClose.addEventListener('click', closeModal);

  document.addEventListener('keydown', function (event) {
    if (modal.hidden) return;
    if (event.key === 'Escape') { closeModal(); return; }
    if (event.key !== 'Tab') return;

    // Фокус не должен уходить за пределы открытого окна (п. 8.3 ТЗ)
    var stops = modalDialog.querySelectorAll('a[href], button:not([disabled])');
    if (!stops.length) return;
    var first = stops[0];
    var last = stops[stops.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      last.focus();
      event.preventDefault();
    } else if (!event.shiftKey && document.activeElement === last) {
      first.focus();
      event.preventDefault();
    }
  });

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      setCategory(tab.dataset.category);
      renderAndTrack();
    });
  });

  search.addEventListener('input', renderAndTrack);
  purpose.addEventListener('change', renderAndTrack);
  scale.addEventListener('change', renderAndTrack);
  reset.addEventListener('click', resetAll);
  emptyReset.addEventListener('click', resetAll);

  // Открыли ссылку с готовой выборкой — восстанавливаем её до первой отрисовки
  var start = new URLSearchParams(location.search);
  if (start.get('cat') && CATEGORY_LABEL[start.get('cat')]) setCategory(start.get('cat'));
  if (start.get('purpose')) purpose.value = start.get('purpose');
  if (start.get('scale')) scale.value = start.get('scale');
  if (start.get('q')) search.value = start.get('q');

  render();
})();
