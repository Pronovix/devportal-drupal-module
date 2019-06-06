"use strict";

Drupal.behaviors.guidesInPageNavigation = {
  attach: function (context, settings) {
    Drupal.guidesInPageNavigation.outline(context, settings);
  }
};

Drupal.guidesInPageNavigation = {
  breakpoint: 900,
  isSetUp: false,
  headingsOffsetTopLookup: null,
  navigation: null,
  flexWrapper: null,
  mobileBtn: null,
  sortedHeadings: null,
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
    var scrollTop = window.pageYOffset + this.getToolbarHeight() + 80;
    var isNewSpySet = false;
    var activeNav, item;

    // Finding the active section in a pre-ordered (by distance) list.
    for (var i = 0; i < this.sortedHeadings.length; i++) {
      item = this.sortedHeadings[i];
      if (item.offsetTop <= scrollTop) {
        // Special case, because visually the last element
        // might not be at the top of the page
        if (this.isScrolledToBottom()) {
          item = this.sortedHeadings[0];
        }

        activeNav = this.navigation.querySelector('a.guides__active-nav');

        // Removing the active status.
        if (activeNav) {
          // Do nothing if the active section is the same.
          if (activeNav.getAttribute('href').slice(1) === item.getAttribute('id')) {
            return;
          }
          else {
            activeNav.classList.remove('guides__active-nav');
            activeNav.blur();
          }
        }

        // Adding the active status to the new header.
        var link = this.navigation.querySelector('a[href*=' + item.getAttribute('id') + ']');
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
      this.updateUrl(item.getAttribute('id'));
    }
  },
  outline: function (context, settings) {
    // There's no .once() method without jQuery.
    if (this.isSetUp) {
      return;
    }

    var self = this;
    var content = document.querySelector('.region-content');
    var mainBlock = document.querySelector('.block-system-main-block');
    var headingsToFind = 'h2';
    var headings = mainBlock.querySelectorAll(headingsToFind);
    this.sortedHeadings = Array.prototype.slice.call(headings).sort(function (a, b) {
      return b.offsetTop - a.offsetTop;
    });
    var flexWrapper = document.createElement('div');
    flexWrapper.setAttribute('id', 'guides__flex-wrapper');
    var nav = document.createElement('div');
    nav.setAttribute('id', 'guides__in-page-nav');

    // Preparing the headings.
    var navHeadings = [], heading;
    for (var i = 0; i < headings.length; i++) {
      heading = headings[i];
      navHeadings.push(heading.cloneNode(true));

      // Setting id to the original headings.
      var id = heading.innerText.toLocaleLowerCase().replace(/\s/g, '-');
      heading.setAttribute('id', id);
    }

    // Creating the link-tree
    var tree = this.createTree(navHeadings);
    nav.appendChild(tree);

    var openMobileBtn = document.createElement('div');
    openMobileBtn.innerHTML = '&lt';
    openMobileBtn.setAttribute('id', 'guides__open-mobile');
    openMobileBtn.addEventListener('click', function () {
      if (nav.classList.contains('guides__util--hidden')) {
        self.openMobile();
      }
      else {
        self.hideMobile();
      }
    });

    flexWrapper.appendChild(openMobileBtn);
    flexWrapper.appendChild(nav);
    content.insertBefore(flexWrapper, content.firstChild);

    this.mobileBtn = openMobileBtn;
    this.flexWrapper = flexWrapper;
    this.navigation = nav;
    this.toggleMobileLayout();

    window.onload = function () {
      window.onresize = function () {
        self.toggleMobileLayout();
      };
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

    var lastInsertedChild, link, href, heading;
    for (var i = 0; i < headingList.length; i++) {
      heading = headingList[i];
      if (!parent.getAttribute('data-parent-type')) {
        parent.setAttribute('data-parent-type', heading.nodeName);
        parent.classList.add('guides__in-page-nav-heading--' + heading.nodeName.toLowerCase());
      }
      lastInsertedChild = document.createElement('li');
      lastInsertedChild.classList.add('guides__in-page-nav-li');
      href = heading.innerText.toLocaleLowerCase().replace(/\s/g, '-');
      link = document.createElement('a');
      link.setAttribute('href', '#' + href);
      link.setAttribute('title', heading.innerText);
      link.innerText = heading.innerText;
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var id = this.getAttribute('href').slice(1);
        self.scrollTo(id);
        if (screen.width <= self.breakpoint) {
          self.hideMobile();
        }
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
  },
  toggleMobileLayout: function () {
    if (screen.width <= this.breakpoint) {
      this.navigation.classList.add('guides__util--hidden');
      this.flexWrapper.style.top = this.getToolbarHeight() + 'px';
      this.navigation.style.maxWidth = '';
    }
    else {
      this.navigation.classList.remove('guides__util--hidden');
      // Fixed values must be set because of display:fixed when sticky.
      this.navigation.style.maxWidth = this.flexWrapper.offsetWidth + 'px';
    }
  },
  openMobile: function () {
    this.navigation.classList.remove('guides__util--hidden');
    this.mobileBtn.innerHTML = '&gt';
  },
  hideMobile: function () {
    this.navigation.classList.add('guides__util--hidden');
    this.mobileBtn.innerHTML = '&lt';
  }
};
