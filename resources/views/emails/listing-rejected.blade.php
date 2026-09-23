@component('mail::message')

Cześć {{ $listing->user->name }},

Niestety, Twoja oferta **{{ $listing->title }}** została odrzucona przez naszą redakcję.

## Szczegóły odrzucenia:
**Powód:** {{ $reason }}

## Co możesz zrobić?
1. Przejrzyj wytyczne platformy
2. Popraw ofertę zgodnie z wytycznymi
3. Wyślij nową ofertę

Jeśli uważasz, że było to błędem, możesz skontaktować się z naszym wsparciem.

@component('mail::button', ['url' => config('app.url') . '/support'])
Skontaktuj się z wsparciem
@endcomponent

Pozdrawiamy,<br>
{{ config('app.name') }} Team
@endcomponent
