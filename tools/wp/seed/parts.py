"""Страница «Запасные части и автоматика»: состав блоков и их значения.

Тексты — из статической версии (site/4/parts/index.html). Модуль читает только
seed-blocks.py, наполнение запускается оттуда.
"""

PAGE = 'parts'


def blocks():
    """Запчасти: шапка, группы комплектующих, исходные данные, порядок работы, переход в сервис и форма."""
    return [
        {
            # Фон раздела не задаём: в статике шапка светлая, без photo-bed.
            'block': 'page-head',
            'fields': {
                'tts_page_head_kicker': 'Комплектующие для действующего производства',
                'tts_page_head_title': 'Запасные части и автоматика',
                'tts_page_head_lead': 'Подбираем узлы и компоненты по модели, спецификации, '
                                      'маркировке или фотографии. Проверяем совместимость '
                                      'и при необходимости связываем поставку с монтажом '
                                      'и настройкой.',
                'tts_page_head_primary': {'title': 'Отправить запрос', 'url': '#contact',
                                          'target': ''},
                'tts_page_head_secondary': {'title': 'Нужен сервис', 'url': '#service-band',
                                            'target': ''},
                'tts_page_head_stats': [
                    {'tts_page_head_stat_value': '8',
                     'tts_page_head_stat_label': 'групп комплектующих'},
                    {'tts_page_head_stat_value': '5',
                     'tts_page_head_stat_label': 'направлений оборудования ТТС'},
                    {'tts_page_head_stat_value': 'Проверка',
                     'tts_page_head_stat_label': 'совместимости до поставки'},
                    {'tts_page_head_stat_value': '24/7',
                     'tts_page_head_stat_label': 'техническая поддержка АСУ'},
                ],
            },
        },
        {
            # Секция #catalog-parts: восемь коротких карточек по четыре в ряд.
            'block': 'cards',
            'anchor': 'catalog-parts',
            'fields': {
                'tts_cards_kicker': 'Что поставляем',
                'tts_cards_title': 'Комплектующие для ключевых узлов производства',
                'tts_cards_lead': 'Структура запроса построена вокруг оборудования ТТС: '
                                  'бетонные заводы, линии сухих смесей, цементные терминалы, '
                                  'пневмотранспорт, ВПИ и системы автоматизации.',
                'tts_cards_grid': 'four',
                'tts_cards_tone': 'light',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Смесители и рабочие органы',
                     'tts_cards_item_text': 'Узлы смешивания и сменные элементы.'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Шнеки и транспортёры',
                     'tts_cards_item_text': 'Винты, секции, опоры и элементы приводов.'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Затворы и клапаны',
                     'tts_cards_item_text': 'Запорные узлы и исполнительные механизмы.'},
                    {'tts_cards_item_index': '04',
                     'tts_cards_item_title': 'Пневматика',
                     'tts_cards_item_text': 'Пневмоцилиндры, распределители '
                                            'и подготовка воздуха.'},
                    {'tts_cards_item_index': '05',
                     'tts_cards_item_title': 'Датчики',
                     'tts_cards_item_text': 'Уровень, влажность, положение и расход.'},
                    {'tts_cards_item_index': '06',
                     'tts_cards_item_title': 'Приводы',
                     'tts_cards_item_text': 'Электродвигатели, мотор-редукторы и редукторы.'},
                    {'tts_cards_item_index': '07',
                     'tts_cards_item_title': 'Шкафы управления',
                     'tts_cards_item_text': 'Компоненты управления, силовая часть '
                                            'и интерфейсы.'},
                    {'tts_cards_item_index': '08',
                     'tts_cards_item_title': 'АСУ ТП',
                     'tts_cards_item_text': 'Элементы собственной автоматизации '
                                            'и программная поддержка.'},
                ],
            },
        },
        {
            # Секция #selection: четыре карточки, приглушённый фон.
            'block': 'cards',
            'anchor': 'selection',
            'fields': {
                'tts_cards_kicker': 'Как ускорить подбор',
                'tts_cards_title': 'Чем точнее исходные данные, тем быстрее проверка '
                                   'совместимости',
                'tts_cards_lead': 'Если точное обозначение неизвестно, отправьте доступные '
                                  'данные. Технический специалист сопоставит их с узлом '
                                  'оборудования и уточнит недостающие параметры.',
                'tts_cards_grid': 'four',
                'tts_cards_tone': 'muted',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Модель оборудования',
                     'tts_cards_item_text': 'Название линии, завода или отдельного узла.'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Маркировка детали',
                     'tts_cards_item_text': 'Шильдик, артикул или обозначение производителя.'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Спецификация',
                     'tts_cards_item_text': 'Чертёж, ведомость или фрагмент документации.'},
                    {'tts_cards_item_index': '04',
                     'tts_cards_item_title': 'Фото и размеры',
                     'tts_cards_item_text': 'Общий вид, место установки '
                                            'и основные размеры.'},
                ],
            },
        },
        {
            # Секция #flow: четыре этапа, светлый фон. Номера блок ставит сам.
            'block': 'steps',
            'anchor': 'flow',
            'fields': {
                'tts_steps_kicker': 'Порядок работы',
                'tts_steps_title': 'От идентификации до установки',
                'tts_steps_lead': 'Каждый шаг фиксируется: заказчик понимает, что именно '
                                  'поставляется и почему выбран именно этот узел.',
                'tts_steps_tone': 'light',
                'tts_steps_items': [
                    {'tts_steps_item_title': 'Получаем запрос',
                     'tts_steps_item_text': 'Фиксируем оборудование, деталь '
                                            'и срочность потребности.'},
                    {'tts_steps_item_title': 'Проверяем совместимость',
                     'tts_steps_item_text': 'Сверяем характеристики, присоединительные '
                                            'размеры и исполнение.'},
                    {'tts_steps_item_title': 'Предлагаем вариант',
                     'tts_steps_item_text': 'Согласовываем состав поставки и необходимые '
                                            'сопутствующие элементы.'},
                    {'tts_steps_item_title': 'Поставляем и подключаем',
                     'tts_steps_item_text': 'При необходимости выполняем монтаж, настройку '
                                            'и проверку работы.'},
                ],
            },
        },
        {
            # Секция #service-band: переход на страницу сервиса.
            'block': 'band',
            'anchor': 'service-band',
            'fields': {
                'tts_band_title': 'Запчасти можно заказать вместе с сервисом',
                'tts_band_text': 'Инженер проведёт диагностику, определит нужный узел '
                                 'и проконтролирует его установку.',
                'tts_band_button': {'title': 'Перейти к сервису', 'url': '/service/',
                                    'target': ''},
                'tts_band_tone': 'dark',
            },
        },
        {
            # Поле выбора статики «Категория» переносим в варианты выбора блока.
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Запрос на подбор',
                'tts_form_title': 'Отправьте данные\nоборудования или детали',
                'tts_form_lead': 'Технический специалист уточнит исполнение, проверит '
                                 'совместимость и предложит следующий шаг.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Пн–Пт, 9:00–18:00'},
                ],
                'tts_form_card_title': 'Подобрать комплектующие',
                'tts_form_note': 'Укажите контакты и всё, что известно о детали.',
                'tts_form_direction_label': 'Категория',
                'tts_form_directions': [
                    {'tts_form_direction': 'Требуется уточнить', 'tts_form_direction_default': True},
                    {'tts_form_direction': 'Смесители и рабочие органы'},
                    {'tts_form_direction': 'Шнеки и транспортёры'},
                    {'tts_form_direction': 'Затворы и клапаны'},
                    {'tts_form_direction': 'Пневматика'},
                    {'tts_form_direction': 'Датчики'},
                    {'tts_form_direction': 'Приводы'},
                    {'tts_form_direction': 'Шкафы управления'},
                    {'tts_form_direction': 'АСУ ТП'},
                ],
                'tts_form_comment_label': 'Оборудование и деталь',
                'tts_form_comment_hint': 'Модель, маркировка, размеры — всё, что известно',
                'tts_form_submit': 'Отправить запрос',
                # Экран «спасибо»: в статике он описывает прототип («в рабочей
                # версии…»), поэтому текст берём такой же, как на главной.
                'tts_form_done_title': 'Заявка отправлена',
                'tts_form_done_text': 'Мы получили обращение и свяжемся с вами в рабочее время.',
                'tts_form_again': 'Отправить ещё один запрос',
                'tts_form_source': 'parts',
            },
        },
    ]
