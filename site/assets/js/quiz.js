(function () {
  // One structured source for the whole selector: the option lists, the recommendation
  // matrix from the brief and the catalogue cards. When this moves to WordPress it
  // becomes the equipment post type plus one settings screen — nothing is duplicated
  // into templates, so a new model only has to be described here once.
  var CATALOG_PAGE = {
    zsss: '/catalog/smartdrymix/',
    beton: '/catalog/smartbeton/',
    terminal: '/catalog/smartstock/'
  };

  var DIRECTIONS = {
    zsss: {
      label: 'Заводы сухих смесей',
      capacities: [
        ['5-10', '5–10 т/ч'],
        ['20-40', '20–40 т/ч'],
        ['50+', 'Свыше 50 т/ч']
      ],
      recommend: {
        '5-10': {
          title: 'SmartDryMix 5 G-L',
          text: 'Компактный гипсовый завод горизонтальной компоновки, 5 т/ч.'
        },
        '20-40': {
          title: 'SmartDryMix 20 C-L',
          text: 'Горизонтальный цементно-песчаный завод среднего масштаба, 20 т/ч.'
        },
        '50+': {
          title: 'SmartDryMix 50 GC-T',
          text: 'Крупная универсальная башенная линия для гипсовых и цементно-песчаных смесей, 50 т/ч.'
        }
      },
      cards: {
        '5-10': [
          { code: '5 G-L', name: 'SmartDryMix 5 G-L', capacity: '5 т/ч', text: 'Компактный гипсовый завод горизонтальной компоновки.' },
          { code: '20 C-L', name: 'SmartDryMix 20 C-L', capacity: '20 т/ч', text: 'Альтернатива с запасом мощности для развития производства.' }
        ],
        '20-40': [
          { code: '20 C-L', name: 'SmartDryMix 20 C-L', capacity: '20 т/ч', text: 'Цементно-песчаный завод среднего масштаба.' },
          { code: '50 GC-T', name: 'SmartDryMix 50 GC-T', capacity: '50 т/ч', text: 'Башенная линия с запасом для расширенной продуктовой программы.' }
        ],
        '50+': [
          { code: '50 GC-T', name: 'SmartDryMix 50 GC-T', capacity: '50+ т/ч', text: 'Крупная универсальная башенная линия.' }
        ]
      }
    },

    beton: {
      label: 'Бетонные заводы',
      capacities: [
        ['60', 'До 60 м³/ч'],
        ['60-80', '60–80 м³/ч'],
        ['90-100', '90–100 м³/ч'],
        ['100+', 'Свыше 100 м³/ч']
      ],
      recommend: {
        '60': {
          title: 'SmartBeton 60',
          text: 'До 50 м³/ч. Компактный завод для старта производства товарного бетона — для площадок с ограниченным пространством, стабильно работает в любых климатических условиях.'
        },
        '60-80': {
          title: 'SmartBeton 90',
          text: 'До 75 м³/ч. Завод средней производительности с гибкой комплектацией под тип смеси.'
        },
        '90-100': {
          title: 'SmartBeton 120',
          text: 'До 90 м³/ч. Универсальный завод для товарного бетона и широкого спектра бетонных смесей.'
        },
        '100+': {
          title: 'SmartBeton 135',
          text: 'До 100 м³/ч. Для крупных объёмов и масштабных задач.'
        }
      },
      cards: {
        '60': [
          { code: '60', name: 'SmartBeton 60', capacity: 'до 50 м³/ч', text: 'Компактный завод для старта производства товарного бетона.' },
          { code: '90', name: 'SmartBeton 90', capacity: 'до 75 м³/ч', text: 'Вариант с запасом производительности и гибкой комплектацией.' }
        ],
        '60-80': [
          { code: '90', name: 'SmartBeton 90', capacity: 'до 75 м³/ч', text: 'Завод средней производительности с гибкой комплектацией.' },
          { code: '120', name: 'SmartBeton 120', capacity: 'до 90 м³/ч', text: 'Универсальная модель с запасом производительности.' }
        ],
        '90-100': [
          { code: '120', name: 'SmartBeton 120', capacity: 'до 90 м³/ч', text: 'Универсальный завод для широкого спектра бетонных смесей.' },
          { code: '135', name: 'SmartBeton 135', capacity: 'до 100 м³/ч', text: 'Решение для крупных объёмов и высокой загрузки.' }
        ],
        '100+': [
          { code: '135', name: 'SmartBeton 135', capacity: 'до 100 м³/ч', text: 'Старшая готовая конфигурация для масштабных задач.' }
        ]
      }
    },

    terminal: {
      label: 'Цементные терминалы',
      capacities: [
        ['1000', '1000 тонн'],
        ['2000', '2000 тонн'],
        ['3000', '3000 тонн'],
        ['5000', '5000 тонн']
      ],
      recommend: {
        '1000': { title: 'SmartStock 1000', text: 'Готовый склад цемента на 1000 тонн: приёмка из вагонов-хопперов через приямок, хранение нескольких марок и подача потребителям пневмотранспортом.' },
        '2000': { title: 'SmartStock 2000', text: 'Готовый склад цемента на 2000 тонн: приёмка из вагонов-хопперов через приямок, хранение нескольких марок и подача потребителям пневмотранспортом.' },
        '3000': { title: 'SmartStock 3000', text: 'Готовый склад цемента на 3000 тонн: приёмка из вагонов-хопперов через приямок, хранение нескольких марок и подача потребителям пневмотранспортом.' },
        '5000': { title: 'SmartStock 5000', text: 'Склад цемента на 5000 тонн: приёмка из вагонов-хопперов через приямок, хранение нескольких марок и подача потребителям пневмотранспортом.' }
      },
      cards: {
        '1000': [
          { code: '1000', name: 'SmartStock 1000', capacity: '1000 тонн', text: 'Компактный терминал или интеграция с действующим производством.' },
          { code: '2000', name: 'SmartStock 2000', capacity: '2000 тонн', text: 'Вариант с увеличенным запасом для развития площадки.' }
        ],
        '2000': [
          { code: '2000', name: 'SmartStock 2000', capacity: '2000 тонн', text: 'Терминал среднего масштаба для производства или дистрибуции.' },
          { code: '3000', name: 'SmartStock 3000', capacity: '3000 тонн', text: 'Промышленный склад с запасом по вместимости.' }
        ],
        '3000': [
          { code: '3000', name: 'SmartStock 3000', capacity: '3000 тонн', text: 'Промышленный склад с несколькими марками цемента.' },
          { code: '5000', name: 'SmartStock 5000', capacity: '5000 тонн', text: 'Крупный распределительный комплекс с запасом вместимости.' }
        ],
        '5000': [
          { code: '5000', name: 'SmartStock 5000', capacity: '5000 тонн', text: 'Крупный комплекс для масштабной логистики цемента.' }
        ]
      }
    }
  };

  var direction = document.getElementById('quizDirection');
  var capacity = document.getElementById('quizCapacity');
  var stage = document.getElementById('quizStage');
  var submit = document.getElementById('quizSubmit');
  var output = document.getElementById('quizOutput');
  var cards = document.getElementById('quizCards');

  function track(event, payload) {
    window.dataLayer = window.dataLayer || [];
    var data = { event: event, page_url: location.href, page_title: document.title };
    for (var key in payload) data[key] = payload[key];
    window.dataLayer.push(data);
  }

  function hideOutput() {
    output.hidden = true;
    cards.replaceChildren();
  }

  function fillCapacities() {
    var list = DIRECTIONS[direction.value].capacities;
    capacity.replaceChildren.apply(capacity, list.map(function (pair) {
      var option = document.createElement('option');
      option.value = pair[0];
      option.textContent = pair[1];
      return option;
    }));
    hideOutput();
  }

  function element(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  function buildCard(item, index, key) {
    var card = element('article', 'model');

    var plate = element('div', 'model__plate on-dark');
    plate.append(
      element('span', 'model__badge' + (index ? ' model__badge--alt' : ''),
        index ? 'С запасом производительности' : 'Основная рекомендация'),
      element('span', 'model__code', item.code)
    );

    var spec = element('dl', 'model__spec');
    spec.append(
      element('dt', 'model__spec-label', 'Производительность / вместимость'),
      element('dd', 'model__spec-value', item.capacity)
    );

    var actions = element('div', 'model__actions');

    var more = element('a', 'btn btn--ghost', 'Подробнее');
    more.href = CATALOG_PAGE[key];
    more.setAttribute('data-stage', '');

    var quote = element('a', 'btn btn--solid', 'Получить КП');
    quote.href = '/#contact';
    quote.setAttribute('data-stage', '');
    quote.addEventListener('click', function () {
      track('quote_click', { direction: DIRECTIONS[key].label, model: item.name });
    });

    actions.append(more, quote);

    var body = element('div', 'model__body');
    body.append(
      element('div', 'model__category', 'Позиция из каталога'),
      element('h4', 'model__name', item.name),
      element('p', 'model__text', item.text),
      spec,
      actions
    );

    card.append(plate, body);
    return card;
  }

  function showResult() {
    var key = direction.value;
    var value = capacity.value;
    var config = DIRECTIONS[key];
    var recommendation = config.recommend[value];
    if (!recommendation) return;

    document.getElementById('recTitle').textContent = recommendation.title;
    document.getElementById('recText').textContent = recommendation.text;
    document.getElementById('recCapacity').textContent =
      'Параметр: ' + capacity.options[capacity.selectedIndex].text;
    document.getElementById('recStage').textContent = 'Стадия: ' + stage.value;

    cards.replaceChildren.apply(cards, (config.cards[value] || []).map(function (item, index) {
      return buildCard(item, index, key);
    }));

    output.hidden = false;
    track('quiz_show_result', {
      direction: config.label,
      capacity: capacity.options[capacity.selectedIndex].text,
      stage: stage.value,
      model: recommendation.title
    });
  }

  fillCapacities();
  direction.addEventListener('change', fillCapacities);
  capacity.addEventListener('change', hideOutput);
  stage.addEventListener('change', hideOutput);
  submit.addEventListener('click', showResult);
})();
