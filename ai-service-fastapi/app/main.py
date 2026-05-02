import os
import re
from typing import List

from dotenv import load_dotenv
from fastapi import FastAPI
from openai import OpenAI
from pydantic import BaseModel, Field

load_dotenv()

app = FastAPI(title="TalentSync AI Service")

GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
GROQ_MODEL = os.getenv("GROQ_MODEL", "llama-3.3-70b-versatile")

groq_client = None
if GROQ_API_KEY:
    groq_client = OpenAI(
        api_key=GROQ_API_KEY,
        base_url="https://api.groq.com/openai/v1",
    )

KNOWN_SKILLS = [
    "React",
    "Laravel",
    "Python",
    "JavaScript",
    "TypeScript",
    "FastAPI",
    "Docker",
    "PostgreSQL",
    "MySQL",
    "REST API",
    "Angular",
    "Spring Boot",
    "Flutter",
    "PHP",
    "SQL",
    "Node.js",
    "Vue.js",
    "Next.js",
    "MongoDB",
    "Git",
    "Linux",
    "Tailwind CSS",
    "Bootstrap",
    "Firebase",
    "C#",
    "Java",
    "Kotlin",
    "Swift",
]

SOFT_SKILLS_MAP = {
    "communication": "Communication",
    "leadership": "Leadership",
    "teamwork": "Travail en équipe",
    "problem solving": "Résolution de problèmes",
    "adaptability": "Adaptabilité",
    "organisation": "Organisation",
    "autonomy": "Autonomie",
    "creativity": "Créativité",
}

LANGUAGE_KEYWORDS = {
    "Français": ["français", "french"],
    "Anglais": ["anglais", "english"],
    "Arabe": ["arabe", "arabic"],
    "Allemand": ["allemand", "german"],
    "Espagnol": ["espagnol", "spanish"],
}

ROLE_HINTS = {
    "Développeur Frontend": [
        "react", "javascript", "typescript", "vue", "angular", "next.js", "tailwind css"
    ],
    "Développeur Backend": [
        "laravel", "fastapi", "python", "php", "java", "spring boot", "sql", "mysql", "postgresql"
    ],
    "Développeur Full Stack": [
        "react", "laravel", "javascript", "php", "sql", "api"
    ],
    "Développeur Mobile": [
        "flutter", "swift", "kotlin", "firebase"
    ],
    "Ingénieur DevOps": [
        "docker", "linux", "git"
    ],
    "Développeur Data / API": [
        "python", "fastapi", "sql", "postgresql", "mongodb"
    ],
}


class CVRequest(BaseModel):
    text: str


class MatchRequest(BaseModel):
    candidate_text: str
    job_description: str
    required_skills: List[str] = Field(default_factory=list)


def normalize_text(text: str) -> str:
    return re.sub(r"\s+", " ", text.lower()).strip()


def clean_cv_text(text: str) -> str:
    cleaned = re.sub(r"\s+", " ", text)
    cleaned = re.sub(r"([A-Za-zÀ-ÿ])\1{3,}", r"\1", cleaned)
    cleaned = cleaned.replace("ProfilProfil", "Profil")
    cleaned = cleaned.replace("FormationsFormations", "Formations")
    cleaned = cleaned.replace("CompétencesCompétences", "Compétences")
    cleaned = cleaned.replace("ExperienceExperience", "Experience")
    return cleaned.strip()


def unique_keep_order(items: List[str]) -> List[str]:
    return list(dict.fromkeys(items))


def extract_skills(text: str) -> List[str]:
    normalized = normalize_text(text)
    found: List[str] = []

    patterns = {
        "React": [r"\breact\b", r"\breactjs\b", r"\breact\.js\b"],
        "Laravel": [r"\blaravel\b"],
        "Python": [r"\bpython\b"],
        "JavaScript": [r"\bjavascript\b"],
        "TypeScript": [r"\btypescript\b"],
        "FastAPI": [r"\bfastapi\b"],
        "Docker": [r"\bdocker\b"],
        "PostgreSQL": [r"\bpostgresql\b", r"\bpostgres\b"],
        "MySQL": [r"\bmysql\b"],
        "REST API": [r"\brest api\b", r"\brestful\b", r"\bapi rest\b"],
        "Angular": [r"\bangular\b"],
        "Spring Boot": [r"\bspring boot\b", r"\bspringboot\b"],
        "Flutter": [r"\bflutter\b"],
        "PHP": [r"\bphp\b"],
        "SQL": [r"\bsql\b"],
        "Node.js": [r"\bnode\.js\b", r"\bnodejs\b", r"\bnode\b"],
        "Vue.js": [r"\bvue\.js\b", r"\bvuejs\b", r"\bvue\b"],
        "Next.js": [r"\bnext\.js\b", r"\bnextjs\b"],
        "MongoDB": [r"\bmongodb\b", r"\bmongo\b"],
        "Git": [r"\bgit\b", r"\bgithub\b", r"\bgitlab\b"],
        "Linux": [r"\blinux\b"],
        "Tailwind CSS": [r"\btailwind\b", r"\btailwind css\b"],
        "Bootstrap": [r"\bbootstrap\b"],
        "Firebase": [r"\bfirebase\b"],
        "C#": [r"\bc#\b", r"\bcsharp\b"],
        "Java": [r"\bjava\b"],
        "Kotlin": [r"\bkotlin\b"],
        "Swift": [r"\bswift\b"],
    }

    for skill in KNOWN_SKILLS:
        skill_patterns = patterns.get(skill, [rf"\b{re.escape(skill.lower())}\b"])
        if any(re.search(pattern, normalized) for pattern in skill_patterns):
            found.append(skill)

    return unique_keep_order(found)


