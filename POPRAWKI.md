# Do poprawy

Backlog. **Nowe pomysły zapisujemy tutaj — bez zmian w kodzie**, dopóki nie będzie osobnej zgody na wdrożenie.

---

## Do zrobienia

- Treści z bazy (tytuły projektów, opisy) mogą nadal być po angielsku — to content, nie UI. Uzupełnić ręcznie w adminie.

---

## Zrobione

- Placeholdery `[…]` nie są już renderowane: brak danych = ukryty element albo empty state.
- Wstępy sekcji (realizacje, doświadczenie, stack, GitHub/OSS) w `SiteSetting`, edytowalne w adminie.
- `metaDescription` trafia do `<meta name="description">` w `<head>`.
- Empty states dla pustej listy projektów / doświadczenia / stacku.
- Menu ma link do `#stack`.
- Własny favicon (`/favicon.svg`) zamiast ikony Symfony.
- Skip-link „Przejdź do treści”.
- CRUD użytkowników w EasyAdmin (hasło hashowane; nie można usunąć siebie ani ostatniego admina). `app:create-admin` zostaje.
- Pola projektu uproszczone: krótki opis (tylko karta), opis projektu, opcjonalne wyzwanie i rozwiązanie. Usunięte: przegląd, problem, efekt, wnioski, decyzje techniczne, architektura, lista wyzwań.
- Case study nie powtarza krótkiego opisu; Wyzwanie i Rozwiązanie tylko gdy wypełnione.
- Osiągnięcia w doświadczeniu opcjonalne — lista na stronie tylko gdy jest choć jeden punkt.

Wcześniej (zostawione):

- Stack: usunięte nieużywane pola **slug**, **kolor**, **adres strony**. Zostały nazwa, kategoria, ikona, publikacja, wyróżnienie, kolejność. Slug projektu zostaje (`/projects/{slug}`).
- Rola w projekcie **opcjonalna** — sekcja na case study tylko gdy pole jest wypełnione.
- Okładka projektu: wgrywanie w adminie (przycisk + przeciągnij i upuść) do `public/uploads/projects/`.

---

## Świadomie poza zakresem (na razie)

- Komendy CLI po angielsku — nie UI przeglądarki.
- Osobny system tłumaczeń — nieplanowany; teksty UI są na sztywno po polsku, locale aplikacji to `pl`.
- React / i18n frontowy — nie dotyczy tego projektu.
- Formularz kontaktowy — zostaje `mailto:`.
