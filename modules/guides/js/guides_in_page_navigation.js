"use strict";

Drupal.behaviors.guidesInPageNavigation = {
  attach: function (context, settings) {
    Drupal.guidesInPageNavigation.outline(context, settings);
  }
};

Drupal.guidesInPageNavigation = {
  isSetUp: false,
  headingsOffsetTopLookup: null,
  navigation: null,
  isScrolledToBottom: function () {
    return (window.innerHeight + window.pageYOffset) >= document.body.offsetHeight;
  },
  getToolbarHeight: function () {
    var adminToolbarHeight = document.getElementById('toolbar-bar').offsetHeight;
    var adminTrayHeight = 0;
    var adminTray = document.getElementById('toolbar-item-administration-tray');

    if (adminTray.classList.contains('is-active') && adminTray.classList.contains('toolbar-tray-horizontal')) {
      adminTrayHeight = adminTray.offsetHeight;
    }
    return adminToolbarHeight + adminTrayHeight;
  },
  updateUrl: function (headingId) {
    var urlSplit = window.location.href.split('#');
    var url = urlSplit[0];
    var id = urlSplit[1];
    if (headingId === null) {
      if (id) {
        history.pushState(null, null, url);
      }
    }
    //  True if setting for the first time OR
    //  a new section has been active.
    else if (!id || (id && id !== headingId)) {
      history.pushState(null, null, url + '#' + headingId);
    }
  },
  setSticky: function () {
    var offsetTop = this.navigation.getBoundingClientRect().top;
    var scrollTop = window.pageYOffset;
    if (scrollTop > offsetTop) {
      this.navigation.classList.add('guides__in-page-nav--sticky');
      this.navigation.style.marginTop = this.getToolbarHeight() + 'px';
    }
    else {
      this.navigation.classList.remove('guides__in-page-nav--sticky');
      this.navigation.style.marginTop = 0 + 'px';
    }
  },
  setSpy: function () {
    // 80 enables the spy to trigger a bit sooner than when
    // the content reaches the bottom of the admin toolbar.
    var scrollTop = window.pageYOffset + 80;
    var isNewSpySet = false;
    var activeNav = null;
    var item = null;

    // Finding the active section in a pre-ordered (by distance) list.
    for (var i = 0; i < this.headingsOffsetTopLookup.length; i++) {
      item = this.headingsOffsetTopLookup[i];
      if (item.offsetTop <= scrollTop) {

        if (this.isScrolledToBottom()) {
          item = this.headingsOffsetTopLookup[0];
        }

        // if (i === 1) {
        //   item = this.headingsOffsetTopLookup[0];
        // }

        activeNav = this.navigation.querySelector('a.guides__active-nav');

        // Removing the active status.
        if (activeNav) {
          // Do nothing if the active section is the same.
          if (activeNav.getAttribute('href').slice(1) === item.headerId) {
            return;
          }
          else {
            activeNav.classList.remove('guides__active-nav');
          }
        }

        // Adding the active status to the new header.
        var link = this.navigation.querySelector('a[href*=' + item.headerId + ']');
        link.classList.add('guides__active-nav');
        isNewSpySet = true;
        break;
      }
    }

    // True, when there was NO match in the for().
    if (!isNewSpySet) {
      var navToReset = this.navigation.querySelector('a.guides__active-nav');
      if (navToReset) {
        navToReset.classList.remove('guides__active-nav');
      }
      this.updateUrl(null);
    }
    else {
      this.updateUrl(item.headerId);
    }
  },
  createLookup: function (headings) {
    var lookup = [];

    for (var i = 0; i < headings.length; i++) {
      lookup.push({
        headerId: headings[i].getAttribute('id'),
        offsetTop: headings[i].offsetTop
      });
    }

    // Sorting descending by distance from the top.
    lookup.sort(function (a, b) {
      return b.offsetTop - a.offsetTop;
    });
    this.headingsOffsetTopLookup = lookup;
  },
  outline: function (context, settings) {
    // There's no .once() method without jQuery.
    if (this.isSetUp) {
      return;
    }

    var self = this;
    var content = document.querySelector('.region-content');
    var mainBlock = document.querySelector('.block-system-main-block');
    var headingsToFind = 'h2, h3';
    var headings = mainBlock.querySelectorAll(headingsToFind);
    var flexWrapper = document.createElement('div');
    flexWrapper.setAttribute('id', 'guides__flex-wrapper');
    var nav = document.createElement('div');
    nav.setAttribute('id', 'guides__in-page-nav');
    var navHeadings = [];
    var heading = null;

    for (var i = 0; i < headings.length; i++) {
      heading = headings[i];
      navHeadings.push(heading.cloneNode(true));

      // Setting id to the original headings.
      var id = heading.innerText.toLocaleLowerCase().replace(/\s/g, '-');
      heading.setAttribute('id', id);
    }

    var tree = this.createTree(navHeadings);
    nav.appendChild(tree);

    var openMobileBtn = document.createElement('div');
    openMobileBtn.innerHTML = '&lt';
    openMobileBtn.setAttribute('id', 'guides__open-mobile');
    openMobileBtn.addEventListener('click', function () {
      if (nav.classList.contains('guides__util--hidden')) {
        nav.classList.remove('guides__util--hidden');
        this.innerHTML = '&gt';
      }
      else {
        nav.classList.add('guides__util--hidden');
        this.innerHTML = '&lt';
      }
    });

    flexWrapper.appendChild(openMobileBtn);
    flexWrapper.appendChild(nav);
    content.insertBefore(flexWrapper, content.firstChild);
    //content.insertBefore(openMobileBtn, content.firstChild);

    this.navigation = nav;

    if (screen.width <= 767) {
      nav.classList.add('guides__util--hidden');
      flexWrapper.style.top = this.getToolbarHeight() + 'px';
    }
    else {
      // Fixed values must be set because of display:fixed when sticky.
      this.navigation.style.maxWidth = flexWrapper.offsetWidth + 'px';
    }

    this.createLookup(headings);

    window.onload = function () {
      window.addEventListener('scroll', function (e) {
        self.setSticky();
        self.setSpy();
      });
      var id = window.location.href.split('#')[1];
      self.scrollTo(id);
    };

    this.isSetUp = true;

  },
  createTree: function (headingList) {
    var self = this;
    var parent = document.createElement('ul');
    var result = parent;

    function findParent(node, nodeName) {
      if (!node) {
        return null;
      }
      else if (node.dataset.parentType === nodeName) {
        return node;
      }
      else {
        return findParent(node.parentNode, nodeName);
      }
    }

    function setParent(i) {
      // End of the list.
      if (!headingList[i + 1]) {
        return;
      }
      // The next one is the same type.
      else if (headingList[i].nodeName === headingList[i + 1].nodeName) {
        return;
      }
      // The next one is a different type.
      else {
        var p = findParent(lastInsertedChild, headingList[i + 1].nodeName);
        if (p !== null) {
          parent = p;
        }
        else {
          var newParent = document.createElement('ul');
          var newLi = document.createElement('li');
          newLi.appendChild(newParent);
          parent.appendChild(newLi);
          // Setting the "pointer" to the current head.
          parent = newParent;
        }
      }
    }

    var lastInsertedChild;
    var link;
    var href;
    for (var i = 0; i < headingList.length; i++) {
      if (!parent.getAttribute('data-parent-type')) {
        parent.setAttribute('data-parent-type', headingList[i].nodeName);
        parent.classList.add('guides__in-page-nav-ul--' + headingList[i].nodeName.toLowerCase());
      }
      lastInsertedChild = document.createElement('li');
      lastInsertedChild.classList.add('guides__in-page-nav-li');
      href = headingList[i].innerText.toLocaleLowerCase().replace(/\s/g, '-');
      link = document.createElement('a');
      link.setAttribute('href', '#' + href);
      link.setAttribute('title', headingList[i].innerText);
      link.innerText = headingList[i].innerText;
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var id = this.getAttribute('href').slice(1);
        self.scrollTo(id);
      });
      lastInsertedChild.appendChild(link);
      parent.appendChild(lastInsertedChild);
      setParent(i);
    }
    return result;
  },
  scrollTo: function (id) {
    if (id) {
      var scrollToHeading = document.getElementById(id);
      if (scrollToHeading) {
        // Subtracting the height of the admin toolbar.
        window.scrollTo(0, window.pageYOffset + scrollToHeading.getBoundingClientRect().top - this.getToolbarHeight());
      }
    }
    else {
      window.scrollTo(0, 0);
    }
  }
};
