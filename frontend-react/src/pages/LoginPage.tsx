import { useState } from "react";
import type { FormEvent } from "react";
import api from "../api/axios";

type Props = {
  onAuthSuccess: (user: { id: number; name: string; email: string }) => void;
  goToRegister: () => void;
};

export default function LoginPage({ onAuthSuccess, goToRegister }: Props) {
  const [form, setForm] = useState({
    email: "",
    password: "",
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    try {
      setLoading(true);
      setError("");

      const response = await api.post("/login", form);

      localStorage.setItem("auth_token", response.data.token);
      onAuthSuccess(response.data.user);
    } catch (err: any) {
      setError(
        err?.response?.data?.message || "Erreur lors de la connexion."
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Connexion</h1>
        <p>Connecte-toi à TalentSync AI.</p>

        <form onSubmit={handleSubmit} className="candidate-form">
          <div className="form-group">
            <label>Email</label>
            <input
              type="email"
              placeholder="Ex : recruteur@gmail.com"
              value={form.email}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, email: e.target.value }))
              }
            />
          </div>

          <div className="form-group">
            <label>Mot de passe</label>
            <input
              type="password"
              placeholder="Votre mot de passe"
              value={form.password}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, password: e.target.value }))
              }
            />
          </div>

          <button type="submit" className="primary-button" disabled={loading}>
            {loading ? "Connexion..." : "Se connecter"}
          </button>
        </form>

        {error && <div className="alert alert-error">{error}</div>}

        <p className="auth-switch">
          Pas encore de compte ?{" "}
          <button type="button" className="auth-link" onClick={goToRegister}>
            Créer un compte
          </button>
        </p>
      </div>
    </div>
  );
}