@php use Illuminate\Support\Facades\Storage; @endphp
<div
    id="conversations"
    wire:poll="fetchNewConversations"
    style="height: 65vh;overflow: auto;padding: 0 32px 64px"
>
    @foreach($conversations as $conversation)
        <div
            style="margin-bottom: 8px; align-items: start; display: flex; flex-direction: {{ $conversation->sender === 'admin' ? 'row-reverse' : 'row' }};">
            <img class="rounded-full"
                 style="width: 2rem; "
                 src="https://ui-avatars.com/api/?&length=1&name={{$conversation->sender}}&size=64" alt=""/>
            <div>
                @if($conversation->message)
                    <div
                        style="font-size: 14px; margin: 0 8px 8px; background-color: {{ $conversation->sender === 'user' ? '#e2c4ff' : '#a855f7' }}; color: {{ $conversation->sender === 'user' ? '#000000' : '#FFFFFF' }}; padding: 0.75rem; border-radius: 0.5rem; display: inline-block;">
                        {{$conversation->message}}
                    </div>
                @endif
                @if($conversation->attachments)
                    <div style="max-width: 150px">
                        @foreach($conversation->attachments as $attachment)
                            <a href="{{ Storage::url($attachment) }}" target="_blank"
                               style="font-size: 14px; margin: 0 8px 8px; background-color: {{ $conversation->sender === 'user' ? '#e2c4ff' : '#a855f7' }}; color: {{ $conversation->sender === 'user' ? '#000000' : '#FFFFFF' }}; border-radius: 0.5rem; display: inline-block;">
                                <img src="{{Storage::url($attachment)}}" alt="attachment">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@script
<script>
    setTimeout(() => {
        const conversations = document.getElementById('conversations');
        conversations.scrollTo({
            top: conversations.scrollHeight,
        });
    }, 500);

    $wire.on('conversationAdded', () => {
        const conversations = document.getElementById('conversations');
        conversations.scrollTo({
            top: conversations.scrollHeight,
            behavior: 'smooth'
        });
    });
</script>
@endscript
