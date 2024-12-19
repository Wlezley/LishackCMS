const toggler = document.querySelector(".btn");

if (toggler !== undefined && toggler !== null) {
  toggler.addEventListener("click", function () {
    const sidebar = document.querySelector(".sidebar");
    const content = document.querySelector("#content");
    const menuSwitch = document.querySelector("#menu-switch");

    if (sidebar) {
      sidebar.classList.toggle("collapsed");
    }

    if (content) {
      content.classList.toggle("collapsed");
      content.classList.toggle("fullsize");
    }

    if (menuSwitch) {
      menuSwitch.classList.toggle("collapsed");
      menuSwitch.classList.toggle("fullsize");
    }
  });
}
