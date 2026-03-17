export interface GroupMember {
    id: number;
    name: string;
    avatar?: string;
    status: 'online' | 'away' | 'offline';
}

export interface GroupInterface {
    id: number;
    name: string;
    avatar?: string;
    background?: string;
    tag?: string;
    description?: string;
    memberCount: number;
    members?: GroupMember[];
}
