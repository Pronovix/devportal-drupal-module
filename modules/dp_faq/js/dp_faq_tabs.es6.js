/**
 * @file
 * Make tab navigation like filter for FAQ pages according to the select list of terms.
 */

(($, Drupal) => {
  /**
   * Process Category option elements and make tab menu filter.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches in_page_navigation behaviors.
   */

  const getActiveValue = selector => {
    const select = document.querySelector(selector);
    return select.options[select.selectedIndex].value;
  };

  const buildTab = (value, text) => {
    const li = document.createElement('LI');
    const anchor = document.createElement('A');
    anchor.setAttribute('data-option-value', value);
    // anchor.setAttribute('href', `#${value}`);
    anchor.appendChild(document.createTextNode(text));
    if (value === getActiveValue('.view--devportal-faq .form-select')) {
      anchor.className = 'is-active';
      const span = document.createElement('span');
      span.className = 'visually-hidden';
      span.appendChild(document.createTextNode('(active tab)'));
      anchor.appendChild(span);
    }
    li.appendChild(anchor);

    return li;
  };

  const buildTabs = data => {
    return data.map(item => {
      const { value, text } = item;
      return buildTab(value, text);
    });
  };

  const makeNavWrapper = data => {
    const text = document.createTextNode('Primary tabs');
    const title = document.createElement('H2');
    title.className = 'visually-hidden';
    title.appendChild(text);
    const ul = document.createElement('UL');
    ul.className = 'tabs';
    ul.classList.add('tabs--primary');
    const liList = buildTabs(data);
    liList.forEach(li => ul.appendChild(li));
    const nav = document.createElement('NAV');
    nav.className = 'tab-navigation';
    nav.appendChild(title);
    nav.appendChild(ul);
    return nav;
  };

  Drupal.behaviors.optionsToTabs = {
    // Temporary solution to avoid unused-vars error on settings:
    // eslint-disable-next-line no-unused-vars
    attach: (context, settings) => {
      const sources = Array.from(
        (context || document).querySelectorAll('.view--devportal-faq select option')
      ).map(source => {
        const { value, text } = source;
        return { value, text };
      });

      if (sources.length) {
        const handleClick = e => {
          e.preventDefault();
          (context || document).querySelector('.view--devportal-faq .form-select')
            .value = e.target.getAttribute('data-option-value');

          (context || document).querySelector('#views-exposed-form-devportal-faq-page-1 .form-submit')
            .click();
        };

        (context || document).querySelector('#views-exposed-form-devportal-faq-page-1').insertAdjacentElement('beforebegin', makeNavWrapper(sources));

        const links = (context || document).querySelectorAll('.view--devportal-faq .tabs--primary a');
        links.forEach(link => link.addEventListener('click', handleClick));
      }
    },
  };
})(jQuery, Drupal);
