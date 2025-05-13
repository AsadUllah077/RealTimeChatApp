<div>
    <style>
        .asad::placeholder {
            color: green !important;
            font-weight: bold;
        }
    </style>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                <div class="w-full">

                    {{-- Chat messages container --}}
                    <div id="chat-container" class="max-h-[500px] overflow-y-auto pr-2">

                        @foreach ($messages as $message)
                            @if ($message['sender_id'] !== auth()->id())
                                {{-- Receiver --}}
                                <div class="grid pb-11">
                                    <div class="flex gap-2.5 mb-4">
                                        <img src="https://pagedone.io/asset/uploads/1710412177.png" alt="User image"
                                            class="w-10 h-11">
                                        <div class="grid">
                                            <h5 class="text-gray-900 text-sm font-semibold leading-snug pb-1">
                                                {{ $message['sender']['name'] }}
                                            </h5>
                                            <div class="w-max grid">
                                                <div
                                                    class="px-3.5 py-2 bg-gray-100 rounded justify-start items-center gap-3 inline-flex">
                                                    <h5 class="text-gray-900 text-sm font-normal leading-snug">
                                                        {{ $message['message'] }}
                                                    </h5>
                                                </div>
                                                <div class="justify-end items-center inline-flex mb-2.5">
                                                    <h6 class="text-gray-500 text-xs font-normal leading-4 py-1">
                                                        {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Sender --}}
                                <div class="flex gap-2.5 justify-end pb-4">
                                    <div class="">
                                        <div class="grid mb-2">
                                            <h5
                                                class="text-right text-gray-900 text-sm font-semibold leading-snug pb-1">
                                                You</h5>
                                            <div class="px-3 py-2 bg-indigo-600 rounded">
                                                <h2 class="text-white text-sm font-normal leading-snug">
                                                    {{ $message['message'] }}
                                                </h2>
                                            </div>
                                            <div class="justify-start items-center inline-flex">
                                                <h3 class="text-gray-500 text-xs font-normal leading-4 py-1">
                                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <img src="https://pagedone.io/asset/uploads/1704091591.png" alt="Your image"
                                        class="w-10 h-11">
                                </div>
                            @endif
                        @endforeach

                    </div>

                    {{-- Input form --}}
                    <form wire:submit.prevent="sendMessage" wire:ignore
                        class="w-full mt-4 pl-3 pr-1 py-1 rounded-3xl border border-gray-200 items-center gap-2 inline-flex justify-between">
                        <div class="flex items-center gap-2 w-full">
                            <input id="typeHere" wire:keydown='userTyping' wire:model.live.debounce.250ms="message"
                                wire:key="message-input-{{ now()->timestamp }}"
                                class="rounded grow shrink basis-0 text-black text-xs font-medium leading-4 focus:outline-none"
                                placeholder="Type here...">
                        </div>
                        <button type="submit" class="items-center flex px-3 py-2 bg-blue-600 rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                fill="none">
                                <path
                                    d="M9.04071 6.959L6.54227 9.45744M6.89902 10.0724L7.03391 10.3054C8.31034 12.5102 8.94855 13.6125 9.80584 13.5252C10.6631 13.4379 11.0659 12.2295 11.8715 9.81261L13.0272 6.34566C13.7631 4.13794 14.1311 3.03408 13.5484 2.45139C12.9657 1.8687 11.8618 2.23666 9.65409 2.97257L6.18714 4.12822C3.77029 4.93383 2.56187 5.33664 2.47454 6.19392C2.38721 7.0512 3.48957 7.68941 5.69431 8.96584L5.92731 9.10074C6.23326 9.27786 6.38623 9.36643 6.50978 9.48998C6.63333 9.61352 6.72189 9.7665 6.89902 10.0724Z"
                                    stroke="white" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                            <h3 class="text-white text-xs font-semibold leading-4 px-2">Send</h3>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script type="module">
        let typingTimeOut = null;
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('message-sent', () => {
                let input = document.querySelector('[wire\\:model="message"]');
                if (input) input.value = '';
            });

            Livewire.on('message-load', () => {
                setTimeout(() => {
                    let chatContainer = document.getElementById('chat-container');
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                }, 100);
            });

            Echo.private(`chat-channel.{{ Auth::id() }}`)
                .listen('MessageSendEvent', (e) => {
                    @this.dispatch('message-received', {
                        message: e.message
                    });
                });


            Echo.private(`chat-channel.{{ $sender_id }}`)
                .listen('MessageTyping', (e) => {
                    let messageContainer = document.getElementById('typeHere');
                    if (messageContainer) {
                        messageContainer.placeholder = 'Typing.....';

                        messageContainer.classList.add('asad');
                    }

                    clearTimeout(typingTimeOut);
                    typingTimeOut = setTimeout(() => {
                        if (messageContainer) {
                            messageContainer.classList.remove('asad');
                            messageContainer.placeholder = 'Typing here..';
                        }
                    }, 2000)
                });
        });
    </script>
@endscript
