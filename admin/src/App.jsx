import { BrowserRouter, Routes, Route, useNavigate, useParams } from "react-router-dom";
import { Nav } from "./components/layout/Nav";
import { Footer } from "./components/layout/Footer";
import { Home } from "./pages/Home";
import { Pricing } from "./pages/Pricing";
import { FeaturesOverview } from "./pages/FeaturesOverview";
import { HowItWorks } from "./pages/HowItWorks";
import { HelpCentre } from "./pages/HelpCentre";
import { GettingStarted } from "./pages/GettingStarted";
import { Blog } from "./pages/Blog";
import { About } from "./pages/About";
import { Contact } from "./pages/Contact";
import { BizType } from "./pages/BizType";
import { Signup } from "./pages/auth/Signup";
import { Login } from "./pages/auth/Login";
import { AppointmentsPage } from "./pages/features/AppointmentsPage";
import { POSPage } from "./pages/features/POSPage";
import { ClientsPage } from "./pages/features/ClientsPage";
import { StaffPage } from "./pages/features/StaffPage";
import { RetailPage } from "./pages/features/RetailPage";
import { MarketingPage } from "./pages/features/MarketingPage";
import { ReviewsPage } from "./pages/features/ReviewsPage";
import { AnalyticsPage } from "./pages/features/AnalyticsPage";
import { MultiLocationPage } from "./pages/features/MultiLocationPage";
import { WebsitePage } from "./pages/features/WebsitePage";
import { BIZ_BY_SLUG, routeFor } from "./constants/routes";

function PageShell({ children }) {
  const navigate = useNavigate();
  const nav = (key, opts = {}) => {
    navigate(routeFor(key, opts));
    window.scrollTo(0, 0);
  };

  return (
    <>
      <Nav nav={nav} />
      {children}
      <Footer nav={nav} />
    </>
  );
}

function BizTypeRoute() {
  const navigate = useNavigate();
  const { slug } = useParams();
  const type = BIZ_BY_SLUG[slug] || "Hair Salon";
  const nav = (key, opts = {}) => {
    navigate(routeFor(key, opts));
    window.scrollTo(0, 0);
  };

  return (
    <PageShell>
      <BizType type={type} nav={nav} />
    </PageShell>
  );
}

function RoutedPage({ Page, ...props }) {
  const navigate = useNavigate();
  const nav = (key, opts = {}) => {
    navigate(routeFor(key, opts));
    window.scrollTo(0, 0);
  };

  return (
    <PageShell>
      <Page nav={nav} {...props} />
    </PageShell>
  );
}

export default function App() {
  const base = import.meta.env.BASE_URL.replace(/\/$/, "");
  const basename = base === "" ? undefined : base;

  return (
    <BrowserRouter basename={basename}>
      <Routes>
        <Route path="/" element={<RoutedPage Page={Home} />} />
        <Route path="/pricing" element={<RoutedPage Page={Pricing} />} />
        <Route path="/features" element={<RoutedPage Page={FeaturesOverview} />} />
        <Route path="/features/appointments" element={<RoutedPage Page={AppointmentsPage} />} />
        <Route path="/features/pos" element={<RoutedPage Page={POSPage} />} />
        <Route path="/features/clients" element={<RoutedPage Page={ClientsPage} />} />
        <Route path="/features/staff" element={<RoutedPage Page={StaffPage} />} />
        <Route path="/features/retail" element={<RoutedPage Page={RetailPage} />} />
        <Route path="/features/marketing" element={<RoutedPage Page={MarketingPage} />} />
        <Route path="/features/reviews" element={<RoutedPage Page={ReviewsPage} />} />
        <Route path="/features/analytics" element={<RoutedPage Page={AnalyticsPage} />} />
        <Route path="/features/multi-location" element={<RoutedPage Page={MultiLocationPage} />} />
        <Route path="/features/website" element={<RoutedPage Page={WebsitePage} />} />
        <Route path="/business/:slug" element={<BizTypeRoute />} />
        <Route path="/how-it-works" element={<RoutedPage Page={HowItWorks} />} />
        <Route path="/help-centre" element={<RoutedPage Page={HelpCentre} />} />
        <Route path="/getting-started" element={<RoutedPage Page={GettingStarted} />} />
        <Route path="/blog" element={<RoutedPage Page={Blog} />} />
        <Route path="/about" element={<RoutedPage Page={About} />} />
        <Route path="/contact" element={<RoutedPage Page={Contact} />} />
        <Route path="/signup" element={<Signup />} />
        <Route path="/login" element={<Login />} />
      </Routes>
    </BrowserRouter>
  );
}
