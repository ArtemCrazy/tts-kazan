"""Сборка внутренних страниц из общего набора блоков.

Шапка, подвал и меню одинаковы на всех страницах, а относительные адреса
у каждой свои. Держать это копией в семи файлах — гарантированно разъехаться,
поэтому разметка собирается отсюда, а страницы описываются только контентом
(см. build-pages.py).

Классы блоков живут в assets/css/page.css.
"""

import html

# Адреса страниц относительно корня концепции (site/4/).
# Отсюда же строится меню: пока страницы нет в SITE, ссылка на неё остаётся
# заглушкой и показывает подсказку вместо перехода.
SITE = {
    'home': '',
    'catalog': 'catalog/',
    'smartdrymix': 'catalog/smartdrymix/',
    'smartbeton': 'catalog/smartbeton/',
    'vpi': 'catalog/vpi/',
    'smartstock': 'catalog/smartstock/',
    'pkn': 'catalog/pkn/',
    'service': 'service/',
    'parts': 'parts/',
    'privacy': 'privacy/',
    'personal': 'personal-data/',
    'cookie': 'cookie/',
    'notfound': '404/',
}

# Ничего не осталось: все разделы, на которые ведут ссылки, собраны.
# Заглушкой остаётся только внешний квиз Marquiz — ждём ссылку от заказчика.
STAGED = {}

DIRECTIONS = [
    ('smartdrymix', 'Заводы сухих смесей', 'SmartDryMix 5–50+ т/ч'),
    ('smartbeton', 'Бетонные заводы', 'Товарный бетон, ЖБИ и дороги'),
    ('vpi', 'Заводы ВПИ', 'Готовые линии QUNFENG + ТТС'),
    ('smartstock', 'Цементные терминалы', 'SmartStock 1000–5000 тонн'),
    ('pkn', 'Пневмокамерные насосы', 'ПКН 10–60 т/ч, подача до 250 м'),
]

ARROW = ('<svg class="btn__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" '
         'focusable="false"><path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" '
         'stroke-width="2" stroke-linecap="square"></path></svg>')

ARROW_SM = ('<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">'
            '<path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" '
            'stroke-linecap="square"></path></svg>')


def e(text):
    return html.escape(text, quote=False)


class Ctx:
    """Контекст страницы: сколько уровней до корня концепции и кто мы такие."""

    def __init__(self, key, depth):
        self.key = key
        self.depth = depth
        self.up = '../' * depth

    def url(self, key):
        """Относительный адрес другой страницы концепции."""
        target = SITE[key]
        if self.key == key:
            return './'
        return (self.up + target) or './'

    def asset(self, path):
        # страницы отсчитываются от site/4/, а assets лежат уровнем выше — в site/
        return '../' * (self.depth + 1) + 'assets/' + path

    def link(self, key, extra=''):
        """Атрибуты ссылки: собранная страница — обычная, несобранная — заглушка."""
        if key in SITE:
            current = ' aria-current="page"' if key == self.key else ''
            return 'href="%s"%s%s' % (self.url(key), current, extra)
        return 'href="%s" data-stage%s' % (STAGED[key], extra)


def head(ctx, title, description, extra_css=()):
    css = '\n'.join('<link rel="stylesheet" href="%s">' % ctx.asset('css/' + name)
                    for name in ('style.css', 'page.css') + tuple(extra_css))
    return f'''<!doctype html>
<html lang="ru" data-hero="render">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{e(title)}</title>
<meta name="description" content="{html.escape(description)}">
<link rel="icon" href="{ctx.asset('img/favicon.png')}" type="image/png">
<link rel="apple-touch-icon" href="{ctx.asset('img/apple-touch-icon.png')}">
<link rel="preload" href="{ctx.asset('fonts/montserrat-cyrillic.woff2')}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{ctx.asset('fonts/montserrat.css')}">
{css}
<script>document.documentElement.classList.add('js');</script>
</head>
<body>
'''


