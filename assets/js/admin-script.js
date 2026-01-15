jQuery(document).ready(function ($) {
  // Accordion Toggle
  $(".samh-menu-header").on("click", function (e) {
    if (
      $(e.target).closest(".samh-toggle").length ||
      $(e.target).closest(".samh-drag-handle").length
    ) {
      return;
    }

    var $item = $(this).closest(".samh-menu-item");

    // Only toggle if it has submenus
    if ($item.hasClass("has-submenus")) {
      $item.find(".samh-submenu-list").slideToggle(200);
      $item.toggleClass("expanded");
    }
  });

  // Sortable
  $(".samh-grid").sortable({
    items: ".samh-menu-item",
    handle: ".samh-drag-handle",
    placeholder: "samh-sortable-placeholder",
    update: function (event, ui) {
      updateMenuOrder();
    },
  });

  function updateMenuOrder() {
    var order = [];
    $(".samh-menu-item").each(function () {
      var slug = $(this).data("slug");
      if (slug) {
        order.push(slug);
      }
    });
    $("#samh_menu_order").val(order.join(","));
  }

  // Submenu Sortable
  $(".samh-submenu-list").sortable({
    items: ".samh-submenu-item",
    handle: ".samh-submenu-drag-handle",
    placeholder: "samh-sortable-placeholder", // Reusing placeholder style
    update: function (event, ui) {
      updateSubmenuOrder();
    },
  });

  function updateSubmenuOrder() {
    var submenuOrder = {};
    $(".samh-submenu-list").each(function () {
      var $parentItem = $(this).closest(".samh-menu-item");
      var parentSlug = $parentItem.data("slug");
      var subSlugs = [];

      $(this)
        .find(".samh-submenu-item")
        .each(function () {
          var slug = $(this).data("slug");
          if (slug) {
            subSlugs.push(slug);
          }
        });

      if (parentSlug && subSlugs.length > 0) {
        submenuOrder[parentSlug] = subSlugs;
      }
    });
    $("#samh_submenu_order").val(JSON.stringify(submenuOrder));
  }
});
