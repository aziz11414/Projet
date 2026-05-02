from pathlib import Path  # Gestion des chemins
import joblib  # Charger modèle et scaler
import pandas as pd  # Manipulation des données
import streamlit as st  # Interface web


# 📁 Définition des chemins
BASE_DIR = Path(__file__).resolve().parent  # Dossier actuel
MODEL_DIR = BASE_DIR / "model"  # Dossier du modèle
MODEL_PATH = MODEL_DIR / "model.pkl"  # Modèle sauvegardé
SCALER_PATH = MODEL_DIR / "scaler.pkl"  # Scaler sauvegardé


# ⚡ Charger une seule fois (cache)
@st.cache_resource
def load_artifacts():
    model = joblib.load(MODEL_PATH)  # Charger modèle
    scaler = joblib.load(SCALER_PATH)  # Charger scaler
    return model, scaler


# 🖥️ Interface utilisateur
st.title("Student Performance Prediction")
st.write("Entrez les informations de l'etudiant pour estimer sa reussite.")


# 📥 Inputs utilisateur
age = st.number_input("Age", min_value=15, max_value=25, value=18)
studytime = st.slider("Study Time", min_value=1, max_value=4, value=2)
failures = st.slider("Failures", min_value=0, max_value=3, value=0)
absences = st.number_input("Absences", min_value=0, max_value=100, value=0)


# ▶️ Bouton prédiction
if st.button("Predict"):
    try:
        # Charger modèle + scaler
        model, scaler = load_artifacts()

        # Créer un DataFrame avec les données saisies
        features = pd.DataFrame(
            [
                {
                    "age": age,
                    "studytime": studytime,
                    "failures": failures,
                    "absences": absences,
                }
            ]
        )

        # 🔄 Normaliser les données
        scaled_features = scaler.transform(features)

        # 🔮 Faire la prédiction
        prediction = int(model.predict(scaled_features)[0])

        # 📊 Probabilité de réussite
        probability = float(model.predict_proba(scaled_features)[0][1])

        # 📢 Affichage du résultat
        if prediction == 1:
            st.success(f"Reussi ({probability:.2%})")
        else:
            st.error(f"Echoue ({probability:.2%})")

    except Exception as exc:
        # ❌ Gestion des erreurs
        st.error(f"Erreur de prediction: {exc}")