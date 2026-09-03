document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menu-btn');
    const navLinks = document.getElementById('nav-links');
    const carousel = document.getElementById('featured-carousel');
    const prevButton = document.getElementById('carousel-prev');
    const nextButton = document.getElementById('carousel-next');
    const authModal = document.getElementById('auth-modal');
    const authModalCopy = document.getElementById('auth-modal-copy');
    const openModalButtons = Array.from(document.querySelectorAll('[data-open-auth-modal]'));
    const closeModalButtons = Array.from(document.querySelectorAll('[data-close-auth-modal]'));

    menuButton?.addEventListener('click', () => {
        navLinks?.classList.toggle('is-open');
    });

    const scrollAmount = () => {
        if (!carousel) {
            return 0;
        }

        const card = carousel.querySelector('.featured-card');
        return card ? card.getBoundingClientRect().width + 18 : 320;
    };

    prevButton?.addEventListener('click', () => {
        carousel?.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
    });

    nextButton?.addEventListener('click', () => {
        carousel?.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
    });

    const openModal = (productName) => {
        if (!authModal) {
            return;
        }

        if (authModalCopy) {
            authModalCopy.textContent = productName
                ? `Para comprar ${productName} y descubrir mas favoritos de PIL Andina, registrate o inicia sesion.`
                : 'Accede a tu cuenta para comprar tus productos favoritos, revisar disponibilidad y continuar tu pedido con total confianza.';
        }

        authModal.classList.add('is-open');
        authModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    };

    const closeModal = () => {
        if (!authModal) {
            return;
        }

        authModal.classList.remove('is-open');
        authModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    };

    openModalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openModal(button.dataset.productName || '');
        });
    });

    closeModalButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});
