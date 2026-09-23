<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Potwierdź swój adres e-mail</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">Potwierdź swój adres e-mail</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">Cześć {{ $user->name }},</p>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Dziękujemy za rejestrację na platformie <strong>Gołębiowy Lot</strong>! Zanim Twoje konto zostanie zweryfikowane przez administratora, potwierdź proszę swój adres e-mail.
      </p>

      <div style="text-align:center;margin:28px 0;">
        <a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px;">
          Potwierdź adres e-mail
        </a>
      </div>

      <p style="color:#6b7280;font-size:13px;line-height:1.6;">
        Link jest ważny przez 60 minut. Jeśli nie zakładałeś/aś konta na naszej platformie, zignoruj tę wiadomość.
      </p>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }}
      </p>
    </div>
  </div>
</body>
</html>
