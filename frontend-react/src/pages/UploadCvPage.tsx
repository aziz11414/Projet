import { useState } from "react";
import type { ChangeEvent, FormEvent } from "react";
import api from "../api/axios";

type UploadResult = {
  skills?: string[];
  summary?: string;
};

export default function UploadCvPage() {
  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<UploadResult | null>(null);
  const [error, setError] = useState("");

  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] ?? null;
    setFile(selectedFile);
    setError("");
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (!file) {
      setError("Veuillez sélectionner un fichier CV.");
      return;
    }

    try {
      setLoading(true);
      setError("");
      setResult(null);

      const formData = new FormData();
      formData.append("cv", file);

      const response = await api.post("/ai/upload-cv", formData);

      setResult(response.data);
    } catch (err: any) {
      console.error(err);
      setError(
        err?.response?.data?.message ||
          "Une erreur est survenue lors de l'upload du CV."
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <form onSubmit={handleSubmit}>
        <input
          type="file"
          accept=".txt,.pdf,.doc,.docx"
          onChange={handleFileChange}
        />
        <button type="submit" disabled={loading}>
          {loading ? "Analyse en cours..." : "Uploader et analyser"}
        </button>
      </form>

      {file && <p>{file.name}</p>}
      {error && <p>{error}</p>}

      {result && (
        <div>
          <h2>Résultat</h2>
          <p>{result.summary}</p>
          <ul>
            {result.skills?.map((skill) => (
              <li key={skill}>{skill}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}