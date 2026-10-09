(() => {
    const slider = document.querySelector("[data-home-carousel]");
    if (!slider) return;

    const slides = Array.from(slider.querySelectorAll(".hero-slide"));
    const dots = Array.from(slider.querySelectorAll(".home-slider-dot"));
    const previousButton = slider.querySelector("[data-slide-previous]");
    const nextButton = slider.querySelector("[data-slide-next]");
    const toggleButton = slider.querySelector("[data-slide-toggle]");
    const status = slider.querySelector(".home-slider-status");
    if (!slides.length || !previousButton || !nextButton || !toggleButton || !status) return;

    const labels = slides.map(slide => slide.dataset.slideLabel || "Photo HEMIP");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let currentIndex = Math.max(0, slides.findIndex(slide => slide.classList.contains("is-active")));
    let pausedByUser = reduceMotion.matches;
    let timer = null;

    function stopRotation() {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    }

    function updateToggle() {
        toggleButton.setAttribute("aria-pressed", pausedByUser ? "true" : "false");
        toggleButton.setAttribute(
            "aria-label",
            pausedByUser ? "Reprendre le défilement des photos" : "Mettre le défilement des photos en pause"
        );
        toggleButton.textContent = pausedByUser ? "▶" : "Ⅱ";
    }

    function showSlide(index, announce) {
        currentIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            const active = slideIndex === currentIndex;
            slide.classList.toggle("is-active", active);
            slide.setAttribute("aria-hidden", active ? "false" : "true");
            slide.setAttribute("aria-label", `${slideIndex + 1} sur ${slides.length} : ${labels[slideIndex]}`);
        });
        dots.forEach((dot, dotIndex) => {
            if (dotIndex === currentIndex) {
                dot.setAttribute("aria-current", "true");
            } else {
                dot.removeAttribute("aria-current");
            }
        });
        status.setAttribute("aria-live", announce ? "polite" : "off");
        status.textContent = `${String(currentIndex + 1).padStart(2, "0")} — ${labels[currentIndex]}`;
        if (announce) {
            window.setTimeout(() => status.setAttribute("aria-live", "off"), 1000);
        }
    }

    function startRotation() {
        stopRotation();
        if (pausedByUser || document.hidden || slides.length < 2) return;
        timer = window.setInterval(() => showSlide(currentIndex + 1, false), 6500);
    }

    function manualChange(index) {
        pausedByUser = true;
        stopRotation();
        updateToggle();
        showSlide(index, true);
    }

    previousButton.addEventListener("click", () => manualChange(currentIndex - 1));
    nextButton.addEventListener("click", () => manualChange(currentIndex + 1));
    dots.forEach((dot, index) => dot.addEventListener("click", () => manualChange(index)));

    toggleButton.addEventListener("click", () => {
        pausedByUser = !pausedByUser;
        updateToggle();
        startRotation();
    });

    // Le focus sur les commandes arrête l’autoplay sans inverser le bouton pause/reprise.
    slider.addEventListener("focusin", event => {
        if (event.target !== toggleButton && !pausedByUser) {
            pausedByUser = true;
            stopRotation();
            updateToggle();
        }
    });

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopRotation();
        else startRotation();
    });

    const formationCta = document.querySelector('.hero-buttons a[href="#formations"]');
    const formationMenu = document.getElementById("formations");
    if (formationCta && formationMenu) {
        const formationButton = formationMenu.querySelector(".dropdown-btn");
        formationCta.addEventListener("click", () => {
            if (window.innerWidth <= 768) {
                const menuButton = document.getElementById("menu-btn");
                const navList = document.getElementById("nav-list");
                if (menuButton && navList && !navList.classList.contains("active")) menuButton.click();
            }
            formationMenu.classList.add("active");
            formationButton?.setAttribute("aria-expanded", "true");
            formationButton?.focus({ preventScroll: true });
        });

        document.addEventListener("click", event => {
            if (!formationMenu.contains(event.target) && !formationCta.contains(event.target)) {
                formationMenu.classList.remove("active");
                formationButton?.setAttribute("aria-expanded", "false");
            }
        });
    }

    const onMotionPreferenceChange = event => {
        if (event.matches) {
            pausedByUser = true;
            stopRotation();
            updateToggle();
        }
    };
    if (typeof reduceMotion.addEventListener === "function") {
        reduceMotion.addEventListener("change", onMotionPreferenceChange);
    } else if (typeof reduceMotion.addListener === "function") {
        reduceMotion.addListener(onMotionPreferenceChange);
    }

    showSlide(currentIndex, false);
    updateToggle();
    startRotation();
})();
