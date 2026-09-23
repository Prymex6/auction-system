<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Nowy błąd na produkcji</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:640px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:20px;color:#991b1b;margin:0 0 16px;">Nowy błąd na produkcji</h1>

      <p style="color:#111827;font-size:15px;font-weight:bold;margin:0 0 4px;">{{ $errorLog->exception_class }}</p>
      <p style="color:#374151;font-size:14px;line-height:1.5;margin:0 0 16px;">{{ $errorLog->message }}</p>

      <table style="width:100%;font-size:13px;color:#4b5563;border-collapse:collapse;">
        <tr><td style="padding:4px 0;width:100px;"><strong>Plik</strong></td><td>{{ $errorLog->file }}:{{ $errorLog->line }}</td></tr>
        <tr><td style="padding:4px 0;"><strong>URL</strong></td><td>{{ $errorLog->url }}</td></tr>
        <tr><td style="padding:4px 0;"><strong>Metoda</strong></td><td>{{ $errorLog->method }}</td></tr>
      </table>

      <p style="color:#6b7280;font-size:13px;line-height:1.6;margin-top:24px;">
        Pełny stack trace i historia wystąpień dostępne w panelu admina, zakładka „Błędy”.
      </p>
    </div>
  </div>
</body>
</html>
