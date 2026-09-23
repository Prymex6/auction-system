<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::where('is_admin', true)->first() ?? User::factory()->create(['is_admin' => true]);

        foreach ($this->posts() as $i => $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                array_merge($post, [
                    'author_id' => $admin->id,
                    'published' => true,
                    'published_at' => now()->subDays(2 * ($i + 1)),
                ])
            );
        }
    }

    private function posts(): array
    {
        return [
            [
                'title' => 'Jak wybrać gołębia pocztowego — kompletny poradnik',
                'slug' => 'jak-wybrac-golebia-pocztowego',
                'category' => 'Poradnik',
                'excerpt' => 'Rodowód, budowa, kondycja i temperament — na co naprawdę patrzeć przy wyborze gołębia, żeby nie przepłacić i nie kupić kota w worku.',
                'image' => '/images/blog/jak-wybrac-golebia-pocztowego.jpg',
                'content' => <<<'HTML'
<p>Zakup gołębia pocztowego to inwestycja — czasem kilkaset, a czasem kilkanaście tysięcy złotych. Ten poradnik przeprowadzi Cię przez wszystko, na co warto patrzeć, zanim podejmiesz decyzję.</p>

<h2>Zacznij od celu</h2>
<p>Zanim zaczniesz przeglądać oferty, odpowiedz sobie na pytanie: po co kupujesz? Inny ptak sprawdzi się jako reproduktor do wzmocnienia linii hodowlanej, inny jako młody perspektywiczny lotnik, a jeszcze inny, gdy budujesz gołębnik od zera. Reproduktor nie musi już latać — liczą się jego geny i wyniki potomstwa. U młodego lotnika kluczowa jest kondycja i pochodzenie od sprawdzonych par.</p>

<h2>Rodowód to podstawa</h2>
<p>Dobry rodowód mówi więcej niż najpiękniejsze zdjęcie. Sprawdź wyniki lotowe przodków — nie tylko rodziców, ale i dziadków. Zwróć uwagę, czy wyniki pochodzą z lotów konkursowych potwierdzonych przez oddział PZHGP, oraz czy linia jest znana z dystansu, który Cię interesuje: sprinty do 400 km to zupełnie inna specjalizacja niż loty maratońskie powyżej 700 km.</p>
<p>Numer obrączki rodowej musi zgadzać się z dokumentacją. Przy odbiorze ptaka zawsze porównaj obrączkę na nodze z rodowodem — to <strong>pierwsza i najważniejsza weryfikacja autentyczności</strong>.</p>

<h2>Ocena budowy</h2>
<p>Doświadczeni hodowcy oceniają gołębia w ręku, ale kilka rzeczy widać też na dobrych zdjęciach:</p>
<ul>
<li>zwartą, wyważoną sylwetkę</li>
<li>mocne, elastyczne lotki</li>
<li>oko z wyraźną, dobrze wybarwioną tęczówką</li>
<li>suchą, jasną woskówkę</li>
</ul>
<p>Upierzenie powinno być gładkie i przylegające — matowe, nastroszone pióra mogą świadczyć o problemach zdrowotnych lub przebytej chorobie.</p>

<h2>Wiek ma znaczenie</h2>
<p>Młode ptaki (roczniki bieżące) kupuje się głównie „na pochodzenie" — ich wartość lotowa dopiero się okaże. Ptaki 2-4 letnie mają już wyniki, ale są droższe. Reproduktory powyżej 6-7 lat bywają okazją cenową, jednak sprawdź, czy para nadal zapładnia jaja.</p>

<h2>Zadawaj pytania</h2>
<p>Dobry sprzedawca chętnie odpowie na pytania o szczepienia (zwłaszcza przeciwko paramyksowirozie — obowiązkowe), przebyte choroby, wyniki lotowe i powód sprzedaży. Unikaj ofert, w których sprzedający zbywa pytania ogólnikami albo nie chce pokazać rodowodu przed zakupem. Na naszej platformie każdą ofertę możesz omówić przez wiadomości — korzystaj z tego śmiało.</p>
HTML,
            ],
            [
                'title' => 'Zdrowie gołębi — profilaktyka, szczepienia i pierwsze objawy chorób',
                'slug' => 'zdrowie-golebi-profilaktyka',
                'category' => 'Zdrowie',
                'excerpt' => 'Kalendarz szczepień, kwarantanna nowych ptaków i objawy, których nie wolno zignorować. Podstawy zdrowego gołębnika w jednym miejscu.',
                'image' => '/images/blog/zdrowie-golebi-profilaktyka.jpg',
                'content' => <<<'HTML'
<p>Zdrowe stado to fundament każdej hodowli. Większość poważnych problemów zdrowotnych w gołębniku zaczyna się od zaniedbanej profilaktyki albo od jednego ptaka wprowadzonego bez kwarantanny.</p>

<h2>Kalendarz szczepień</h2>
<p>Absolutna podstawa to coroczne szczepienie przeciwko paramyksowirozie (PMV-1) — jest obowiązkowe dla ptaków biorących udział w lotach i wystawach. Szczepimy najpóźniej 4 tygodnie przed pierwszym lotem lub wystawą. Wielu hodowców szczepi też przeciwko ospie gołębiej (szczególnie w regionach, gdzie choroba występuje) oraz salmonellozie.</p>
<p>Prowadź książkę zdrowia stada: daty szczepień, preparaty, numery serii. Przy sprzedaży ptaka taka dokumentacja podnosi wiarygodność oferty i wartość gołębia.</p>

<h2>Kwarantanna — żelazna zasada</h2>
<p>Każdy nowy ptak — kupiony, znaleziony, wracający z wystawy — powinien spędzić minimum 3-4 tygodnie w osobnym pomieszczeniu, zanim dołączy do stada. W tym czasie obserwuj odchody, apetyt i zachowanie. To najtańsze ubezpieczenie hodowli, jakie istnieje: jeden chory ptak potrafi położyć całe stado.</p>

<h2>Objawy, których nie wolno ignorować</h2>
<ul>
<li>nastroszenie i apatia</li>
<li>wodniste lub zielone odchody utrzymujące się dłużej niż dobę</li>
<li>wyciek z nozdrzy lub oczu</li>
<li>skręt szyi (typowy dla paramyksowirozy)</li>
<li>nagła utrata masy przy zachowanym apetycie (podejrzenie robaczycy lub kokcydiozy)</li>
<li>zmiany dyfterytyczne w dziobie</li>
</ul>
<p>Każdy z tych objawów to sygnał do izolacji ptaka i kontaktu z lekarzem weterynarii — najlepiej takim, który zna się na ptakach.</p>

<h2>Higiena gołębnika</h2>
<p>Sucho, przewiewnie, bez przeciągów — te trzy słowa opisują dobry gołębnik. Wilgoć to sprzymierzeniec kokcydiów i grzybic. Czyść podłogi regularnie, poidła myj codziennie (woda stojąca to wylęgarnia trichomonadozy), a karmidła nie mogą być zanieczyszczone odchodami.</p>
<p>Raz w roku warto wykonać profilaktyczne badanie odchodów zbiorczych w kierunku pasożytów wewnętrznych — kosztuje niewiele, a pozwala leczyć celowanie zamiast „w ciemno".</p>
HTML,
            ],
            [
                'title' => 'Rodowody i obrączki — jak zweryfikować pochodzenie gołębia',
                'slug' => 'rodowody-i-obraczki-weryfikacja',
                'category' => 'Poradnik',
                'excerpt' => 'Numer obrączki, karta własności i rodowód — co dokładnie sprawdzić przy zakupie, żeby mieć pewność, że kupujesz ptaka z prawdziwym pochodzeniem.',
                'image' => '/images/blog/rodowody-i-obraczki-weryfikacja.jpg',
                'content' => <<<'HTML'
<p>Rodowód to metryka gołębia — a jak każdy dokument, bywa niestety podrabiany lub „podkręcany". Oto jak zweryfikować pochodzenie ptaka krok po kroku.</p>

<h2>Co mówi numer obrączki</h2>
<p>Polska obrączka rodowa zawiera oznaczenie kraju (PL), rok założenia, numer oddziału i numer kolejny ptaka. Obrączkę zakłada się pisklęciu w pierwszych dniach życia — dorosłemu ptakowi nie da się jej założyć bez śladów. Sprawdź, czy obrączka nie jest rozcięta, klejona lub podejrzanie luźna, a rocznik na obrączce zgadza się z deklarowanym wiekiem ptaka.</p>

<h2>Rodowód i karta własności</h2>
<p>Rodowód wystawia hodowca i zawiera dane przodków zwykle do 4-5 pokoleń. Wiarygodny rodowód ma numery obrączek wszystkich przodków, a nie tylko nazwy linii. Karta własności obrączki to osobny dokument — przy zakupie sprzedający powinien przekazać Ci ją razem z ptakiem, bo to ona formalnie potwierdza przeniesienie własności.</p>
<p>Czerwona lampka powinna się zapalić, gdy:</p>
<ul>
<li>sprzedający nie ma karty własności</li>
<li>rodowód jest „w przygotowaniu"</li>
<li>numery na rodowodzie nie zgadzają się z obrączką</li>
<li>ten sam przodek pojawia się w wielu ofertach różnych sprzedawców z różnymi wynikami</li>
</ul>

<h2>Wyniki lotowe — jak je czytać</h2>
<p>Wynik konkursowy ma sens tylko w kontekście: liczby gołębi w konkursie, dystansu i listy konkursowej oddziału lub okręgu. „1. miejsce" z lotu, w którym startowało 50 ptaków, znaczy co innego niż czołówka z kilku tysięcy. Proś o konkretne listy konkursowe — rzetelny hodowca ma je pod ręką.</p>

<h2>Weryfikacja przy odbiorze</h2>
<p>Przy przekazaniu ptaka zawsze:</p>
<ul>
<li>porównaj numer obrączki z rodowodem i kartą własności</li>
<li>obejrzyj obrączkę pod kątem śladów manipulacji</li>
<li>zrób zdjęcie ptaka z widoczną obrączką</li>
</ul>
<p>Dobre udokumentowanie przekazania chroni obie strony transakcji — i kupującego, i sprzedającego.</p>
HTML,
            ],
            [
                'title' => 'Bezpieczny zakup gołębia przez internet — krok po kroku',
                'slug' => 'bezpieczny-zakup-golebia-online',
                'category' => 'Bezpieczeństwo',
                'excerpt' => 'Od pierwszej wiadomości do odbioru ptaka: jak kupować gołębie online rozważnie i na co uważać, żeby transakcja była bezpieczna dla obu stron.',
                'image' => '/images/blog/bezpieczny-zakup-golebia-online.jpg',
                'content' => <<<'HTML'
<p>Internet otworzył hodowcom dostęp do ptaków z całej Polski i Europy. Żeby jednak zakup na odległość był równie bezpieczny jak z ręki do ręki, warto trzymać się kilku zasad.</p>

<h2>Sprawdź sprzedającego</h2>
<p>Na naszej platformie każde konto przechodzi weryfikację administratora, a przy sprzedawcy widzisz jego reputację i opinie od innych kupujących. Zajrzyj na profil: jak długo jest w serwisie, ile ma zakończonych transakcji, co piszą o nim inni. Zweryfikowani użytkownicy widzą też numer telefonu sprzedawcy — krótka rozmowa telefoniczna mówi o hodowcy więcej niż dziesięć wiadomości.</p>

<h2>Zadawaj konkretne pytania</h2>
<p>Napisz przez formularz wiadomości przy ofercie i zapytaj o:</p>
<ul>
<li>aktualne zdjęcia lub wideo ptaka (najlepiej z widoczną obrączką)</li>
<li>skan rodowodu przed zakupem</li>
<li>szczepienia i stan zdrowia</li>
<li>powód sprzedaży</li>
</ul>
<p>Sprzedający, który zwleka z pokazaniem rodowodu „do czasu wpłaty", powinien wzbudzić Twoją czujność.</p>

<h2>Ustal warunki na piśmie</h2>
<p>Cena, sposób płatności, termin i forma przekazania ptaka, co dokładnie wchodzi w zestaw (karta własności, rodowód oryginalny czy kopia) — wszystko ustalcie w wiadomościach na platformie. Korespondencja zostaje i w razie sporu jest dowodem dla obu stron.</p>

<h2>Odbiór osobisty czy transport?</h2>
<p>Jeśli to możliwe, wybierz odbiór osobisty — ocenisz ptaka w ręku przed finalizacją. Przy większych odległościach korzystaj wyłącznie ze sprawdzonych przewoźników żywych zwierząt (kursują regularnie między giełdami i wystawami). Nigdy nie wysyłaj gołębia zwykłą paczką kurierską — to zagrożenie dla życia ptaka i naruszenie przepisów o ochronie zwierząt.</p>

<h2>Gdy coś pójdzie nie tak</h2>
<p>Ptak niezgodny z opisem? Udokumentuj rozbieżności zdjęciami od razu przy odbiorze i skontaktuj się ze sprzedającym — większość spraw da się wyjaśnić polubownie. Oferty naruszające zasady zgłaszaj przyciskiem „Zgłoś" — każde zgłoszenie trafia do administracji.</p>
HTML,
            ],
            [
                'title' => 'Transport gołębia po zakupie — dobrostan ptaka i dobre praktyki',
                'slug' => 'transport-golebia-po-zakupie',
                'category' => 'Poradnik',
                'excerpt' => 'Właściwy transporter, woda, temperatura i czas podróży — jak przewieźć gołębia, żeby dotarł do nowego gołębnika w pełnej kondycji.',
                'image' => '/images/blog/transport-golebia-po-zakupie.jpg',
                'content' => <<<'HTML'
<p>Nawet najlepszy gołąb może stracić kondycję — albo zdrowie — przez źle zorganizowany transport. Kilka prostych zasad sprawi, że ptak dotrze do Ciebie w dobrej formie.</p>

<h2>Właściwy transporter</h2>
<p>Gołębia przewozimy w sztywnym transporterze z wentylacją z co najmniej dwóch stron, na tyle przestronnym, by ptak mógł stać i się obrócić, ale nie na tyle dużym, żeby obijał się przy hamowaniu. Dno wyłóż grubą warstwą słomy lub trocin. Kartonowe pudło sprawdzi się wyłącznie awaryjnie i tylko na krótkim dystansie — z wyciętymi otworami wentylacyjnymi.</p>
<p>Na jeden transporter — jeden ptak, chyba że przewozisz zgraną parę. Obce sobie gołębie w jednym pojemniku to stres i ryzyko okaleczeń.</p>

<h2>Woda i karma</h2>
<p>Przy podróży do 4-5 godzin ptak nie potrzebuje karmienia w trasie — nakarm go lekko 2-3 godziny przed drogą. Przy dłuższych trasach zaplanuj postój z wodą; odwodnienie to największe zagrożenie transportowe, zwłaszcza latem.</p>

<h2>Temperatura i pora dnia</h2>
<p>Latem przewoź rano lub wieczorem, nigdy nie zostawiaj transportera w nagrzanym aucie — nawet kilka minut w pełnym słońcu może być groźne. Zimą chroń ptaka przed przeciągiem i gwałtownymi zmianami temperatury. Optymalnie: klimatyzowane wnętrze auta, transporter zabezpieczony przed przesuwaniem, z dala od nawiewu.</p>

<h2>Po przyjeździe</h2>
<p>Nowego ptaka wpuść od razu do pomieszczenia kwarantanny (nie do stada!), zapewnij świeżą wodę z elektrolitami i spokój. Pierwszej doby nie martw się mniejszym apetytem — to normalna reakcja na stres. Obserwuj odchody i zachowanie przez kolejne 3-4 tygodnie, zanim ptak dołączy do reszty gołębnika.</p>
<p>Pamiętaj: sposób przekazania ptaka ustalają między sobą kupujący i sprzedający — z poszanowaniem dobrostanu zwierzęcia i przepisów o ochronie zwierząt. Dobrze zorganizowany odbiór to wizytówka obu stron.</p>
HTML,
            ],
            [
                'title' => 'Żywienie gołębi pocztowych — od podstaw po okres rozpłodowy',
                'slug' => 'zywienie-golebi-pocztowych',
                'category' => 'Zdrowie',
                'excerpt' => 'Mieszanki, minerały, grit i woda — jak żywić gołębie w spoczynku, pierzeniu i rozpłodzie, żeby stado było w stałej kondycji.',
                'image' => '/images/blog/zywienie-golebi-pocztowych.jpg',
                'content' => <<<'HTML'
<p>Żywienie to obok genetyki najważniejszy czynnik kondycji stada. Dobra wiadomość: nie potrzebujesz drogich cudownych mieszanek — potrzebujesz systematyczności i dopasowania karmy do okresu w roku.</p>

<h2>Podstawa: zbilansowana mieszanka</h2>
<p>Baza żywienia gołębia pocztowego to mieszanki zbożowo-strączkowe: kukurydza, pszenica, jęczmień, groch, wyka, sorgo, słonecznik. Proporcje zmieniają się w zależności od okresu — w spoczynku zimowym ograniczamy strączkowe (białko) na rzecz jęczmienia, a w okresie wychowu młodych zwiększamy udział grochu i wyki nawet do 30-35%.</p>
<p>Kupuj karmę z pewnego źródła i przechowuj w suchym miejscu w szczelnych pojemnikach. Ziarno zatęchłe lub porażone pleśnią to prosta droga do grzybic i zatruć.</p>

<h2>Grit i minerały — nie dodatek, a konieczność</h2>
<p>Gołąb nie ma zębów — ziarno rozciera w mielcu dzięki gritowi. Mieszanka mineralna z gritem, czerwonką i węglem drzewnym musi być dostępna cały rok, wymieniana regularnie (zbrylona i zanieczyszczona traci wartość). W okresie nieśności gołębice potrzebują dodatkowego wapnia — skorupy jaj to sygnał: cienkie i kruche oznaczają niedobór.</p>

<h2>Woda ważniejsza niż karma</h2>
<p>Świeża woda codziennie — bez wyjątków. Poidła myj każdego dnia, bo stojąca woda to główne źródło trichomonadozy w stadzie. Raz w tygodniu wielu hodowców podaje do wody jabłkowy ocet (łyżka na litr) lub ziołowe herbatki — wspierają florę przewodu pokarmowego.</p>

<h2>Okres pierzenia i rozpłodu</h2>
<p>Pierzenie (późne lato i jesień) to ogromny wysiłek metaboliczny: zwiększ udział nasion oleistych (słonecznik, rzepak, siemię lniane) — siarka i nienasycone kwasy tłuszczowe budują nowe pióro. W rozpłodzie kluczowe jest białko i wapń; u młodych rosnących na dobrze żywionych karmicielach różnicę widać już w gnieździe.</p>

<h2>Czego unikać</h2>
<ul>
<li>pieczywa (zakwasza i puchnie w wolu)</li>
<li>solonych i przetworzonych resztek ze stołu</li>
<li>gwałtownych zmian mieszanki z dnia na dzień</li>
</ul>
<p>Każdą zmianę żywienia wprowadzaj stopniowo przez 5-7 dni.</p>
HTML,
            ],
        ];
    }
}
