<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Helper</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .message { padding: 8px 12px; margin: 6px 0; border-radius: 6px; }
        .user { background: #eef6ff; border: 1px solid #cfe3ff; }
        .assistant { background: #f5f5f5; border: 1px solid #e3e3e3; }
        .meta { color: #666; font-size: 12px; margin-bottom: 4px; }
        form { display: flex; gap: 8px; margin-top: 16px; }
        textarea { width: 100%; height: 80px; padding: 8px; }
        button { padding: 8px 14px; }
        .header { display:flex; justify-content: space-between; align-items: center; }
        .hint { font-size: 12px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Chat Helper</h1>
        <div class="hint">All messages are public. No auth required.</div>
    </div>

    <div id="messages">
        @forelse($messages as $m)
            <div class="message {{ $m['role'] }}">
                <div class="meta">{{ ucfirst($m['role']) }} • {{ $m['time'] ?? '' }}</div>
                <div class="content">{{ $m['content'] }}</div>
            </div>
        @empty
            <p>No messages yet. Ask something about PHP/Web.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('chat-helper.send') }}">
        @csrf
        <textarea name="message" placeholder="Type your message..." required></textarea>
        <button type="submit">Send</button>
    </form>
</div>
</body>
</html>