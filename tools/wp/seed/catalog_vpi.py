"""Страница «Заводы ВПИ QUNFENG + ТТС»: состав блоков и их значения.

Тексты — из статической версии (site/4/catalog/vpi/index.html). Модуль читает
только seed-blocks.py, наполнение запускается оттуда.

Что важно помнить по этой странице:

* карточки комплектаций блок models берёт из раздела «Оборудование»
  по направлению «vpi» — тексты карточек здесь не дублируются;
* таблица комплектаций — блок matrix; в статике она шла внутри секции моделей,
  поэтому своей шапки не имеет, подпись для читалок подставится сама;
* оговорка про расчётный характер экономических показателей (п. 16 ТЗ) лежит
  в лиде блока figures и со страницы уходить не должна.
"""

PAGE = 'catalog/vpi'


def blocks():
    """Блоки страницы ВПИ по порядку статической вёрстки."""
    return [
        {
            'block': 'page-head',
            'fields': {
                'tts_page_head_kicker': 'QUNFENG + ТТС · готовые линии ВПИ',
                'tts_page_head_title': 'Готовая линия ВПИ под ключ',
                'tts_page_head_lead': 'Вибропрессование от рецептуры до готовой продукции. '
                                      'ТТС отвечает за бетонный узел, дозирование, автоматизацию '
                                      'и запуск, QUNFENG — за формование, пресс-формы '
                                      'и околопрессовое оборудование.',
                'tts_page_head_crumb': 'Заводы ВПИ',
                'tts_page_head_under_catalog': 1,
                'tts_page_head_primary': {'title': 'Подобрать линию', 'url': '#models', 'target': ''},
                'tts_page_head_secondary': {'title': 'Обсудить проект', 'url': '#contact', 'target': ''},
                'tts_page_head_photo': 'vpi',
                'tts_page_head_stats': [
                    {'tts_page_head_stat_value': '3',
                     'tts_page_head_stat_label': 'уровня комплектации линии'},
                    {'tts_page_head_stat_value': '15–40',
                     'tts_page_head_stat_label': 'м³/ч бетонной смеси'},
                    {'tts_page_head_stat_value': '30–66',
                     'tts_page_head_stat_label': 'изделий за цикл'},
                    {'tts_page_head_stat_value': '24/7',
                     'tts_page_head_stat_label': 'техническая поддержка АСУ'},
                ],
            },
        },
        {
            'block': 'cards',
            'anchor': 'problem',
            'fields': {
                'tts_cards_kicker': 'Проблематика',
                'tts_cards_title': 'Почему разрозненная линия не выходит на мощность',
                'tts_cards_lead': 'Когда узлы закупаются у разных поставщиков, за результат линии '
                                  'не отвечает никто. Готовая линия снимает этот риск на этапе '
                                  'проектирования.',
                'tts_cards_grid': 'four',
                'tts_cards_tone': 'light',
                'tts_cards_items': [
                    {'tts_cards_item_index': '01',
                     'tts_cards_item_title': 'Оборудование разных поставщиков',
                     'tts_cards_item_text': 'Бетонный узел, пресс и околопрессовое оборудование '
                                            'закупаются отдельно, а стыковка узлов остаётся '
                                            'задачей заказчика.'},
                    {'tts_cards_item_index': '02',
                     'tts_cards_item_title': 'Рецептура без лабораторной проработки',
                     'tts_cards_item_text': 'Жёсткая смесь для вибропрессования требует подбора '
                                            'состава: без него страдают геометрия и прочность '
                                            'изделий.'},
                    {'tts_cards_item_index': '03',
                     'tts_cards_item_title': 'Разрозненная поддержка',
                     'tts_cards_item_text': 'При отклонениях приходится обращаться к нескольким '
                                            'поставщикам, и каждый отвечает только за свой узел.'},
                    {'tts_cards_item_index': '04',
                     'tts_cards_item_title': 'Несогласованный темп узлов',
                     'tts_cards_item_text': 'Если производительность бетонного узла и пресса '
                                            'не согласована, линия не выходит на плановую '
                                            'мощность.'},
                ],
            },
        },
        {
            'block': 'steps',
            'anchor': 'chain',
            'fields': {
                'tts_steps_kicker': 'Единая технологическая цепочка',
                'tts_steps_title': 'Вся линия от одного подрядчика',
                'tts_steps_lead': 'Линия проектируется как одна система: от хранения сырья '
                                  'до паллетированной продукции на складе.',
                'tts_steps_tone': 'muted',
                'tts_steps_items': [
                    {'tts_steps_item_title': 'Хранение и дозирование сырья',
                     'tts_steps_item_text': 'Силосы, бункеры заполнителей и дозирование '
                                            'компонентов под выбранную рецептуру.'},
                    {'tts_steps_item_title': 'БСУ SmartBeton',
                     'tts_steps_item_text': 'Бетонный узел с двумя смесителями CO-NELE '
                                            'и собственной АСУ SmartDose и SmartMix.'},
                    {'tts_steps_item_title': 'Вибропресс QUNFENG',
                     'tts_steps_item_text': 'Формование изделий на прессах серии QS с подбором '
                                            'пресс-форм под номенклатуру.'},
                    {'tts_steps_item_title': 'Камеры выдержки',
                     'tts_steps_item_text': 'Контролируемый набор прочности изделий до разгрузки.'},
                    {'tts_steps_item_title': 'Разгрузка и паллетирование',
                     'tts_steps_item_text': 'Съём готовой продукции, укладка на паллеты '
                                            'и подготовка к отгрузке.'},
                ],
            },
        },
        {
            'block': 'cards',
            'anchor': 'roles',
            'fields': {
                'tts_cards_kicker': 'Зоны ответственности',
                'tts_cards_title': 'Два производителя — один согласованный результат',
                'tts_cards_lead': 'Разделение зафиксировано на этапе проекта, поэтому за стык '
                                  'узлов и итоговую производительность линии отвечает подрядчик, '
                                  'а не заказчик.',
                'tts_cards_grid': 'two',
                'tts_cards_tone': 'light',
                'tts_cards_items': [
                    {'tts_cards_item_tag': 'ТТС',
                     'tts_cards_item_title': 'Бетонный узел и автоматизация',
                     'tts_cards_item_text': 'БСУ SmartBeton и смесители CO-NELE, дозирование, '
                                            'собственная АСУ ТП SmartMix и SmartDose, '
                                            'проектирование, монтаж, пусконаладка '
                                            'и поддержка 24/7.',
                     'tts_cards_item_note': 'Проект, запуск и сопровождение линии'},
                    {'tts_cards_item_tag': 'QUNFENG',
                     'tts_cards_item_title': 'Формование и околопрессовое оборудование',
                     'tts_cards_item_text': 'Вибропрессы серии QS 1000–2000, пресс-формы, '
                                            'камеры выдержки, разгрузка и паллетирование '
                                            'готовых изделий.',
                     'tts_cards_item_note': 'Формование и обработка изделий'},
                ],
            },
        },
        {
            # Карточки комплектаций ведутся в разделе «Оборудование», направление «vpi».
            # Лимит 3 — столько уровней показывает статическая страница (30 S, 60 S, 90 S),
            # остальные базовые конфигурации перечислены в примечании под таблицей.
            #
            # Важно: в направлении «vpi» семь моделей (15 S … 90 S), а блок берёт
            # первые по порядку из раздела «Оборудование». Чтобы на странице вышли
            # именно 30 S, 60 S и 90 S, эти три модели должны стоять в разделе
            # первыми — иначе поднимутся 15 S, 25 S и 30 S.
            'block': 'models',
            'anchor': 'models',
            'fields': {
                'tts_models_kicker': 'Комплектации',
                'tts_models_title': 'Три уровня производительности',
                'tts_models_lead': 'Базовые комплектации линии. Совместимость с конкретным '
                                   'вибропрессом и фактические показатели подтверждаются '
                                   'после инженерной сверки.',
                'tts_models_direction': {'term': 'vpi'},
                # В направлении семь моделей, а на странице показываем три
                # основные комплектации — как в согласованной версии сайта
                'tts_models_chosen': {'posts': ['SmartBeton 30 S', 'SmartBeton 60 S', 'SmartBeton 90 S']},
                'tts_models_limit': 3,
                'tts_models_grid': 'models--3',
                'tts_models_tone': 'muted',
                'tts_models_cta': 'Подобрать линию',
            },
        },
        {
            # Таблица комплектаций. Шапка секции пустая: в статике таблица шла
            # сразу за карточками моделей, без своего заголовка.
            'block': 'matrix',
            'fields': {
                'tts_matrix_tone': 'muted',
                'tts_matrix_columns': [
                    {'tts_matrix_column': 'Комплектация'},
                    {'tts_matrix_column': 'Бетон, м³/ч'},
                    {'tts_matrix_column': 'Изделий за цикл'},
                    {'tts_matrix_column': 'Базовый состав'},
                ],
                'tts_matrix_rows': [
                    {'tts_matrix_row_label': 'SmartBeton 30 S + QS 1000–1200',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'до 15'},
                         {'tts_matrix_cell': 'до 30'},
                         {'tts_matrix_cell': '2 × CO-NELE; SmartMix'},
                     ]},
                    {'tts_matrix_row_label': 'SmartBeton 60 S + QS 1500',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'до 30'},
                         {'tts_matrix_cell': 'до 54'},
                         {'tts_matrix_cell': '2 × CO-NELE; SmartMix'},
                     ]},
                    {'tts_matrix_row_label': 'SmartBeton 90 S + QS 2000',
                     'tts_matrix_cells': [
                         {'tts_matrix_cell': 'до 40'},
                         {'tts_matrix_cell': 'до 66'},
                         {'tts_matrix_cell': '2 × CO-NELE; SmartMix'},
                     ]},
                ],
                'tts_matrix_note': 'Дополнительные базовые конфигурации: SmartBeton 15 S, 25 S, '
                                   '50 S и 70 S. Характеристики и совместимость с конкретным '
                                   'вибропрессом выводятся только после инженерной сверки.',
            },
        },
        {
            # Оговорка п. 16 ТЗ: экономика — расчётный ориентир, а не гарантия.
            # Текст перенесён дословно и должен остаться на странице.
            'block': 'figures',
            'anchor': 'economy',
            'fields': {
                'tts_figures_kicker': 'Экономика линии',
                'tts_figures_title': 'Управляемая себестоимость и выход на плановую мощность',
                'tts_figures_lead': 'Все показатели экономики являются расчётными ориентирами, '
                                    'а не гарантией результата. Они зависят от сырья, рецептуры, '
                                    'ассортимента, цен, загрузки, логистики и других исходных '
                                    'данных — по вашим данным мы выполним индивидуальный '
                                    'технико-экономический расчёт.',
                'tts_figures_tone': 'dark',
                'tts_figures_items': [
                    {'tts_figures_item_value': '12–16',
                     'tts_figures_item_label': 'месяцев — ориентировочная окупаемость'},
                    {'tts_figures_item_value': 'до 15%',
                     'tts_figures_item_label': 'снижение расхода цемента'},
                    {'tts_figures_item_value': 'до 20%',
                     'tts_figures_item_label': 'снижение эксплуатационных затрат'},
                    {'tts_figures_item_value': '85–95%',
                     'tts_figures_item_label': 'целевая загрузка линии'},
                ],
            },
        },
        {
            'block': 'points',
            'fields': {
                'tts_points_kicker': 'Номенклатура',
                'tts_points_title': 'Одна линия — широкая номенклатура ВПИ',
                'tts_points_tone': 'dark',
                'tts_points_items': [
                    {'tts_points_item_title': 'Тротуарная плитка',
                     'tts_points_item_text': 'Основная продукция линии при смене пресс-форм.'},
                    {'tts_points_item_title': 'Брусчатка',
                     'tts_points_item_text': 'Изделия повышенной прочности для проездов '
                                             'и площадок.'},
                    {'tts_points_item_title': 'Бордюрный камень',
                     'tts_points_item_text': 'Дорожные и садовые бордюры в составе одной линии.'},
                    {'tts_points_item_title': 'Стеновые блоки',
                     'tts_points_item_text': 'Стеновые изделия при соответствующей комплектации '
                                             'пресс-форм.'},
                ],
            },
        },
        {
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Обсудим проект',
                'tts_form_title': 'Получите конфигурацию QUNFENG + ТТС под ваш продукт',
                'tts_form_lead': 'Инженер уточнит планируемые изделия, требуемую '
                                 'производительность, данные площадки и логистику, затем '
                                 'предложит состав линии и следующий этап проекта.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Техническая поддержка АСУ — 24/7'},
                ],
                'tts_form_card_title': 'Оставьте исходные данные',
                'tts_form_note': 'Перезвоним в рабочее время и уточним задачу.',
                'tts_form_direction_label': 'Планируемые изделия',
                'tts_form_directions': [
                    {'tts_form_direction': 'Тротуарная плитка'},
                    {'tts_form_direction': 'Брусчатка'},
                    {'tts_form_direction': 'Бордюрный камень'},
                    {'tts_form_direction': 'Стеновые блоки'},
                    {'tts_form_direction': 'Несколько видов изделий'},
                ],
                'tts_form_comment_label': 'Комментарий',
                'tts_form_comment_hint': 'Производительность, площадка, сроки запуска',
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
