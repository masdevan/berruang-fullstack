import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { UserList, UserDetail, MessageField, GroupList } from '../chat';
import type { UserInterface } from '../../interface/UserInterface';
import type { MessageInterface } from '../../interface/MessageInterface';
import type { GroupInterface } from '../../interface/GroupInterface';

interface ChatLayoutProps {
    title?: string;
    users: UserInterface[];
    selectedUserId?: number;
    messages?: MessageInterface[];
    currentUserId?: number;
    groups?: GroupInterface[];
    selectedGroupId?: number;
}

export default function ChatLayout({
    title = 'Messages',
    users,
    selectedUserId,
    messages = [],
    currentUserId = 1,
    groups = [],
    selectedGroupId,
}: ChatLayoutProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedUser, setSelectedUser] = useState<UserInterface | undefined>(
        users.find((u) => u.id === selectedUserId)
    );
    const [activeGroupId, setActiveGroupId] = useState<number | undefined>(selectedGroupId);
    const [isDirectMessage, setIsDirectMessage] = useState<boolean>(true);
    const [showDetail, setShowDetail] = useState<boolean>(true);

    const handleSelectUser = (userId: number) => {
        const user = users.find((u) => u.id === userId);
        setSelectedUser(user);
        setIsDirectMessage(true);
        setActiveGroupId(undefined);
        setShowDetail(true);
    };

    const handleSelectGroup = (groupId: number) => {
        setActiveGroupId(groupId);
        setIsDirectMessage(false);
        setSelectedUser(undefined);
        setShowDetail(true);
    };

    const handleSelectDirectMessage = () => {
        setIsDirectMessage(true);
        setActiveGroupId(undefined);
    };

    const handleToggleDetail = () => {
        setShowDetail((prev) => !prev);
    };

    return (
        <>
            <Head title={title} />
            <div className="flex h-screen bg-[#090909]">
                <GroupList
                    groups={groups}
                    selectedGroupId={activeGroupId}
                    onSelectGroup={handleSelectGroup}
                    onSelectDirectMessage={handleSelectDirectMessage}
                    isDirectMessageSelected={isDirectMessage}
                />
                <UserList
                    users={users}
                    selectedUserId={selectedUser?.id}
                    onSelectUser={handleSelectUser}
                    searchQuery={searchQuery}
                    onSearchChange={setSearchQuery}
                />
                <MessageField
                    user={selectedUser}
                    messages={messages}
                    currentUserId={currentUserId}
                    onToggleDetail={handleToggleDetail}
                    showDetail={showDetail}
                />
                {showDetail && <UserDetail user={selectedUser} />}
            </div>
        </>
    );
}