def extract_soft_skills(text: str) -> List[str]:
    normalized = normalize_text(text)
    found: List[str] = []

    for keyword, label in SOFT_SKILLS_MAP.items():
        if keyword in normalized:
            found.append(label)

    return unique_keep_order(found)


def detect_languages(text: str) -> List[str]:
    normalized = normalize_text(text)
    found: List[str] = []

    for language, keywords in LANGUAGE_KEYWORDS.items():
        if any(keyword in normalized for keyword in keywords):
            found.append(language)

    return unique_keep_order(found)


def estimate_seniority_fallback(normalized: str) -> str:
    if any(word in normalized for word in ["senior", "lead", "principal", "architect", "tech lead"]):
        return "Senior"
    if any(word in normalized for word in ["junior", "intern", "stagiaire", "trainee", "débutant"]):
        return "Junior"
    return "Intermédiaire"


def estimate_years_experience(text: str, allow_fallback: bool = True) -> int:
    normalized = normalize_text(text)

    patterns = [
        r"(\d+)\+?\s+years",
        r"(\d+)\+?\s+ans",
        r"experience of (\d+)",
        r"(\d+)\+?\s+years of experience",
        r"(\d+)\+?\s+ans d[’']expérience",
        r"(\d+)\s+an[s]?\s+exp",
    ]

    candidates: List[int] = []
    for pattern in patterns:
        matches = re.findall(pattern, normalized)
        for match in matches:
            try:
                value = int(match)
                if 0 <= value <= 40:
                    candidates.append(value)
            except ValueError:
                pass

    if candidates:
        return max(candidates)

    if not allow_fallback:
        return 0

    seniority = estimate_seniority_fallback(normalized)
    if seniority == "Senior":
        return 5
    if seniority == "Junior":
        return 1
    return 3


def estimate_seniority(text: str) -> str:
    normalized = normalize_text(text)

    if any(word in normalized for word in ["senior", "lead", "principal", "architect", "tech lead"]):
        return "Senior"
    if any(word in normalized for word in ["junior", "intern", "stagiaire", "trainee", "débutant"]):
        return "Junior"

    years = estimate_years_experience(text, allow_fallback=False)
    if years >= 5:
        return "Senior"
    if years <= 1 and years > 0:
        return "Junior"

    return "Intermédiaire"


def recommend_roles(skills: List[str], text: str) -> List[str]:
    normalized = normalize_text(text)
    recommendations: List[str] = []
    skill_set = {skill.lower() for skill in skills}

    for role, hints in ROLE_HINTS.items():
        score = 0
        for hint in hints:
            if hint.lower() in normalized or hint.lower() in skill_set:
                score += 1
        if score >= 2:
            recommendations.append(role)

    if not recommendations:
        if {"react", "laravel"} <= skill_set or {"javascript", "php"} <= skill_set:
            recommendations.append("Développeur Full Stack")
        elif "react" in skill_set or "angular" in skill_set or "vue.js" in skill_set:
            recommendations.append("Développeur Frontend")
        elif "laravel" in skill_set or "fastapi" in skill_set or "python" in skill_set:
            recommendations.append("Développeur Backend")
        elif "flutter" in skill_set:
            recommendations.append("Développeur Mobile")

    return unique_keep_order(recommendations)


def extract_relevant_excerpt(text: str, max_length: int = 140) -> str:
    cleaned = clean_cv_text(text)

    sections = [
        "profil", "à propos", "about", "resume", "summary",
        "expérience", "experience", "compétences", "skills"
    ]

    lower_cleaned = cleaned.lower()
    for section in sections:
        index = lower_cleaned.find(section)
        if index != -1:
            return cleaned[index:index + max_length].strip()

    return cleaned[:max_length].strip()


