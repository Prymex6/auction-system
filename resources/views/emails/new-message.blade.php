<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Nowa wiadomość</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px;">
    <div style="background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
      <h1 style="font-size:22px;color:#111827;margin:0 0 16px;">💬 Nowa wiadomość od {{ $sender->name }}</h1>

      <p style="color:#374151;font-size:15px;line-height:1.6;">Cześć {{ $message->recipient->name }},</p>

      <p style="color:#374151;font-size:15px;line-height:1.6;">
        Otrzymałeś nową wiadomość od <strong>{{ $sender->name }}</strong>
      </p>

      <h3 style="color:#111827;font-size:16px;margin:20px 0 8px;">Wiadomość:</h3>
      <blockquote style="margin:0;padding:12px 16px;border-left:4px solid #e5e7eb;color:#4b5563;font-size:15px;line-height:1.6;">
        {{ \Illuminate\Support\Str::limit($message->content, 200) }}
      </blockquote>

      <div style="text-align:center;margin:28px 0;">
        <a href="{{ config('app.url') }}/messages/user/{{ $sender->id }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:10px;">
          Odpowiedz na wiadomość
        </a>
      </div>

      <p style="color:#374151;font-size:15px;margin-top:24px;">
        Pozdrawiamy,<br>{{ config('app.name') }} Team
      </p>
    </div>
  </div>
</body>
</html>
