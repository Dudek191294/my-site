# Backup i przywracanie bazy

Ściągawka z komendami do kopiowania. Dump SQL **nie wrzucaj do GIT** — zawiera hashe haseł admina.

| Gdzie | Katalog projektu | Baza | Użytkownik Postgres |
|---|---|---|---|
| Laptop | `/Users/daniel/Desktop/Projekty/Testy/Moja_strona` | `app` | `postgres` |
| VPS | `/www/DOCKER/my-site` | `app` | `postgres` |

Komendy `docker compose …` odpalaj **z katalogu projektu**.

Pliki dumpów: `var/backups/` (ten folder jest w `.gitignore`).

---

## 1. Backup lokalnie

```bash
cd /Users/daniel/Desktop/Projekty/Testy/Moja_strona

mkdir -p var/backups

docker compose up -d db

docker compose exec -T db pg_dump -U postgres -d app --no-owner --no-acl > var/backups/app-local-$(date +%F-%H%M).sql
```

Sprawdzenie, że plik nie jest pusty:

```bash
ls -lh var/backups/
```

---

## 2. Backup na serwerze

Zaloguj się na VPS i:

```bash
cd /www/DOCKER/my-site

mkdir -p var/backups

docker compose up -d db

docker compose exec -T db pg_dump -U postgres -d app --no-owner --no-acl > var/backups/app-prod-$(date +%F-%H%M).sql

ls -lh var/backups/
```

Zrób to **zawsze przed** `doctrine:migrations:migrate` na produkcji.

---

## 3. Kopiowanie dumpa: laptop → serwer

Na **laptopie**:

```bash
cd /Users/daniel/Desktop/Projekty/Testy/Moja_strona

scp var/backups/NAZWA-PLIKU.sql root@vps-04c360e1:/www/DOCKER/my-site/var/backups/
```

Zamień `NAZWA-PLIKU.sql` na prawdziwą nazwę z `ls var/backups/`.

Jeśli `scp` pyta o hosta, to adres VPS — ten sam, którym wchodzisz przez SSH.

---

## 4. Kopiowanie dumpa: serwer → laptop

Na **laptopie**:

```bash
cd /Users/daniel/Desktop/Projekty/Testy/Moja_strona

mkdir -p var/backups

scp root@vps-04c360e1:/www/DOCKER/my-site/var/backups/NAZWA-PLIKU.sql var/backups/
```

---

## 5. Wgranie kopii lokalnie (restore)

**Kasuje obecną lokalną bazę `app` i wstawia dump.** Konta admina, projekty, stack — wszystko z pliku. Produkcji to nie rusza.

```bash
cd /Users/daniel/Desktop/Projekty/Testy/Moja_strona

docker compose up -d

docker compose exec php php bin/console doctrine:database:drop --force --if-exists

docker compose exec php php bin/console doctrine:database:create

docker compose exec -T db psql -U postgres -d app < var/backups/NAZWA-PLIKU.sql
```

Potem strona: http://localhost:8085

Jeśli dump jest ze **starego** schematu, a kod jest nowszy, po restore dopnij migracje:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 6. Wgranie kopii na serwerze (restore)

**To nadpisuje produkcyjną bazę.** Robisz to tylko gdy chcesz wrócić do konkretnego dumpa (awaria, pomyłka).

Najpierw nowy backup „na wszelki wypadek”:

```bash
cd /www/DOCKER/my-site

mkdir -p var/backups

docker compose exec -T db pg_dump -U postgres -d app --no-owner --no-acl > var/backups/app-prod-przed-restore-$(date +%F-%H%M).sql
```

Potem restore:

```bash
cd /www/DOCKER/my-site

docker compose exec php php bin/console doctrine:database:drop --force --if-exists

docker compose exec php php bin/console doctrine:database:create

docker compose exec -T db psql -U postgres -d app < var/backups/NAZWA-PLIKU.sql
```

Sprawdź stronę publiczną i `/login`.

Nie używaj na VPS:

```bash
docker compose down -v
docker volume rm my-site_postgres_data
php bin/console app:dev:reset-database
```

Te komendy kasują bazę bez wgrywania dumpa.

---

## 7. Tylko treść CMS (bez kont admina)

Gdy chcesz przenieść projekty / stack / doświadczenie / ustawienia, a **zostawić** użytkowników na danym środowisku:

**Zapis z bazy do pliku (laptop albo serwer):**

```bash
docker compose exec php php bin/console app:content:export
```

Powstaje `content/portfolio.yaml` — ten plik **może** iść do GIT.

**Wgranie treści do bazy:**

```bash
docker compose exec php php bin/console app:content:import
```

Najpierw suchy test (nic nie zapisuje):

```bash
docker compose exec php php bin/console app:content:import --dry-run
```

To **nie** jest pełny backup bazy. Nie przywraca adminów ani tabeli migracji.

---

## 8. Kiedy czego używać

| Chcę | Komenda |
|---|---|
| Kopia bezpieczeństwa przed migracją | `pg_dump` (sekcja 1 albo 2) |
| Zabrać bazę z VPS na laptopa | `scp` (sekcja 4) + restore lokalnie (sekcja 5) |
| Wgrać dump z laptopa na VPS | prawie nigdy — nadpisze produkcję; raczej sekcja 7 |
| Wgrać projekty na produkcję bez ruszania admina | `app:content:export` → GIT / `scp` YAML → `app:content:import` |
| Odtworzyć produkcję po awarii | restore dumpa na serwerze (sekcja 6) |

---

## 9. Szybki deploy na VPS (bez kasowania danych)

```bash
cd /www/DOCKER/my-site

mkdir -p var/backups
docker compose exec -T db pg_dump -U postgres -d app --no-owner --no-acl > var/backups/app-prod-$(date +%F-%H%M).sql

git pull origin main

docker compose up -d --build

docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

docker compose exec php php bin/console cache:clear
```

Import YAML tylko gdy **świadomie** chcesz wgrać treść z `content/portfolio.yaml`:

```bash
docker compose exec php php bin/console app:content:import
```
