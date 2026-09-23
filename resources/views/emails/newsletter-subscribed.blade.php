<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Zapisano do newslettera</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">Witaj w newsletterze!</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Zapisałeś/aś adres <strong>{{ $email }}</strong> do newslettera platformy <strong>Gołębiowy Lot</strong>. Będziemy informować Cię o nowych aukcjach i wiadomościach z serwisu.
      </p>

      <p style="color:#6b7280;font-size:13px;line-height:1.6;margin-top:24px;">
        Jeśli nie chcesz już otrzymywać wiadomości, możesz się wypisać w każdej chwili:
        <a href="{{ $url }}" style="color:#2563eb;">wypisz mnie z newslettera</a>.
      </p>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }}
      </p>
    </div>
  </div>
</body>
</html>
