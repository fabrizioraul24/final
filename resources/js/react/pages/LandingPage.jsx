const menuItems = ['Nuestra historia', 'Productos', 'Nuestras plantas'];
const tickerItems = ['CALIDAD', 'NUTRICIÓN', 'CONFIANZA', 'BOLIVIA'];

const stats = [
  { value: '65', label: 'años creciendo\njuntos' },
  { value: '3', label: 'plantas\nen Bolivia' },
  { value: '14', label: 'categorías\nde productos' },
];

const categories = [
  {
    code: '01',
    title: 'Leches',
    subtitle: 'Nutrición que acompaña cada mañana',
    tone: 'sky',
    icon: 'glass',
  },
  {
    code: '02',
    title: 'Yogurts',
    subtitle: 'Sabor, fruta y practicidad',
    tone: 'pink',
    icon: 'yogurt',
  },
  {
    code: '03',
    title: 'Jugos y néctares',
    subtitle: 'Frutas seleccionadas para refrescar',
    tone: 'gold',
    icon: 'fruit',
  },
  {
    code: '04',
    title: 'Helados',
    subtitle: 'Pequeños momentos de felicidad',
    tone: 'cream',
    icon: 'icecream',
  },
];

const plants = [
  {
    code: '01',
    city: 'Cochabamba',
    description: 'Donde comenzó nuestra historia en 1960',
  },
  {
    code: '02',
    city: 'La Paz',
    description: 'Conectados con la región altiplánica',
  },
  {
    code: '03',
    city: 'Santa Cruz',
    description: 'Capacidad moderna para el oriente',
  },
];

const footerExplore = ['Nosotros', 'Productos', 'Plantas'];
const footerConnect = ['Trabaja con nosotros', 'Contáctanos', 'Blog PIL'];

function Logo() {
  return (
    <div className="logo" aria-label="PIL Bolivia">
      <div className="logo-mark">
        <span>PIL</span>
      </div>
      <span className="logo-text">BOLIVIA</span>
    </div>
  );
}

function FindPILSection() {
  return (
    <section className="find-section">
      <div className="find-card">
        <div className="find-copy">
          <div className="section-kicker dark-kicker">SIEMPRE CERCA DE TI</div>
          <h2 className="find-title">Encuentra PIL cerca de casa.</h2>
        </div>

        <div className="find-action">
          <p>Visita nuestras Bodegas PIL y PIL Express. Consulta el punto de venta mas cercano.</p>
          <a className="find-button" href="#">
            <span>Ver puntos de venta</span>
            <span aria-hidden="true">↗</span>
          </a>
        </div>
      </div>
    </section>
  );
}

function Footer() {
  return (
    <footer className="landing-footer">
      <div className="footer-grid">
        <div className="footer-brand">
          <div className="footer-logo">PIL</div>
          <p>Nutrición, sabor y confianza para las familias bolivianas.</p>
        </div>

        <div className="footer-col">
          <div className="footer-label">Explora</div>
          {footerExplore.map((item) => (
            <a href="#" key={item}>{item}</a>
          ))}
        </div>

        <div className="footer-col">
          <div className="footer-label">Conecta</div>
          {footerConnect.map((item) => (
            <a href="#" key={item}>{item}</a>
          ))}
        </div>

        <div className="footer-contact">
          <div className="footer-label">Línea gratuita</div>
          <strong>800-10-4848</strong>
          <span>Atención al cliente</span>
        </div>
      </div>

      <div className="footer-bottom">
        <span>© 2026 PIL Bolivia</span>
        <span>Hecho con orgullo boliviano</span>
        <a href="#inicio">Volver arriba ↑</a>
      </div>
    </footer>
  );
}

function Nav() {
  return (
    <header className="topbar">
      <Logo />
      <nav className="menu" aria-label="Principal">
        {menuItems.map((item) => (
          <a key={item} href="#">
            {item}
          </a>
        ))}
      </nav>
      <div className="topbar-actions">
        <a className="pill pill-outline" href="/login">
          Ingreso
        </a>
        <a className="pill pill-white" href="#">
          <span>Contáctanos</span>
          <span aria-hidden="true">↗</span>
        </a>
      </div>
    </header>
  );
}

function Hero() {
  return (
    <main className="hero">
      <div className="hero-left">
        <div className="eyebrow">
          <span className="eyebrow-line" />
          <span>DESDE 1960</span>
        </div>

        <h1 className="headline">
          <span>Alimentando</span>
          <span>
            a <em>Bolivia.</em>
          </span>
        </h1>

        <p className="lead">
          65 años creciendo junto a las familias bolivianas, con alimentos
          nutritivos, deliciosos y de confianza.
        </p>

        <div className="cta-row">
          <a className="pill pill-coral" href="#">
            <span>Conoce nuestros productos</span>
            <span aria-hidden="true">→</span>
          </a>

          <a className="history-link" href="#">
            <span>Nuestra historia</span>
            <span aria-hidden="true">↓</span>
          </a>
        </div>

        <div className="microcopy">
          <strong>Calidad que nos une</strong>
          <span>Tradición · Nutrición · Confianza</span>
        </div>
      </div>

      <div className="hero-visual">
        <div className="visual-accent" />
        <div className="years-badge">
          <strong>65</strong>
          <span>AÑOS</span>
          <span>CONTIGO</span>
        </div>

        <div className="photo-frame">
          <img
            src="https://images.pexels.com/photos/7504997/pexels-photo-7504997.jpeg?auto=compress&cs=tinysrgb&w=1600"
            alt="Madre e hija tomando leche"
          />
        </div>

        <div className="sabor-note">sabor de casa</div>
        <div className="discover-rail">
          <span>DESCUBRIR</span>
          <span aria-hidden="true">←</span>
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
          <span key={`${item}-${index}`}>
            {item}
            <i aria-hidden="true">✦</i>
          </span>
        ))}
      </div>
    </footer>
  );
}

