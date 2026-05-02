# Documentation Complete du Projet EduPredict

## 1. Presentation generale

EduPredict est une application web de prediction de reussite scolaire.
Le projet combine :

- un frontend web Laravel base sur Blade et Bootstrap
- un backend web Laravel pour la logique metier, l'authentification et la persistence MySQL
- un backend Python FastAPI pour la prediction machine learning
- une interface Streamlit optionnelle pour tester le modele Python en dehors de Laravel

L'objectif principal est de permettre a un etudiant de saisir des informations academiques, d'obtenir une prediction de reussite, puis d'enregistrer cette prediction dans l'application. L'administrateur dispose ensuite d'un tableau de bord global avec statistiques et historique.

## 2. Objectifs fonctionnels

Le projet couvre les besoins suivants :

- inscription d'un utilisateur etudiant
- connexion avec gestion de session
- separation des roles `student` et `admin`
- saisie d'un formulaire de prediction
- appel d'une API Python locale pour calculer la prediction
- enregistrement du resultat dans la base de donnees
- consultation de l'historique cote etudiant
- consultation de statistiques globales cote administrateur

## 3. Architecture globale

Le projet est compose de deux repertoires principaux :

- `edupredict/` : application Laravel
- `student_project/student_project/` : partie Python machine learning

Architecture logique :

1. Le navigateur ouvre l'application Laravel.
2. Laravel affiche les pages HTML du frontend.
3. L'etudiant soumet le formulaire de prediction.
4. Laravel appelle l'API Python `POST /predict`.
5. FastAPI charge le modele et retourne un resultat JSON.
6. Laravel enregistre le resultat dans MySQL.
7. Les dashboards etudiant et admin lisent les donnees depuis la base.

## 4. Technologies utilisees

### Cote Laravel

- PHP 8.2+
- Laravel 12
- Blade
- Bootstrap
- Eloquent ORM
- MySQL
- Vite
- Node.js / npm

### Cote Python

- Python 3.10+
- pandas
- scikit-learn
- joblib
- FastAPI
- uvicorn
- Streamlit

## 5. Organisation des dossiers

### Application Laravel

- `app/Http/Controllers/` : logique des pages et actions
- `app/Http/Middleware/` : controle des acces par role
- `app/Models/` : modeles Eloquent
- `config/` : configuration Laravel
- `database/migrations/` : structure de la base
- `database/seeders/` : creation des donnees initiales
- `resources/views/` : templates Blade
- `routes/web.php` : routes web

### Projet Python

- `api.py` : API FastAPI de prediction
- `app.py` : interface Streamlit de test
- `train.py` : entrainement du modele
- `data/student.csv` : dataset source
- `model/model.pkl` : modele entraine
- `model/scaler.pkl` : scaler entraine
- `model/columns.pkl` : liste des features

## 6. Frontend, backend et separation des responsabilites

### Frontend principal

Le frontend principal est dans Laravel via les vues Blade :

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/student/dashboard.blade.php`
- `resources/views/admin/dashboard.blade.php`

Ce frontend affiche les formulaires, les tableaux, les cartes de statistiques et les messages d'erreur/succes.

### Backend principal

Le backend principal est egalement Laravel :

- `AuthController.php` gere login, register et logout
- `StudentController.php` gere l'affichage du dashboard etudiant et l'appel a l'API Python
- `AdminController.php` gere les statistiques globales
- `RoleMiddleware.php` protege l'acces selon le role

### Backend machine learning

Le backend de prediction est en Python :

- `train.py` entraine le modele
- `api.py` expose le modele sous forme d'API HTTP

### Frontend secondaire de test

`app.py` fournit une interface Streamlit pour tester directement le modele Python sans passer par Laravel. Cette interface est utile en phase de developpement, mais elle n'est pas obligatoire pour le fonctionnement final de l'application web.

## 7. Description detaillee du frontend Laravel

### Authentification

Le frontend d'authentification contient :

- une page de connexion
- une page d'inscription

Lorsqu'un utilisateur se connecte :

- Laravel verifie l'email et le mot de passe
- la session est regeneree
- l'utilisateur est redirige vers le dashboard correspondant a son role

### Dashboard etudiant

Le dashboard etudiant permet :

- de voir son historique de predictions
- de visualiser son taux de reussite personnel
- de soumettre une nouvelle prediction
- de consulter la probabilite de reussite renvoyee par l'API

### Dashboard admin

Le dashboard admin permet :

- de consulter toutes les predictions
- d'afficher des statistiques globales
- de voir la repartition des resultats
- de suivre la moyenne des probabilites

## 8. Description detaillee du backend Laravel

### Routes

Les routes sont definies dans `routes/web.php`.

Principales routes :

- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /logout`
- `GET /student/dashboard`
- `POST /student/predict`
- `GET /admin/dashboard`

