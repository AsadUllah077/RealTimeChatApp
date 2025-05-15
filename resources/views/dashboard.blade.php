<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        <a href="{{route('group-cht-ui')}}" class="bg-blue-500 py-3 px-2">Groups</a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-700">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 font-medium">#</th>
                                    <th class="px-4 py-2 font-medium">Name</th>
                                    <th class="px-4 py-2 font-medium">Email</th>
                                    <th class="px-4 py-2 font-medium">Chat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($users as $user)
                                <tr>
                                        <td class="px-4 py-2">{{ $loop->index + 1 }}</td>
                                        <td class="px-4 py-2">{{ $user->name }}</td>
                                        <td class="px-4 py-2">{{ $user->email }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('chat', $user->id) }}" class="relative inline-block">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                     class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                                </svg>

                                                <span id="readcount-{{$user->id}}" class="{{$user->unread_messages_count > 0  ? 'absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full' : ''}}">
                                                    {{ $user->unread_messages_count > 0 ? $user->unread_messages_count : '' }}
                                                </span>

                                            </a>
                                        </td>

                                    </tr>
                                    @endforeach

                                <!-- More rows... -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<script type="module">
     Echo.private(`unread-channel.{{ Auth::id() }}`)
                .listen('UnreadMessages', (e) => {
                    let messageBox = document.getElementById(`readcount-${e.senderId}`);
                    if (messageBox) {
                        console.log(e);
                        messageBox.textContent = e.count > 0 ? e.count :'';
                        e.count > 0 ?  messageBox.className = 'absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full' : '';
                    }
                    // console.log(e.count);
                    if(e.count > 0){
                        const audio = new Audio('{{asset('sounds/sound.mp3')}}');
                        audio.play();
                    }

                });


</script>