def build_local_summary(
    text: str,
    skills: List[str],
    years: int,
    seniority: str,
    roles: List[str],
    languages: List[str],
) -> str:
    skill_part = ", ".join(skills[:5]) if skills else "diverses compétences techniques"
    role_part = roles[0] if roles else "profil IT"
    lang_part = ", ".join(languages[:3]) if languages else None

    summary = (
        f"Candidat {seniority.lower()} avec environ {years} ans d’expérience. "
        f"Compétences principales : {skill_part}. "
        f"Profil orienté {role_part.lower()}."
    )

    if lang_part:
        summary += f" Langues détectées : {lang_part}."

    excerpt = extract_relevant_excerpt(text)
    if excerpt:
        summary += f" Extrait pertinent : {excerpt}."

    return summary.strip()


def build_groq_summary(
    text: str,
    skills: List[str],
    years: int,
    seniority: str,
    roles: List[str],
    languages: List[str],
) -> str:
    if not groq_client:
        return build_local_summary(text, skills, years, seniority, roles, languages)

    prompt = f"""
Tu es un assistant RH expert.
Rédige un résumé professionnel en français, clair, naturel et concis à partir du CV.
Le ton doit être recruteur, pas robotique.
Ne pas inventer d'informations.
Maximum 90 mots.
Pas de listes.
Pas d'emojis.

Informations extraites :
- Niveau : {seniority}
- Années d'expérience estimées : {years}
- Compétences techniques : {", ".join(skills) if skills else "Aucune"}
- Langues : {", ".join(languages) if languages else "Aucune"}
- Postes recommandés : {", ".join(roles) if roles else "Non déterminé"}

Texte CV :
{text[:2500]}
""".strip()

    try:
        response = groq_client.chat.completions.create(
            model=GROQ_MODEL,
            messages=[
                {
                    "role": "system",
                    "content": "Tu rédiges des synthèses RH professionnelles en français.",
                },
                {
                    "role": "user",
                    "content": prompt,
                },
            ],
            temperature=0.3,
            max_tokens=180,
        )

        content = response.choices[0].message.content.strip()
        if content:
            return content

    except Exception:
        pass

    return build_local_summary(text, skills, years, seniority, roles, languages)


@app.get("/health")
def health_check():
    return {"status": "ok"}


@app.post("/parse-cv")
def parse_cv(data: CVRequest):
    text = clean_cv_text(data.text)
    skills = extract_skills(text)
    soft_skills = extract_soft_skills(text)
    languages = detect_languages(text)
    years = estimate_years_experience(text)
    seniority = estimate_seniority(text)
    roles = recommend_roles(skills, text)
    summary = build_groq_summary(text, skills, years, seniority, roles, languages)

    return {
        "summary": summary,
        "skills": skills,
        "soft_skills": soft_skills,
        "languages": languages,
        "experience_level": seniority,
        "years_of_experience_estimate": years,
        "recommended_roles": roles,
    }


@app.post("/match-candidate-job")
def match_candidate_job(data: MatchRequest):
    candidate_text = clean_cv_text(data.candidate_text)
    job_description = clean_cv_text(data.job_description)

    candidate_skills = extract_skills(candidate_text)
    candidate_skill_set = {skill.lower() for skill in candidate_skills}

    required_skills = data.required_skills or extract_skills(job_description)
    required_skills = unique_keep_order(required_skills)

    matched_skills = [
        skill for skill in required_skills if skill.lower() in candidate_skill_set
    ]
    missing_skills = [
        skill for skill in required_skills if skill.lower() not in candidate_skill_set
    ]

    skill_score = (len(matched_skills) / len(required_skills)) * 75 if required_skills else 0

    keyword_bonus = 0.0
    normalized_candidate = normalize_text(candidate_text)
    normalized_job = normalize_text(job_description)

    for word in ["senior", "lead", "manager", "api", "backend", "frontend", "full stack", "mobile"]:
        if word in normalized_candidate and word in normalized_job:
            keyword_bonus += 2.5

    years = estimate_years_experience(candidate_text)
    experience_bonus = min(years * 2, 15)

    if estimate_seniority(candidate_text) == "Senior" and "senior" in normalized_job:
        experience_bonus += 5

    missing_penalty = min(len(missing_skills) * 2, 10)

    score = round(skill_score + keyword_bonus + experience_bonus - missing_penalty, 2)
    score = max(0, min(score, 100))

    if score >= 85:
        notes = "Excellent profil pour ce poste."
    elif score >= 70:
        notes = "Très bon profil avec quelques écarts mineurs."
    elif score >= 50:
        notes = "Profil globalement compatible, mais des ajustements peuvent être nécessaires."
    else:
        notes = "Profil encore peu aligné avec ce poste."

    return {
        "score": score,
        "matched_skills": matched_skills,
        "missing_skills": missing_skills,
        "candidate_skills": candidate_skills,
        "required_skills": required_skills,
        "experience_level": estimate_seniority(candidate_text),
        "years_of_experience_estimate": years,
        "notes": notes,
    }