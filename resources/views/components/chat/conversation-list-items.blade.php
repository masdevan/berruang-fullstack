@props(['users' => [], 'onlineIds' => []])

@foreach ($users as $user)
    <x-chat.conversation-item
        name="{{ $user->name }}"
        avatar="{{ $user->initials() }}"
        last-message="{{ $user->bio ?: '@'.$user->username }}"
        about="{{ $user->bio }}"
        :online="in_array($user->id, $onlineIds)" />
@endforeach
