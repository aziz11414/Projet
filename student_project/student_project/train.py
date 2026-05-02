from pathlib import Path  # Pour gérer les chemins de fichiers
import joblib  # Pour sauvegarder le modèle et le scaler
import pandas as pd  # Manipulation des données
from sklearn.linear_model import LogisticRegression  # Modèle ML
from sklearn.metrics import accuracy_score, classification_report  # Évaluation
from sklearn.model_selection import train_test_split  # Séparer train/test
from sklearn.preprocessing import StandardScaler  # Normalisation

# 📁 Définition des chemins
BASE_DIR = Path(__file__).resolve().parent  # Dossier du fichier actuel
DATA_PATH = BASE_DIR / "data" / "student.csv"  # Chemin du dataset
MODEL_DIR = BASE_DIR / "model"  # Dossier pour sauvegarder le modèle
MODEL_PATH = MODEL_DIR / "model.pkl"  # Fichier modèle
SCALER_PATH = MODEL_DIR / "scaler.pkl"  # Fichier scaler
COLUMNS_PATH = MODEL_DIR / "columns.pkl"  # Colonnes utilisées

# 🎯 Features utilisées pour le modèle
FEATURES = ["age", "studytime", "failures", "absences"]


# 📥 Charger les données
def load_training_data():
    df = pd.read_csv(DATA_PATH)  # Lire le fichier CSV

    # Garder seulement les colonnes utiles
    df = df[FEATURES + ["G3"]].copy()

    # Créer la variable cible (target)
    # 1 = réussite (>=10), 0 = échec (<10)
    df["target"] = (df["G3"] >= 10).astype(int)

    return df


# 🧠 Entraîner le modèle
def train_model():
    df = load_training_data()

    # Séparer les variables explicatives (X) et la cible (y)
    x = df[FEATURES]
    y = df["target"]

    # Diviser en données d'entraînement et de test
    x_train, x_test, y_train, y_test = train_test_split(
        x,
        y,
        test_size=0.2,  # 20% pour le test
        random_state=42,  # reproductibilité
        stratify=y,  # garder la même proportion des classes
    )

    # 🔄 Normalisation des données
    scaler = StandardScaler()
    x_train_scaled = scaler.fit_transform(x_train)  # apprendre + transformer
    x_test_scaled = scaler.transform(x_test)  # transformer seulement

    # 🤖 Création du modèle
    model = LogisticRegression(max_iter=1000, random_state=42)

    # Entraînement
    model.fit(x_train_scaled, y_train)

    # 🔍 Prédiction
    predictions = model.predict(x_test_scaled)

    #  Évaluation
    accuracy = accuracy_score(y_test, predictions)

    # 📁 Créer le dossier model s’il n’existe pas
    MODEL_DIR.mkdir(exist_ok=True)

    # 💾 Sauvegarde du modèle et des outils
    joblib.dump(model, MODEL_PATH)
    joblib.dump(scaler, SCALER_PATH)
    joblib.dump(FEATURES, COLUMNS_PATH)

    # 📢 Affichage des résultats
    print(f"Accuracy: {accuracy:.4f}")
    print(classification_report(y_test, predictions))
    print(f"Modele sauvegarde: {MODEL_PATH}")
    print(f"Scaler sauvegarde: {SCALER_PATH}")


# ▶️ Point d’entrée du programme
if __name__ == "__main__":
    train_model()