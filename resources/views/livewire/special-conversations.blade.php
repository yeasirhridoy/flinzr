<div
    id="conversations"
    class="h-[65vh] overflow-auto pb-12"
>
    @foreach($conversations as $conversation)
        <div @class(["flex my-1 items-start gap-1", "flex-row-reverse"=>$conversation->sender==='admin'])>
            <img class="rounded-full size-10"
                 src="https://ui-avatars.com/api/?&length=1&name={{$conversation->sender}}&size=64" alt=""/>
            <div @class(["shadow p-2 rounded-lg inline-block", "bg-gray-500"=> $conversation->sender === 'user', "bg-primary-500"=> $conversation->sender === 'admin'])>
                {{$conversation->message}}
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
            // behavior: 'smooth'
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
