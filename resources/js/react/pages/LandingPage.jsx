import React, { useEffect, useMemo, useRef, useState } from 'react';

function formatCurrency(value) {
    const amount = Number(value || 0);

    if (amount <= 0) {
        return 'Consultar';
    }

    return `Bs ${amount.toFixed(2)}`;
}

function truncate(text, limit = 105) {
    if (!text) {
        return 'Un producto PIL pensado para compartir sabor y confianza.';
    }

    return text.length > limit ? `${text.slice(0, limit - 3)}...` : text;
}

export default function LandingPage({
    nav,
    hero,
    status,
    featuredProducts,
    productsCount,
    authModal,
}) {
    const [menuOpen, setMenuOpen] = useState(false);
    const [modalOpen, setModalOpen] = useState(false);
    const [modalProductName, setModalProductName] = useState('');
    const carouselRef = useRef(null);

    const modalCopy = useMemo(() => {
        if (!modalProductName) {
            return authModal.defaultCopy;
        }

        return `Para comprar ${modalProductName} y descubrir mas favoritos de PIL Andina, registrate o inicia sesion.`;
    }, [authModal.defaultCopy, modalProductName]);

    useEffect(() => {
        document.body.classList.toggle('modal-open', modalOpen);

        return () => {
            document.body.classList.remove('modal-open');
        };
    }, [modalOpen]);

    useEffect(() => {
        const handleEscape = (event) => {
            if (event.key === 'Escape') {
                setModalOpen(false);
            }
        };

        document.addEventListener('keydown', handleEscape);

        return () => {
            document.removeEventListener('keydown', handleEscape);
        };
    }, []);

    const scrollCarousel = (direction) => {
        const carousel = carouselRef.current;

        if (!carousel) {
            return;
        }

        const card = carousel.querySelector('.featured-card');
        const amount = card ? card.getBoundingClientRect().width + 18 : 320;

        carousel.scrollBy({
            left: direction * amount,
            behavior: 'smooth',
        });
    };

    const openModal = (productName = '') => {
        setModalProductName(productName);
        setModalOpen(true);
    };

    return (
        <>
            <div className="landing-shell">
                <nav className="main-nav">
                    <a href="#inicio" className="brand-mark">
                        <span className="brand-icon"><i className="ri-heart-3-line" /></span>
                        <span>
                            <strong>PIL Andina</strong>
                            <small>Uniendo a las familias bolivianas</small>
                        </span>
                    </a>

                    <button
                        id="menu-btn"
                        className="menu-toggle"
                        type="button"
                        aria-label="Abrir menu"
                        onClick={() => setMenuOpen((value) => !value)}
                    >
                        <i className="ri-menu-line" />
                    </button>

                    <div id="nav-links" className={`nav-links${menuOpen ? ' is-open' : ''}`}>
                        {nav.map((link) => (
                            <a key={link.label} href={link.href} className={link.className || undefined}>
                                {link.label}
                            </a>
                        ))}
                    </div>
                </nav>

                <main>
                    <section id="inicio" className="hero-panel">
                        <div className="hero-copy">
                            <span className="eyebrow">
                                <i className="ri-award-line" />
                                Sabor, confianza y tradicion
                            </span>
                            <h1>{hero.title}</h1>
                            <p>{hero.description}</p>

                            <div className="hero-actions">
                                <a href="#destacados" className="btn-primary">
                                    <span>Ver favoritos</span>
                                    <i className="ri-arrow-right-line" />
                                </a>
                                <button type="button" className="btn-secondary" onClick={() => openModal()}>
                                    <span>Comprar ahora</span>
                                </button>
                            </div>

                            <div className="hero-promise">
                                {hero.promises.map((promise) => (
                                    <article key={promise.text} className="promise-pill">
                                        <i className={promise.icon} />
                                        <span>{promise.text}</span>
                                    </article>
                                ))}
                            </div>

                            {!status.connectionAvailable && (
                                <div className="status-banner warning">
                                    <i className="ri-alert-line" />
                                    <span>No pudimos cargar el catalogo en este momento. Intenta nuevamente en unos instantes.</span>
                                </div>
                            )}

                            {status.connectionAvailable && !status.stockAvailable && (
                                <div className="status-banner soft">
                                    <i className="ri-information-line" />
                                    <span>El catalogo ya esta disponible. Algunas cantidades pueden confirmarse al ingresar a tu cuenta.</span>
                                </div>
                            )}
                        </div>

                        <div className="hero-visual">
                            <div className="hero-image-card">
                                <img src={hero.imageUrl} alt="Productos PIL Andina" />
                            </div>
                            <div className="visual-badge badge-top">
                                <strong>{hero.startingPriceLabel}</strong>
                                <span>listas para compartir</span>
                            </div>
                            {hero.heroProductName && (
                                <div className="visual-badge badge-bottom">
                                    <strong>{hero.heroProductName}</strong>
                                    <span>uno de los mas elegidos</span>
                                </div>
                            )}
                        </div>
                    </section>

                    <section id="promesa" className="insight-grid">
                        {hero.insights.map((insight) => (
                            <article key={insight.title} className="insight-card">
                                <i className={insight.icon} />
                                <strong>{insight.title}</strong>
                                <p>{insight.text}</p>
                            </article>
                        ))}
                    </section>

                    <section id="destacados" className="carousel-section">
                        <div className="section-heading">
                            <div>
                                <span className="eyebrow">
                                    <i className="ri-fire-line" />
                                    Los mas elegidos
                                </span>
                                <h2>Favoritos que conquistan a las familias bolivianas</h2>
                            </div>
                            <div className="carousel-actions">
                                <button type="button" className="carousel-btn" aria-label="Anterior" onClick={() => scrollCarousel(-1)}>
                                    <i className="ri-arrow-left-s-line" />
                                </button>
                                <button type="button" className="carousel-btn" aria-label="Siguiente" onClick={() => scrollCarousel(1)}>
                                    <i className="ri-arrow-right-s-line" />
                                </button>
                            </div>
                        </div>

                        <div className="carousel-viewport">
                            <div id="featured-carousel" className="featured-carousel" ref={carouselRef}>
                                {featuredProducts.length > 0 ? featuredProducts.map((product) => (
                                    <article key={product.id} className="featured-card">
                                        <div className="card-media">
                                            <img src={product.imageUrl} alt={product.name} />
                                            <span className="featured-badge">Favorito PIL</span>
                                        </div>
                                        <div className="card-body">
                                            <div className="card-topline">
                                                <span>{product.categoryName}</span>
                                                <small>{product.totalSoldLabel}</small>
                                            </div>
                                            <h3>{product.name}</h3>
                                            <p>{truncate(product.description)}</p>
                                        </div>
                                        <div className="card-footer">
                                            <div>
                                                <strong>{formatCurrency(product.price)}</strong>
                                                <span>{product.stockLabel}</span>
                                            </div>
                                            <button type="button" className="mini-cta" onClick={() => openModal(product.name)}>
                                                Comprar
                                            </button>
                                        </div>
                                    </article>
                                )) : (
                                    <article className="empty-state">
                                        <i className="ri-inbox-archive-line" />
                                        <strong>No hay productos disponibles por ahora.</strong>
                                        <p>Vuelve pronto para descubrir los favoritos de PIL Andina.</p>
                                    </article>
                                )}
                            </div>
                        </div>
                    </section>

                    <section id="momentos" className="flow-section">
                        <div className="section-heading">
                            <div>
                                <span className="eyebrow">
                                    <i className="ri-sun-foggy-line" />
                                    Momentos que importan
                                </span>
                                <h2>Una marca cercana para cada momento del dia</h2>
                            </div>
                        </div>

                        <div className="flow-grid">
                            {hero.moments.map((moment) => (
                                <article key={moment.number} className="flow-card">
                                    <span>{moment.number}</span>
                                    <h3>{moment.title}</h3>
                                    <p>{moment.text}</p>
                                </article>
                            ))}
                        </div>
                    </section>
                </main>
            </div>

            <div id="auth-modal" className={`auth-modal${modalOpen ? ' is-open' : ''}`} aria-hidden={modalOpen ? 'false' : 'true'}>
                <div className="auth-modal__backdrop" onClick={() => setModalOpen(false)} />
                <div className="auth-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
                    <button
                        type="button"
                        className="auth-modal__close"
                        aria-label="Cerrar"
                        onClick={() => setModalOpen(false)}
                    >
                        <i className="ri-close-line" />
                    </button>
                    <span className="eyebrow">
                        <i className="ri-user-heart-line" />
                        Continua con PIL Andina
                    </span>
                    <h3 id="auth-modal-title">Registrate o inicia sesion para poder hacer tu compra.</h3>
                    <p id="auth-modal-copy">{modalCopy}</p>
                    <div className="auth-modal__actions">
                        <a href={authModal.registerUrl} className="btn-primary">Registrarme</a>
                        <a href={authModal.loginUrl} className="btn-secondary">Iniciar sesion</a>
                    </div>
                </div>
            </div>
        </>
    );
}
