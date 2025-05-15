<div>
    <style>
        .asad::placeholder {
            color: green !important;
            font-weight: bold;
        }

        audio {
            max-width: 100%;
            height: 40px;
            outline: none;
        }

        audio::-webkit-media-controls-panel {
            background-color: #e5e7eb;
            border-radius: 0.375rem;
        }

        audio::-webkit-media-controls-play-button,
        audio::-webkit-media-controls-mute-button {
            background-color: #4f46e5;
            border-radius: 50%;
        }

        audio::-webkit-media-controls-current-time-display,
        audio::-webkit-media-controls-time-remaining-display {
            color: #111827;
            font-size: 0.75rem;
        }

        .recording-indicator {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                    <div id="chat-container" x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                         class="max-h-[500px] overflow-y-auto pr-2">
                        @foreach ($messages as $message)
                            @if ($message['sender_id'] !== auth()->id())
                                {{-- Receiver --}}
                                <div class="grid pb-11">
                                    <div class="flex gap-2.5 mb-4">
                                        <img src="https://pagedone.io/asset/uploads/1710412177.png" alt="User image" class="w-10 h-11">
                                        <div class="grid">
                                            <h5 class="text-gray-900 text-sm font-semibold leading-snug pb-1">
                                                {{ $message['sender']['name'] }}
                                            </h5>
                                            <div class="w-max grid">
                                                <div class="px-3.5 py-2 bg-gray-100 rounded justify-start items-center gap-3 inline-flex flex-col">
                                                    {{-- Check if audio exists --}}
                                                    @if (!empty($message['audio_path']))
                                                        <audio controls>
                                                            <source src="{{ asset('storage/' . $message['audio_path']) }}" type="audio/wav">
                                                            Your browser does not support the audio element.
                                                        </audio>

                                                    {{-- Else check if a file exists --}}
                                                    @elseif (!empty($message['file_path']))
                                                        @php
                                                            $fileType = pathinfo($message['file_path'], PATHINFO_EXTENSION);
                                                            $isImage = in_array(strtolower($fileType), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                        @endphp

                                                        {{-- Display image or file download --}}
                                                        @if ($isImage)
                                                            <img src="{{ asset('storage/' . $message['file_path']) }}"
                                                                 alt="Image"
                                                                 class="max-w-xs rounded shadow">
                                                        @else
                                                            <a href="{{ asset('storage/' . $message['file_path']) }}"
                                                               target="_blank"
                                                               class="text-blue-600 underline hover:text-blue-800 text-sm">
                                                                📎 Download File
                                                            </a>
                                                        @endif
                                                    @endif

                                                    {{-- Display text message if it exists --}}
                                                    @if (!empty($message['message']))
                                                        <h5 class="text-gray-900 text-sm font-normal leading-snug">
                                                            {{ $message['message'] }}
                                                        </h5>
                                                    @endif
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
                                            <h5 class="text-right text-gray-900 text-sm font-semibold leading-snug pb-1">You</h5>
                                            <div class="px-3 py-2 bg-indigo-600 rounded">
                                                @if (isset($message['audio_path']) && $message['audio_path'])
                                                    <audio controls>
                                                        <source src="{{ asset('storage/' . $message['audio_path']) }}" type="audio/wav">
                                                        Your browser does not support audio messages.
                                                    </audio>
                                                @elseif (isset($message['file_path']) && $message['file_path'])
                                                    {{-- Your existing file display --}}
                                                @else
                                                    <h2 class="text-white text-sm font-normal leading-snug">
                                                        {{ $message['message'] }}
                                                    </h2>
                                                @endif
                                            </div>
                                            <div class="justify-start items-center inline-flex">
                                                <h3 class="text-gray-500 text-xs font-normal leading-4 py-1">
                                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <img src="https://pagedone.io/asset/uploads/1704091591.png" alt="Your image" class="w-10 h-11">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Input form --}}
                    <form wire:submit.prevent="sendMessage" wire:ignore
                        class="w-full mt-4 pl-3 pr-1 py-1 rounded-3xl border border-gray-200 items-center gap-2 inline-flex justify-between">
                        <div class="flex items-center gap-2 w-full">
                            <!-- Voice message button -->
                            <button type="button"
                                @mousedown="$wire.startRecording()"
                                @touchstart="$wire.startRecording()"
                                @mouseup="$wire.stopRecording()"
                                @touchend="$wire.stopRecording()"
                                @mouseleave="$wire.stopRecording()"
                                class="p-2 text-gray-600 hover:text-blue-600 focus:outline-none"
                                :class="{ 'text-red-600 recording-indicator': $wire.isRecording }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                                </svg>
                            </button>

                            <!-- Audio preview -->
                            <template x-if="$wire.audioBlobUrl">
                                <div class="flex items-center gap-2">
                                    <audio x-bind:src="$wire.audioBlobUrl" controls class="h-8"></audio>
                                    <button type="button" @click="$wire.clearRecording()" class="text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <!-- Text input -->
                            <input id="typeHere" wire:keydown='userTyping' wire:model.live.debounce.250ms="message"
                                wire:key="message-input-{{ now()->timestamp }}"
                                class="rounded grow shrink basis-0 text-black text-xs font-medium leading-4 focus:outline-none"
                                placeholder="Type here...">
                        </div>

                        <!-- File upload button -->
                        <label for="fileChat">
                            <input wire:model="file" type="file" class="hidden" name="file" id="fileChat">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m0-3-3-3m0 0-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                            </svg>
                        </label>

                        <!-- Send button -->
                        <button type="submit" class="items-center flex px-3 py-2 bg-blue-600 rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M9.04071 6.959L6.54227 9.45744M6.89902 10.0724L7.03391 10.3054C8.31034 12.5102 8.94855 13.6125 9.80584 13.5252C10.6631 13.4379 11.0659 12.2295 11.8715 9.81261L13.0272 6.34566C13.7631 4.13794 14.1311 3.03408 13.5484 2.45139C12.9657 1.8687 11.8618 2.23666 9.65409 2.97257L6.18714 4.12822C3.77029 4.93383 2.56187 5.33664 2.47454 6.19392C2.38721 7.0512 3.48957 7.68941 5.69431 8.96584L5.92731 9.10074C6.23326 9.27786 6.38623 9.36643 6.50978 9.48998C6.63333 9.61352 6.72189 9.7665 6.89902 10.0724Z" stroke="white" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                            <h3 class="text-white text-xs font-semibold leading-4 px-2">Send</h3>
                        </button>
                    </form>

                    {{-- File preview section --}}
                    @if ($file)
                        @php
                            $mimeType = $file->getMimeType();
                            $isImage = str_starts_with($mimeType, 'image/');
                            $fileName = $file->getClientOriginalName();
                        @endphp

                        <div class="mt-2 p-2 bg-gray-100 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if ($isImage)
                                        <img src="{{ $file->temporaryUrl() }}"
                                            class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow"
                                            alt="Image preview">
                                        <span class="text-sm text-gray-700">{{ $fileName }}</span>
                                    @else
                                        <div class="mt-1 text-xs text-gray-500">
                                            <span class="text-blue-600 hover:underline">
                                                {{ $fileName }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" wire:click="$set('file', null)"
                                    class="text-red-500 hover:text-red-700 font-bold">
                                    ×
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script type="module">
    document.addEventListener('livewire:initialized', () => {
        // Voice recording functionality
        let mediaRecorder;
        let audioChunks = [];

        Livewire.on('start-recording', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                    const reader = new FileReader();

                    reader.onloadend = () => {
                        const base64data = reader.result.split(',')[1];
                        Livewire.dispatch('voice-recorded', { audioBlob: base64data });
                    };

                    reader.readAsDataURL(audioBlob);

                    // Stop all tracks in the stream
                    stream.getTracks().forEach(track => track.stop());
                };

                mediaRecorder.start();
            } catch (error) {
                console.error('Error accessing microphone:', error);
                alert('Could not access microphone. Please check permissions.');
            }
        });

        Livewire.on('stop-recording', () => {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
        });

        // Existing chat functionality
        let typingTimeOut = null;

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
                        messageContainer.placeholder = 'Type here...';
                    }
                }, 2000)
            }).listen('MessageSendEvent', (e) => {
                const audio = new Audio('{{ asset('sounds/sound.mp3') }}');
                audio.play();
            });

        Livewire.on('message-sent', () => {
            let input = document.getElementById('typeHere');
            if (input) input.value = '';
        });

        Livewire.hook('message.processed', (component) => {
            if (component.serverMemo.data.messages) {
                setTimeout(() => {
                    let chatContainer = document.getElementById('chat-container');
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                }, 100);
            }
        });

        Livewire.on('message-load-send', () => {
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
    });
</script>
@endscript
