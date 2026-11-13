<div class="container py-4">
    <h1>Live Chat Inbox</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="card mb-4">
        <div class="card-body">
            <h2 class="h5">AI Provider Settings</h2>
            <form method="POST" action="{{ route('livechat.admin.settings.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="ai_provider">AI Provider</label>
                    <select class="form-select" id="ai_provider" name="ai_provider" required>
                        <option value="gemini" @selected($aiProvider === 'gemini')>Google Gemini</option>
                        <option value="openai" @selected($aiProvider === 'openai')>OpenAI</option>
                        <option value="custom" @selected($aiProvider === 'custom')>Custom Gateway</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="ai_model">AI Model</label>
                    <input class="form-control" id="ai_model" name="ai_model" value="{{ $aiModel }}" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="api_key">API Key</label>
                    <input class="form-control" id="api_key" name="api_key" type="password" maxlength="1000" autocomplete="new-password">
                    <small class="text-muted">{{ $hasApiKey ? 'An API key is saved. Leave blank to keep it.' : 'No API key saved yet.' }}</small>
                </div>
                <button class="btn btn-primary" type="submit">Save AI Settings</button>
            </form>
        </div>
    </section>

    @forelse ($conversations as $conversation)
        <article class="card mb-3">
            <div class="card-body">
                <h2 class="h5">{{ $conversation->customer_name ?: 'Visitor' }}</h2>
                <p class="text-muted">{{ $conversation->customer_phone }}</p>
                @foreach ($conversation->messages as $message)
                    <p><strong>{{ ucfirst($message->sender_type) }}:</strong> {{ $message->message }}</p>
                @endforeach
            </div>
        </article>
    @empty
        <p>No conversations yet.</p>
    @endforelse

    {{ $conversations->links() }}
</div>