### AuthController

Responsabilites :

- afficher les formulaires de connexion et d'inscription
- authentifier l'utilisateur
- creer un nouvel etudiant avec le role `student`
- fermer la session

### StudentController

Responsabilites :

- recuperer les predictions de l'utilisateur courant
- calculer les statistiques personnelles
- valider le formulaire de prediction
- appeler l'API Python
- capturer les erreurs de connexion ou de reponse API
- enregistrer le resultat dans la table `students`

Les champs envoyes a l'API sont :

- `age`
- `studytime`
- `failures`
- `absences`

### AdminController

Responsabilites :

- afficher toutes les predictions
- calculer le nombre total de predictions
- calculer le taux de reussite global
- produire les donnees necessaires aux graphiques et indicateurs

### RoleMiddleware

Ce middleware controle que l'utilisateur connecte possede bien le role attendu. En cas de role incorrect, Laravel retourne une erreur HTTP 403.

## 9. Description detaillee du backend Python

### train.py

`train.py` realise les taches suivantes :

1. lecture du fichier CSV
2. selection des variables utiles
3. creation de la variable cible `target`
4. separation train/test
5. normalisation avec `StandardScaler`
6. entrainement d'une `LogisticRegression`
7. evaluation du modele
8. sauvegarde du modele et du scaler

### api.py

`api.py` expose deux routes :

- `GET /`
- `POST /predict`

La route `POST /predict` :

1. recoit un JSON
2. transforme ce JSON en DataFrame
3. applique le scaler
4. appelle le modele
5. retourne :
   - `prediction`
   - `probability_success`

### app.py

`app.py` est une interface Streamlit simple qui :

- charge le modele et le scaler
- affiche des champs de saisie
- effectue une prediction locale
- montre un resultat lisible pour l'utilisateur

## 10. Modele de donnees

### Table users

Colonnes importantes :

- `id`
- `name`
- `email`
- `role`
- `password`
- `remember_token`
- `created_at`
- `updated_at`

Le role est ajoute par migration et vaut par defaut `student`.

### Table students

Colonnes importantes :

- `id`
- `user_id`
- `name`
- `age`
- `study_time`
- `failures`
- `absence`
- `filiere`
- `probability`
- `result`
- `created_at`
- `updated_at`

Cette table enregistre l'ensemble des predictions effectuees depuis l'application.

## 11. Jeu de donnees et logique ML

Le dataset source est `data/student.csv`.

Les features retenues dans le code sont :

- `age`
- `studytime`
- `failures`
- `absences`

La cible est construite a partir de `G3` :

- `1` si `G3 >= 10`
- `0` si `G3 < 10`

Le modele utilise est une regression logistique.

## 12. Flux complet d'une prediction

1. L'etudiant se connecte.
2. Il ouvre son dashboard.
3. Il remplit le formulaire.
4. Laravel valide les champs.
5. Laravel envoie une requete HTTP a `PREDICT_API_URL`.
6. FastAPI calcule la prediction.
7. FastAPI retourne un JSON.
8. Laravel convertit le JSON en resultat metier :
   - `Reussi`
   - `Echoue`
9. Laravel stocke la prediction en base.
10. Le dashboard se recharge avec le nouveau resultat.

## 13. Variables d'environnement

### Fichier `.env`

Le fichier `.env` contient les vraies valeurs utilisees localement.

Exemples importants :

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `PREDICT_API_URL`

### Fichier `.env.example`

Le fichier `.env.example` est un modele partageable. Il sert de point de depart pour generer un `.env` fonctionnel.

## 14. Installation complete sous Windows

### Prerequis

Installer au minimum :

- PHP 8.2 ou plus
- Composer
- Node.js et npm
- Python 3.10 ou plus
- MySQL

### Installation de Laravel

