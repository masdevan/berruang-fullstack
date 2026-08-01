@props(['users' => [], 'onlineIds' => []])

@foreach ($users as $user)
    @php
        $customName = $user->pivot?->first_name
            ? trim($user->pivot->first_name.' '.($user->pivot->last_name ?? ''))
            : null;
    @endphp
    <x-chat.conversation-item
        :name="$customName ?: $user->name"
        :avatar="$user->initials()"
        :last-message="$user->bio ?: '@'.$user->username"
        :about="$user->bio"
        :real-name="$user->name"
        :username="$user->username"
        :custom-name="$customName"
        :online="in_array($user->id, $onlineIds)" />
@endforeach
