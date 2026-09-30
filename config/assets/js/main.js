const menuBtn = document.getElementById("menu-btn");
const navList = document.getElementById("nav-list");

const dropdowns = document.querySelectorAll(".dropdown");
const submenus = document.querySelectorAll(".submenu");



/* ==============================
   MENU HAMBURGER
================================= */

menuBtn.addEventListener("click", () => {

    navList.classList.toggle("active");

    /* Changer l'icône */

    if (navList.classList.contains("active")) {

        menuBtn.innerHTML = `
            <i class="ri-close-line"></i>
        `;

    } else {

        menuBtn.innerHTML = `
            <i class="ri-menu-line"></i>
        `;

    }

});



/* ==============================
   DROPDOWN
================================= */

dropdowns.forEach(dropdown => {

    const button = dropdown.querySelector(".dropdown-btn");

    button.addEventListener("click", (event) => {

        /* seulement sur mobile */

        if (window.innerWidth <= 768) {

            event.stopPropagation();

            dropdown.classList.toggle("active");

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

        navList.classList.remove("active");

        menuBtn.innerHTML = `
            <i class="ri-menu-line"></i>
        `;

        dropdowns.forEach(dropdown => {
            dropdown.classList.remove("active");
        });

        submenus.forEach(submenu => {
            submenu.classList.remove("active");
        });

    }

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

