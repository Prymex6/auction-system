<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Twoje konto zostało zablokowane</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">🚫 Twoje konto zostało zablokowane</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">Cześć {{ $user->name }},</p>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Twoje konto na platformie {{ config('app.name') }} zostało zablokowane.
      </p>

      <h3 style="color:#111827;font-size:16px;margin:20px 0 4px;">Powód:</h3>
      <p style="color:#374151;font-size:15px;line-height:1.6;margin:0;">{{ $reason }}</p>

      <h3 style="color:#111827;font-size:16px;margin:20px 0 4px;">Czas blokady:</h3>
      <p style="color:#374151;font-size:15px;line-height:1.6;margin:0;">{{ $duration ?: 'Na zawsze' }}</p>

      <h3 style="color:#111827;font-size:16px;margin:24px 0 8px;">Co się teraz stanie?</h3>
      <ul style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;margin:0;">
        <li>Nie będziesz mógł logować się na swoje konto</li>
        <li>Twoje oferty i aukcje zostaną wstrzymane</li>
        <li>Dowiesz się, kiedy będziesz mógł powrócić</li>
      </ul>

      <p style="color:#374151;font-size:15px;line-height:1.6;margin-top:20px;">
        Jeśli uważasz, że było to błędem lub chciałbyś się odwołać, prosimy o kontakt z naszym wsparciem.
      </p>

      <div style="text-align:center;margin:28px 0;">
        <a href="{{ config('app.url') }}/support/appeal" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px;">
          Złóż odwołanie
        </a>
      </div>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }} Team
      </p>
    </div>
  </div>
</body>
</html>