def chrome_top(ctx, topline_right, skip_to, cta='#contact'):
    items = []
    for key, title, note in DIRECTIONS:
        items.append(
            f'          <a class="dropdown__link" {ctx.link(key)}>\n'
            f'            <span class="dropdown__title">{e(title)}</span>\n'
            f'            <span class="dropdown__note">{e(note)}</span>\n'
            f'          </a>')
    items.append(
        f'          <a class="dropdown__link dropdown__link--all" {ctx.link("catalog")}>\n'
        f'            <span class="dropdown__title">Весь каталог</span>\n'
        f'            <span class="dropdown__note">23 позиции, поиск и фильтры</span>\n'
        f'          </a>')
    catalog_items = '\n'.join(items)

    return f'''
<a class="skip-link" href="#{skip_to}">Перейти к содержанию</a>

<div class="topline">
  <div class="shell topline__inner">
    <span class="topline__item">Инжиниринг для строительной индустрии Казахстана с 2006 года</span>
    <span class="topline__item topline__item--muted">{e(topline_right)}</span>
  </div>
</div>

<header class="masthead">
  <div class="shell masthead__inner">
    <a class="brand" href="{ctx.url('home')}">
      <img class="brand__logo brand__logo--knockout" src="{ctx.asset('img/tts-logo-light.png')}" width="1148" height="426" alt="ТТС Инжиниринг">
      <img class="brand__logo brand__logo--ink" src="{ctx.asset('img/tts-logo.png')}" width="1148" height="426" alt="" aria-hidden="true">
      <span class="brand__text">
        <span class="brand__name">ТТС Инжиниринг</span>
        <span class="brand__region">Казахстан</span>
      </span>
    </a>

    <nav class="nav" id="nav" aria-label="Основная навигация">
      <a class="nav__link" href="{ctx.url('home')}#catalog">Оборудование</a>

      <div class="nav__group">
        <button class="nav__link nav__toggle" type="button" aria-expanded="false" aria-controls="menu-catalog">
          Каталог
          <svg class="nav__chevron" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
            <path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"></path>
          </svg>
        </button>
        <div class="dropdown" id="menu-catalog" hidden>
{catalog_items}
        </div>
      </div>

      <div class="nav__group">
        <button class="nav__link nav__toggle" type="button" aria-expanded="false" aria-controls="menu-service">
          Сервис
          <svg class="nav__chevron" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
            <path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"></path>
          </svg>
        </button>
        <div class="dropdown dropdown--narrow" id="menu-service" hidden>
          <a class="dropdown__link" {ctx.link('service')}>
            <span class="dropdown__title">Инженерный сервис</span>
            <span class="dropdown__note">Диагностика, запуск и поддержка 24/7</span>
          </a>
          <a class="dropdown__link" {ctx.link('parts')}>
            <span class="dropdown__title">Запасные части</span>
            <span class="dropdown__note">Подбор комплектующих и автоматики</span>
          </a>
        </div>
      </div>

      <a class="nav__link" href="{ctx.url('home')}#company">О компании</a>
      <a class="nav__link" href="{ctx.url('home')}#projects">Проекты</a>
    </nav>

    <a class="btn btn--solid btn--sm masthead__cta" href="{cta}">Получить расчёт</a>

    <button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="nav" aria-label="Открыть меню">
      <span class="burger__bar"></span>
      <span class="burger__bar"></span>
      <span class="burger__bar"></span>
    </button>
  </div>
</header>

<main>
'''


