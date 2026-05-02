import { useState } from "react";
import type { ChangeEvent, FormEvent } from "react";
import api from "../api/axios";

type AiResult = {
  skills?: string[];
  summary?: string;
  soft_skills?: string[];
  languages?: string[];
  experience_level?: string;
  years_of_experience_estimate?: number;
  recommended_roles?: string[];
};

type Candidate = {
  id: number;
  full_name: string;
  email?: string;
  phone?: string;
  summary?: string;
  cv_path?: string;
};

type ValidationErrors = Record<string, string[]>;

export default function CreateCandidatePage() {
  const [form, setForm] = useState({
    full_name: "",
    email: "",
    phone: "+216",
  });

  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [candidate, setCandidate] = useState<Candidate | null>(null);
  const [aiResult, setAiResult] = useState<AiResult | null>(null);
  const [error, setError] = useState("");
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>(
    {}
  );

  const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;

    if (name === "phone") {
      let cleaned = value.replace(/[^\d+]/g, "");

      if (!cleaned.startsWith("+216")) {
        cleaned = "+216";
      }

      const rest = cleaned.replace("+216", "").replace(/\D/g, "").slice(0, 8);

      setForm((prev) => ({
        ...prev,
        phone: `+216${rest}`,
      }));
      return;
    }

    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] ?? null;
    setFile(selectedFile);
    setValidationErrors((prev) => ({
      ...prev,
      cv: [],
    }));
    setError("");
  };

  const resetForm = () => {
    setForm({
      full_name: "",
      email: "",
      phone: "+216",
    });
    setFile(null);
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (!file) {
      setError("Veuillez sélectionner un CV.");
      return;
    }

    try {
      setLoading(true);
      setError("");
      setValidationErrors({});
      setCandidate(null);
      setAiResult(null);

      const formData = new FormData();
      formData.append("full_name", form.full_name);
      formData.append("email", form.email);
      formData.append("phone", form.phone);
      formData.append("cv", file);

      const response = await api.post("/candidates", formData);

      setCandidate(response.data.candidate);
      setAiResult(response.data.ai_result);
      resetForm();
    } catch (err: any) {
      if (err?.response?.status === 422) {
        setValidationErrors(err.response.data.errors || {});
        setError(err.response.data.message || "Erreur de validation.");
      } else {
        setError(
          err?.response?.data?.message ||
            "Une erreur est survenue lors de la création du candidat."
        );
      }
    } finally {
      setLoading(false);
    }
  };

  const getFieldError = (field: string) => validationErrors[field]?.[0];

  return (
    <div className="page-shell">
      <div className="app-layout">
        <section className="hero-panel">
          <span className="hero-badge">Recruitment AI Assistant</span>

          <h2 className="hero-title">Créer un candidat</h2>

          <p className="hero-description">
            Ajoutez un candidat, importez son CV et obtenez automatiquement un
            résumé, une analyse des compétences et une estimation du niveau
            d’expérience.
          </p>

          <div className="hero-features">
            <div className="feature-card">Analyse rapide du CV</div>
            <div className="feature-card">
              Extraction automatique des compétences
            </div>
            <div className="feature-card">Résumé IA en français</div>
          </div>
        </section>

        <section className="form-card">
          <div className="card-header">
            <h2>Nouveau candidat</h2>
            <p>
              Importez un CV au format <strong>.txt</strong>,{" "}
              <strong>.pdf</strong> ou <strong>.docx</strong>.
            </p>
          </div>

          <form onSubmit={handleSubmit} className="candidate-form">
            <div className="form-group">
              <label>Nom complet</label>
              <input
                name="full_name"
                placeholder="Ex : Mohamed Amine Ben Salah"
                value={form.full_name}
                onChange={handleChange}
              />
              {getFieldError("full_name") && (
                <span className="field-error">{getFieldError("full_name")}</span>
              )}
            </div>

            <div className="form-group">
              <label>Email</label>
              <input
                name="email"
                type="email"
                placeholder="Ex : mohamed.bensalah@gmail.com"
                value={form.email}
                onChange={handleChange}
              />
              {getFieldError("email") && (
                <span className="field-error">{getFieldError("email")}</span>
              )}
            </div>

            <div className="form-group">
              <label>Téléphone</label>
              <input
                name="phone"
                placeholder="+21612345678"
                value={form.phone}
                onChange={handleChange}
              />
              {getFieldError("phone") && (
                <span className="field-error">{getFieldError("phone")}</span>
              )}
            </div>

            <div className="form-group">
              <label>CV</label>
              <input
                type="file"
                accept=".txt,.pdf,.doc,.docx"
                onChange={handleFileChange}
                className="file-input"
              />
              {file && <span className="file-name">{file.name}</span>}
              {getFieldError("cv") && (
                <span className="field-error">{getFieldError("cv")}</span>
              )}
            </div>

            <button type="submit" className="primary-button" disabled={loading}>
              {loading ? "Création en cours..." : "Créer le candidat"}
            </button>
          </form>

          {error && <div className="alert alert-error">{error}</div>}

          {candidate && (
            <div className="result-card">
              <h3>Candidat créé</h3>

              <div className="result-grid">
                <div>
                  <span className="result-label">ID</span>
                  <p>{candidate.id}</p>
                </div>
                <div>
                  <span className="result-label">Nom</span>
                  <p>{candidate.full_name}</p>
                </div>
                <div>
                  <span className="result-label">Email</span>
                  <p>{candidate.email || "-"}</p>
                </div>
                <div>
                  <span className="result-label">Téléphone</span>
                  <p>{candidate.phone || "-"}</p>
                </div>
              </div>

              <div className="result-block">
                <span className="result-label">Résumé enregistré</span>
                <p>{candidate.summary || "-"}</p>
              </div>
            </div>
          )}

          {aiResult && (
            <div className="result-card">
              <h3>Analyse IA</h3>

              <div className="result-block">
                <span className="result-label">Résumé</span>
                <p>{aiResult.summary || "-"}</p>
              </div>

              <div className="result-block">
                <span className="result-label">Compétences techniques</span>
                {aiResult.skills && aiResult.skills.length > 0 ? (
                  <div className="skills-list">
                    {aiResult.skills.map((skill) => (
                      <span key={skill} className="skill-chip">
                        {skill}
                      </span>
                    ))}
                  </div>
                ) : (
                  <p>Aucune compétence détectée.</p>
                )}
              </div>

              {aiResult.soft_skills && aiResult.soft_skills.length > 0 && (
                <div className="result-block">
                  <span className="result-label">Soft skills</span>
                  <div className="skills-list">
                    {aiResult.soft_skills.map((skill) => (
                      <span key={skill} className="skill-chip">
                        {skill}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {aiResult.languages && aiResult.languages.length > 0 && (
                <div className="result-block">
                  <span className="result-label">Langues</span>
                  <div className="skills-list">
                    {aiResult.languages.map((language) => (
                      <span key={language} className="skill-chip">
                        {language}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              <div className="result-block">
                <span className="result-label">Expérience estimée</span>
                <p>
                  {aiResult.experience_level || "-"}
                  {typeof aiResult.years_of_experience_estimate === "number"
                    ? ` (${aiResult.years_of_experience_estimate} ans)`
                    : ""}
                </p>
              </div>

              {aiResult.recommended_roles &&
                aiResult.recommended_roles.length > 0 && (
                  <div className="result-block">
                    <span className="result-label">Postes recommandés</span>
                    <div className="skills-list">
                      {aiResult.recommended_roles.map((role) => (
                        <span key={role} className="skill-chip">
                          {role}
                        </span>
                      ))}
                    </div>
                  </div>
                )}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}