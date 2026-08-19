@php
    use Illuminate\Support\Str;

    $startingPrice = $products->where('price_for_landing', '>', 0)->min('price_for_landing');
    $heroProduct = $featuredProducts->first();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIL Andina | Uniendo a las familias bolivianas</title>
    <meta name="description" content="PIL Andina acompana a las familias bolivianas con productos de calidad, sabor y confianza. Descubre nuestros mas elegidos.">
    <link rel="stylesheet" href="{{ asset('landing/landing.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
</head>
<body>
    <div class="landing-shell">
        <nav class="main-nav">
            <a href="#inicio" class="brand-mark">
                <span class="brand-icon"><i class="ri-heart-3-line"></i></span>
                <span>
                    <strong>PIL Andina</strong>
                    <small>Uniendo a las familias bolivianas</small>
                </span>
            </a>

            <button id="menu-btn" class="menu-toggle" type="button" aria-label="Abrir menu">
                <i class="ri-menu-line"></i>
            </button>

            <div id="nav-links" class="nav-links">
                <a href="#destacados">Favoritos</a>
                <a href="#promesa">Nuestra promesa</a>
                <a href="#momentos">Momentos PIL</a>
                <a href="{{ url('/login') }}" class="nav-cta">Ingresar</a>
            </div>
        </nav>

        <main>
            <section id="inicio" class="hero-panel">
                <div class="hero-copy">
                    <span class="eyebrow">
                        <i class="ri-award-line"></i>
                        Sabor, confianza y tradicion
                    </span>
                    <h1>PIL Andina, el sabor que acompana cada mesa boliviana.</h1>
                    <p>
                        Desde el desayuno hasta los momentos que reunen a toda la familia, llevamos calidad,
                        frescura y carino en cada producto. Descubre los preferidos de nuestros clientes y deja que
                        PIL Andina este presente en tu hogar.
                    </p>

                    <div class="hero-actions">
                        <a href="#destacados" class="btn-primary">
                            <span>Ver favoritos</span>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                        <button type="button" class="btn-secondary" data-open-auth-modal>
                            <span>Comprar ahora</span>
                        </button>
                    </div>

                    <div class="hero-promise">
                        <article class="promise-pill">
                            <i class="ri-cup-line"></i>
                            <span>Calidad que une generaciones</span>
                        </article>
                        <article class="promise-pill">
                            <i class="ri-truck-line"></i>
                            <span>Presencia que acompana a toda Bolivia</span>
                        </article>
                        <article class="promise-pill">
                            <i class="ri-star-smile-line"></i>
                            <span>Productos elegidos por miles de familias</span>
                        </article>
                    </div>

                    @if (! $connectionAvailable)
                        <div class="status-banner warning">
                            <i class="ri-alert-line"></i>
                            <span>No pudimos cargar el catalogo en este momento. Intenta nuevamente en unos instantes.</span>
                        </div>
                    @elseif (! $stockAvailable)
                        <div class="status-banner soft">
                            <i class="ri-information-line"></i>
                            <span>El catalogo ya esta disponible. Algunas cantidades pueden confirmarse al ingresar a tu cuenta.</span>
                        </div>
                    @endif
                </div>

                <div class="hero-visual">
                    <div class="hero-image-card">
                        <img src="{{ asset('landing/landing_assets/products.png') }}" alt="Productos PIL Andina">
                    </div>
                    <div class="visual-badge badge-top">
                        <strong>{{ $startingPrice ? 'Desde Bs ' . number_format((float) $startingPrice, 2) : 'Calidad PIL' }}</strong>
                        <span>listas para compartir</span>
                    </div>
                    @if ($heroProduct)
                        <div class="visual-badge badge-bottom">
                            <strong>{{ $heroProduct->name }}</strong>
                            <span>uno de los mas elegidos</span>
                        </div>
                    @endif
                </div>
            </section>

            <section id="promesa" class="insight-grid">
                <article class="insight-card">
                    <i class="ri-home-heart-line"></i>
                    <strong>Hecho para el hogar</strong>
                    <p>Sabores que acompanan desayunos, meriendas y momentos especiales en familia.</p>
                </article>
                <article class="insight-card">
                    <i class="ri-shield-check-line"></i>
                    <strong>Confianza que permanece</strong>
                    <p>Una marca que ha crecido junto a Bolivia ofreciendo calidad consistente y cercana.</p>
                </article>
                <article class="insight-card">
                    <i class="ri-service-line"></i>
                    <strong>Compra simple y segura</strong>
                    <p>Descubre tus favoritos y da el siguiente paso con una cuenta para comprar con tranquilidad.</p>
                </article>
            </section>

            <section id="destacados" class="carousel-section">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">
                            <i class="ri-fire-line"></i>
                            Los mas elegidos
                        </span>
                        <h2>Favoritos que conquistan a las familias bolivianas</h2>
                    </div>
                    <div class="carousel-actions">
                        <button type="button" class="carousel-btn" id="carousel-prev" aria-label="Anterior">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>
                        <button type="button" class="carousel-btn" id="carousel-next" aria-label="Siguiente">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>
                    </div>
                </div>

                <div class="carousel-viewport">
                    <div id="featured-carousel" class="featured-carousel">
                        @forelse ($featuredProducts as $product)
                            @php
                                $imageUrl = $product->getImageUrl();
                                $price = (float) $product->price_for_landing;
                                $stock = $product->available_qty;
                            @endphp
                            <article class="featured-card">
                                <div class="card-media">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}">
                                    <span class="featured-badge">Favorito PIL</span>
                                </div>
                                <div class="card-body">
                                    <div class="card-topline">
                                        <span>{{ $product->category_name }}</span>
                                        <small>{{ $product->total_sold > 0 ? number_format($product->total_sold) . ' vendidos' : 'Muy pedido' }}</small>
                                    </div>
                                    <h3>{{ $product->name }}</h3>
                                    <p>{{ Str::limit($product->description ?: 'Un producto PIL pensado para compartir sabor y confianza.', 105) }}</p>
                                </div>
                                <div class="card-footer">
                                    <div>
                                        <strong>{{ $price > 0 ? 'Bs ' . number_format($price, 2) : 'Consultar' }}</strong>
                                        <span>{{ is_null($stock) ? 'Stock al ingresar' : ($stock > 0 ? $stock . ' disponibles' : 'Alta demanda') }}</span>
                                    </div>
                                    <button type="button" class="mini-cta" data-open-auth-modal data-product-name="{{ $product->name }}">
                                        Comprar
                                    </button>
                                </div>
                            </article>
                        @empty
                            <article class="empty-state">
                                <i class="ri-inbox-archive-line"></i>
                                <strong>No hay productos disponibles por ahora.</strong>
                                <p>Vuelve pronto para descubrir los favoritos de PIL Andina.</p>
                            </article>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="momentos" class="flow-section">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">
                            <i class="ri-sun-foggy-line"></i>
                            Momentos que importan
                        </span>
                        <h2>Una marca cercana para cada momento del dia</h2>
                    </div>
                </div>

                <div class="flow-grid">
                    <article class="flow-card">
                        <span>01</span>
                        <h3>Desayunos con energia</h3>
                        <p>Empieza cada manana con productos que llenan de sabor y bienestar a toda la familia.</p>
                    </article>
                    <article class="flow-card">
                        <span>02</span>
                        <h3>Meriendas para compartir</h3>
                        <p>Convierte cada pausa en un momento especial con los sabores mas queridos de PIL Andina.</p>
                    </article>
                    <article class="flow-card">
                        <span>03</span>
                        <h3>Confianza todos los dias</h3>
                        <p>Cuando eliges PIL, eliges una marca que acompana a Bolivia con calidad y cercania.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>

    <div id="auth-modal" class="auth-modal" aria-hidden="true">
        <div class="auth-modal__backdrop" data-close-auth-modal></div>
        <div class="auth-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
            <button type="button" class="auth-modal__close" data-close-auth-modal aria-label="Cerrar">
                <i class="ri-close-line"></i>
            </button>
            <span class="eyebrow">
                <i class="ri-user-heart-line"></i>
                Continua con PIL Andina
            </span>
            <h3 id="auth-modal-title">Registrate o inicia sesion para poder hacer tu compra.</h3>
            <p id="auth-modal-copy">Accede a tu cuenta para comprar tus productos favoritos, revisar disponibilidad y continuar tu pedido con total confianza.</p>
            <div class="auth-modal__actions">
                <a href="{{ url('/register') }}" class="btn-primary">Registrarme</a>
                <a href="{{ url('/login') }}" class="btn-secondary">Iniciar sesion</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('landing/landing.js') }}"></script>
</body>
</html>