def chrome_bottom(ctx, scripts):
    equipment = '\n'.join(
        f'      <a {ctx.link(key)}>{e(title)}</a>' for key, title, _ in DIRECTIONS)
    tags = '\n'.join('<script src="%s" defer></script>' % ctx.asset('js/' + name)
                     for name in scripts)
    return f'''</main>

<footer class="footer">
  <div class="shell footer__grid">
    <div class="footer__brand">
      <img class="footer__logo" src="{ctx.asset('img/tts-logo-light.png')}" width="1148" height="426" alt="ТТС Инжиниринг">
      <p class="footer__about">Заводы, терминалы и технологические линии для строительной индустрии Казахстана.</p>
    </div>

    <nav class="footer__col" aria-label="Оборудование">
      <h3 class="footer__heading">Оборудование</h3>
{equipment}
    </nav>

    <nav class="footer__col" aria-label="Компания">
      <h3 class="footer__heading">Компания</h3>
      <a href="{ctx.url('home')}#company">О компании</a>
      <a href="{ctx.url('home')}#projects">Проекты</a>
      <a {ctx.link('service')}>Инженерный сервис</a>
      <a {ctx.link('parts')}>Запасные части</a>
    </nav>

    <nav class="footer__col" aria-label="Документы">
      <h3 class="footer__heading">Документы</h3>
      <a {ctx.link('privacy')}>Политика конфиденциальности</a>
      <a {ctx.link('personal')}>Согласие на обработку персональных данных</a>
      <a {ctx.link('cookie')}>Политика использования файлов cookie</a>
      <span class="footer__legal">БИН 191141028147</span>
    </nav>
  </div>

  <div class="shell">
    <div class="footer__catalog">
      <div class="footer__catalog-copy">
        <strong class="footer__catalog-title">Весь каталог оборудования</strong>
        <span class="footer__catalog-note">23 конфигурации в пяти направлениях — с поиском и фильтрами.</span>
      </div>
      <a class="btn btn--ghost footer__catalog-link" {ctx.link('catalog')}>
        Открыть весь каталог
        {ARROW}
      </a>
    </div>
  </div>

  <div class="shell footer__bottom">
    <span>© 2026 ТТС Инжиниринг Казахстан</span>
  </div>
</footer>

<a class="quiz-cta" href="/marquiz/" aria-label="Получить расчёт проекта" data-stage data-marquiz>
  <svg class="quiz-cta__icon" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
    <path d="M3 4h14M3 10h14M3 16h9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"></path>
  </svg>
  <span class="quiz-cta__label">Получить расчёт проекта</span>
</a>

<div class="notice" id="notice" role="status" aria-live="polite" hidden></div>

{tags}
</body>
</html>
'''


# ---------------------------------------------------------------- блоки

def page_head(ctx, spec):
    crumbs = ['<li class="crumbs__item"><a class="crumbs__link" href="%s">Главная</a></li>' % ctx.url('home')]
    if spec.get('under_catalog'):
        crumbs.append('<li class="crumbs__item"><a class="crumbs__link" href="%s">Каталог оборудования</a></li>'
                      % ctx.url('catalog'))
    crumbs.append('<li class="crumbs__item" aria-current="page">%s</li>' % e(spec['crumb']))

    actions = ''
    if spec.get('actions'):
        primary, secondary = spec['actions']
        actions = f'''
      <div class="page-head__actions">
        <a class="btn btn--solid btn--lg" href="#{primary[1]}">
          {e(primary[0])}
          {ARROW}
        </a>
        <a class="btn btn--ghost btn--lg" href="#{secondary[1]}">{e(secondary[0])}</a>
      </div>'''

    stats = ''
    if spec.get('stats'):
        cells = '\n'.join(
            f'''        <li class="page-stats__cell">
          <strong class="page-stats__value">{e(value)}</strong>
          <span class="page-stats__label">{e(label)}</span>
        </li>''' for value, label in spec['stats'])
        stats = f'\n      <ul class="page-stats">\n{cells}\n      </ul>'

    photo = ''
    if spec.get('photo'):
        photo = ' photo-bed" data-photo="%s' % spec['photo']
    return f'''  <section class="page-head{photo}">
    <div class="shell">
      <nav class="crumbs" aria-label="Хлебные крошки">
        <ol class="crumbs__list">
          {chr(10).join('          ' + c for c in crumbs).strip()}
        </ol>
      </nav>

      <p class="kicker">{e(spec['kicker'])}</p>
      <h1 class="page-head__title">{e(spec['title'])}</h1>
      <p class="page-head__lead">{e(spec['lead'])}</p>{actions}{stats}
    </div>
  </section>
'''


def section_head(kicker, title, lead=None):
    out = [f'      <div class="section-head">',
           f'        <p class="kicker section-head__kicker">{e(kicker)}</p>',
           f'        <h2 class="section-head__title">{e(title)}</h2>']
    if lead:
        out.append(f'        <p class="section-head__lead">{e(lead)}</p>')
    out.append('      </div>')
    return '\n'.join(out)


def cards(items, modifier=''):
    blocks = []
    for item in items:
        badge = (f'<span class="card__index">{e(item["index"])}</span>' if item.get('index')
                 else f'<span class="card__tag">{e(item["tag"])}</span>' if item.get('tag') else '')
        note = f'\n          <p class="card__note">{e(item["note"])}</p>' if item.get('note') else ''
        blocks.append(f'''        <article class="card">
          {badge}
          <h3 class="card__title">{e(item['title'])}</h3>
          <p class="card__text">{e(item['text'])}</p>{note}
        </article>''')
    cls = 'cards' + (' ' + modifier if modifier else '')
    return f'      <div class="{cls}">\n' + '\n'.join(blocks) + '\n      </div>'


