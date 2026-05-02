import { useEffect, useMemo, useState } from "react";
import type { FormEvent } from "react";
import api from "../api/axios";

type Job = {
  id: number;
  title: string;
  description: string;
  required_skills?: string[];
  status?: string;
};

type RankedCandidate = {
  candidate: {
    id: number;
    full_name: string;
    email?: string;
    phone?: string;
    summary?: string;
  };
  score: number;
  matched_skills: string[];
  missing_skills: string[];
  notes?: string;
  experience_level?: string;
  years_of_experience_estimate?: number;
};

const initialForm = {
  title: "",
  description: "",
  required_skills: "",
  status: "open",
};

export default function JobsPage() {
  const [jobs, setJobs] = useState<Job[]>([]);
  const [selectedJobId, setSelectedJobId] = useState<number | null>(null);
  const [ranking, setRanking] = useState<RankedCandidate[]>([]);
  const [loadingRanking, setLoadingRanking] = useState(false);
  const [loadingJobs, setLoadingJobs] = useState(false);

  const [form, setForm] = useState(initialForm);
  const [editingJobId, setEditingJobId] = useState<number | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState("");
  const [jobToDelete, setJobToDelete] = useState<Job | null>(null);
  const [deletingJobId, setDeletingJobId] = useState<number | null>(null);

  const selectedJob = useMemo(
    () => jobs.find((job) => job.id === selectedJobId) || null,
    [jobs, selectedJobId]
  );

  const fetchJobs = async () => {
    try {
      setLoadingJobs(true);
      const response = await api.get("/jobs");
      setJobs(response.data);
    } catch (error: any) {
      setMessage(
        error?.response?.data?.message ||
          "Impossible de charger les offres."
      );
    } finally {
      setLoadingJobs(false);
    }
  };

  const fetchRanking = async (jobId: number) => {
    setLoadingRanking(true);
    try {
      const response = await api.get(`/jobs/${jobId}/ranking`);
      setRanking(response.data.ranking || []);
    } catch (error: any) {
      setMessage(
        error?.response?.data?.message ||
          "Impossible de charger le ranking."
      );
    } finally {
      setLoadingRanking(false);
    }
  };

  useEffect(() => {
    fetchJobs();
  }, []);

  const resetForm = () => {
    setForm(initialForm);
    setEditingJobId(null);
  };

  const handleCreateOrUpdateJob = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const requiredSkills = form.required_skills
      .split(",")
      .map((skill) => skill.trim())
      .filter(Boolean);

    try {
      setSubmitting(true);
      setMessage("");

      if (editingJobId) {
        await api.put(`/jobs/${editingJobId}`, {
          title: form.title,
          description: form.description,
          required_skills: requiredSkills,
          status: form.status,
        });

        setMessage("Offre modifiée avec succès.");
      } else {
        await api.post("/jobs", {
          title: form.title,
          description: form.description,
          required_skills: requiredSkills,
          status: form.status,
        });

        setMessage("Offre créée avec succès.");
      }

      const currentSelected = selectedJobId;
      resetForm();
      await fetchJobs();

      if (currentSelected) {
        await fetchRanking(currentSelected);
      }
    } catch (error: any) {
      setMessage(
        error?.response?.data?.message ||
          "Une erreur est survenue lors de l'enregistrement de l’offre."
      );
    } finally {
      setSubmitting(false);
    }
  };

  const handleSelectJob = async (jobId: number) => {
    setSelectedJobId(jobId);
    await fetchRanking(jobId);
  };

  const handleEditJob = (job: Job) => {
    setEditingJobId(job.id);
    setForm({
      title: job.title,
      description: job.description,
      required_skills: (job.required_skills || []).join(", "),
      status: job.status || "open",
    });
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const openDeleteModal = (job: Job) => {
    setJobToDelete(job);
  };

  const closeDeleteModal = () => {
    setJobToDelete(null);
  };

  const confirmDeleteJob = async () => {
    if (!jobToDelete) return;

    try {
      setDeletingJobId(jobToDelete.id);
      setMessage("");

      await api.delete(`/jobs/${jobToDelete.id}`);

      if (selectedJobId === jobToDelete.id) {
        setSelectedJobId(null);
        setRanking([]);
      }

      if (editingJobId === jobToDelete.id) {
        resetForm();
      }

      closeDeleteModal();
      await fetchJobs();
      setMessage("Offre supprimée avec succès.");
    } catch (error: any) {
      setMessage(
        error?.response?.data?.message ||
          "Une erreur est survenue lors de la suppression de l’offre."
      );
    } finally {
      setDeletingJobId(null);
    }
  };

  return (
    <div className="page-shell">
      <div className="list-page">
        <div className="list-header">
          <h1>Jobs & Ranking</h1>
          <p>
            Créez, modifiez ou supprimez une offre puis laissez l’IA classer
            automatiquement les candidats.
          </p>
        </div>

        {message && <div className="alert alert-info">{message}</div>}

        <div className="two-columns">
          <section className="entity-card">
            <h2>{editingJobId ? "Modifier une offre" : "Créer une offre"}</h2>

            <form onSubmit={handleCreateOrUpdateJob} className="candidate-form">
              <div className="form-group">
                <label>Titre</label>
                <input
                  value={form.title}
                  onChange={(e) =>
                    setForm((prev) => ({ ...prev, title: e.target.value }))
                  }
                  placeholder="Ex : Développeur Full Stack Laravel / React"
                />
              </div>

              <div className="form-group">
                <label>Description</label>
                <textarea
                  className="modern-textarea"
                  value={form.description}
                  onChange={(e) =>
                    setForm((prev) => ({
                      ...prev,
                      description: e.target.value,
                    }))
                  }
                  placeholder="Décrivez le poste, les responsabilités, le contexte technique et le profil recherché..."
                />
              </div>

              <div className="form-group">
                <label>Compétences requises</label>
                <input
                  value={form.required_skills}
                  onChange={(e) =>
                    setForm((prev) => ({
                      ...prev,
                      required_skills: e.target.value,
                    }))
                  }
                  placeholder="Ex : React, Laravel, Docker, FastAPI"
                />
              </div>

              <div className="form-group">
                <label>Statut</label>
                <select
                  className="modern-select"
                  value={form.status}
                  onChange={(e) =>
                    setForm((prev) => ({
                      ...prev,
                      status: e.target.value,
                    }))
                  }
                >
                  <option value="open">Open</option>
                  <option value="closed">Closed</option>
                  <option value="draft">Draft</option>
                </select>
              </div>

              <div className="job-form-actions">
                <button
                  type="submit"
                  className="primary-button compact-button"
                  disabled={submitting}
                >
                  {submitting
                    ? editingJobId
                      ? "Modification..."
                      : "Création..."
                    : editingJobId
                    ? "Enregistrer"
                    : "Créer l’offre"}
                </button>

                {editingJobId && (
                  <button
                    type="button"
                    className="secondary-button"
                    onClick={resetForm}
                  >
                    Annuler
                  </button>
                )}
              </div>
            </form>
          </section>

          <section className="entity-card">
            <div className="jobs-panel-header">
              <div>
                <h2>Offres</h2>
                <p className="job-meta">
                  {loadingJobs
                    ? "Chargement des offres..."
                    : `${jobs.length} offre${jobs.length > 1 ? "s" : ""} disponible${jobs.length > 1 ? "s" : ""}`}
                </p>
              </div>
            </div>

            <div className="jobs-list">
              {jobs.map((job) => (
                <div
                  key={job.id}
                  className={`job-card-shell ${
                    selectedJobId === job.id ? "job-card-shell-active" : ""
                  }`}
                >
                  <button
                    type="button"
                    className={`job-item ${
                      selectedJobId === job.id ? "job-item-active" : ""
                    }`}
                    onClick={() => handleSelectJob(job.id)}
                  >
                    <div>
                      <strong>{job.title}</strong>
                      <p className="job-meta">
                        {(job.required_skills || []).join(", ") ||
                          "Aucune compétence renseignée"}
                      </p>
                    </div>
                    <span className="job-status">{job.status || "open"}</span>
                  </button>

                  <div className="job-item-actions">
                    <button
                      type="button"
                      className="secondary-button"
                      onClick={() => handleEditJob(job)}
                    >
                      Modifier
                    </button>

                    <button
                      type="button"
                      className="delete-button"
                      onClick={() => openDeleteModal(job)}
                      disabled={deletingJobId === job.id}
                    >
                      {deletingJobId === job.id ? "Suppression..." : "Supprimer"}
                    </button>
                  </div>
                </div>
              ))}

              {!loadingJobs && jobs.length === 0 && (
                <p className="empty-state">Aucune offre disponible.</p>
              )}
            </div>
          </section>
        </div>

        <section className="entity-card ranking-section">
          <div className="ranking-header-row">
            <div>
              <h2>Ranking candidats</h2>
              {selectedJob && (
                <p className="job-meta ranking-job-meta">
                  Offre sélectionnée : <strong>{selectedJob.title}</strong>
                </p>
              )}
            </div>
          </div>

          {loadingRanking && (
            <p className="empty-state">Calcul du ranking en cours...</p>
          )}

          {!loadingRanking && ranking.length === 0 && (
            <p className="empty-state">
              Sélectionnez une offre pour afficher le classement.
            </p>
          )}

          {!loadingRanking && ranking.length > 0 && (
            <div className="ranking-list">
              {ranking.map((item, index) => (
                <div key={item.candidate.id} className="ranking-card">
                  <div className="ranking-top">
                    <div>
                      <span className="ranking-badge">#{index + 1}</span>
                      <h3>{item.candidate.full_name}</h3>
                      <p className="muted-line">{item.candidate.email || "-"}</p>
                    </div>

                    <div className="score-box">{item.score}%</div>
                  </div>

                  <p className="ranking-notes">{item.notes}</p>

                  <div className="chips-block">
                    <span className="result-label">Compétences matchées</span>
                    <div className="skills-list">
                      {item.matched_skills.length > 0 ? (
                        item.matched_skills.map((skill) => (
                          <span key={skill} className="skill-chip">
                            {skill}
                          </span>
                        ))
                      ) : (
                        <span className="skill-chip">Aucune</span>
                      )}
                    </div>
                  </div>

                  <div className="chips-block">
                    <span className="result-label">Compétences manquantes</span>
                    <div className="skills-list">
                      {item.missing_skills.length > 0 ? (
                        item.missing_skills.map((skill) => (
                          <span key={skill} className="missing-chip">
                            {skill}
                          </span>
                        ))
                      ) : (
                        <span className="skill-chip">Aucune</span>
                      )}
                    </div>
                  </div>

                  <div className="chips-block">
                    <span className="result-label">Expérience estimée</span>
                    <p className="card-text">
                      {item.experience_level || "-"}
                      {typeof item.years_of_experience_estimate === "number"
                        ? ` (${item.years_of_experience_estimate} ans)`
                        : ""}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </section>

        {jobToDelete && (
          <div className="modal-overlay" onClick={closeDeleteModal}>
            <div className="modal-card" onClick={(e) => e.stopPropagation()}>
              <div className="modal-icon">!</div>
              <h3>Confirmer la suppression</h3>
              <p>
                Voulez-vous vraiment supprimer l’offre{" "}
                <strong>{jobToDelete.title}</strong> ?
                Cette action supprimera aussi les scores associés.
              </p>

              <div className="modal-actions">
                <button
                  type="button"
                  className="secondary-button"
                  onClick={closeDeleteModal}
                >
                  Annuler
                </button>

                <button
                  type="button"
                  className="delete-button"
                  onClick={confirmDeleteJob}
                  disabled={deletingJobId === jobToDelete.id}
                >
                  {deletingJobId === jobToDelete.id
                    ? "Suppression..."
                    : "Confirmer"}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}