import { useState } from 'react';
import type { UserInterface } from '../../interface/UserInterface';
import SearchMenu from './partials/SearchMenu';
import { ProfileAvatar } from './partials';
import { EllipsisVerticalIcon, UserCircleIcon, ArrowRightOnRectangleIcon } from '@heroicons/react/24/outline';

interface UserListProps {
    users: UserInterface[];
    selectedUserId?: number;
    onSelectUser: (userId: number) => void;
    searchQuery: string;
    onSearchChange: (query: string) => void;
}

export default function UserList({
    users,
    selectedUserId,
    onSelectUser,
    searchQuery,
    onSearchChange,
}: UserListProps) {
    const [showMenu, setShowMenu] = useState(false);

    const filteredUsers = users.filter(
        (user) =>
            user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <aside className="w-80 flex flex-col bg-[#090909] border-r border-[#111111] relative">
            <div className="h-[60px] flex items-center px-2 border-b border-[#111111]">
                <SearchMenu
                    searchQuery={searchQuery}
                    onSearchChange={onSearchChange}
                />
                <div className="relative">
                    <button
                        className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer"
                        onClick={() => setShowMenu(!showMenu)}
                    >
                        <EllipsisVerticalIcon className="w-5 h-5" />
                    </button>
                    {showMenu && (
                        <div className='absolute right-0 top-full mt-2 z-50 w-40'>
                            <div className="bg-[#111111] mb-1 border border-[#222222] rounded-lg shadow-xl overflow-hidden">
                                <button className="w-full px-3 py-2 text-left text-white text-sm hover:bg-[#222222] flex items-center gap-2 cursor-pointer">
                                    <UserCircleIcon className="w-4 h-4" />
                                    Edit Profile
                                </button>
                            </div>
                            <div className="bg-[#111111] border border-[#222222] rounded-lg shadow-xl overflow-hidden">
                                <button className="w-full px-3 py-2 text-left text-white text-sm hover:bg-[#222222] flex items-center gap-2 cursor-pointer">
                                    <ArrowRightOnRectangleIcon className="w-4 h-4" />
                                    Logout
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
            {showMenu && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setShowMenu(false)} />
                </>
            )}

            <div className="flex-1 overflow-y-auto custom-scrollbar">
                {filteredUsers.map((user) => (
                    <div
                        key={user.id}
                        onClick={() => onSelectUser(user.id)}
                        className={`flex items-center px-3 py-2 cursor-pointer hover:bg-[#111111] transition-colors ${
                            selectedUserId === user.id
                                ? 'bg-[#111111]'
                                : ''
                        }`}
                    >
                        <ProfileAvatar
                            name={user.name}
                            avatar={user.avatar}
                            status={user.status}
                            size="md"
                            showStatus
                        />

                        <div className="ml-2 flex-1 min-w-0">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-white truncate">
                                    {user.name}
                                </p>
                                {user.lastMessageTime && (
                                    <span className="text-xs text-gray-500">
                                        {user.lastMessageTime}
                                    </span>
                                )}
                            </div>
                            <div className="flex items-center justify-between">
                                <p className="text-xs text-gray-500 truncate">
                                    {user.lastMessage || 'No messages yet'}
                                </p>
                                {typeof user.unreadCount === 'number' && user.unreadCount > 0 && (
                                    <span className="flex-shrink-0 ml-2 w-5 h-5 flex items-center justify-center text-xs font-medium text-white bg-yellow-700 rounded-full">
                                        {user.unreadCount}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </aside>
    );
}
