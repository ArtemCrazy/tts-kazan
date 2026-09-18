"""Главная страница: состав блоков и их значения.

Тексты — из статической версии (site/4/index.html). Модуль читает только
seed-blocks.py, наполнение запускается оттуда.
"""

PAGE = 'home'


def blocks():
    """Блоки главной в том же порядке, что в site/4/index.html."""
    return [
        {
            'block': 'hero',
            'anchor': 'hero',
            'fields': {
                'tts_hero_title': 'Заводы и производственные линии для стройматериалов',
                'tts_hero_accent': 'от проекта до запуска',
                'tts_hero_lead': 'Оборудование для производства сухих строительных смесей, '
                                 'товарного бетона, ЖБИ, ВПИ и строительства «под ключ».',
                'tts_hero_primary': {'title': 'Перейти в каталог', 'url': '/catalog/', 'target': ''},
                'tts_hero_secondary': {'title': 'Выбрать оборудование', 'url': '#quiz', 'target': ''},
                'tts_hero_photo': {'image': 'img/hero-plant-tts.webp',
                                    'alt': 'Бетонный завод ТТС: силосы цемента, смесительный узел, конвейер и склад заполнителей'},
                'tts_hero_tag': 'Проектирование и поставка под ключ',
                'tts_hero_proofs': [
                    {'tts_hero_proof_text': 'Собственное производство',
                     'tts_hero_proof_icon': 'proof-production'},
                    {'tts_hero_proof_text': 'Оборудование в наличии',
                     'tts_hero_proof_icon': 'proof-stock'},
                    {'tts_hero_proof_text': 'Филиал в Алматы — проекты по Казахстану',
                     'tts_hero_proof_icon': 'proof-almaty'},
                ],
            },
        },
        {
            'block': 'quiz',
            'anchor': 'quiz',
            'fields': {
                'tts_quiz_kicker': 'Подбор за 2 минуты',
                'tts_quiz_title': 'Какой объект вы планируете?',
                'tts_quiz_lead': 'Ответьте на три вопроса — покажем базовую конфигурацию '
                                 'и подготовим исходные данные для инженера.',
                'tts_quiz_label_object': 'Тип объекта',
                'tts_quiz_label_capacity': 'Производительность / хранение',
                'tts_quiz_label_stage': 'Стадия проекта',
                'tts_quiz_default_object': 'concrete',  # в статике открыт «Бетонный завод»
                'tts_quiz_stages': [
                    {'tts_quiz_stage': 'Формируем идею'},
                    {'tts_quiz_stage': 'Выбираем технологию', 'tts_quiz_stage_default': True},
                    {'tts_quiz_stage': 'Есть площадка и ТЗ'},
                ],
                'tts_quiz_submit': 'Показать решение',
                'tts_quiz_result_kicker': 'Предварительная рекомендация',
                'tts_quiz_cta': {'title': 'Получить инженерный расчёт', 'url': '#contact', 'target': ''},
                'tts_quiz_matches_title': 'Оборудование под выбранные параметры',
                'tts_quiz_matches_note': 'Первая карточка — основная рекомендация. Вторая, если '
                                         'доступна, показывает вариант с запасом производительности.',
            },
        },
        {
            'block': 'categories',
            'anchor': 'catalog',
            'fields': {
                'tts_categories_kicker': 'Каталог оборудования',
                'tts_categories_title': 'Выберите направление',
                'tts_categories_lead': 'На главной — только основные категории. Полный каталог '
                                       'с фильтрами и характеристиками находится на отдельных страницах.',
                # Номера 01–03 шаблон ставит сам по порядку карточек — поле
                # tts_category_index не заполняем.
                'tts_categories_cards': [
                    {
                        'tts_category_title': 'Заводы сухих смесей',
                        'tts_category_text': 'SmartDryMix 5–50+ т/ч. Горизонтальные и башенные '
                                             'компоновки под продукт и сырьё заказчика.',
                        'tts_category_link': {'title': 'Смотреть модели',
                                              'url': '/catalog/smartdrymix/', 'target': ''},
                        'tts_category_render': 'zsss',
                        'tts_category_alt': 'Завод сухих смесей SmartDryMix башенной компоновки: '
                                            'силосы, элеватор и участок отгрузки',
                    },
                    {
                        'tts_category_title': 'Бетонные заводы',
                        'tts_category_text': 'Решения для товарного бетона, ЖБИ, ВПИ, дорог '
                                             'и аэропортов производительностью 10–450 м³/ч.',
                        'tts_category_link': {'title': 'Смотреть модели',
                                              'url': '/catalog/smartbeton/', 'target': ''},
                        'tts_category_render': 'beton',
                        'tts_category_alt': 'Бетонный завод SmartBeton: силосы, смесительная башня '
                                            'и подача заполнителей',
                    },
                    {
                        'tts_category_title': 'Цементные терминалы',
                        'tts_category_text': 'SmartStock 1000–5000 тонн с механической '
                                             'или пневматической подачей цемента.',
                        'tts_category_link': {'title': 'Смотреть модели',
                                              'url': '/catalog/smartstock/', 'target': ''},
                        'tts_category_render': 'terminal',
                        'tts_category_alt': 'Цементный терминал SmartStock: батарея силосов на опорах',
                    },
                ],
            },
        },
        {
            'block': 'feature',
            'anchor': 'vpi',
            'fields': {
                'tts_feature_kicker': 'QUNFENG + ТТС · готовые линии ВПИ',
                'tts_feature_title': 'Вибропрессование под ключ — от рецептуры до паллетирования',
                'tts_feature_lead': 'ТТС проектирует БСУ, дозирование, автоматизацию и запуск. '
                                    'QUNFENG комплектует участок формования, пресс-формы '
                                    'и околопрессовое оборудование. Все узлы работают как единая '
                                    'технологическая линия.',
                'tts_feature_points': [
                    {'tts_feature_point': 'Три основные связки: SmartBeton 30 S / 60 S / 90 S '
                                          '+ вибропрессы QUNFENG'},
                    {'tts_feature_point': 'До 15–40 м³ бетона в час и до 30–66 изделий за цикл'},
                    {'tts_feature_point': 'Один подрядчик отвечает за интеграцию, запуск '
                                          'и поддержку 24/7'},
                ],
                'tts_feature_cta': {'title': 'Смотреть линии QUNFENG + ТТС',
                                    'url': '/catalog/vpi/', 'target': ''},
                'tts_feature_shot': {'image': 'img/vpi-press.webp',
                                    'alt': 'Вибропресс QUNFENG с пультом управления, гидростанцией и участком подачи поддонов'},
                'tts_feature_caption': 'единая линия ВПИ',
                'tts_feature_partner': {'image': 'img/qunfeng-logo.png'},
                'tts_feature_mirror': 0,
            },
        },
        {
            'block': 'feature',
            'anchor': 'lines',
            'fields': {
                'tts_feature_kicker': 'Бетонные заводы',
                'tts_feature_title': 'Бетонные заводы под ключ — от проекта до запуска',
                'tts_feature_lead': 'Проектируем и комплектуем бетонные заводы под требуемый продукт, '
                                    'производительность, логистику и условия площадки.',
                'tts_feature_points': [
                    {'tts_feature_point': 'Производительность от 10 до 450 м³/ч'},
                    {'tts_feature_point': 'Решения для товарного бетона, ЖБИ, ВПИ, дорог и аэропортов'},
                    {'tts_feature_point': 'Собственная АСУ SmartMix, монтаж и пусконаладка'},
                ],
                'tts_feature_cta': {'title': 'Подобрать бетонный завод',
                                    'url': '/catalog/smartbeton/', 'target': ''},
                'tts_feature_shot': {'image': 'img/beton-plant.webp',
                                    'alt': 'Бетонный завод SmartBeton: четыре силоса цемента, смесительный узел, конвейер, склад заполнителей и автобетоносмеситель'},
                'tts_feature_caption': 'SmartBeton 60 / 90 / 120 / 135',
                # Вторая секция подряд — зеркальная раскладка, как в статике
                # (section.feature.feature--mirror). Логотипа партнёра здесь нет.
                'tts_feature_mirror': 1,
            },
        },
        {
            'block': 'about',
            'anchor': 'company',
            'fields': {
                'tts_about_kicker': 'О компании',
                'tts_about_title': 'Инженерная экспертиза, производство и локальная поддержка',
                # Абзацы разделяются переводом строки — шаблон разбирает их сам.
                'tts_about_text': 'ТТС Инжиниринг объединяет проектирование, собственное производство '
                                  'оборудования и автоматизации, поставку и сопровождение промышленных '
                                  'объектов.\n'
                                  'Филиал в Алматы помогает учитывать требования площадки, логистику '
                                  'и особенности реализации проектов в Казахстане.',
                'tts_about_button': {'title': 'Посмотреть проекты', 'url': '#projects', 'target': ''},
                'tts_about_stats': [
                    {'tts_about_stat_value': '20+',
                     'tts_about_stat_label': 'лет на рынке промышленного оборудования',
                     'tts_about_stat_words': 0},
                    {'tts_about_stat_value': '1 000+',
                     'tts_about_stat_label': 'реализованных проектов по странам СНГ',
                     'tts_about_stat_words': 0},
                    # Значение словами — в вёрстке это stats__value--text
                    {'tts_about_stat_value': 'Собственная АСУ ТП',
                     'tts_about_stat_label': 'Разработка, внедрение и сопровождение',
                     'tts_about_stat_words': 1},
                    {'tts_about_stat_value': 'Техподдержка 24/7',
                     'tts_about_stat_label': 'Оперативная помощь в любое время',
                     'tts_about_stat_words': 1},
                ],
            },
        },
        {
            'block': 'service',
            'anchor': 'service',
            'fields': {
                'tts_service_block_kicker': 'Сервис и комплектующие',
                'tts_service_block_title': 'Поддерживаем производство после запуска',
                'tts_service_block_lead': 'Инженеры ТТС сопровождают оборудование, автоматику '
                                          'и технологический процесс — от первого запуска '
                                          'до планового обслуживания и срочной помощи.',
                'tts_service_block_trust': [
                    {'tts_service_block_trust_value': '24/7',
                     'tts_service_block_trust_label': 'техническая поддержка'},
                    {'tts_service_block_trust_value': 'На площадке',
                     'tts_service_block_trust_label': 'шеф-монтаж, пусконаладка и обучение'},
                    {'tts_service_block_trust_value': 'Единый контур',
                     'tts_service_block_trust_label': 'оборудование, собственная АСУ и комплектующие'},
                ],
                # Номера 01 и 02 шаблон ставит сам по порядку карточек.
                'tts_service_block_cards': [
                    {
                        'tts_service_block_card_tag': 'Минимум простоев',
                        'tts_service_block_card_title': 'Инженерный сервис',
                        'tts_service_block_card_text': 'Сопровождаем промышленное оборудование '
                                                       'на всём сроке эксплуатации и рассматриваем '
                                                       'неисправность в контексте всей технологической '
                                                       'линии.',
                        # Пункты списка — по одному в строке
                        'tts_service_block_card_points': 'Технический аудит и диагностика\n'
                                                         'Шеф-монтаж, подключение и пусконаладка\n'
                                                         'Калибровка, пробный выпуск и обучение персонала\n'
                                                         'Регламентное обслуживание и срочная техпомощь',
                        'tts_service_block_card_link': {'title': 'Подробнее о сервисе',
                                                        'url': '/service/', 'target': ''},
                        'tts_service_block_card_style': 'solid',
                        'tts_service_block_card_request': {'title': 'Оставить заявку',
                                                           'url': '#contact', 'target': ''},
                    },
                    {
                        'tts_service_block_card_tag': 'Совместимость узлов',
                        'tts_service_block_card_title': 'Запасные части и автоматика',
                        'tts_service_block_card_text': 'Подбираем комплектующие по модели, '
                                                       'спецификации, маркировке или фотографии '
                                                       'и проверяем совместимость до поставки.',
                        'tts_service_block_card_points': 'Узлы смесителей, шнеки, затворы и приводы\n'
                                                         'Датчики, пневматика и электрокомпоненты\n'
                                                         'Шкафы управления и элементы собственной АСУ\n'
                                                         'Подбор, поставка, монтаж и проверка работы',
                        'tts_service_block_card_link': {'title': 'Перейти к запчастям',
                                                        'url': '/parts/', 'target': ''},
                        # В статике вторая кнопка с рамкой (btn--ghost)
                        'tts_service_block_card_style': 'ghost',
                        'tts_service_block_card_request': {'title': 'Отправить запрос',
                                                           'url': '#contact', 'target': ''},
                    },
                ],
            },
        },
        {
            'block': 'pkn',
            'anchor': 'pkn',
            'fields': {
                'tts_pkn_photo': {'image': 'img/pkn-pump.webp',
                                    'alt': 'Пневмокамерный насос: приёмная воронка, смотровой люк, выпускной патрубок и запорная арматура'},
                'tts_pkn_caption': 'Дополнительное оборудование',
                'tts_pkn_kicker': 'Пневмотранспорт',
                'tts_pkn_title': 'Пневмокамерные насосы',
                'tts_pkn_lead': 'Для подачи цемента и других сухих сыпучих материалов на терминалах, '
                                'бетонных заводах и предприятиях ЖБИ.',
                'tts_pkn_specs': [
                    {'tts_pkn_spec_value': '10–60', 'tts_pkn_spec_label': 'т/ч'},
                    {'tts_pkn_spec_value': 'до 250', 'tts_pkn_spec_label': 'м по горизонтали'},
                    {'tts_pkn_spec_value': 'до 30', 'tts_pkn_spec_label': 'м по вертикали'},
                ],
                'tts_pkn_note': 'Поставляются в собранном виде, в комплект поставки входит '
                                'шкаф управления.',
                'tts_pkn_button': {'title': 'Подобрать ПКН', 'url': '/catalog/pkn/', 'target': ''},
            },
        },
        {
            'block': 'projects',
            'anchor': 'projects',
            'fields': {
                'tts_projects_kicker': 'Проекты в Казахстане',
                'tts_projects_title': 'Решения под продукт, площадку и задачу',
                # Лида в статике нет, поле оставляем пустым.
                'tts_projects_lead': '',
                # Карточки приходят из раздела «Проекты», в статике их три.
                'tts_projects_count': 3,
                'tts_projects_button': {'title': 'Обсудить похожий проект',
                                        'url': '#contact', 'target': ''},
            },
        },
        {
            'block': 'faq',
            'anchor': 'faq',
            'fields': {
                'tts_faq_block_kicker': 'Частые вопросы',
                'tts_faq_block_title': 'Подбор начинается с вашей задачи',
                'tts_faq_block_place': 'home',
            },
        },
        {
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Обсудим проект',
                'tts_form_title': 'Получите расчёт\nоборудования под\nвашу задачу',
                'tts_form_lead': 'Инженер уточнит продукт, производительность и исходные данные '
                                 'площадки, затем предложит состав оборудования и следующий этап проекта.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Пн–Пт, 9:00–18:00'},
                ],
                'tts_form_card_title': 'Оставьте контакты',
                'tts_form_note': 'Перезвоним в рабочее время и уточним задачу.',
                'tts_form_direction_label': 'Направление',
                'tts_form_directions': [
                    {'tts_form_direction': 'Заводы сухих смесей'},
                    {'tts_form_direction': 'Бетонные заводы', 'tts_form_direction_default': True},
                    {'tts_form_direction': 'Заводы ВПИ'},
                    {'tts_form_direction': 'Цементные терминалы'},
                    {'tts_form_direction': 'Инженерный сервис'},
                    {'tts_form_direction': 'Запасные части и автоматика'},
                ],
                'tts_form_comment_label': 'Комментарий',
                'tts_form_comment_hint': 'Кратко опишите задачу',
                'tts_form_submit': 'Получить консультацию',
                'tts_form_done_title': 'Заявка отправлена',
                'tts_form_done_text': 'Мы получили обращение и свяжемся с вами в рабочее время.',
                'tts_form_again': 'Отправить ещё одну заявку',
                'tts_form_source': 'general',
            },
        },
    ]