def models(items, modifier=''):
    blocks = []
    for m in items:
        specs = '\n'.join(f'''            <div>
              <strong class="model__value">{e(value)}</strong>
              <span class="model__label">{e(label)}</span>
            </div>''' for value, label in m['specs'])
        blocks.append(f'''        <article class="model">
          <div class="model__top">
            <span class="model__tag">{e(m['tag'])}</span>
            <span class="model__code">{e(m['code'])}</span>
          </div>
          <h3 class="model__title">{e(m['title'])}</h3>
          <p class="model__text">{e(m['text'])}</p>
          <div class="model__specs">
{specs}
          </div>
          <a class="btn btn--solid model__link" href="#contact" data-model="{e(m['value'])}">
            {e(m.get('cta', 'Запросить комплектацию'))}
            {ARROW}
          </a>
        </article>''')
    cls = 'models' + (' ' + modifier if modifier else '')
    return f'      <div class="{cls}">\n' + '\n'.join(blocks) + '\n      </div>'


def steps(items):
    modifier = {3: ' steps--3', 4: ' steps--4'}.get(len(items), '')
    blocks = '\n'.join(f'''        <li class="step">
          <span class="step__index">{i + 1:02d}</span>
          <h3 class="step__title">{e(title)}</h3>
          <p class="step__text">{e(text)}</p>
        </li>''' for i, (title, text) in enumerate(items))
    return f'      <ol class="steps{modifier}">\n{blocks}\n      </ol>'


def points(items):
    blocks = '\n'.join(f'''          <li class="point">
            <h4 class="point__title">{e(title)}</h4>
            <p class="point__text">{e(text)}</p>
          </li>''' for title, text in items)
    return f'        <ul class="points">\n{blocks}\n        </ul>'


def figures(items):
    cells = '\n'.join(f'''          <div class="figures__cell">
            <strong class="figures__value">{e(value)}</strong>
            <span class="figures__label">{e(label)}</span>
          </div>''' for value, label in items)
    return f'        <div class="figures">\n{cells}\n        </div>'


def matrix(headers, rows, note=None):
    head_cells = '\n'.join(f'              <th scope="col">{e(h)}</th>' for h in headers)
    body = '\n'.join(
        '            <tr>\n              <th scope="row">%s</th>\n%s\n            </tr>' % (
            e(row[0]),
            '\n'.join(f'              <td>{e(c)}</td>' for c in row[1:]))
        for row in rows)
    tail = f'\n      <p class="matrix__note">{e(note)}</p>' if note else ''
    return f'''      <div class="matrix__wrap">
        <table class="matrix">
          <caption class="visually-hidden">Сравнение конфигураций</caption>
          <thead>
            <tr>
{head_cells}
            </tr>
          </thead>
          <tbody>
{body}
          </tbody>
        </table>
      </div>{tail}'''


def band(ctx, title, text, cta, target):
    return f'''      <div class="band">
        <div>
          <strong class="band__title">{e(title)}</strong>
          <p class="band__text">{e(text)}</p>
        </div>
        <a class="btn btn--solid" {ctx.link(target)}>
          {e(cta)}
          {ARROW}
        </a>
      </div>'''


def section(body, ident=None, tone='light'):
    tone_cls = {'light': 'page-section--light on-light',
                'muted': 'page-section--muted on-light',
                'dark': 'page-section--dark'}[tone]
    attr = f' id="{ident}"' if ident else ''
    return f'''  <section class="page-section {tone_cls}"{attr}>
    <div class="shell">
{body}
    </div>
  </section>
'''


def split(left, right, ident=None):
    attr = f' id="{ident}"' if ident else ''
    return f'''  <section class="page-section page-section--dark"{attr}>
    <div class="shell split">
      <div>
{left}
      </div>
      <div>
{right}
      </div>
    </div>
  </section>
'''


