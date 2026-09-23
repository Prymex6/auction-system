<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>{{ $success ? 'Wypisano z newslettera' : 'Nieprawidłowy link' }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);text-align:center;">
      @if ($success)
        <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">Wypisano z newslettera</h1>
        <p style="color:#374151;font-size:15px;line-height:1.6;">
          Adres <strong>{{ $email }}</strong> został wypisany z newslettera platformy Gołębiowy Lot. Możesz zamknąć tę stronę.
        </p>
      @else
        <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">Nieprawidłowy link</h1>
        <p style="color:#374151;font-size:15px;line-height:1.6;">
          Ten link do wypisania z newslettera jest nieprawidłowy lub uszkodzony.
        </p>
      @endif
      <a href="{{ config('app.url') }}" style="display:inline-block;margin-top:16px;color:#2563eb;text-decoration:none;font-weight:bold;">
        Wróć na stronę główną
      </a>
    </div>
  </div>
</body>
</html>
