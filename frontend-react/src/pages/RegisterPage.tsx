import { useState } from "react";
import type { FormEvent } from "react";
import api from "../api/axios";

type Props = {
  onAuthSuccess: (user: { id: number; name: string; email: string }) => void;
  goToLogin: () => void;
};

export default function RegisterPage({ onAuthSuccess, goToLogin }: Props) {
  const [form, setForm] = useState({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    try {
      setLoading(true);
      setError("");

      const response = await api.post("/register", form);

      localStorage.setItem("auth_token", response.data.token);
      onAuthSuccess(response.data.user);
    } catch (err: any) {
      setError(
        err?.response?.data?.message || "Erreur lors de l'inscription."
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Créer un compte</h1>
        <p>Inscris-toi pour accéder à TalentSync AI.</p>

        <form onSubmit={handleSubmit} className="candidate-form">
          <div className="form-group">
            <label>Nom</label>
            <input
              type="text"
              placeholder="Ex : Aziz Hammami"
              value={form.name}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, name: e.target.value }))
              }
            />
          </div>

          <div className="form-group">
            <label>Email</label>
            <input
              type="email"
              placeholder="Ex : aziz@gmail.com"
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
              placeholder="Minimum 6 caractères"
              value={form.password}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, password: e.target.value }))
              }
            />
          </div>

          <div className="form-group">
            <label>Confirmer le mot de passe</label>
            <input
              type="password"
              placeholder="Retapez le mot de passe"
              value={form.password_confirmation}
              onChange={(e) =>
                setForm((prev) => ({
                  ...prev,
                  password_confirmation: e.target.value,
                }))
              }
            />
          </div>

          <button type="submit" className="primary-button" disabled={loading}>
            {loading ? "Création..." : "Créer mon compte"}
          </button>
        </form>

        {error && <div className="alert alert-error">{error}</div>}

        <p className="auth-switch">
          Déjà un compte ?{" "}
          <button type="button" className="auth-link" onClick={goToLogin}>
            Se connecter
          </button>
        </p>
      </div>
    </div>
  );
}