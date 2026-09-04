const tickerItems = ['CALIDAD', 'NUTRICION', 'CONFIANZA', 'BOLIVIA'];

const stats = [
  { value: '65', label: 'anos creciendo\njuntos' },
  { value: '3', label: 'plantas\nen Bolivia' },
  { value: '14', label: 'categorias\nde productos' },
];

const fixedCategories = [
  {
    code: '01',
    title: 'Leches',
    subtitle: 'Nutricion que acompana cada manana',
    categoryName: 'Leches',
    tone: 'sky',
    icon: 'glass',
    productImageUrl: 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=1200&q=80',
  },
  {
    code: '02',
    title: 'Yogurts',
    subtitle: 'Sabor, fruta y practicidad',
    categoryName: 'Yogurts',
    tone: 'pink',
    icon: 'yogurt',
    productImageUrl: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=1200&q=80',
  },
  {
    code: '03',
    title: 'Jugos y nectares',
    subtitle: 'Frutas seleccionadas para refrescar',
    categoryName: 'Jugos y nectares',
    tone: 'gold',
    icon: 'fruit',
    productImageUrl: 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=1200&q=80',
  },
  {
    code: '04',
    title: 'Mantequillas y mermeladas',
    subtitle: 'Suavidad y dulzor para acompanar tus panes',
    categoryName: 'Mantequillas y mermeladas',
    tone: 'cream',
    icon: 'fruit',
    productImageUrl: 'https://www.ful-filled.com/wp-content/uploads/2020/05/RJZ_8225.jpg',
  },
];

const plants = [
  { code: '01', city: 'Cochabamba', description: 'Donde comenzo nuestra historia en 1960' },
  { code: '02', city: 'La Paz', description: 'Conectados con la region altiplanica' },
  { code: '03', city: 'Santa Cruz', description: 'Capacidad moderna para el oriente' },
];

function catalogCategoryUrl(catalogUrl, categoryName) {
  if (!categoryName) return catalogUrl;

  return `${catalogUrl}?categoria=${encodeURIComponent(categoryName)}`;
}

function Logo() {
  return (
    <div className="logo" aria-label="PIL Bolivia">
      <div className="logo-mark"><span>PIL</span></div>
      <span className="logo-text">BOLIVIA</span>
    </div>
  );
}

function Nav({ catalogUrl }) {
  const menuItems = [
    { label: 'Nuestra historia', href: '#historia' },
    { label: 'Productos', href: catalogUrl },
    { label: 'Nuestras plantas', href: '#plantas' },
  ];

  return (
    <header className="topbar" id="inicio">
      <Logo />
      <nav className="menu" aria-label="Principal">
        {menuItems.map((item) => (
          <a key={item.label} href={item.href}>{item.label}</a>
        ))}
      </nav>
      <div className="topbar-actions">
        <a className="pill pill-outline" href="/login">Ingreso</a>
        <a className="pill pill-white" href={catalogUrl}>
          <span>Ver catalogo</span>
          <i className="ri-arrow-right-up-line" aria-hidden="true" />
        </a>
      </div>
    </header>
  );
}

function Hero({ catalogUrl }) {
  return (
    <main className="hero">
      <div className="hero-left">
        <div className="eyebrow">
          <span className="eyebrow-line" />
          <span>DESDE 1960</span>
        </div>
        <h1 className="headline">
          <span>Alimentando</span>
          <span>a <em>Bolivia.</em></span>
        </h1>
        <p className="lead">
          65 anos creciendo junto a las familias bolivianas, con alimentos nutritivos,
          deliciosos y de confianza.
        </p>
        <div className="cta-row">
          <a className="pill pill-coral" href={catalogUrl}>
            <span>Conoce nuestros productos</span>
            <i className="ri-arrow-right-line" aria-hidden="true" />
          </a>
          <a className="history-link" href="#historia">
            <span>Nuestra historia</span>
            <i className="ri-arrow-down-line" aria-hidden="true" />
          </a>
        </div>
        <div className="microcopy">
          <strong>Calidad que nos une</strong>
          <span>Tradicion &middot; Nutricion &middot; Confianza</span>
        </div>
      </div>

      <div className="hero-visual">
        <div className="visual-accent" />
        <div className="years-badge">
          <strong>65</strong>
          <span>ANOS</span>
          <span>CONTIGO</span>
        </div>
        <div className="photo-frame">
          <img src="https://images.pexels.com/photos/7504997/pexels-photo-7504997.jpeg?auto=compress&cs=tinysrgb&w=1600" alt="Familia compartiendo leche" />
        </div>
        <div className="sabor-note">sabor de casa</div>
        <div className="discover-rail">
          <span>DESCUBRIR</span>
          <i className="ri-arrow-left-line" aria-hidden="true" />
        </div>
      </div>
    </main>
  );
}

