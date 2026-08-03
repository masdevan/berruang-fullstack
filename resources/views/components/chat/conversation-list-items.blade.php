@props(['users' => [], 'meta' => [], 'drafts' => []])

@foreach ($users as $user)
    @php
        $customName = ($user->pivot?->first_name || $user->pivot?->last_name)
            ? trim(($user->pivot?->first_name ?? '').' '.($user->pivot?->last_name ?? ''))
            : null;
        $itemMeta = $meta[$user->id] ?? [];
    @endphp
    <x-chat.conversation-item
        :name="$customName ?: $user->name"
        :avatar="$user->avatar ? $user->avatarUrl(36) : $user->initials()"
        :avatar-full="$user->avatar ? $user->avatarFullUrl() : ''"
        :has-avatar="$user->avatar ? '1' : '0'"
        :custom="$customName ? '1' : '0'"
        :last-message="$itemMeta['last'] ?? ($user->bio ?: '@'.$user->username)"
        :time="$itemMeta['time'] ?? ''"
        :unread="$itemMeta['unread'] ?? 0"
        :last-sent="$itemMeta['sent'] ?? false"
        :last-read="$itemMeta['read'] ?? false"
        :about="$user->bio"
        :real-name="$user->name"
        :draft="$drafts[$user->username] ?? ''"
        :username="$user->username"
        :custom-name="$customName"
        :user-id="$user->id" />
@endforeach
