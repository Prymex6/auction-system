<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        // The documents name the operator, which differs per instance and is
        // not something to keep in the repository.
        $details = [
            '{{operator}}' => config('platform.operator.name').', '.config('platform.operator.address'),
            '{{operator_name}}' => config('platform.operator.name'),
            '{{contact_email}}' => config('platform.contact.email'),
            '{{contact_phone}}' => config('platform.contact.phone'),
        ];

        foreach ($this->pages() as $page) {
            $page['content'] = strtr($page['content'], $details);
            LegalPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function pages(): array
    {
        return [
            [
                'slug' => 'terms',
                'title' => 'Regulamin serwisu',
                'content' => <<<'TXT'
## §1. Postanowienia ogólne
1. Niniejszy Regulamin określa zasady korzystania z serwisu Gołębiowy Lot (golebiowylot.pl) — internetowej platformy aukcyjnej gołębi pocztowych.
2. Właścicielem i operatorem Serwisu jest {{operator}} (dalej: „Operator").
3. Korzystanie z Serwisu oznacza akceptację niniejszego Regulaminu w całości.
4. Serwis przeznaczony jest wyłącznie dla osób pełnoletnich posiadających pełną zdolność do czynności prawnych.

## §2. Definicje
1. Serwis — platforma internetowa Gołębiowy Lot dostępna pod adresem golebiowylot.pl.
2. Użytkownik — osoba fizyczna lub prawna posiadająca konto w Serwisie.
3. Sprzedający — Użytkownik wystawiający gołębia na aukcję lub w opcji „Kup teraz".
4. Kupujący — Użytkownik składający ofertę w aukcji lub korzystający z opcji „Kup teraz".
5. Aukcja — procedura sprzedaży gołębia w drodze licytacji.
6. Licytacja (oferta) — wiążące oświadczenie Kupującego o gotowości zakupu gołębia za wskazaną kwotę.
7. Postąpienie — minimalna kwota, o którą nowa oferta musi przewyższać aktualną; wysokość postąpienia wskazywana jest przy ofercie i może zależeć od aktualnej ceny.
8. Auto-licytacja — funkcja automatycznego podbijania ofert w imieniu Kupującego do wskazanego przez niego limitu maksymalnego.
9. Kup teraz — sprzedaż za stałą cenę wskazaną w ofercie, bez licytacji.

## §3. Rejestracja i konto
1. Rejestracja w Serwisie jest bezpłatna i wymaga podania prawdziwych danych.
2. Użytkownik zobowiązany jest do zachowania poufności danych logowania.
3. Konto podlega aktywacji przez administratora Serwisu.
4. Zabrania się posiadania więcej niż jednego konta bez zgody Operatora.
5. Operator może zawiesić lub usunąć konto naruszające Regulamin.

## §4. Zasady aukcji i licytacji
1. Wystawienie gołębia wymaga podania rzetelnych informacji: rasy, płci, roku urodzenia, numeru obrączki oraz aktualnych zdjęć.
2. Aukcja wymaga zatwierdzenia przez administratora przed publikacją. Czas trwania aukcji wskazany jest w ofercie.
3. Licytacja rozpoczyna się od ceny wywoławczej. Każda kolejna oferta musi przewyższać aktualną cenę co najmniej o postąpienie.
4. Złożona oferta jest wiążąca i nie może zostać wycofana.
5. Korzystając z auto-licytacji, Kupujący upoważnia system do automatycznego składania w jego imieniu ofert przewyższających oferty innych Kupujących o postąpienie, do wskazanego limitu. Limit auto-licytacji nie jest widoczny dla innych Użytkowników.
6. Sprzedający nie może licytować własnych aukcji — bezpośrednio ani przez osoby trzecie. Zabronione jest sztuczne podbijanie ceny.
7. Oferta złożona w ostatnich minutach aukcji może automatycznie przedłużyć jej czas (mechanizm anty-sniper); łączna liczba przedłużeń jest ograniczona.
8. Jeżeli oferta łączy licytację z opcją „Kup teraz", skorzystanie z „Kup teraz" kończy aukcję z pierwszeństwem przed trwającą licytacją.
9. Aukcję wygrywa Użytkownik z najwyższą ofertą w momencie jej zakończenia. Z chwilą zakończenia aukcji dochodzi do zawarcia umowy sprzedaży między Sprzedającym a zwycięzcą licytacji.
10. Sprzedający może wycofać aukcję wyłącznie do momentu złożenia pierwszej oferty; później — jedynie za zgodą Operatora w uzasadnionych przypadkach.
11. Operator może anulować oferty złożone z naruszeniem Regulaminu (np. z kont fikcyjnych); w takim przypadku wygrywa najwyższa ważna oferta.
12. Umowa sprzedaży wiąże strony niezależnie od tego, że rozliczenie następuje poza Serwisem. Uchylanie się od jej wykonania — odmowa zapłaty przez zwycięzcę licytacji albo odmowa wydania gołębia przez Sprzedającego — stanowi naruszenie Regulaminu i, po zgłoszeniu przez drugą stronę, może skutkować zawieszeniem lub blokadą konta.

## §4a. Treści Użytkowników
1. Publikując ofertę, Użytkownik oświadcza, że posiada pełne prawa do zamieszczanych treści (zdjęcia, opisy, dokumenty rodowodowe).
2. Użytkownik udziela Operatorowi nieodpłatnej, niewyłącznej licencji na wyświetlanie tych treści w Serwisie oraz w materiałach promujących Serwis, na czas publikacji oferty.
3. Zabronione jest publikowanie treści naruszających prawo, prawa osób trzecich lub dobre obyczaje.
4. Operator może usunąć lub zablokować treści naruszające Regulamin lub przepisy prawa.

## §5. Płatności i dostawa
1. Rozliczenie transakcji następuje bezpośrednio między Kupującym a Sprzedającym.
2. Kupujący zobowiązany jest do kontaktu ze Sprzedającym w ciągu 48 godzin od wygrania aukcji.
3. Sposób przekazania gołębia (odbiór osobisty, transport specjalistyczny) ustalają strony transakcji.
4. Zaleca się dokumentowanie stanu gołębia przy przekazaniu.

## §5a. Status Sprzedającego i prawa konsumenta
1. Sprzedającym w Serwisie może być osoba prywatna lub przedsiębiorca. Sprzedający zobowiązany jest zgodnie z prawdą wskazać swój status na żądanie Operatora lub Kupującego.
2. Przy zakupie od osoby prywatnej przepisy o ochronie konsumentów — w tym prawo odstąpienia od umowy zawartej na odległość — nie mają zastosowania.
3. Sprzedający będący przedsiębiorcą samodzielnie odpowiada za realizację obowiązków wobec konsumentów (informacje przedumowne, prawo odstąpienia, rękojmia/niezgodność towaru z umową).
4. Operator nie jest stroną umowy sprzedaży i nie występuje jako sprzedawca oferowanych gołębi.

## §6. Odpowiedzialność
1. Operator nie jest stroną transakcji zawieranych między Użytkownikami.
2. Sprzedający ponosi pełną odpowiedzialność za zgodność opisu ze stanem faktycznym, w tym za autentyczność rodowodu i numeru obrączki.
3. Operator dokłada starań w zakresie weryfikacji ogłoszeń, ale nie gwarantuje ich prawdziwości.
4. Zabronione jest wystawianie ptaków chorych, kradzionych lub o sfałszowanym pochodzeniu.
5. Sprzedaż i przekazywanie ptaków musi odbywać się zgodnie z przepisami o ochronie zwierząt, w szczególności z poszanowaniem ich dobrostanu podczas transportu.
6. Zabroniona jest sprzedaż gatunków objętych ochroną prawną.

## §7. Reklamacje
1. Reklamacje dotyczące działania Serwisu należy zgłaszać na adres {{contact_email}}.
2. Operator rozpatruje reklamacje w terminie 14 dni roboczych.
3. Spory między Użytkownikami strony rozwiązują we własnym zakresie; Operator może pośredniczyć na wniosek obu stron.

## §7a. Dostępność funkcji Serwisu
1. Operator może czasowo ograniczyć wybrane funkcje Serwisu (np. licytacje lub samodzielne wystawianie aukcji przez Użytkowników), w szczególności w początkowej fazie działania platformy.
2. W okresie ograniczenia sprzedaż odbywa się w formule „Kup teraz" — zakup po cenie wskazanej w ofercie jest wiążący.
3. O włączeniu pełnej funkcjonalności Operator poinformuje komunikatem w Serwisie.

## §7b. Moderacja treści i odwołania
1. Każda oferta podlega weryfikacji przez Operatora przed publikacją; konta nowych Użytkowników wymagają aktywacji.
2. Operator może odrzucić lub usunąć ofertę oraz zawiesić konto w przypadku naruszenia Regulaminu, przepisów prawa lub uzasadnionego podejrzenia oszustwa, informując Użytkownika o przyczynie.
3. Od decyzji o odrzuceniu oferty, usunięciu treści lub zawieszeniu konta Użytkownik może odwołać się w terminie 14 dni na adres {{contact_email}}. Operator rozpatruje odwołanie w terminie 14 dni.
4. Zgłoszenia nielegalnych treści można składać przyciskiem „Zgłoś" przy ofercie lub na adres e-mail Operatora.

## §8. Postanowienia końcowe
1. Operator zastrzega sobie prawo do zmiany Regulaminu z 14-dniowym wyprzedzeniem.
2. O zmianach Użytkownicy zostaną poinformowani drogą e-mail oraz komunikatem w Serwisie.
3. W sprawach nieuregulowanych zastosowanie mają przepisy prawa polskiego, w szczególności Kodeksu cywilnego.
4. Regulamin wchodzi w życie z dniem publikacji.
TXT,
            ],
            [
                'slug' => 'privacy',
                'title' => 'Polityka prywatności',
                'content' => <<<'TXT'
## 1. Administrator danych
Administratorem danych osobowych jest {{operator}} (kontakt: {{contact_email}}). Dane przetwarzane są zgodnie z Rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 (RODO).

## 2. Jakie dane zbieramy
1. Dane rejestracyjne: imię, nazwisko, adres e-mail, numer telefonu, miasto.
2. Dane transakcyjne: historia aukcji, licytacji i wiadomości w Serwisie.
3. Dane techniczne: adres IP, typ przeglądarki, historia logowań (bezpieczeństwo konta).
4. Dane opcjonalne: adres, kod pocztowy, opis profilu (bio), zdjęcie.

## 3. Cele i podstawy przetwarzania
1. Świadczenie usług Serwisu — art. 6 ust. 1 lit. b RODO (wykonanie umowy).
2. Bezpieczeństwo i zapobieganie nadużyciom — art. 6 ust. 1 lit. f RODO (uzasadniony interes).
3. Obsługa reklamacji i kontakt — art. 6 ust. 1 lit. b i f RODO.
4. Wypełnienie obowiązków prawnych — art. 6 ust. 1 lit. c RODO.
5. Wysyłka newslettera (informacje o nowych ofertach i platformie) — art. 6 ust. 1 lit. a RODO (Twoja dobrowolna zgoda wyrażona przy zapisie). Zgodę możesz cofnąć w każdej chwili, wypisując się z newslettera lub pisząc na adres kontaktowy — dane zostaną wtedy usunięte z listy.

## 3a. Dane publikowane w Serwisie i wymiana danych między stronami transakcji
1. Wystawiając ofertę, Sprzedający publikuje w Serwisie swoją nazwę użytkownika, miasto oraz treść oferty; numer telefonu udostępniany jest wyłącznie zweryfikowanym Użytkownikom.
2. Po zakończeniu aukcji lub zakupie strony transakcji otrzymują wzajemnie dane kontaktowe niezbędne do wykonania umowy: nazwę użytkownika, numer telefonu i adres e-mail drugiej strony.
3. Podstawą prawną obu udostępnień jest niezbędność do wykonania umowy (art. 6 ust. 1 lit. b RODO). Strona otrzymująca dane może je wykorzystać wyłącznie w celu realizacji transakcji.

## 4. Komu udostępniamy dane
1. Dane kontaktowe (telefon) Sprzedającego widoczne są wyłącznie dla Użytkowników zweryfikowanych przez administratora.
2. Dane mogą być powierzane podmiotom świadczącym usługi hostingu i utrzymania Serwisu.
3. Za Twoją zgodą dane analityczne (zanonimizowane) przetwarza Google Ireland Ltd. w ramach usługi Google Analytics 4 — szczegóły w Polityce cookies.
4. Dane mogą zostać udostępnione uprawnionym organom państwowym na ich żądanie.
5. Nie sprzedajemy danych osobowych podmiotom trzecim.

## 5. Okres przechowywania
1. Dane konta — przez okres posiadania konta oraz 12 miesięcy po jego usunięciu (dochodzenie roszczeń).
2. Historia transakcji — 6 lat (obowiązki podatkowe i rachunkowe).
3. Logi techniczne — maksymalnie 24 miesiące.

## 6. Twoje prawa
Masz prawo do: dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, przenoszenia danych, wniesienia sprzeciwu oraz skargi do Prezesa Urzędu Ochrony Danych Osobowych (uodo.gov.pl). Wnioski realizujemy w terminie 30 dni.

## 7. Bezpieczeństwo
1. Hasła przechowywane są wyłącznie w postaci zaszyfrowanej (bcrypt).
2. Serwis oferuje dwuskładnikowe uwierzytelnianie (2FA).
3. Rejestrujemy historię logowań i urządzeń, umożliwiając zdalne wylogowanie.
4. Połączenia z Serwisem szyfrowane są protokołem TLS.
TXT,
            ],
            [
                'slug' => 'cookies',
                'title' => 'Polityka cookies',
                'content' => <<<'TXT'
## 1. Czym są pliki cookies
Pliki cookies to niewielkie pliki tekstowe zapisywane na Twoim urządzeniu podczas korzystania z Serwisu. Służą do utrzymania sesji, zapamiętywania preferencji i zapewnienia bezpieczeństwa.

## 2. Jakie cookies stosujemy
1. Niezbędne — utrzymanie sesji zalogowanego Użytkownika, token bezpieczeństwa (CSRF) oraz zapamiętanie Twojej decyzji o zgodzie. Bez nich Serwis nie działa poprawnie. Cookies: XSRF-TOKEN, laravel_session, pigeon_cookie_consent_v1.
2. Funkcjonalne — zapamiętanie listy obserwowanych aukcji i preferencji interfejsu (localStorage).
3. Analityczne (Google Analytics 4) — pomiar odwiedzin i sposobu korzystania z Serwisu (odwiedzane strony, czas wizyty). Uruchamiane WYŁĄCZNIE po Twojej dobrowolnej zgodzie (art. 6 ust. 1 lit. a RODO), z włączoną anonimizacją adresu IP. Cookies: _ga, _gid, _gat. Dostawca: Google Ireland Ltd.
4. Nie stosujemy cookies reklamowych ani profilujących.

## 3. Zarządzanie cookies
1. Przy pierwszej wizycie wyświetlamy baner zgody — możesz zaakceptować wszystkie cookies, tylko niezbędne albo dostosować wybór.
2. Swoją decyzję możesz zmienić w każdej chwili klikając „Ustawienia cookies" w stopce strony. Cofnięcie zgody usuwa cookies analityczne.
3. Możesz też usunąć lub zablokować cookies w ustawieniach przeglądarki — zablokowanie niezbędnych uniemożliwi logowanie.
4. Dane zapisane w localStorage usuniesz czyszcząc dane witryny w przeglądarce.

## 4. Zmiany polityki
O istotnych zmianach niniejszej polityki poinformujemy komunikatem w Serwisie.
TXT,
            ],
            [
                'slug' => 'help',
                'title' => 'Centrum pomocy',
                'content' => <<<'TXT'
## Pierwsze kroki
1. Załóż konto przyciskiem „Rejestracja" — podaj prawdziwe dane, ułatwi to transakcje.
2. Poczekaj na aktywację konta przez administratora (zwykle do 24 godzin).
3. Uzupełnij profil: miasto, telefon i opis zwiększają zaufanie innych hodowców.

## Jak licytować
1. Wejdź na stronę aukcji i podaj kwotę wyższą od aktualnej ceny.
2. Możesz ustawić auto-licytację: podaj maksymalną kwotę, a system będzie przebijał oferty za Ciebie.
3. Licytacja w ostatnich minutach może przedłużyć aukcję (ochrona przed sniperami).
4. Po wygranej otrzymasz powiadomienie i dane kontaktowe sprzedającego.

## Jak sprzedawać
1. Kliknij „Wystaw gołębia" i wypełnij formularz: rasa, płeć, rok, obrączka, zdjęcia.
2. Dodaj zdjęcia rodowodu — aukcje z rodowodem osiągają wyższe ceny.
3. Aukcja pojawi się publicznie po zatwierdzeniu przez administratora.
4. Po zakończeniu skontaktuj się ze zwycięzcą w ciągu 48 godzin.

## Bezpieczeństwo konta
1. Włącz dwuskładnikowe uwierzytelnianie (2FA) w ustawieniach profilu.
2. Sprawdzaj historię logowań — możesz zdalnie wylogować nieznane urządzenia.
3. Nigdy nie podawaj hasła innym osobom; administracja nigdy o nie nie poprosi.

## Kontakt z pomocą
Nie znalazłeś odpowiedzi? Napisz: {{contact_email}} — odpowiadamy w dni robocze w ciągu 24 godzin.
TXT,
            ],
            [
                'slug' => 'how-it-works',
                'title' => 'Jak to działa?',
                'content' => <<<'TXT'
## 1. Załóż konto
Rejestracja trwa dwie minuty i jest całkowicie bezpłatna. Po weryfikacji przez administratora otrzymasz pełny dostęp do platformy — możesz przeglądać oferty, obserwować gołębie i kontaktować się z hodowcami.

## 2. Znajdź gołębia dla siebie
Przeglądaj oferty według rasy, płci i ceny. Każda oferta zawiera zdjęcia, numer obrączki, rodowód i opis od hodowcy. Kliknij serduszko, aby dodać ofertę do obserwowanych.

## 3. Skontaktuj się ze sprzedawcą
Masz pytania o pochodzenie, wyniki lotowe albo cenę? Napisz wiadomość bezpośrednio z poziomu oferty — sprzedawca otrzyma powiadomienie i szybko odpowie. Zalogowani użytkownicy widzą też numer telefonu hodowcy.

## 4. Ustal szczegóły transakcji
Cena, sposób płatności i przekazanie gołębia (odbiór osobisty lub specjalistyczny transport) — wszystko ustalasz bezpośrednio ze sprzedawcą. Platforma nie pobiera prowizji od transakcji.

## 5. Odbierz swojego championa
Przy odbiorze zweryfikuj numer obrączki z rodowodem i udokumentuj przekazanie. Po transakcji możesz wystawić sprzedawcy opinię — budujesz w ten sposób zaufanie w społeczności.

## Sprzedajesz gołębie?
W obecnej fazie serwisu oferty publikuje administracja platformy. Chcesz wystawić swoje gołębie? Skontaktuj się z nami — pomożemy przygotować profesjonalną ofertę. Wkrótce samodzielne wystawianie będzie dostępne dla wszystkich zweryfikowanych hodowców.
TXT,
            ],
            [
                'slug' => 'contact',
                'title' => 'Kontakt',
                'content' => <<<'TXT'
## Dane kontaktowe
Gołębiowy Lot (golebiowylot.pl) — platforma aukcyjna gołębi pocztowych
Właściciel: {{operator_name}}

E-mail: {{contact_email}}
Telefon: {{contact_phone}}

## Zgłoszenia
1. Problemy techniczne: opisz problem i dołącz zrzut ekranu — przyspieszy to diagnozę.
2. Zgłoszenia naruszeń: użyj przycisku „Zgłoś" przy aukcji lub profilu użytkownika.
3. Reklamacje: rozpatrujemy w terminie 14 dni roboczych.
4. Ochrona danych (RODO): w tytule wiadomości wpisz „Dane osobowe".

## Współpraca
Jesteś organizatorem lotów lub związkiem hodowców? Napisz do nas — chętnie porozmawiamy o współpracy.
TXT,
            ],
            [
                'slug' => 'faq',
                'title' => 'Najczęściej zadawane pytania (FAQ)',
                'content' => <<<'TXT'
## Konto i rejestracja
1. Czy rejestracja jest płatna? — Nie, założenie i prowadzenie konta jest bezpłatne.
2. Dlaczego moje konto jest nieaktywne? — Nowe konta aktywuje administrator, zwykle w ciągu 24 godzin.
3. Jak włączyć 2FA? — W ustawieniach profilu, sekcja „Bezpieczeństwo". Potrzebna aplikacja typu Google Authenticator.

## Licytacje
1. Czy mogę wycofać ofertę? — Nie, złożona licytacja jest wiążąca (§4 Regulaminu).
2. Jak działa auto-licytacja? — Podajesz maksymalną kwotę; system przebija innych minimalnym krokiem aż do Twojego limitu.
3. Dlaczego aukcja się przedłużyła? — Licytacja w ostatnich minutach automatycznie przedłuża aukcję (mechanizm anty-sniper).
4. Co jeśli wygram i się rozmyślę? — Z chwilą zakończenia aukcji dochodzi do zawarcia wiążącej umowy ze sprzedającym — to, że płatność następuje bezpośrednio na jego konto, niczego tu nie zmienia. Uchylanie się od transakcji narusza Regulamin i po zgłoszeniu może skutkować blokadą konta.

## Sprzedaż
1. Dlaczego moja aukcja nie jest widoczna? — Każda aukcja wymaga zatwierdzenia przez administratora.
2. Ile aukcji mogę wystawić? — Limit zależy od typu konta; aktualne limity znajdziesz w ustawieniach platformy.
3. Czy mogę edytować aukcję? — Tylko przed zatwierdzeniem (status „oczekująca"). Aktywnych aukcji nie można edytować.

## Transakcje
1. Jak płacę za gołębia? — Rozliczenie następuje bezpośrednio między stronami; platforma nie pośredniczy w płatności.
2. Jak odebrać gołębia? — Odbiór osobisty lub specjalistyczny transport żywych zwierząt — do ustalenia ze sprzedającym.
3. Gołąb nie zgadza się z opisem — co robić? — Udokumentuj rozbieżności i skontaktuj się ze sprzedającym; w razie sporu zgłoś sprawę przez przycisk „Zgłoś".
TXT,
            ],
            [
                'slug' => 'security',
                'title' => 'Bezpieczeństwo',
                'content' => <<<'TXT'
## Jak chronimy Twoje konto
1. Hasła przechowujemy wyłącznie w postaci zaszyfrowanej (bcrypt) — nie znamy Twojego hasła.
2. Dwuskładnikowe uwierzytelnianie (2FA) oparte o TOTP — zalecamy włączenie w ustawieniach.
3. Historia logowań i urządzeń z możliwością zdalnego wylogowania.
4. Limity prób logowania chroniące przed atakami siłowymi.

## Jak chronimy transakcje
1. Każda aukcja przechodzi weryfikację administratora przed publikacją.
2. System zgłoszeń pozwala raportować podejrzane aukcje i użytkowników.
3. Konta naruszające zasady są blokowane, a próby oszustw dokumentowane.
4. Dane kontaktowe sprzedających widoczne są wyłącznie dla użytkowników zweryfikowanych przez administratora.

## Dobre praktyki hodowcy
1. Sprawdzaj reputację i opinie sprzedającego przed licytacją.
2. Weryfikuj numer obrączki z rodowodem przy odbiorze ptaka.
3. Dokumentuj przekazanie gołębia (zdjęcia, protokół).
4. Podejrzane oferty zgłaszaj przyciskiem „Zgłoś" — reagujemy priorytetowo.

## Zgłaszanie problemów bezpieczeństwa
Znalazłeś lukę w zabezpieczeniach? Napisz na {{contact_email}} z dopiskiem „Bezpieczeństwo". Prosimy o odpowiedzialne ujawnianie — nie publikuj szczegółów przed naprawą.
TXT,
            ],
        ];
    }
}
