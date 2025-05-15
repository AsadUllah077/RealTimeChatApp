<!-- resources/views/livewire/group-chat-list.blade.php -->
<div class="bg-white rounded-lg shadow p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Group Chats</h2>
        <div class="flex space-x-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search groups..."
                   class="px-3 py-2 border rounded-md text-sm">
            <button wire:click="$toggle('showCreateModal')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">
                New Group
            </button>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($groups as $group)
            <a href="{{ route('group.chat', $group->id) }}"
               class="block p-3 border rounded-lg hover:bg-gray-50 transition">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            {{ substr($group->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $group->name }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $group->members->count() }} members
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-gray-500 text-center py-4">No groups found</p>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Create New Group</h3>
                    <button wire:click="$toggle('showCreateModal')" class="text-gray-500 hover:text-gray-700">
                        &times;
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="groupName" class="block text-sm font-medium text-gray-700">Group Name</label>
                        <input wire:model="newGroup.name" type="text" id="groupName"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="groupDescription" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea wire:model="newGroup.description" id="groupDescription" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Add Members</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($users as $user)
                                <div class="flex items-center">
                                    <input wire:model="newGroup.members" value="{{ $user->id }}"
                                           type="checkbox" id="user-{{ $user->id }}"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="user-{{ $user->id }}" class="ml-2 block text-sm text-gray-900">
                                        {{ $user->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button wire:click="$toggle('showCreateModal')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button wire:click="createGroup"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Group
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
