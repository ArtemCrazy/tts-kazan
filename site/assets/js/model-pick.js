// «Запросить комплектацию» на карточке модели: подставляет модель в форму,
// прокручивает к ней и отправляет quote_click (п.13 ТЗ).
// Один файл на все страницы направлений — разметка карточки везде одна.
(function () {
  var select = document.getElementById('leadModel');
  var contact = document.getElementById('contact');
  if (!select || !contact) return;

  document.addEventListener('click', function (event) {
    var link = event.target.closest('[data-model]');
    if (!link) return;
    event.preventDefault();

    var model = link.getAttribute('data-model');
    var found = false;
    for (var i = 0; i < select.options.length; i++) {
      if (select.options[i].value === model) {
        select.selectedIndex = i;
        found = true;
        break;
      }
    }

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: 'quote_click',
      page_url: location.href,
      page_title: document.title,
      item_name: model,
      // если модели нет в списке формы, инженер увидит её только из комментария —
      // такое расхождение нужно видеть в аналитике, а не молча терять
      item_matched: found,
      cta_source: 'model_card'
    });

    contact.scrollIntoView({ block: 'start' });
    var name = document.getElementById('leadName');
    if (name) name.focus({ preventScroll: true });
  });
})();
