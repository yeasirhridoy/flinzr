<div
    id="conversations"
    class="h-[65vh] overflow-auto pb-12 px-4"
>
    @foreach($conversations as $conversation)
        <div @class(["flex my-1 items-start gap-2", "flex-row-reverse"=>$conversation->sender==='admin'])>
            <img class="rounded-full size-6"
                 src="https://ui-avatars.com/api/?&length=1&name={{$conversation->sender}}&size=64" alt=""/>
            <div @class(["p-3 rounded-lg text-[14px] inline-block", "bg-blue-200 text-black"=> $conversation->sender === 'user', "bg-primary-500"=> $conversation->sender === 'admin'])>
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
