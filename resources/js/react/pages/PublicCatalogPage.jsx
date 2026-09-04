import { useEffect, useMemo, useState } from 'react';

const PAGE_SIZE = 12;

function normalizeText(value) {
  return String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function categoryMatches(productCategory, selectedCategory) {
  const productText = normalizeText(productCategory);
  const targetText = normalizeText(selectedCategory);

  if (!targetText) return true;
  if (productText.includes(targetText)) return true;

  return targetText
    .split(/\s+y\s+|\s+|\//)
    .filter((term) => term.length > 3)
    .some((term) => productText.includes(term));
}

function Logo() {
  return (
    <div className="logo" aria-label="PIL Bolivia">
      <div className="logo-mark"><span>PIL</span></div>
      <span className="logo-text">BOLIVIA</span>
    </div>
  );
}

function PublicNav({ landingUrl }) {
  return (
    <header className="topbar catalog-topbar" id="inicio">
      <a className="logo-link" href={landingUrl} aria-label="Volver al inicio">
        <Logo />
      </a>
      <nav className="menu" aria-label="Principal">
        <a href={`${landingUrl}#historia`}>Nuestra historia</a>
        <a href="/catalogo">Productos</a>
        <a href={`${landingUrl}#plantas`}>Nuestras plantas</a>
      </nav>
      <div className="topbar-actions">
        <a className="pill pill-outline" href="/login">Ingreso</a>
        <a className="pill pill-white" href="/register">Registrarme</a>
      </div>
    </header>
  );
}

function CatalogCard({ product, onBuy }) {
  return (
    <article className="catalog-card" key={product.id}>
      <div className="catalog-card-media">
        <img src={product.imageUrl} alt={product.name} />
        <span className={product.stockAvailable === false ? 'stock-pill out' : 'stock-pill'}>
          {product.stockAvailable === false ? 'Stock no disponible' : 'Stock disponible'}
        </span>
      </div>
      <div className="catalog-card-body">
        <span>{product.categoryName}</span>
        <h3>{product.name}</h3>
        <p>{product.description}</p>
      </div>
      <div className="catalog-card-footer">
        <strong>{Number(product.price || 0) > 0 ? `Bs ${Number(product.price).toFixed(2)}` : 'Consultar'}</strong>
        <button type="button" onClick={() => onBuy(product)}>Comprar</button>
      </div>
    </article>
  );
}

function AuthModal({ product, copy, registerUrl, loginUrl, onClose }) {
  if (!product) return null;

  return (
    <div className="auth-modal active" onClick={(event) => event.target === event.currentTarget && onClose()}>
      <div className="auth-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
        <button type="button" className="auth-modal__close" onClick={onClose} aria-label="Cerrar"><i className="ri-close-line" /></button>
        <div className="auth-modal__content">
          <span className="auth-modal__eyebrow">Compra segura PIL</span>
          <h3 id="auth-modal-title">Inicia sesion o registrate para continuar.</h3>
          <p>{copy}</p>
          <div className="auth-modal__product">
            <div className="auth-modal__thumb">
              <img src={product.imageUrl} alt={product.name} />
            </div>
            <div className="auth-modal__product-copy">
              <span>{product.categoryName}</span>
              <strong>{product.name}</strong>
              <small className={product.stockAvailable === false ? 'out' : ''}>
                {product.stockAvailable === false ? 'Stock no disponible' : 'Stock disponible'}
              </small>
            </div>
            <div className="auth-modal__price">
              {Number(product.price || 0) > 0 ? `Bs ${Number(product.price).toFixed(2)}` : 'Consultar'}
            </div>
          </div>
          <div className="auth-modal__actions">
            <a href={registerUrl} className="pill pill-coral"><i className="ri-user-add-line" aria-hidden="true" />Registrarme</a>
            <a href={loginUrl} className="pill pill-outline-dark"><i className="ri-login-circle-line" aria-hidden="true" />Iniciar sesion</a>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function PublicCatalogPage({
  products = [],
  categories = [],
  selectedCategoryId = null,
  selectedCategoryName = null,
  landingUrl = '/',
  authModal = {},
}) {
  const [activeCategoryId, setActiveCategoryId] = useState(selectedCategoryId);
  const [activeCategoryName, setActiveCategoryName] = useState(selectedCategoryName);
  const [authProduct, setAuthProduct] = useState(null);
  const [visibleCount, setVisibleCount] = useState(PAGE_SIZE);

  const filteredProducts = useMemo(() => {
    if (activeCategoryId) {
      return products.filter((product) => Number(product.categoryId) === Number(activeCategoryId));
    }

    if (activeCategoryName) {
      return products.filter((product) => categoryMatches(product.categoryName, activeCategoryName));
    }

    return products;
  }, [activeCategoryId, activeCategoryName, products]);

  const visibleProducts = useMemo(
    () => filteredProducts.slice(0, visibleCount),
    [filteredProducts, visibleCount],
  );

  const hasMoreProducts = visibleCount < filteredProducts.length;

  useEffect(() => {
    setVisibleCount(PAGE_SIZE);
  }, [activeCategoryId, activeCategoryName]);

  useEffect(() => {
    if (!hasMoreProducts) return undefined;

    let ticking = false;

    const loadMoreIfNeeded = () => {
      const pageBottom = window.scrollY + window.innerHeight;
      const loadPoint = document.documentElement.scrollHeight - 850;

      if (pageBottom >= loadPoint) {
        setVisibleCount((current) => Math.min(current + PAGE_SIZE, filteredProducts.length));
      }

      ticking = false;
    };

    const onScroll = () => {
      if (ticking) return;

      ticking = true;
      window.requestAnimationFrame(loadMoreIfNeeded);
    };

    loadMoreIfNeeded();
    window.addEventListener('scroll', onScroll, { passive: true });

    return () => window.removeEventListener('scroll', onScroll);
  }, [filteredProducts.length, hasMoreProducts, visibleCount]);

  const loadMoreProducts = () => {
    setVisibleCount((current) => Math.min(current + PAGE_SIZE, filteredProducts.length));
  };

  const resetCategory = () => {
    setActiveCategoryId(null);
    setActiveCategoryName(null);
  };

  const selectCategory = (categoryId) => {
    setActiveCategoryId(categoryId);
    setActiveCategoryName(null);
  };

  return (
    <div className="page catalog-page">
      <PublicNav landingUrl={landingUrl} />
      <section className="catalog-section catalog-section--standalone" id="catalogo">
        <div className="catalog-head">
          <div>
            <h1 className="section-title dark">Catalogo de productos.</h1>
            <p className="section-copy dark-copy">
              Explora los productos disponibles. Para comprar, primero debes registrarte o iniciar sesion.
            </p>
          </div>
          <div className="catalog-filter-row" aria-label="Filtrar por categoria">
            <button type="button" className={!activeCategoryId && !activeCategoryName ? 'is-active' : ''} onClick={resetCategory}>Todos</button>
            {categories.map((category) => (
              <button
                key={category.id}
                type="button"
                className={
                  Number(activeCategoryId) === Number(category.id)
                  || (!activeCategoryId && normalizeText(activeCategoryName) === normalizeText(category.name))
                    ? 'is-active'
                    : ''
                }
                onClick={() => selectCategory(category.id)}
              >
                {category.name}
              </button>
            ))}
          </div>
        </div>

        <div className="catalog-grid">
          {filteredProducts.length ? visibleProducts.map((product) => (
            <CatalogCard product={product} key={product.id} onBuy={setAuthProduct} />
          )) : (
            <article className="catalog-empty">
              <strong>No hay productos disponibles por ahora.</strong>
              <span>Vuelve pronto para revisar el catalogo PIL.</span>
            </article>
          )}
          {hasMoreProducts && (
            <div className="catalog-loader">
              <span aria-hidden="true" />
              <button type="button" onClick={loadMoreProducts}>Ver mas productos</button>
            </div>
          )}
        </div>
      </section>
      <AuthModal
        product={authProduct}
        copy={authModal.defaultCopy || 'Accede a tu cuenta para comprar tus productos favoritos.'}
        registerUrl={authModal.registerUrl || '/register'}
        loginUrl={authModal.loginUrl || '/login'}
        onClose={() => setAuthProduct(null)}
      />
    </div>
  );
}
