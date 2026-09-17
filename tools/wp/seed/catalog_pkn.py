"""Страница «Пневмокамерные насосы»: состав блоков и их значения.

Тексты — из статической версии (site/4/catalog/pkn/index.html). Модуль читает
только seed-blocks.py, наполнение запускается оттуда.

Карточки насосов блок models берёт из раздела «Оборудование» по направлению
«pkn» — тексты карточек здесь не дублируются. Таблица общих характеристик
в статике шла внутри секции «Конструкция ПКН», поэтому у блока matrix своей
шапки нет: подпись для читалок подставится сама.
"""

PAGE = 'catalog/pkn'


def blocks():
    """Блоки страницы ПКН по порядку статической вёрстки."""
    return [
        {
            'block': 'page-head',
            'fields': {
                'tts_page_head_kicker': 'Пневмотранспорт сыпучих материалов',
                'tts_page_head_title': 'Пневмокамерные насосы',
                'tts_page_head_lead': 'Перемещают цемент и другие сыпучие и порошкообразные '
                                      'материалы сжатым воздухом. Применяются в строительной, '
                                      'пищевой и химической промышленности.',
                'tts_page_head_crumb': 'Пневмокамерные насосы',
                'tts_page_head_under_catalog': 1,
                'tts_page_head_primary': {'title': 'Смотреть модели', 'url': '#models',
                                          'target': ''},
                'tts_page_head_secondary': {'title': 'Рассчитать трассу подачи', 'url': '#contact',
                                            'target': ''},
                'tts_page_head_photo': 'pkn',
                'tts_page_head_stats': [
                    {'tts_page_head_stat_value': '10–60',
                     'tts_page_head_stat_label': 'т/ч — производительность в зависимости '
                                                 'от модели'},
                    {'tts_page_head_stat_value': 'до 250',
                     'tts_page_head_stat_label': 'метров — дальность подачи по трассе'},
                    {'tts_page_head_stat_value': 'до 30',
                     'tts_page_head_stat_label': 'метров — высота подачи материала'},
                    {'tts_page_head_stat_value': '4 м',
                     'tts_page_head_stat_label': 'сокращённый разгонный участок'},
                ],
            },
        },
        {
            'block': 'cards',
            'anchor': 'usage',
            'fields': {
                'tts_cards_kicker': 'Области применения',
                'tts_cards_title': 'Подача материала там, где механический транспорт неудобен',
                'tts_cards_lead': 'Трассу можно провести на значительное расстояние и в обход '
                                  'препятствий. Подбор начинается с материала, требуемой '
                                  'производительности, длины и высоты маршрута.',
                'tts_cards_grid': 'three',
                'tts_cards_tone': 'light',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Строительная промышленность',
                     'tts_cards_item_text': 'Подача цемента и сухих компонентов на бетонных '
                                            'заводах, цементных терминалах, предприятиях ЖБИ '
                                            'и производствах сухих смесей.',
                     'tts_cards_item_note': 'Основное применение — цемент'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Пищевая промышленность',
                     'tts_cards_item_text': 'Перемещение совместимых порошкообразных '
                                            'и мелкозернистых материалов в закрытой транспортной '
                                            'системе.',
                     'tts_cards_item_note': 'Конфигурация зависит от материала'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Химическая промышленность',
                     'tts_cards_item_text': 'Транспортировка совместимых сыпучих компонентов '
                                            'между технологическими участками предприятия.',
                     'tts_cards_item_note': 'Проверка свойств среды обязательна'},
                ],
            },
        },
        {
            'block': 'steps',
            'anchor': 'principle',
            'fields': {
                'tts_steps_kicker': 'Принцип работы',
                'tts_steps_title': 'Сжатый воздух перемещает материал по трубопроводу',
                'tts_steps_lead': 'Материал загружается в герметичную камеру. Воздух создаёт '
                                  'рабочее давление, переводит продукт в подвижное состояние '
                                  'и выталкивает его по заданной трассе с относительно низким '
                                  'расходом энергии.',
                'tts_steps_tone': 'muted',
                'tts_steps_items': [
                    {'tts_steps_item_title': 'Заполнение камеры',
                     'tts_steps_item_text': 'Сыпучий материал поступает в вертикальную камеру '
                                            'насоса через загрузочный узел.'},
                    {'tts_steps_item_title': 'Закрытие затвора',
                     'tts_steps_item_text': 'Дисковый затвор закрывает камеру перед подачей '
                                            'сжатого воздуха.'},
                    {'tts_steps_item_title': 'Подготовка материала',
                     'tts_steps_item_text': 'Сжатый воздух воздействует на материал и формирует '
                                            'условия для его движения.'},
                    {'tts_steps_item_title': 'Подача по трассе',
                     'tts_steps_item_text': 'Материал выталкивается по трубопроводу на расстояние '
                                            'до 250 м и высоту до 30 м.'},
                ],
            },
        },
        {
            # Карточки насосов ведутся в разделе «Оборудование», направление «pkn».
            # Лимит 0 — показать все модели направления (в статике их пять:
            # Т10, Т20, Т40, Т40Н, Т60).
            'block': 'models',
            'anchor': 'models',
            'fields': {
                'tts_models_kicker': 'Линейка оборудования',
                'tts_models_title': 'Модели ПКН производительностью от 10 до 60 т/ч',
                'tts_models_lead': 'Ниже — ориентиры для предварительного выбора. Исполнение, '
                                   'привод и фактическая производительность подтверждаются после '
                                   'расчёта материала, трассы и имеющейся компрессорной системы.',
                'tts_models_direction': {'term': 'pkn'},
                'tts_models_limit': 0,
                'tts_models_grid': 'models--3',
                'tts_models_tone': 'light',
                # В статике подпись кнопки своя у каждой карточки («Подобрать Т10»,
                # «Подобрать Т60»), а блок задаёт одну на всю секцию.
                'tts_models_cta': 'Подобрать модель',
            },
        },
        {
            'block': 'figures',
            'anchor': 'specs',
            'fields': {
                'tts_figures_kicker': 'Готовность к монтажу',
                'tts_figures_title': 'Поставка в собранном виде',
                'tts_figures_lead': 'Насос комплектуется шкафом управления с уже заданными '
                                    'настройками. На площадке остаётся установить оборудование, '
                                    'подключить коммуникации и выполнить пусконаладку.',
                'tts_figures_tone': 'dark',
                'tts_figures_items': [
                    {'tts_figures_item_value': '2 года',
                     'tts_figures_item_label': 'гарантия на оборудование'},
                    {'tts_figures_item_value': 'в сборе',
                     'tts_figures_item_label': 'со шкафом управления'},
                ],
            },
        },
        {
            'block': 'points',
            'fields': {
                'tts_points_kicker': 'Общие характеристики',
                'tts_points_title': 'Исходные параметры для подбора',
                'tts_points_tone': 'dark',
                'tts_points_items': [
                    {'tts_points_item_title': 'Производительность',
                     'tts_points_item_text': '10–60 т/ч, в зависимости от модели.'},
                    {'tts_points_item_title': 'Дальность и высота подачи',
                     'tts_points_item_text': 'До 250 м по трассе и до 30 м по высоте.'},
                    {'tts_points_item_title': 'Разгонный участок',
                     'tts_points_item_text': 'Сокращённая длина — 4 метра.'},
                    {'tts_points_item_title': 'Электропитание и воздух',
                     'tts_points_item_text': '380 В / 50 Гц; сжатый воздух 0,6 МПа, 5–12 м³/мин.'},
                ],
            },
        },
        {
            'block': 'cards',
            'anchor': 'construction',
            'fields': {
                'tts_cards_kicker': 'Конструкция ПКН',
                'tts_cards_title': 'Узлы для управляемой и герметичной подачи',
                'tts_cards_lead': 'Конструкция рассчитана на сокращение монтажного времени, '
                                  'стабильное управление потоком и работу под механическими '
                                  'нагрузками.',
                'tts_cards_grid': 'four',
                'tts_cards_tone': 'muted',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Разгонный участок — 4 м',
                     'tts_cards_item_text': 'Сокращённая длина без потери производительности '
                                            'уменьшает требования к свободному пространству '
                                            'на объекте.'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Дисковый затвор',
                     'tts_cards_item_text': 'Ручной или пневматический привод позволяет выбрать '
                                            'подходящий уровень автоматизации и точно '
                                            'регулировать подачу.'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Клапаны и датчики',
                     'tts_cards_item_text': 'Электромагнитные клапаны и датчики положения '
                                            'контролируют процесс и помогают исключить утечки '
                                            'материала.'},
                    {'tts_cards_item_index': '04',
                     'tts_cards_item_title': 'Стальной корпус',
                     'tts_cards_item_text': 'Корпус из углеродистой стали устойчив '
                                            'к механическим нагрузкам промышленной эксплуатации.'},
                ],
            },
        },
        {
            # Таблица общих характеристик. Шапка секции пустая: в статике таблица
            # шла сразу за карточками конструкции, без своего заголовка.
            'block': 'matrix',
            'fields': {
                'tts_matrix_tone': 'muted',
                'tts_matrix_columns': [
                    {'tts_matrix_column': 'Параметр'},
                    {'tts_matrix_column': 'Значение'},
                ],
                'tts_matrix_rows': [
                    {'tts_matrix_row_label': 'Производительность',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': '10–60 т/ч, в зависимости от модели'},
                     ]},
                    {'tts_matrix_row_label': 'Дальность подачи',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'до 250 м'},
                     ]},
                    {'tts_matrix_row_label': 'Высота подачи',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'до 30 м'},
                     ]},
                    {'tts_matrix_row_label': 'Разгонный участок',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': '4 м'},
                     ]},
                    {'tts_matrix_row_label': 'Электропитание',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': '380 В / 50 Гц'},
                     ]},
                    {'tts_matrix_row_label': 'Сжатый воздух',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': '0,6 МПа, 5–12 м³/мин'},
                     ]},
                    {'tts_matrix_row_label': 'Комплектация',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'в сборе, со шкафом управления'},
                     ]},
                    {'tts_matrix_row_label': 'Гарантия',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': '2 года'},
                     ]},
                ],
                'tts_matrix_note': 'Пневматика комплектуется компонентами Camozzi и SMC: '
                                   'каталожные узлы дают повторяемость характеристик '
                                   'и предсказуемую замену при обслуживании.',
            },
        },
        {
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Обсудим задачу',
                'tts_form_title': 'Подберём ПКН и проверим трассу подачи',
                'tts_form_lead': 'Инженер уточнит материал, требуемую производительность, длину '
                                 'и высоту трассы, а также имеющуюся компрессорную систему, '
                                 'затем предложит модель и схему подачи.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Техническая поддержка — 24/7'},
                ],
                'tts_form_card_title': 'Оставьте исходные данные',
                'tts_form_note': 'Перезвоним в рабочее время и уточним задачу.',
                'tts_form_direction_label': 'Отрасль',
                'tts_form_directions': [
                    {'tts_form_direction': 'Строительная промышленность'},
                    {'tts_form_direction': 'Пищевая промышленность'},
                    {'tts_form_direction': 'Химическая промышленность'},
                    {'tts_form_direction': 'Другое'},
                ],
                'tts_form_comment_label': 'Комментарий',
                'tts_form_comment_hint': 'Материал, производительность, длина и высота трассы',
                'tts_form_submit': 'Получить консультацию',
                # Экран «спасибо» — как на главной (tools/wp/seed/home.py): в статике
                # тут текст про демо-версию прототипа, на рабочем сайте он неверен.
                'tts_form_done_title': 'Заявка отправлена',
                'tts_form_done_text': 'Мы получили обращение и свяжемся с вами в рабочее время.',
                'tts_form_again': 'Отправить ещё одну заявку',
                'tts_form_source': 'catalog',
                'tts_form_picked': 1,
            },
        },
    ]
