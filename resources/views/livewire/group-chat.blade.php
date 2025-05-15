<div>
    <style>
        /* Your existing styles remain the same */
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
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .reaction {
            font-size: 0.75rem;
            background: white;
            border-radius: 10px;
            padding: 0 4px;
            border: 1px solid #e5e7eb;
            position: absolute;
            bottom: -8px;
            right: 5px;
        }

        .reaction-picker {
            position: absolute;
            bottom: 40px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 5px;
            display: none;
            /* Hidden by default */
            flex-wrap: wrap;
            gap: 5px;
            z-index: 10;
            width: 150px;
            transition: opacity 0.2s, transform 0.2s;
            opacity: 0;
            transform: translateY(10px);
        }

        .reaction-picker[style*="display: block"] {
            opacity: 1;
            transform: translateY(0);
        }

        [x-cloak] {
            display: none !important;
        }

        .reaction-picker {
            /* existing styles */
            transition: all 0.2s ease;
        }

        .reaction-emoji {
            cursor: pointer;
            font-size: 1.2rem;
            padding: 3px;
            border-radius: 50%;
            transition: transform 0.2s;
        }

        .reaction-emoji {
            cursor: pointer;
            font-size: 1.2rem;
            padding: 3px;
            border-radius: 50%;
        }

        .reaction-emoji:hover {
            background: #f3f4f6;
            transform: scale(1.2);
        }

        .file-container {
            max-width: 200px;
            padding: 8px;
            background: #f3f4f6;
            border-radius: 8px;
            margin-top: 4px;
        }

        .file-preview {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
        }

        .file-download {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.875rem;
            margin-top: 4px;
        }

        .file-download:hover {
            text-decoration: underline;
        }
    </style>

    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $group->name }}
            </h2>
            <span class="ml-2 text-sm text-gray-500">
                ({{ $group->members->count() }} members)
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                <div class="w-full">
                    <!-- Group members list -->
                    @foreach ($group->members as $member)
                        <div class="flex flex-col items-center">
                            <div class="relative">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($member->user->name) }}&background=random"
                                    alt="{{ $member->user->name }}"
                                    class="w-10 h-10 rounded-full border-2
                                 {{ $member->is_admin ? 'border-yellow-400' : 'border-gray-200' }}">
                                @if ($member->is_admin)
                                    <span class="absolute -top-1 -right-1 bg-yellow-400 rounded-full p-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs mt-1">{{ $member->user->name }}</span>
                        </div>
                    @endforeach

                    <!-- Typing indicator -->
                    @if (count($typingUsers) > 0)
                        <div class="text-sm text-gray-500 mb-2">
                            {{ implode(', ', $typingUsers) }} {{ count($typingUsers) > 1 ? 'are' : 'is' }} typing...
                        </div>
                    @endif

                    <div id="chat-container" x-data="{ selectedMessage: null }" x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                        class="max-h-[500px] overflow-y-auto pr-2">
                        @foreach ($messages as $message)
                            @if ($message['sender_id'] !== auth()->id())
                                <!-- Receiver Message -->
                                <div class="grid pb-11 relative">
                                    <div class="flex gap-2.5 mb-4">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($message['sender']['name']) }}&background=random"
                                            alt="User image" class="w-10 h-11 rounded-full">
                                        <div class="grid">
                                            <h5 class="text-gray-900 text-sm font-semibold leading-snug pb-1">
                                                {{ $message['sender']['name'] }}
                                            </h5>
                                            <div class="w-max grid">
                                                <div class="px-3.5 py-2 bg-gray-100 rounded justify-start items-center gap-3 inline-flex relative"
                                                    @click.away="selectedMessage = null">
                                                    @if (isset($message['audio_path']) && $message['audio_path'])
                                                        <audio controls>
                                                            <source
                                                                src="{{ asset('storage/' . $message['audio_path']) }}"
                                                                type="audio/wav">
                                                            Your browser does not support audio messages.
                                                        </audio>
                                                    @elseif (isset($message['file_path']) && $message['file_path'])
                                                        <div class="file-container">
                                                            @if (isset($message['mime_type']) && Str::startsWith($message['mime_type'], 'image/'))
                                                                <img src="{{ asset('storage/' . $message['file_path']) }}"
                                                                    class="file-preview" alt="File preview">
                                                            @endif

                                                            <a href="{{ asset('storage/' . $message['file_path']) }}"
                                                                target="_blank" class="file-download">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                    fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ basename($message['file_path']) }}
                                                            </a>
                                                        </div>
                                                    @else
                                                        <h5 class="text-gray-900 text-sm font-normal leading-snug">
                                                            {{ $message['message'] }}
                                                        </h5>
                                                    @endif
                                                    <button onclick="toggleReactionPicker({{ $message['id'] }})"
                                                        class="absolute -bottom-4 right-0 text-gray-500 hover:text-gray-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                                        </svg>
                                                    </button>

                                                    <div id="reaction-picker-{{ $message['id'] }}"
                                                        class="reaction-picker" style="display: none;">
                                                        @foreach ($reactionTypes as $reaction)
                                                            <span class="reaction-emoji"
                                                                onclick="addReaction({{ $message['id'] }}, '{{ $reaction }}')">
                                                                {{ $reaction }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if (!empty($message['reactions']))
                                                    <div class="flex items-center gap-1 mt-1">
                                                        @foreach (collect($message['reactions'])->groupBy('reaction') as $reaction => $reactions)
                                                            <span class="reaction"
                                                                wire:click="addReaction({{ $message['id'] }}, '{{ $reaction }}')"
                                                                title="{{ collect($reactions)->pluck('user.name')->join(', ') }}">
                                                                {{ $reaction }}
                                                                @if (count($reactions) > 1)
                                                                    {{ count($reactions) }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                    <div class="justify-end items-center inline-flex mb-2.5">
                                                        <h6 class="text-gray-500 text-xs font-normal leading-4 py-1">
                                                            {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                        </h6>
                                                        @if (!empty($message['seen_by']) && collect($message['seen_by'])->pluck('user_id')->contains(Auth::id()))
                                                            <span class="ml-1 text-xs text-gray-400">Seen</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Sender Message -->
                                <div class="flex gap-2.5 justify-end pb-4 relative">
                                    <div class="">
                                        <div class="grid mb-2">
                                            <h5
                                                class="text-right text-gray-900 text-sm font-semibold leading-snug pb-1">
                                                You</h5>
                                            <div class="px-3 py-2 bg-indigo-600 rounded relative"
                                                @click.away="selectedMessage = null">
                                                @if (isset($message['audio_path']) && $message['audio_path'])
                                                    <audio controls>
                                                        <source src="{{ asset('storage/' . $message['audio_path']) }}"
                                                            type="audio/wav">
                                                        Your browser does not support audio messages.
                                                    </audio>
                                                @elseif (isset($message['file_path']) && $message['file_path'])
                                                    <div class="file-container">
                                                        @if (isset($message['mime_type']) && Str::startsWith($message['mime_type'], 'image/'))
                                                            <img src="{{ asset('storage/' . $message['file_path']) }}"
                                                                class="file-preview" alt="File preview">
                                                        @endif

                                                        <a href="{{ asset('storage/' . $message['file_path']) }}"
                                                            target="_blank" class="file-download">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            {{ basename($message['file_path']) }}
                                                        </a>
                                                    </div>
                                                @else
                                                    <h2 class="text-white text-sm font-normal leading-snug">
                                                        {{ $message['message'] }}
                                                    </h2>
                                                @endif
                                                <button onclick="toggleReactionPicker({{ $message['id'] }})"
                                                    class="absolute -bottom-4 right-0 text-gray-200 hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                                    </svg>
                                                </button>

                                                <div id="reaction-picker-{{ $message['id'] }}"
                                                    class="reaction-picker" style="display: none;">
                                                    @foreach ($reactionTypes as $reaction)
                                                        <span class="reaction-emoji"
                                                            onclick="addReaction({{ $message['id'] }}, '{{ $reaction }}')">
                                                            {{ $reaction }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 justify-end">
                                                @if (!empty($message['reactions']))
                                                <div class="flex items-center gap-1 mt-1">
                                                    @foreach (collect($message['reactions'])->groupBy('reaction') as $reaction => $reactions)
                                                        <span class="reaction"
                                                            wire:click="addReaction({{ $message['id'] }}, '{{ $reaction }}')"
                                                            title="{{ collect($reactions)->pluck('user.name')->join(', ') }}">
                                                            {{ $reaction }}
                                                            @if (count($reactions) > 1)
                                                                {{ count($reactions) }}
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                                <div class="justify-start items-center inline-flex">
                                                    <h3 class="text-gray-500 text-xs font-normal leading-4 py-1">
                                                        {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                    </h3>
                                                    @if (!empty($message['seen_by']) && count($message['seen_by']) > 0)
                                                        <span class="ml-1 text-xs text-gray-400">
                                                            Seen by {{ count($message['seen_by']) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random"
                                        alt="Your image" class="w-10 h-11 rounded-full">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Input form -->
                    <form wire:submit.prevent="sendMessage" wire:ignore
                        class="w-full mt-4 pl-3 pr-1 py-1 rounded-3xl border border-gray-200 items-center gap-2 inline-flex justify-between">
                        <div class="flex items-center gap-2 w-full">
                            <!-- Voice message button -->
                            <button type="button" @mousedown="$wire.startRecording()"
                                @touchstart="$wire.startRecording()" @mouseup="$wire.stopRecording()"
                                @touchend="$wire.stopRecording()" @mouseleave="$wire.stopRecording()"
                                class="p-2 text-gray-600 hover:text-blue-600 focus:outline-none"
                                :class="{ 'text-red-600 recording-indicator': $wire.isRecording }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                                </svg>
                            </button>

                            <!-- Audio preview -->
                            <template x-if="$wire.audioBlobUrl">
                                <div class="flex items-center gap-2">
                                    <audio x-bind:src="$wire.audioBlobUrl" controls class="h-8"></audio>
                                    <button type="button" @click="$wire.clearRecording()" class="text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6 cursor-pointer">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m0-3-3-3m0 0-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                            </svg>
                        </label>

                        <!-- Send button -->
                        <button type="submit" class="items-center flex px-3 py-2 bg-blue-600 rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 16 16" fill="none">
                                <path
                                    d="M9.04071 6.959L6.54227 9.45744M6.89902 10.0724L7.03391 10.3054C8.31034 12.5102 8.94855 13.6125 9.80584 13.5252C10.6631 13.4379 11.0659 12.2295 11.8715 9.81261L13.0272 6.34566C13.7631 4.13794 14.1311 3.03408 13.5484 2.45139C12.9657 1.8687 11.8618 2.23666 9.65409 2.97257L6.18714 4.12822C3.77029 4.93383 2.56187 5.33664 2.47454 6.19392C2.38721 7.0512 3.48957 7.68941 5.69431 8.96584L5.92731 9.10074C6.23326 9.27786 6.38623 9.36643 6.50978 9.48998C6.63333 9.61352 6.72189 9.7665 6.89902 10.0724Z"
                                    stroke="white" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                            <h3 class="text-white text-xs font-semibold leading-4 px-2">Send</h3>
                        </button>
                    </form>

                    <!-- File preview section -->
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
                                    @endif
                                    <span class="text-sm text-gray-700">{{ $fileName }}</span>
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
<script>
    let currentOpenPicker = null;

    // Toggle reaction picker visibility (make this global)
    window.toggleReactionPicker = function(messageId) {
    const pickerId = `reaction-picker-${messageId}`;
    const picker = document.getElementById(pickerId);

    if (window.currentOpenPicker && window.currentOpenPicker !== picker) {
        window.currentOpenPicker.style.display = 'none';
    }

    if (picker.style.display === 'block') {
        picker.style.display = 'none';
        window.currentOpenPicker = null;
    } else {
        picker.style.display = 'block';
        window.currentOpenPicker = picker;
    }
};

window.addReaction = function(messageId, reaction) {
    if (window.currentOpenPicker) {
        window.currentOpenPicker.style.display = 'none';
        window.currentOpenPicker = null;
    }

    // Directly call the Livewire component method
    Livewire.first().call('addReaction', messageId, reaction);
};

    document.addEventListener('click', function(event) {
        if (
            !event.target.closest('.reaction-picker') &&
            !event.target.closest('[onclick*="toggleReactionPicker"]')
        ) {
            if (currentOpenPicker) {
                currentOpenPicker.style.display = 'none';
                currentOpenPicker = null;
            }
        }
    });

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
                        Livewire.dispatch('voice-recorded', {
                            audioBlob: base64data
                        });
                    };

                    reader.readAsDataURL(audioBlob);
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

        // Typing indicator functionality
        Echo.private(`group-chat.${@js($groupId)}`)
            .listen('MessageTyping', (e) => {
                if (e.userId !== @js(auth()->id())) {
                    Livewire.dispatch('user-typing', {
                        userId: e.userId,
                        groupId: e.groupId
                    });
                }
            });

        // Message received functionality
        Echo.private(`group-chat.${@js($groupId)}`)
            .listen('GroupMessageSendEvent', (e) => {
                @this.dispatch('message-received', {
                    message: e.message
                });
            });

        // Message seen functionality
        Echo.private(`chat.${@js(auth()->id())}`)
            .listen('MessageSeenEvent', (e) => {
                @this.dispatch('message-received');
            });

        // Message reaction functionality
        Echo.private(`chat.${@js(auth()->id())}`)
            .listen('MessageReactionEvent', (e) => {
                @this.dispatch('message-received');
            });

        // Scroll to bottom when new message arrives
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
    });
</script>

@endscript
