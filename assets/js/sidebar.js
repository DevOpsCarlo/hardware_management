document.addEventListener("DOMContentLoaded", function () {
  const toggleSubmenus = document.querySelectorAll(".toggle-submenu");
  const submenus = document.querySelectorAll(".submenu");

  toggleSubmenus.forEach((toggle, index) => {
    toggle.addEventListener("click", () => {
      if (submenus[index]) {
        submenus[index].classList.toggle("hidden");

        // Optional: Rotate chevron
        const icon = toggle.querySelector("i");
        if (icon) {
          icon.classList.toggle("rotate-180");
        }
      }
    });
  });
});
