const menuBtn = document.getElementById("menu-btn");
const navList = document.getElementById("nav-list");

const dropdowns = document.querySelectorAll(".dropdown");
const submenus = document.querySelectorAll(".submenu");



/* ==============================
   MENU HAMBURGER
================================= */

if (menuBtn && navList) {

menuBtn.addEventListener("click", () => {

    const isOpen = navList.classList.toggle("active");
    menuBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
    menuBtn.setAttribute("aria-label", isOpen ? "Fermer le menu de navigation" : "Ouvrir le menu de navigation");
    menuBtn.textContent = isOpen ? "×" : "☰";

});

}



/* ==============================
   DROPDOWN
================================= */

dropdowns.forEach(dropdown => {

    const button = dropdown.querySelector(".dropdown-btn");

    button.addEventListener("click", (event) => {

        /* seulement sur mobile */

        if (window.innerWidth <= 768) {

            event.stopPropagation();

            const isOpen = dropdown.classList.toggle("active");
            button.setAttribute("aria-expanded", isOpen ? "true" : "false");

        }

    });

});



/* ==============================
   SOUS MENU
================================= */

submenus.forEach(submenu => {

    const button = submenu.querySelector(".submenu-btn");

    button.addEventListener("click", (event) => {

        /* seulement sur mobile */

        if (window.innerWidth <= 768) {

            event.stopPropagation();

            submenu.classList.toggle("active");

        }

    });

});



/* ==============================
   FERMER EN CHANGEANT DE TAILLE
================================= */

window.addEventListener("resize", () => {

    if (window.innerWidth > 768) {

        navList?.classList.remove("active");

        menuBtn?.setAttribute("aria-expanded", "false");
        menuBtn?.setAttribute("aria-label", "Ouvrir le menu de navigation");
        if (menuBtn) menuBtn.textContent = "☰";

        dropdowns.forEach(dropdown => {
            dropdown.classList.remove("active");
            dropdown.querySelector(".dropdown-btn")?.setAttribute("aria-expanded", "false");
        });

        submenus.forEach(submenu => {
            submenu.classList.remove("active");
        });

    }

});


navList?.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", () => {
        if (window.innerWidth > 768 || !navList.classList.contains("active")) return;
        navList.classList.remove("active");
        menuBtn?.setAttribute("aria-expanded", "false");
        menuBtn?.setAttribute("aria-label", "Ouvrir le menu de navigation");
        if (menuBtn) menuBtn.textContent = "☰";
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove("active");
            dropdown.querySelector(".dropdown-btn")?.setAttribute("aria-expanded", "false");
        });
        submenus.forEach(submenu => submenu.classList.remove("active"));
    });
});


/* APPARITION AU SCROLL*/
const elements = document.querySelectorAll('.reveal');

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, {
    threshold: 0.2
});

elements.forEach(element => {
    observer.observe(element);
});
