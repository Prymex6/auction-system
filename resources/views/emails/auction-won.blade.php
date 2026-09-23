<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Wygrałeś aukcję!</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">🎉 Gratulacje! Wygrałeś aukcję!</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Wygrałeś aukcję <strong>{{ $auction->title }}</strong> za <strong>{{ $amount }} PLN</strong>
      </p>

      <h3 style="color:#111827;font-size:16px;margin:20px 0 8px;">Szczegóły aukcji:</h3>
      <ul style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;margin:0;">
        <li><strong>Lp.:</strong> {{ $auction->id }}</li>
        <li><strong>Przedmiot:</strong> {{ $auction->title }}</li>
        <li><strong>Cena zwycięzcy:</strong> {{ $amount }} PLN</li>
        <li><strong>Data zakończenia:</strong> {{ optional($auction->ends_at)->format('d.m.Y H:i') }}</li>
      </ul>

      <h3 style="color:#111827;font-size:16px;margin:20px 0 8px;">Następne kroki:</h3>
      <ol style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;margin:0;">
        <li>Sprawdź wiadomości od sprzedawcy {{ $auction->seller->name ?? '' }}</li>
        <li>Uzgodnij szczegóły wysyłki i płatności</li>
        <li>Wyślij płatność</li>
        <li>Odbierz przedmiot</li>
      </ol>

      <p style="color:#374151;font-size:15px;line-height:1.6;margin-top:20px;">
        Dziękujemy za uczestnictwo w naszej platformie!
      </p>

      <div style="text-align:center;margin:28px 0;">
        <a href="{{ config('app.url') }}/auctions/{{ $auction->id }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px;">
          Przejdź do aukcji
        </a>
      </div>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }} Team
      </p>
    </div>
  </div>
</body>
</html>