def contact(ctx, spec):
    fields = []
    for field in spec.get('selects', []):
        options = '\n'.join(
            f'              <option value="{e(v)}"{" selected" if sel else ""}>{e(v)}</option>'
            for v, sel in field['options'])
        ident = ' id="leadModel"' if field['name'] == 'model' else ''
        fields.append(f'''          <label class="field">
            <span class="field__label">{e(field['label'])}</span>
            <select class="field__select"{ident} name="{field['name']}">
{options}
            </select>
          </label>''')

    comment_label = spec.get('comment_label', 'Комментарий')
    comment_hint = spec.get('comment_hint', 'Кратко опишите задачу')
    fields.append(f'''          <label class="field">
            <span class="field__label">Ваше имя</span>
            <input class="field__input" id="leadName" name="name" type="text" autocomplete="name" placeholder="Имя Фамилия" required>
            <span class="field__error" data-error></span>
          </label>

          <label class="field">
            <span class="field__label">Телефон для связи</span>
            <input class="field__input" name="phone" type="tel" autocomplete="tel" placeholder="Телефон" required>
            <span class="field__error" data-error></span>
          </label>

          <label class="field">
            <span class="field__label">{e(comment_label)}</span>
            <textarea class="field__input field__textarea" name="comment" rows="3" placeholder="{e(comment_hint)}"></textarea>
          </label>''')

    details = '\n'.join(f'          <span>{e(line)}</span>' for line in spec['details'])
    consent_link = ctx.link('personal')
    title = spec['title'].replace('|', '<br class="br-wide"> ')

    return f'''  <section class="contact photo-bed" id="contact">
    <div class="shell contact__grid">
      <div class="contact__copy">
        <p class="kicker">{e(spec['kicker'])}</p>
        <h2 class="contact__title">{title}</h2>
        <p class="contact__lead">{e(spec['lead'])}</p>

        <div class="contact__details">
          <strong class="contact__office">Филиал ТТС Инжиниринг в Казахстане</strong>
{details}
        </div>
      </div>

      <div class="form-card">
        <form class="form" id="leadForm" novalidate>
          <h3 class="form__title">{e(spec['form_title'])}</h3>
          <p class="form__note">{e(spec['form_note'])}</p>

{chr(10).join(fields)}

          <label class="consent">
            <input class="consent__box" name="consent" type="checkbox" required>
            <span class="consent__text">Я согласен на <a class="consent__link" {consent_link}>обработку персональных данных</a></span>
            <span class="field__error" data-error></span>
          </label>

          <button class="btn btn--solid btn--lg form__submit" type="submit">
            {e(spec.get('submit', 'Получить консультацию'))}
            {ARROW}
          </button>
        </form>

        <div class="form-done" id="leadDone" tabindex="-1" hidden>
          <span class="form-done__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" focusable="false">
              <path d="M4 12l5 5L20 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="square"></path>
            </svg>
          </span>
          <h3 class="form-done__title">Заявка подготовлена</h3>
          <p class="form-done__text">{e(spec['done_text'])}</p>
          <button class="btn btn--ghost" type="button" id="leadAgain">{e(spec.get('again', 'Заполнить ещё раз'))}</button>
        </div>
      </div>
    </div>
  </section>
'''


def doc(sections, updated=None):
    """Текст документа: заголовок раздела, абзацы и перечисления."""
    blocks = []
    for item in sections:
        parts = [f'        <h2 class="doc__title">{e(item["title"])}</h2>']
        for text in item.get('text', []):
            parts.append(f'        <p class="doc__text">{e(text)}</p>')
        if item.get('list'):
            rows = '\n'.join(f'          <li class="doc__item">{e(row)}</li>' for row in item['list'])
            parts.append(f'        <ul class="doc__list">\n{rows}\n        </ul>')
        blocks.append('      <section class="doc__section">\n' + '\n'.join(parts) + '\n      </section>')
    meta = f'\n      <p class="doc__meta">{e(updated)}</p>' if updated else ''
    return '      <div class="doc">\n' + '\n'.join(blocks) + meta + '\n      </div>'


def callout(title, lines):
    body = '\n'.join(f'        <p class="callout__text">{e(line)}</p>' for line in lines)
    return (f'      <div class="callout">\n'
            f'        <strong class="callout__title">{e(title)}</strong>\n'
            f'{body}\n'
            f'      </div>')


def link_cards(ctx, items, modifier='cards--4'):
    blocks = []
    for key, title, text in items:
        blocks.append(f'''        <a class="card card--link" {ctx.link(key)}>
          <h3 class="card__title">{e(title)}</h3>
          <p class="card__text">{e(text)}</p>
        </a>''')
    return f'      <div class="cards {modifier}">\n' + '\n'.join(blocks) + '\n      </div>'
