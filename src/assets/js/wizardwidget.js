$(document).ready(function () {
  //Initialize tooltips
  //  $('.nav-tabs > li a[title]').tooltip();

  const tabEl = document.querySelector('a[data-bs-toggle="tab"]');
  tabEl.addEventListener("shown.bs.tab", (event) => {
    // event.target // newly activated tab
    // event.relatedTarget // previous active tab
    alert("TAB CHANGED");
    var $target = $(event.target);
    // alert(e.targeṭ.id);
    if ($target.parent().hasClass("disabled")) {
      return false;
    }
  });

  // Manage next step button click
  $(document).on("click", ".next-step", function (e) {
    var $tab_active = $(".wizard .nav-tabs li.active");

    var $next_tab = $tab_active.next();
    var $function = jQuery(this).data("function") || false;

    if ($function) {
      var callback = function () {
        // alert("ok");
        $next_tab.removeClass("disabled");
        nextTab($tab_active);
      };

      // Execute data item 'function' on click
      eval($function);
    } else {
       alert($next_tab.data('bs-toggle'));
      $next_tab.removeClass("disabled");
     //nextTab($tab_active);
    }
  });

  // Manage previous step button click
  $(document).on("click", ".prev-step", function (e) {
    var $tab_active = $(".wizard .nav-tabs li.active");
    var $function = jQuery(this).data("function") || false;

    if ($function) {
      var callback = function () {
        prevTab($tab_active);
      };

      // Execute data item 'function' on click
      eval($function);
    } else {
      prevTab($tab_active);
    }
  });

  // Manage save step button click
  $(document).on("click", ".save-step", function (e) {
    var $function = jQuery(this).data("function") || false;
    // Execute data item 'function' on click
    if ($function) {
      eval($function);
    }
  });
});

// 'click' on next tab
function nextTab(elem) {
  alert(eleṃ.id);
  // $(elem).next().find('a[data-bs-toggle="tab"]').click();
  const nextTabLinkEl = $(elem)
    .next()
    .find(".nav-tabs .active")
    .closest("li")
    .next("li")
    .find("a")[0];
  const nextTab = new bootstrap.Tab(nextTabLinkEl);
  nextTab.show();
}

// 'click' on prev tab
function prevTab(elem) {
  $(elem).prev().find('a[data-bs-toggle="tab"]').click();
}
