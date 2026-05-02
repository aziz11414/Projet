import { useEffect, useState } from "react";
import api from "../api/axios";

type Candidate = {
  id: number;
  full_name: string;
  email?: string;
  phone?: string;
  summary?: string;
  cv_path?: string;
  cv_url?: string | null;
  extracted_text?: string;
};

type PaginatedCandidates = {
  data: Candidate[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export default function CandidatesPage() {
  const [candidates, setCandidates] = useState<Candidate[]>([]);
  const [selectedCandidate, setSelectedCandidate] = useState<Candidate | null>(null);
  const [q, setQ] = useState("");
  const [skill, setSkill] = useState("");
  const [loading, setLoading] = useState(false);
  const [loadingDetails, setLoadingDetails] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);
  const [message, setMessage] = useState("");

  const [page, setPage] = useState(1);
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 8,
    total: 0,
  });

  const [candidateToDelete, setCandidateToDelete] = useState<Candidate | null>(null);

  const fetchCandidates = async (targetPage = page) => {
    try {
      setLoading(true);
      setMessage("");

      const response = await api.get<PaginatedCandidates>("/candidates", {
        params: {
          q: q || undefined,
          skill: skill || undefined,
          page: targetPage,
          per_page: 8,
        },
      });

      setCandidates(response.data.data);
      setPagination({
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      });
      setPage(response.data.current_page);
    } catch {
      setMessage("Impossible de charger les candidats.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCandidates(1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleShowDetails = async (candidateId: number) => {
    try {
      setLoadingDetails(true);
      const response = await api.get(`/candidates/${candidateId}`);
      setSelectedCandidate(response.data);
    } catch {
      setMessage("Impossible de charger le détail du candidat.");
    } finally {
      setLoadingDetails(false);
    }
  };

  const openDeleteModal = (candidate: Candidate) => {
    setCandidateToDelete(candidate);
  };

  const closeDeleteModal = () => {
    setCandidateToDelete(null);
  };

  const confirmDelete = async () => {
    if (!candidateToDelete) return;

    try {
      setDeletingId(candidateToDelete.id);
      setMessage("");

      await api.delete(`/candidates/${candidateToDelete.id}`);

      if (selectedCandidate?.id === candidateToDelete.id) {
        setSelectedCandidate(null);
      }

      closeDeleteModal();
      setMessage("Candidat supprimé avec succès.");

      const shouldGoPrevPage =
        candidates.length === 1 && page > 1;

      await fetchCandidates(shouldGoPrevPage ? page - 1 : page);
    } catch (error: any) {
      setMessage(
        error?.response?.data?.message ||
          "Une erreur est survenue lors de la suppression."
      );
    } finally {
      setDeletingId(null);
    }
  };

  const canPrev = pagination.current_page > 1;
  const canNext = pagination.current_page < pagination.last_page;

  return (
    <div className="page-shell">
      <div className="list-page">
        <div className="list-header">
          <h1>Candidats</h1>
          <p>Recherche, filtrage, détail et gestion des profils candidats.</p>
        </div>

        <div className="toolbar">
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="Rechercher par nom, email, résumé..."
          />
          <input
            value={skill}
            onChange={(e) => setSkill(e.target.value)}
            placeholder="Filtrer par compétence (ex : React)"
          />
          <button
            className="primary-button compact-button"
            onClick={() => fetchCandidates(1)}
          >
            {loading ? "Chargement..." : "Filtrer"}
          </button>
        </div>

        {message && <div className="alert alert-info">{message}</div>}

        <div className="entity-card table-card">
          <div className="table-card-header">
            <div>
              <h2>Liste des candidats</h2>
              <p className="table-subtitle">
                {pagination.total} candidat{pagination.total > 1 ? "s" : ""} au total
              </p>
            </div>
          </div>

          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Résumé</th>
                  <th>CV</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {candidates.map((candidate) => (
                  <tr key={candidate.id}>
                    <td>
                      <strong>{candidate.full_name}</strong>
                    </td>
                    <td>{candidate.email || "-"}</td>
                    <td>{candidate.phone || "-"}</td>
                    <td className="summary-cell">
                      {candidate.summary || "Pas de résumé disponible."}
                    </td>
                    <td>
                      {candidate.cv_url ? (
                        <a
                          href={candidate.cv_url}
                          target="_blank"
                          rel="noreferrer"
                          className="table-link"
                        >
                          Ouvrir CV
                        </a>
                      ) : (
                        "-"
                      )}
                    </td>
                    <td>
                      <div className="table-actions">
                        <button
                          type="button"
                          className="secondary-button"
                          onClick={() => handleShowDetails(candidate.id)}
                        >
                          {loadingDetails && selectedCandidate?.id === candidate.id
                            ? "Chargement..."
                            : "Détail"}
                        </button>

                        <button
                          type="button"
                          className="delete-button"
                          onClick={() => openDeleteModal(candidate)}
                          disabled={deletingId === candidate.id}
                        >
                          {deletingId === candidate.id ? "Suppression..." : "Supprimer"}
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}

                {!loading && candidates.length === 0 && (
                  <tr>
                    <td colSpan={6} className="table-empty-cell">
                      Aucun résultat trouvé pour ce filtre.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          <div className="pagination-bar">
            <div className="pagination-info">
              Page {pagination.current_page} sur {pagination.last_page}
            </div>

            <div className="pagination-actions">
              <button
                type="button"
                className="secondary-button"
                onClick={() => canPrev && fetchCandidates(page - 1)}
                disabled={!canPrev || loading}
              >
                Précédent
              </button>

              <button
                type="button"
                className="secondary-button"
                onClick={() => canNext && fetchCandidates(page + 1)}
                disabled={!canNext || loading}
              >
                Suivant
              </button>
            </div>
          </div>
        </div>

        <aside className="entity-card candidate-detail-card candidate-detail-full">
          <h2>Détail candidat</h2>

          {!selectedCandidate && (
            <p className="empty-state">
              Sélectionnez un candidat pour afficher ses informations détaillées.
            </p>
          )}

          {selectedCandidate && (
            <div className="candidate-detail-content">
              <div className="detail-grid">
                <div className="detail-item">
                  <span className="result-label">ID</span>
                  <p>{selectedCandidate.id}</p>
                </div>

                <div className="detail-item">
                  <span className="result-label">Nom complet</span>
                  <p>{selectedCandidate.full_name}</p>
                </div>

                <div className="detail-item">
                  <span className="result-label">Email</span>
                  <p>{selectedCandidate.email || "-"}</p>
                </div>

                <div className="detail-item">
                  <span className="result-label">Téléphone</span>
                  <p>{selectedCandidate.phone || "-"}</p>
                </div>
              </div>

              <div className="detail-item">
                <span className="result-label">Résumé</span>
                <p>{selectedCandidate.summary || "-"}</p>
              </div>

              <div className="detail-item">
                <span className="result-label">CV</span>
                {selectedCandidate.cv_url ? (
                  <a
                    href={selectedCandidate.cv_url}
                    target="_blank"
                    rel="noreferrer"
                    className="table-link"
                  >
                    Ouvrir le CV
                  </a>
                ) : (
                  <p>-</p>
                )}
              </div>

              <div className="detail-item">
                <span className="result-label">Texte extrait du CV</span>
                <p className="detail-text">
                  {selectedCandidate.extracted_text || "Aucun texte extrait disponible."}
                </p>
              </div>
            </div>
          )}
        </aside>

        {candidateToDelete && (
          <div className="modal-overlay" onClick={closeDeleteModal}>
            <div
              className="modal-card"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="modal-icon">!</div>
              <h3>Confirmer la suppression</h3>
              <p>
                Voulez-vous vraiment supprimer le candidat{" "}
                <strong>{candidateToDelete.full_name}</strong> ?
                Cette action est irréversible.
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
                  onClick={confirmDelete}
                  disabled={deletingId === candidateToDelete.id}
                >
                  {deletingId === candidateToDelete.id
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