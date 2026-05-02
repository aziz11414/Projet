from pathlib import Path  # Gestion des chemins de fichiers
import joblib  # Charger le modèle et le scaler
import pandas as pd  # Manipulation des données
from fastapi import FastAPI, HTTPException  # API + gestion des erreurs


# 📁 Définition des chemins
BASE_DIR = Path(__file__).resolve().parent  # Dossier du fichier actuel
MODEL_DIR = BASE_DIR / "model"  # Dossier contenant les fichiers sauvegardés
MODEL_PATH = MODEL_DIR / "model.pkl"  # Modèle entraîné
SCALER_PATH = MODEL_DIR / "scaler.pkl"  # Scaler entraîné


# 🚀 Création de l'application FastAPI
app = FastAPI()

# 📥 Charger le modèle et le scaler au démarrage
model = joblib.load(MODEL_PATH)
scaler = joblib.load(SCALER_PATH)


# 🏠 Route de test
@app.get("/")
def home():
    return {"message": "Student API working"}  # Vérifier que l'API marche


# 🔮 Route de prédiction
@app.post("/predict")
def predict(data: dict):
    try:
        # Transformer les données reçues (JSON) en DataFrame
        df = pd.DataFrame([data])

        # Normaliser les données avec le scaler
        scaled_features = scaler.transform(df)

        # Faire la prédiction (0 ou 1)
        prediction = int(model.predict(scaled_features)[0])

        # Probabilité de réussite (classe 1)
        probability = float(model.predict_proba(scaled_features)[0][1])

        # Retourner le résultat
        return {
            "prediction": prediction,
            "probability_success": probability,
        }

    except Exception as exc:
        # En cas d'erreur, retourner une erreur HTTP
        raise HTTPException(status_code=500, detail=str(exc))