"use strict";

(function ($, Drupal) {
  var getActiveValue = function getActiveValue(selector) {
    var select = document.querySelector(selector);
    return select.options[select.selectedIndex].value;
  };

  var buildTab = function buildTab(value, text) {
    var li = document.createElement('LI');
    var anchor = document.createElement('A');
    anchor.setAttribute('data-option-value', value); 

    anchor.appendChild(document.createTextNode(text));

    if (value === getActiveValue('.view--devportal-faq .form-select')) {
      anchor.className = 'is-active';
      var span = document.createElement('span');
      span.className = 'visually-hidden';
      span.appendChild(document.createTextNode('(active tab)'));
      anchor.appendChild(span);
    }

    li.appendChild(anchor);
    return li;
  };

  var buildTabs = function buildTabs(data) {
    return data.map(function (item) {
      var value = item.value,
          text = item.text;
      return buildTab(value, text);
    });
  };

  var makeNavWrapper = function makeNavWrapper(data) {
    var text = document.createTextNode('Primary tabs');
    var title = document.createElement('H2');
    title.className = 'visually-hidden';
    title.appendChild(text);
    var ul = document.createElement('UL');
    ul.className = 'tabs';
    ul.classList.add('tabs--primary');
    var liList = buildTabs(data);
    liList.forEach(function (li) {
      return ul.appendChild(li);
    });
    var nav = document.createElement('NAV');
    nav.className = 'tab-navigation';
    nav.appendChild(title);
    nav.appendChild(ul);
    return nav;
  };

  Drupal.behaviors.optionsToTabs = {
    attach: function attach(context, settings) {
      var sources = Array.from((context || document).querySelectorAll('.view--devportal-faq select option')).map(function (source) {
        var value = source.value,
            text = source.text;
        return {
          value: value,
          text: text
        };
      });

      if (sources.length) {
        var handleClick = function handleClick(e) {
          e.preventDefault();
          (context || document).querySelector('.view--devportal-faq .form-select').value = e.target.getAttribute('data-option-value');
          (context || document).querySelector('#views-exposed-form-devportal-faq-page-1 .form-submit').click();
        };

        (context || document).querySelector('#views-exposed-form-devportal-faq-page-1').insertAdjacentElement('beforebegin', makeNavWrapper(sources));
        var links = (context || document).querySelectorAll('.view--devportal-faq .tabs--primary a');
        links.forEach(function (link) {
          return link.addEventListener('click', handleClick);
        });
      }
    }
  };
})(jQuery, Drupal);