Depuis le dossier `edupredict` :

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan config:clear
```

### Configuration de la base de donnees

Creer la base :

```sql
CREATE DATABASE classroom;
```

Puis lancer les migrations et le seeder :

```powershell
php artisan migrate
php artisan db:seed
```

### Installation des dependances Python

Depuis `student_project/student_project` :

```powershell
python -m pip install pandas joblib scikit-learn streamlit fastapi uvicorn
```

### Entrainement du modele

```powershell
python train.py
```

## 15. Commandes de lancement

### Lancer Laravel

Terminal 1 :

```powershell
cd c:\Users\user\Desktop\PROJETTTTTTTTT\wafamay\classrrom\edupredict
php artisan serve
```

### Lancer Vite

Terminal 2 :

```powershell
cd c:\Users\user\Desktop\PROJETTTTTTTTT\wafamay\classrrom\edupredict
npm run dev
```

### Lancer l'API FastAPI

Terminal 3 :

```powershell
cd c:\Users\user\Desktop\PROJETTTTTTTTT\wafamay\classrrom\student_project\student_project
python -m uvicorn api:app --host 127.0.0.1 --port 8001
```

### Lancer Streamlit

Terminal 4 optionnel :

```powershell
cd c:\Users\user\Desktop\PROJETTTTTTTTT\wafamay\classrrom\student_project\student_project
streamlit run app.py
```

## 16. Comptes et acces

Le seeder cree un administrateur :

- email : `admin@edupredict.com`
- mot de passe : `Admin12345`

Les comptes etudiants sont crees via le formulaire d'inscription.

## 17. Format de requete et de reponse API

### Requete

URL :

`POST http://127.0.0.1:8001/predict`

Exemple JSON :

```json
{
  "age": 18,
  "studytime": 2,
  "failures": 0,
  "absences": 3
}
```

### Reponse

```json
{
  "prediction": 1,
  "probability_success": 0.87
}
```

Interpretation :

- `prediction = 1` signifie `Reussi`
- `prediction = 0` signifie `Echoue`

## 18. Gestion des erreurs

### Cote Laravel

Si l'API Python est indisponible :

- Laravel intercepte l'erreur de connexion
- le formulaire est recharge
- un message d'erreur explique quelle commande lancer

Si l'API renvoie une erreur HTTP :

- Laravel affiche un message indiquant que le service a repondu avec une erreur

### Cote Python

Si une erreur survient dans `api.py` :

- FastAPI renvoie une `HTTPException` avec un status 500

## 19. Tests et verification

### Verification Laravel

```powershell
php artisan test
php -l app\Http\Controllers\StudentController.php
php -l config\services.php
```

### Verification API Python

Route de sante :

```powershell
python -c "import urllib.request; print(urllib.request.urlopen('http://127.0.0.1:8001/').read().decode())"
```

Test de prediction :

```powershell
python -c "import urllib.request, json; req=urllib.request.Request('http://127.0.0.1:8001/predict', data=json.dumps({'age':18,'studytime':2,'failures':0,'absences':3}).encode(), headers={'Content-Type':'application/json'}); print(urllib.request.urlopen(req).read().decode())"
```

## 20. Points forts du projet

- architecture separee entre application web et service ML
- gestion des roles admin/etudiant
- historique des predictions
- dashboard de suivi
- integration simple entre Laravel et FastAPI
- possibilite de tester le modele avec Streamlit

## 21. Limites actuelles

- pas de fichier `requirements.txt` Python present
- pas de conteneur Docker pour standardiser le lancement
- front Laravel et front Streamlit coexistent, ce qui peut creer de la confusion si l'architecture n'est pas documentee
- la robustesse du service Python depend du bon lancement manuel de `uvicorn`

## 22. Ameliorations recommandees

- ajouter `requirements.txt`
- ajouter un `README` personnalise au projet
- ajouter des tests automatiques pour l'API Python
- centraliser la configuration de lancement
- conteneuriser Laravel, MySQL et FastAPI avec Docker Compose
- ajouter une vraie page de monitoring ou un healthcheck plus complet

## 23. Resume final

EduPredict est une application hybride web + machine learning.

Le frontend principal est fourni par Laravel, le backend principal est Laravel, et la prediction intelligente est delivree par un microservice Python FastAPI. Streamlit joue le role d'outil de demonstration et de test rapide du modele.

Le projet illustre une architecture tres courante en pratique :

- application web classique pour l'experience utilisateur
- service ML separe pour la prediction
- base de donnees relationnelle pour l'historique et les statistiques

## 24. Annexes rapides

### Commandes essentielles

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
npm run dev
python -m pip install pandas joblib scikit-learn streamlit fastapi uvicorn
python train.py
python -m uvicorn api:app --host 127.0.0.1 --port 8001
```

### Repertoires principaux

- `edupredict/`
- `student_project/student_project/`
- `docs/`

### Sortie attendue

L'utilisateur final navigue dans Laravel, tandis que Laravel consomme silencieusement l'API Python pour produire la prediction et l'enregistrer.