function Ticker() {
  return (
    <footer className="ticker" aria-label="Valores de marca">
      <div className="ticker-track">
        {[...tickerItems, ...tickerItems, ...tickerItems].map((item, index) => (
          <span key={`${item}-${index}`}>{item}<i aria-hidden="true">*</i></span>
        ))}
      </div>
    </footer>
  );
}

function HistorySection({ catalogUrl }) {
  return (
    <section className="history-section" id="historia">
      <div className="section-kicker dark-kicker">01 / NUESTRA ESENCIA</div>
      <div className="history-grid">
        <div className="history-left">
          <h2 className="section-title dark">Una historia que se<span>sirve todos los dias.</span></h2>
          <div className="history-photo">
            <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1600&q=80" alt="Planta de produccion" />
            <div className="photo-tag">1960</div>
          </div>
        </div>
        <div className="history-right">
          <p className="section-copy lead-copy dark-copy">
            Nacimos en Cochabamba y crecimos con una idea clara: acercar nutricion
            de calidad a cada rincon del pais.
          </p>
          <p className="section-copy body-copy dark-copy">
            Hoy, nuestro trabajo reune tradicion, tecnologia y el esfuerzo de miles
            de manos bolivianas. Cada producto que llega a tu mesa lleva una historia
            de cuidado y compromiso.
          </p>
          <div className="stats-row">
            {stats.map((stat) => (
              <div className="stat" key={stat.value}>
                <div className="stat-value">{stat.value}</div>
                <div className="stat-label">
                  {stat.label.split('\n').map((line) => <span key={line}>{line}</span>)}
                </div>
              </div>
            ))}
          </div>
          <a className="section-link dark-link" href={catalogUrl}>
            <span>Ver productos PIL</span>
            <i className="ri-arrow-right-up-line" aria-hidden="true" />
          </a>
        </div>
      </div>
    </section>
  );
}

function CategoryIllustration({ icon }) {
  return (
    <div className={`product-illustration ${icon}`} aria-hidden="true">
      {icon === 'glass' && <div className="glass" />}
      {icon === 'yogurt' && <div className="yogurt"><div className="yogurt-spoon" /></div>}
      {icon === 'fruit' && <div className="fruit" />}
    </div>
  );
}

function CategoryCard({ code, title, subtitle, tone, icon, productImageUrl, href }) {
  return (
    <article className={`category-card ${tone}`}>
      <div className="card-code">{code}</div>
      {productImageUrl ? (
        <div className="category-product-photo">
          <img src={productImageUrl} alt={title} />
        </div>
      ) : (
        <CategoryIllustration icon={icon} />
      )}
      <div className="card-copy">
        <span>Categoria PIL</span>
        <h3>{title}</h3>
        <p>{subtitle}</p>
      </div>
      <a className="card-arrow" href={href} aria-label={`Ver ${title}`}>
        <i className="ri-arrow-right-up-line" aria-hidden="true" />
      </a>
    </article>
  );
}

function CategoriesSection({ catalogUrl }) {
  return (
    <section className="categories-section">
      <div className="section-kicker light-kicker">02 / CATEGORIAS MAS VENDIDAS</div>
      <div className="categories-head">
        <h2 className="section-title light">Elige tu favorito.</h2>
        <p className="section-copy light-copy">
          Desde el desayuno hasta ese antojo de la tarde: hay un producto PIL para acompanarte.
        </p>
      </div>
      <div className="cards-grid cards-grid--top">
        {fixedCategories.map((card) => (
          <CategoryCard
            key={card.code}
            {...card}
            href={catalogCategoryUrl(catalogUrl, card.categoryName)}
          />
        ))}
      </div>
      <a className="section-link light-link centered" href={catalogUrl}>
        <span>Ver catalogo de productos</span>
        <i className="ri-arrow-right-line" aria-hidden="true" />
      </a>
    </section>
  );
}

