<div class="container py-4">
    <h1>Live Chat Inbox</h1>

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
