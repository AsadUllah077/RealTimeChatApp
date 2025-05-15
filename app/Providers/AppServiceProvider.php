<?php

namespace App\Providers;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        Broadcast::channel('chat.{userId}', function ($user, $userId) {
            return (int) $user->id === (int) $userId;
        });

        Broadcast::channel('group-chat.{groupId}', function ($user, $groupId) {
            return \App\Models\GroupChatUser::where('group_chat_id', $groupId)
                ->where('user_id', $user->id)
                ->exists();
        });
    }
}
