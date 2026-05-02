 1. Vue d’ensemble du projet
1.1 Architecture générale

Le projet EduPredict est une application web de prédiction de réussite scolaire composée de deux composants principaux :

Backend API (Python FastAPI) : API de prédiction basée sur un modèle de machine learning
Frontend Web (Laravel + Blade) : Interface web avec authentification et tableau de bord
1.2 Flux de communication
Utilisateur → Formulaire → Laravel → HTTP POST → FastAPI → Prédiction → Réponse JSON → Affichage
 2. Installation et configuration
2.1 Prérequis système
Python 3.9+ : pour l’API FastAPI
PHP 8.1+ : pour Laravel
Composer : gestionnaire de paquets PHP
Node.js : pour les assets frontend
MySQL / SQLite : base de données
2.2 Étape 1 : Installation de Laravel
2.2.1 Installer les dépendances PHP
composer install
npm install
2.2.2 Configuration du fichier .env
APP_NAME=EduPredict
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=edupredict
# DB_USERNAME=root
# DB_PASSWORD=

PREDICT_API_URL=http://127.0.0.1:8001/predict
PREDICT_API_START_COMMAND=python -m uvicorn api:app --host 127.0.0.1 --port 8001
2.2.3 Configuration de la base de données
php artisan migrate
php artisan db:seed
2.2.4 Compiler les assets
npm run dev
# ou
npm run build
2.3 Étape 2 : Installation de l’API Python
2.3.1 Accéder au dossier API
cd student_project/student_project
2.3.2 Installer les dépendances Python
pip install --upgrade pip
pip install fastapi uvicorn joblib pandas scikit-learn
2.3.3 Vérifier les fichiers du modèle
ls model/

Fichiers attendus :

model.pkl
scaler.pkl
 3. Démarrage et exécution
3.1 Démarrage de l’API FastAPI
python -m uvicorn api:app --host 127.0.0.1 --port 8001 --reload
Résultat attendu :
Uvicorn running on http://127.0.0.1:8001
Application startup complete
3.2 Vérifier l’API

Ouvrir :

 http://127.0.0.1:8001

Réponse :

{
  "message": "Student API working"
}
3.3 Démarrage Laravel
php artisan serve

Accès :

 http://127.0.0.1:8000

 4. Liaison API – Laravel
4.1 Configuration API
'predict_api' => [
    'url' => env('PREDICT_API_URL', 'http://127.0.0.1:8001/predict'),
    'start_command' => env('PREDICT_API_START_COMMAND'),
],
4.2 Flow de la requête utilisateur
Étape 1 : formulaire
<form method="POST" action="{{ route('student.predict') }}">
@csrf
<input name="name" type="text" required>
<input name="age" type="number" min="15" max="25" required>
<input name="study_time" type="number" min="1" max="4" required>
<input name="failures" type="number" min="0" max="3" required>
<input name="absence" type="number" min="0" required>
<input name="filiere" type="text">
<button type="submit">Prédire</button>
</form>
Étape 2 : route Laravel
Route::post('/student/predict', [StudentController::class, 'predict'])
    ->name('student.predict');
Étape 3 : validation
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'age' => ['required', 'integer', 'min:15', 'max:25'],
    'study_time' => ['required', 'integer', 'min:1', 'max:4'],
    'failures' => ['required', 'integer', 'min:0', 'max:3'],
    'absence' => ['required', 'integer', 'min:0'],
    'filiere' => ['nullable', 'string', 'max:255'],
]);
Étape 4 : appel API
$response = Http::timeout(10)
    ->acceptJson()
    ->post(config('services.predict_api.url'), [
        'age' => $validated['age'],
        'studytime' => $validated['study_time'],
        'failures' => $validated['failures'],
        'absences' => $validated['absence'],
    ])
    ->throw()
    ->json();
Étape 5 : API FastAPI
@app.post("/predict")
def predict(data: dict):
    df = pd.DataFrame([data])
    scaled = scaler.transform(df)
    prediction = int(model.predict(scaled)[0])
    probability = float(model.predict_proba(scaled)[0][1])

    return {
        "prediction": prediction,
        "probability_success": probability
    }
Étape 6 : traitement Laravel
$result = $prediction === 1 ? 'Réussi' : 'Échoué';
Étape 7 : stockage
Student::create([
    'name' => $validated['name'],
    'age' => $validated['age'],
    'study_time' => $validated['study_time'],
    'failures' => $validated['failures'],
    'absence' => $validated['absence'],
    'filiere' => $validated['filiere'] ?? '',
    'probability' => $probability,
    'result' => $result,
]);
 5. Gestion des erreurs
API inaccessible
vérifier FastAPI
vérifier URL .env
vérifier port 8001
Erreur API
vérifier logs Python
vérifier données envoyées
vérifier modèle ML
 6. Commandes utiles
Laravel
php artisan serve
php artisan migrate
php artisan db:seed
npm run dev
Python
pip install -r requirements.txt
python -m uvicorn api:app --reload
