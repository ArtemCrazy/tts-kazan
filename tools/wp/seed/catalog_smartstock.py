"""Страница «Цементные терминалы»: состав блоков и их значения.

Тексты — из статической версии (site/4/catalog/smartstock/index.html). Модуль
читает только seed-blocks.py, наполнение запускается оттуда.

Карточки вместимостей блок models берёт из раздела «Оборудование»
по направлению «smartstock» — тексты карточек здесь не дублируются.
"""

PAGE = 'catalog/smartstock'


def blocks():
    """Блоки страницы цементных терминалов по порядку статической вёрстки."""
    return [
        {
            'block': 'page-head',
            'fields': {
                'tts_page_head_kicker': 'Комплексы под ключ для Казахстана',
                'tts_page_head_title': 'Цементные терминалы SmartStock',
                'tts_page_head_lead': 'Индивидуально спроектированные и полностью '
                                      'автоматизированные комплексы, которые интегрируются '
                                      'в действующее производство и обеспечивают логистику '
                                      'и хранение цемента — от вагона-хоппера до цементовоза '
                                      'или производственного цеха.',
                'tts_page_head_crumb': 'Цементные терминалы',
                'tts_page_head_under_catalog': 1,
                'tts_page_head_primary': {'title': 'Выбрать вместимость', 'url': '#models',
                                          'target': ''},
                'tts_page_head_secondary': {'title': 'Получить схему терминала', 'url': '#contact',
                                            'target': ''},
                'tts_page_head_photo': 'smartstock',
                'tts_page_head_stats': [
                    {'tts_page_head_stat_value': '1000–5000',
                     'tts_page_head_stat_label': 'тонн полезной вместимости'},
                    {'tts_page_head_stat_value': 'ж/д + авто',
                     'tts_page_head_stat_label': 'приём цемента на терминале'},
                    {'tts_page_head_stat_value': 'до 4',
                     'tts_page_head_stat_label': 'марок цемента одновременно'},
                    {'tts_page_head_stat_value': '90',
                     'tts_page_head_stat_label': 'рабочих дней — средний срок выпуска'},
                ],
            },
        },
        {
            # Карточки вместимостей ведутся в разделе «Оборудование»,
            # направление «smartstock». Лимит 0 — показать все модели направления
            # (в статике их четыре: 1000, 2000, 3000, 5000).
            # Сетка пустая: в статике у блока .models нет модификатора, две в ряд.
            'block': 'models',
            'anchor': 'models',
            'fields': {
                'tts_models_kicker': 'Вместимость SmartStock',
                'tts_models_title': 'Выберите масштаб хранения',
                'tts_models_lead': 'Полезная ёмкость, число силосов, марки цемента и схема '
                                   'отгрузки уточняются при проектировании. Каждый вариант '
                                   'адаптируется к логистике и ограничениям площадки.',
                'tts_models_direction': {'term': 'smartstock'},
                'tts_models_limit': 0,
                'tts_models_grid': '',
                'tts_models_tone': 'light',
                'tts_models_cta': 'Запросить расчёт',
            },
        },
        {
            'block': 'cards',
            'anchor': 'routes',
            'fields': {
                'tts_cards_kicker': 'Две технологические ветки',
                'tts_cards_title': 'Как подать цемент из вагона-хоппера',
                'tts_cards_lead': 'Типовой вагон-хоппер вмещает около 65 тонн. Способ разгрузки '
                                  'выбирается по расстояниям, свободному месту, препятствиям '
                                  'и требованиям к обслуживанию.',
                'tts_cards_grid': 'two',
                'tts_cards_tone': 'muted',
                'tts_cards_items': [
                    {'tts_cards_item_tag': 'Механическая подача',
                     'tts_cards_item_title': 'Шнековый транспортёр и нория',
                     'tts_cards_item_text': 'Более быстрая и экономичная подача, но с большим '
                                            'количеством механических узлов для обслуживания. '
                                            'Оптимальна, когда силосы расположены рядом '
                                            'с терминалом и есть место для размещения '
                                            'оборудования.',
                     'tts_cards_item_note': 'Сильная сторона: скорость разгрузки и стоимость'},
                    {'tts_cards_item_tag': 'Пневматическая подача',
                     'tts_cards_item_title': 'Два пневмокамерных насоса',
                     'tts_cards_item_text': 'Разгрузка занимает больше времени, но система проще '
                                            'в обслуживании. Пневмотрасса позволяет подавать '
                                            'цемент на 200–300 метров и обходить существующие '
                                            'здания и другие препятствия.',
                     'tts_cards_item_note': 'Сильная сторона: гибкая трасса и расстояние'},
                ],
            },
        },
        {
            'block': 'steps',
            'anchor': 'flow',
            'fields': {
                'tts_steps_kicker': 'Логика комплекса',
                'tts_steps_title': 'От приёмки до отгрузки',
                'tts_steps_lead': 'АСУ связывает входные и выходные участки терминала в единый '
                                  'технологический контур.',
                'tts_steps_tone': 'light',
                'tts_steps_items': [
                    {'tts_steps_item_title': 'Приём транспорта',
                     'tts_steps_item_text': 'Железнодорожные вагоны-хопперы или автомобильный '
                                            'транспорт.'},
                    {'tts_steps_item_title': 'Разгрузка',
                     'tts_steps_item_text': 'Приямок, приёмный бункер и аспирация точки входа.'},
                    {'tts_steps_item_title': 'Транспортирование',
                     'tts_steps_item_text': 'Механическая ветка или пневмоподача до выбранных '
                                            'силосов.'},
                    {'tts_steps_item_title': 'Хранение',
                     'tts_steps_item_text': 'Контроль уровня и распределение до четырёх марок '
                                            'цемента.'},
                    {'tts_steps_item_title': 'Отгрузка',
                     'tts_steps_item_text': 'Телескопический загрузчик, автовесы или подача '
                                            'в производственный цех.'},
                ],
            },
        },
        {
            # Тёмная секция #asu в статике — двухколоночный .split
            # (site/4/catalog/smartstock/index.html): слева цифры по терминалу,
            # справа что даёт автоматизация. Переносим одним блоком split.
            'block': 'split',
            'anchor': 'asu',
            'fields': {
                'tts_split_kicker': 'Основные преимущества',
                'tts_split_title': 'Терминал как управляемая система',
                'tts_split_lead': 'Проектирование охватывает технологию, бизнес-логику потоков, '
                                  'обслуживание и интеграцию с действующей площадкой.',
                'tts_split_figures': [
                    {'tts_split_figure_value': '90',
                     'tts_split_figure_label': 'рабочих дней — средний срок выпуска'},
                    {'tts_split_figure_value': 'до 4',
                     'tts_split_figure_label': 'марок цемента в одном комплексе'},
                ],
                'tts_split_points_kicker': 'Что даёт автоматизация',
                'tts_split_points_title': 'Управление и прозрачность',
                'tts_split_points': [
                    {'tts_split_point_title': 'Комплексная автоматизация',
                     'tts_split_point_text': 'Собственная АСУ управляет точкой входа — приямком, '
                                             'бункером и аспирацией — и точкой выхода: силосами, '
                                             'загрузчиком и автовесами.'},
                    {'tts_split_point_title': 'Прозрачность для владельца',
                     'tts_split_point_text': 'Остатки доступны в реальном времени по данным '
                                             'ж/д-весов, автовесов и расчётному уровню '
                                             'в силосах — без выезда на площадку.'},
                    {'tts_split_point_title': 'Сложные проекты',
                     'tts_split_point_text': 'Управление потоками разных марок, работа в сложных '
                                             'климатических условиях и реализация нестандартных '
                                             'схем дистрибуции.'},
                    {'tts_split_point_title': 'Удобство эксплуатации',
                     'tts_split_point_text': 'Площадки обслуживания загрузчиков, головки '
                                             'элеватора, фильтров и аварийного обеспыливания '
                                             'закладываются при конструировании.'},
                ],
            },
        },
        {
            'block': 'cards',
            'anchor': 'equipment',
            'fields': {
                'tts_cards_kicker': 'Оснащение терминала',
                'tts_cards_title': 'Оборудование в составе проекта',
                'tts_cards_lead': 'Фактический набор узлов определяется выбранной схемой приёмки, '
                                  'хранения, внутренней логистики и отгрузки.',
                'tts_cards_grid': 'four',
                'tts_cards_tone': 'muted',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Приёмный узел',
                     'tts_cards_item_text': 'Приямок, бункер, разгрузочные устройства '
                                            'и аспирация.'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Силосы',
                     'tts_cards_item_text': 'Требуемый объём, число марок и вариант высоких опор.'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Механическая подача',
                     'tts_cards_item_text': 'Шнеки, ковшовый элеватор и распределительные '
                                            'устройства.'},
                    {'tts_cards_item_index': '04',
                     'tts_cards_item_title': 'Пневмоподача',
                     'tts_cards_item_text': 'Два пневмокамерных насоса и трубопроводная трасса.'},
                    {'tts_cards_item_index': '05',
                     'tts_cards_item_title': 'Отгрузка',
                     'tts_cards_item_text': 'Телескопические загрузчики для цементовозов.'},
                    {'tts_cards_item_index': '06',
                     'tts_cards_item_title': 'Весовой контроль',
                     'tts_cards_item_text': 'Железнодорожные и автомобильные весы.'},
                    {'tts_cards_item_index': '07',
                     'tts_cards_item_title': 'Аспирация',
                     'tts_cards_item_text': 'Фильтры силосов и аварийное обеспыливание.'},
                    {'tts_cards_item_index': '08',
                     'tts_cards_item_title': 'АСУ терминала',
                     'tts_cards_item_text': 'Маршрутизация потоков, остатки и контроль операций.'},
                ],
            },
        },
        {
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Обсудим проект',
                'tts_form_title': 'Получите схему\nSmartStock',
                'tts_form_lead': 'Инженер уточнит вместимость, число марок цемента, вид '
                                 'транспорта, расстояние до силосов и ограничения площадки, '
                                 'затем предложит схему терминала.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Техническая поддержка АСУ — 24/7'},
                ],
                'tts_form_card_title': 'Оставьте исходные данные',
                'tts_form_note': 'Перезвоним в рабочее время и уточним задачу.',
                'tts_form_direction_label': 'Схема подачи',
                'tts_form_directions': [
                    {'tts_form_direction': 'Требуется подобрать', 'tts_form_direction_default': True},
                    {'tts_form_direction': 'Механическая: шнек и нория'},
                    {'tts_form_direction': 'Пневматическая: 2 × ПКН'},
                ],
                'tts_form_comment_label': 'Комментарий',
                'tts_form_comment_hint': 'Марки цемента, вид транспорта, расстояние до силосов',
                'tts_form_submit': 'Получить консультацию',
                # Экран «спасибо» — как на главной (tools/wp/seed/home.py): в статике
                # тут текст про демо-версию прототипа, на рабочем сайте он неверен.
                'tts_form_done_title': 'Заявка отправлена',
                'tts_form_done_text': 'Мы получили обращение и свяжемся с вами в рабочее время.',
                'tts_form_again': 'Отправить ещё одну заявку',
                'tts_form_source': 'catalog',
                # Первое поле формы — список моделей направления, как в статике
                'tts_form_models_label': 'Вместимость',
                'tts_form_models_direction': {'term': 'smartstock'},
            },
        },
    ]
