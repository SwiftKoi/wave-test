Aplikacja do zarządzania listą zbanowanych Pokémonów oraz pobierania informacji o Pokémonach z PokeAPI z filtrowaniem banów.

Pliki konfiguracyjne
1. Root .env (dla Dockera): MYSQL_ROOT_PASSWORD, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, UID, GID.
2. wave/.env (dla Laravela): SUPER_SECRET_KEY=123, DB_CONNECTION=mysql, DB_HOST=db, DB_PORT=3306, DB_DATABASE=wave, DB_USERNAME=wave_user, DB_PASSWORD=root, APP_URL=http://localhost:8080.

Uruchomienie od zera
1. Sklonuj repozytorium.
2. Wejdź do katalogu projektu.
3. Skopiuj konfiguracje:
```bash
cp .env.example .env

cp wave/.env.example wave/.env
```
4. Ustaw DB i SUPER_SECRET_KEY w wave/.env (jak wyżej).
5. Zbuduj i uruchom kontenery:
```bash
docker compose up -d --build
```
6. Zainstaluj zależności PHP:
```bash
docker compose exec web composer install
```
7. Wygeneruj klucz aplikacji:
```bash
docker compose exec web php artisan key:generate
```
8. Wykonaj migracje:
```bash
docker compose exec web php artisan migrate
```
9. Testy
```bash
docker compose exec web php artisan test
```

Kontenery i porty
web: wave-web, http://localhost:8080

phpmyadmin: wave-phpmyadmin, http://localhost:8081

db: wave-db, host: db, port: 3306

Sprawdzenie działania
1. Status kontenerów: docker compose ps
2. API docs (Scribe): http://localhost:8080/docs

Endpointy
1. GET /api/banned - lista zbanowanych Pokémonów.
2. POST /api/banned - dodaje Pokémona do banów (po nazwie lub id, przez PokeAPI).
3. DELETE /api/banned/{bannedPokemon} - usuwa wpis bana po ID rekordu w lokalnej bazie.
4. POST /api/info - zwraca dane Pokémonów z PokeAPI, pomija zbanowane.
