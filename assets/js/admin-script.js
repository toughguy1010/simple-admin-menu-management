jQuery(document).ready(function ($) {
  $(".samh-menu-header").on("click", function (e) {
    // Did we click the toggle switch? If so, don't expand/collapse
    if ($(e.target).closest(".samh-toggle").length) {
      return;
    }

    var $item = $(this).closest(".samh-menu-item");

    // Only toggle if it has submenus
    if ($item.hasClass("has-submenus")) {
      $item.find(".samh-submenu-list").slideToggle(200);
      $item.toggleClass("expanded");
    }
  });
});