function PlantsSection({ catalogUrl }) {
  return (
    <section className="plants-section" id="plantas">
      <div className="plants-photo-wrap">
        <img src="https://images.pexels.com/photos/257700/pexels-photo-257700.jpeg?auto=compress&cs=tinysrgb&w=1600" alt="Planta industrial con camiones de distribucion" />
        <div className="made-badge"><span>Hecho aqui</span><strong>en Bolivia</strong></div>
      </div>
      <div className="plants-content">
        <div className="section-kicker dark-kicker">04 / PRESENCIA NACIONAL</div>
        <h2 className="section-title plants-title">Tres plantas.<span>Un mismo</span><span>compromiso.</span></h2>
        <p className="plants-copy">
          Estamos estrategicamente presentes para producir y acercar alimentos de calidad
          a las familias de todo el pais.
        </p>
        <div className="plants-list">
          {plants.map((plant) => (
            <a className="plant-row" href={catalogUrl} key={plant.code}>
              <span className="plant-code">{plant.code}</span>
              <span className="plant-text"><strong>{plant.city}</strong><span>{plant.description}</span></span>
              <i className="plant-arrow ri-arrow-right-line" aria-hidden="true" />
            </a>
          ))}
        </div>
      </div>
    </section>
  );
}

function FindPILSection({ catalogUrl }) {
  return (
    <section className="find-section">
      <div className="find-card">
        <div className="find-copy">
          <div className="section-kicker dark-kicker">SIEMPRE CERCA DE TI</div>
          <h2 className="find-title">Encuentra PIL cerca de casa.</h2>
        </div>
        <div className="find-action">
          <p>Revisa el catalogo, elige tus favoritos y continua con una cuenta para comprar.</p>
          <a className="find-button" href={catalogUrl}>
            <span>Ver productos</span>
            <i className="ri-arrow-right-up-line" aria-hidden="true" />
          </a>
        </div>
      </div>
    </section>
  );
}

function Footer({ catalogUrl }) {
  const footerExplore = [
    { label: 'Nosotros', href: '#historia' },
    { label: 'Productos', href: catalogUrl },
    { label: 'Plantas', href: '#plantas' },
  ];

  const footerConnect = [
    { label: 'Registrarme', href: '/register' },
    { label: 'Iniciar sesion', href: '/login' },
    { label: 'Catalogo PIL', href: catalogUrl },
  ];

  return (
    <footer className="landing-footer">
      <div className="footer-grid">
        <div className="footer-brand">
          <div className="footer-logo">PIL</div>
          <p>Nutricion, sabor y confianza para las familias bolivianas.</p>
        </div>
        <div className="footer-col">
          <div className="footer-label">Explora</div>
          {footerExplore.map((item) => <a href={item.href} key={item.label}>{item.label}</a>)}
        </div>
        <div className="footer-col">
          <div className="footer-label">Conecta</div>
          {footerConnect.map((item) => <a href={item.href} key={item.label}>{item.label}</a>)}
        </div>
        <div className="footer-contact">
          <div className="footer-label">Linea gratuita</div>
          <strong>800-10-4848</strong>
          <span>Atencion al cliente</span>
        </div>
      </div>
      <div className="footer-bottom">
        <span>2026 PIL Bolivia</span>
        <span>Hecho con orgullo boliviano</span>
        <a href="#inicio">Volver arriba ↑</a>
      </div>
    </footer>
  );
}

export default function App({ catalogUrl = '/catalogo' }) {
  return (
    <div className="page">
      <div className="backdrop-arc" />
      <Nav catalogUrl={catalogUrl} />
      <Hero catalogUrl={catalogUrl} />
      <Ticker />
      <HistorySection catalogUrl={catalogUrl} />
      <CategoriesSection catalogUrl={catalogUrl} />
      <PlantsSection catalogUrl={catalogUrl} />
      <FindPILSection catalogUrl={catalogUrl} />
      <Footer catalogUrl={catalogUrl} />
    </div>
  );
}
