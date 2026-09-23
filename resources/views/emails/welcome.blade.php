<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Witamy w Gołębiowy Lot</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">Witamy w Gołębiowy Lot! 🐦</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">Cześć {{ $user->name }},</p>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Dziękujemy za rejestrację na platformie <strong>Gołębiowy Lot</strong> — najlepszym miejscu do aukcji gołębi pocztowych!
      </p>

      <h3 style="color:#111827;font-size:16px;margin:24px 0 8px;">Co możesz zrobić:</h3>
      <ul style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;margin:0;">
        <li><strong>Przeglądaj aukcje</strong> — odkrywaj oferty od sprzedawców</li>
        <li><strong>Licytuj</strong> — rywalizuj z innymi na aukcjach</li>
        <li><strong>Sprzedawaj</strong> — wystawiaj swoje gołębie i akcesoria</li>
        <li><strong>Komunikuj się</strong> — pisz wiadomości do innych użytkowników</li>
        <li><strong>Obserwuj</strong> — śledź interesujące Cię aukcje</li>
      </ul>

      <h3 style="color:#111827;font-size:16px;margin:24px 0 8px;">Pamiętaj:</h3>
      <ul style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;margin:0;">
        <li>Ustaw 2FA dla bezpieczeństwa konta</li>
        <li>Przeczytaj warunki naszej platformy</li>
        <li>Szanuj innych użytkowników</li>
      </ul>

      <div style="text-align:center;margin:28px 0;">
        <a href="{{ config('app.url') }}/login" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px;">
          Zaloguj się do konta
        </a>
      </div>

      <p style="color:#6b7280;font-size:13px;line-height:1.6;">Jeśli masz pytania, skontaktuj się z naszym wsparciem!</p>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }} Team
      </p>
    </div>
  </div>
</body>
</html>
