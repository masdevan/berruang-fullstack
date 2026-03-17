export interface UserInterface {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    status?: 'online' | 'offline' | 'away';
    lastMessage?: string;
    lastMessageTime?: string;
    unreadCount?: number;
}

