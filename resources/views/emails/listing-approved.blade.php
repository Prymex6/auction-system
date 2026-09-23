@component('mail::message')

Cześć {{ $listing->user->name }},

Miło nam poinformować, że Twoja oferta **{{ $listing->title }}** została zaakceptowana przez naszą redakcję!

## Szczegóły oferty:
- **Tytuł:** {{ $listing->title }}
- **Kategoria:** {{ $listing->category->name }}
- **Rasa:** {{ $listing->breed }}
- **Rocznik:** {{ $listing->year ?? '—' }}
- **Płeć:** {{ $listing->gender ?? '—' }}

## Co teraz?
Twoja oferta jest teraz widoczna dla wszystkich użytkowników. Możesz ją edytować lub usunąć z Twojego panelu.

Licytacje mogą się teraz zacząć! Powiadomimy Cię o każdej nowej licytacji.

@component('mail::button', ['url' => config('app.url') . '/listings/' . $listing->id])
Przejdź do oferty
@endcomponent

Powodzenia!<br>
{{ config('app.name') }} Team
@endcomponent
