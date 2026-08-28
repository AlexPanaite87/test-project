# YouTube AI Video Verifier

Acest proiect este o aplicație backend robustă, construită în Laravel, concepută pentru a căuta automat videoclipuri pe YouTube (trailere, gameplay) pe baza metadatelor produselor și pentru a valida rezultatele folosind un agent AI (Google Gemini).

## Funcționalități
- **Căutare automată pe YouTube:** Folosește YouTube Data API v3 pentru a prelua videoclipuri candidate pe baza numelui și categoriei produsului.
- **Verificare AI (Gemini):** Se integrează cu modelul Gemini pentru a selecta determinist cea mai bună potrivire oficială, oferind un scor de acuratețe și o explicație text clară.
- **Procesare Asincronă:** Căutarea videoclipurilor și validarea AI sunt procesate în fundal (Background Jobs & Queues), asigurând o interfață fluidă, care nu se blochează.
- **Reziliență și Optimizare:** Implementează logică de retry pentru API-uri, sistem de cache și rate limiting pentru a preveni epuizarea cotei de interogări (quota limit).
- **UX și Transparență:** Interfața afișează clar motivele deciziilor luate de AI, lista candidaților analizați și oferă utilizatorului opțiunea de manual override.

## Cerințe preliminare
- **Docker Desktop** - Necesar pentru rularea complet izolată în containere a proiectului, fără necesitatea instalării locale a limbajului PHP, a managerului Composer sau a unui server web.

## Instalare și Configurare

1. **Se clonează repository-ul:**
```bash
git clone https://github.com/AlexPanaite87/test-project
cd test-project
```

2. **Instalarea dependențelor Composer:**
   Deoarece proiectul utilizează Laravel Sail (Docker), se pot instala dependențele inițiale folosind un container temporar:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs
```

3. **Configurarea variabilelor de mediu (.env):**
   Se copiază fișierul de exemplu:
```bash
cp .env.example .env
```
Se deschide fișierul `.env` și se adaugă cheile API (cheile proprii sau cele furnizate):
```env
YOUTUBE_API_KEY="..."
AI_API_KEY="..."
```

4. **Pornirea containerelor Docker:**
```bash
./vendor/bin/sail up -d
```

5. **Generarea cheii aplicației:**
```bash
./vendor/bin/sail artisan key:generate
```

6. **Rularea migrărilor și importul inițial de date (Seeding):**
   Această comandă va construi structura relațională a tabelelor în baza de date. Pentru popularea inițială cu produse, aveți la dispoziție două opțiuni, în funcție de preferință:

* **Opțiunea A (Default Seeder):**
  Populează baza de date folosind setul predefinit de date din cod.
```bash
./vendor/bin/sail artisan migrate --seed
```
* **Opțiunea B (Import CSV):**
  Populează baza de date citind structura și produsele direct din fișierul extern products.csv.
```bash
./vendor/bin/sail artisan migrate: --seed --class=CsvSeeder
```

## Pornirea aplicației

Pentru ca aplicația să funcționeze corect, sarcinile asincrone (apelurile API, verificarea AI) trebuie procesate. Se va porni workerul pentru coada de așteptare într-un tab nou de terminal:

```bash
./vendor/bin/sail artisan queue:work --timeout=180
```

Acum aplicația poate fi accesată în browser la adresa: **http://localhost**.

## Testare

Proiectul include teste unitare și de integrare pentru a verifica persistența în baza de date, parsarea JSON a răspunsurilor AI și funcționarea corectă a joburilor asincrone din Queue.

Pentru a rula suita de teste, se folosește comanda:
```bash
./vendor/bin/sail artisan test
```

## Arhitectură
- **App/Http/Controllers/ProductController:** Gestionează cererile HTTP, filtrarea listei, paginarea și trimiterea sarcinilor către coadă.
- **App/Models/Product & VideoCandidate:** Modelele Eloquent care definesc structura datelor și relația hasMany dintre un produs și candidații săi, și anume videourile de pe YouTube.
- **App/Services/YouTubeClient:** Extrage logica de comunicare cu YouTube Data API v3, ocupându-se de construirea query-urilor.
- **App/Services/AiVerifier:** Construiește promptul determinist, trimite datele către Gemini API și parsează răspunsul JSON pentru a extrage verdictul și scorul.
- **App/Jobs/SearchYoutubeAndVerifyJob:** Încapsulează întregul proces (căutare + verificare + salvare) într-un job asincron, garantând un timp de răspuns instantaneu în UI.
