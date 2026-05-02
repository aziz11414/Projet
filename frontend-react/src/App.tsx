import { useEffect, useMemo, useState } from "react";
import api from "./api/axios";
import CreateCandidatePage from "./pages/CreateCandidatePage";
import CandidatesPage from "./pages/CandidatesPage";
import JobsPage from "./pages/JobsPage";
import LoginPage from "./pages/LoginPage";
import RegisterPage from "./pages/RegisterPage";

type Tab = "create" | "candidates" | "jobs";
type AuthMode = "login" | "register";

type User = {
  id: number;
  name: string;
  email: string;
};

function App() {
  const [tab, setTab] = useState<Tab>("create");
  const [authMode, setAuthMode] = useState<AuthMode>("login");
  const [user, setUser] = useState<User | null>(null);
  const [checkingAuth, setCheckingAuth] = useState(true);

  useEffect(() => {
    const checkUser = async () => {
      const token = localStorage.getItem("auth_token");

      if (!token) {
        setCheckingAuth(false);
        return;
      }

      try {
        const response = await api.get("/me");
        setUser(response.data);
      } catch (error) {
        localStorage.removeItem("auth_token");
        setUser(null);
      } finally {
        setCheckingAuth(false);
      }
    };

    checkUser();
  }, []);

  const handleAuthSuccess = (authenticatedUser: User) => {
    setUser(authenticatedUser);
    setTab("create");
  };

  const handleLogout = async () => {
    try {
      await api.post("/logout");
    } catch (error) {
      // ignore logout API error
    } finally {
      localStorage.removeItem("auth_token");
      setUser(null);
      setAuthMode("login");
      setTab("create");
    }
  };

  const pageTitle = useMemo(() => {
    switch (tab) {
      case "create":
        return "Nouveau candidat";
      case "candidates":
        return "Candidats";
      case "jobs":
        return "Jobs & Ranking";
      default:
        return "TalentSync AI";
    }
  }, [tab]);

  if (checkingAuth) {
    return <div className="loading-screen">Chargement...</div>;
  }

  if (!user) {
    return authMode === "login" ? (
      <LoginPage
        onAuthSuccess={handleAuthSuccess}
        goToRegister={() => setAuthMode("register")}
      />
    ) : (
      <RegisterPage
        onAuthSuccess={handleAuthSuccess}
        goToLogin={() => setAuthMode("login")}
      />
    );
  }

  return (
    <div className="app-root">
      <header className="topbar">
        <div className="topbar-brand">
          <div className="brand-dot" />
          <div>
            <h1>TalentSync AI</h1>
            <p>Bienvenue, {user.name}</p>
          </div>
        </div>

        <div className="topbar-right">
          <nav className="top-nav">
            <button
              type="button"
              className={tab === "create" ? "nav-tab nav-tab-active" : "nav-tab"}
              onClick={() => setTab("create")}
            >
              Nouveau candidat
            </button>

            <button
              type="button"
              className={
                tab === "candidates" ? "nav-tab nav-tab-active" : "nav-tab"
              }
              onClick={() => setTab("candidates")}
            >
              Candidats
            </button>

            <button
              type="button"
              className={tab === "jobs" ? "nav-tab nav-tab-active" : "nav-tab"}
              onClick={() => setTab("jobs")}
            >
              Jobs & Ranking
            </button>
          </nav>

          <button type="button" className="logout-button" onClick={handleLogout}>
            Déconnexion
          </button>
        </div>
      </header>

      <main>
        <section className="section-header">
          <div className="section-header-content">
            <span className="section-kicker">Admin Workspace</span>
            <h2>{pageTitle}</h2>
            <p>
              Gérez les candidats, les offres et le classement intelligent
              depuis votre espace recruteur.
            </p>
          </div>
        </section>

        {tab === "create" && <CreateCandidatePage />}
        {tab === "candidates" && <CandidatesPage />}
        {tab === "jobs" && <JobsPage />}
      </main>
    </div>
  );
}

export default App;