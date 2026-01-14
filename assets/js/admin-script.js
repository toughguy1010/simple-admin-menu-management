jQuery(document).ready(function ($) {
  // Accordion Toggle
  $(".samh-menu-header").on("click", function (e) {
    // Did we click the toggle switch or drag handle? If so, don't expand/collapse
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
});