function HistorySection() {
  return (
    <section className="history-section">
      <div className="section-kicker dark-kicker">01 / NUESTRA ESENCIA</div>
      <div className="history-grid">
        <div className="history-left">
          <h2 className="section-title dark">
            Una historia que se
            <span>sirve todos los días.</span>
          </h2>

          <div className="history-photo">
            <img
              src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1600&q=80"
              alt="Planta de producción"
            />
            <div className="photo-tag">1960</div>
          </div>
        </div>

        <div className="history-right">
          <p className="section-copy lead-copy dark-copy">
            Nacimos en Cochabamba y crecimos con una idea clara: acercar
            nutrición de calidad a cada rincón del país.
          </p>
          <p className="section-copy body-copy dark-copy">
            Hoy, nuestro trabajo reúne tradición, tecnología y el esfuerzo de
            miles de manos bolivianas. Cada producto que llega a tu mesa lleva
            una historia de cuidado y compromiso.
          </p>

          <div className="stats-row">
            {stats.map((stat) => (
              <div className="stat" key={stat.value}>
                <div className="stat-value">{stat.value}</div>
                <div className="stat-label">
                  {stat.label.split('\n').map((line) => (
                    <span key={line}>{line}</span>
                  ))}
                </div>
              </div>
            ))}
          </div>

          <a className="section-link dark-link" href="#">
            <span>Conoce más de nosotros</span>
            <span aria-hidden="true">↗</span>
          </a>
        </div>
      </div>
    </section>
  );
}

function CategoryCard({ code, title, subtitle, tone, icon }) {
  return (
    <article className={`category-card ${tone}`}>
      <div className="card-code">{code}</div>
      <div className={`product-illustration ${icon}`} aria-hidden="true">
        {icon === 'glass' && <div className="glass" />}
        {icon === 'yogurt' && (
          <div className="yogurt">
            <div className="yogurt-spoon" />
          </div>
        )}
        {icon === 'fruit' && <div className="fruit" />}
        {icon === 'icecream' && <div className="icecream" />}
      </div>
      <div className="card-copy">
        <h3>{title}</h3>
        <p>{subtitle}</p>
      </div>
      <button className="card-arrow" type="button" aria-label={title}>
        ↗
      </button>
    </article>
  );
}

function CategoriesSection() {
  return (
    <section className="categories-section">
      <div className="section-kicker light-kicker">02 / PARA CADA MOMENTO</div>
      <div className="categories-head">
        <h2 className="section-title light">Elige tu favorito.</h2>
        <p className="section-copy light-copy">
          Desde el desayuno hasta ese antojo de la tarde: hay un producto PIL
          para acompañarte.
        </p>
      </div>

      <div className="cards-grid">
        {categories.map((card) => (
          <CategoryCard key={card.code} {...card} />
        ))}
      </div>

      <a className="section-link light-link centered" href="#">
        <span>Ver las 14 categorías</span>
        <span aria-hidden="true">→</span>
      </a>
    </section>
  );
}

function PlantsSection() {
  return (
    <section className="plants-section">
      <div className="plants-photo-wrap">
        <img
          src="https://images.pexels.com/photos/257700/pexels-photo-257700.jpeg?auto=compress&cs=tinysrgb&w=1600"
          alt="Planta industrial con camiones de distribución"
        />
        <div className="made-badge">
          <span>Hecho aquí</span>
          <strong>en Bolivia</strong>
        </div>
      </div>

      <div className="plants-content">
        <div className="section-kicker dark-kicker">03 / PRESENCIA NACIONAL</div>
        <h2 className="section-title plants-title">
          Tres plantas.
          <span>Un mismo</span>
          <span>compromiso.</span>
        </h2>
        <p className="plants-copy">
          Estamos estratégicamente presentes para producir y acercar alimentos
          de calidad a las familias de todo el país.
        </p>

        <div className="plants-list">
          {plants.map((plant) => (
            <a className="plant-row" href="#" key={plant.code}>
              <span className="plant-code">{plant.code}</span>
              <span className="plant-text">
                <strong>{plant.city}</strong>
                <span>{plant.description}</span>
              </span>
              <span className="plant-arrow" aria-hidden="true">
                →
              </span>
            </a>
          ))}
        </div>
      </div>
    </section>
  );
}

export default function App() {
  return (
    <div className="page">
      <div className="backdrop-arc" />
      <Nav />
      <Hero />
      <Ticker />
      <HistorySection />
      <CategoriesSection />
      <PlantsSection />
      <FindPILSection />
      <Footer />
    </div>
  );
}
