import { EllipsisVerticalIcon, MagnifyingGlassIcon, ChevronDoubleRightIcon, ChevronDoubleLeftIcon } from '@heroicons/react/24/outline';
import type { UserInterface } from '../../../interface/UserInterface';
import ProfileAvatar from './ProfileAvatar';

interface ChatHeaderProps {
    user: UserInterface;
    onToggleDetail?: () => void;
    showDetail?: boolean;
}

export default function ChatHeader({ user, onToggleDetail, showDetail }: ChatHeaderProps) {
    return (
        <div className="h-[60px] flex items-center justify-between px-4 border-b border-[#111111]">
            <div className="flex items-center">
                <ProfileAvatar
                    name={user.name}
                    avatar={user.avatar}
                    status={user.status}
                    size="md"
                    showStatus
                />
                <div className="ml-3">
                    <p className="text-sm font-medium text-white">
                        {user.name}
                    </p>
                    <p className="text-xs text-gray-500">
                        {user.status === 'online'
                            ? 'Online'
                            : user.status === 'away'
                            ? 'Away'
                            : 'Offline'}
                    </p>
                </div>
            </div>
            <div className="flex items-center gap-1">
                <button className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer">
                    <MagnifyingGlassIcon className="w-5 h-5" />
                </button>
                <button
                    onClick={onToggleDetail}
                    className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer"
                    title={showDetail ? 'Hide details' : 'Show details'}
                >
                    {showDetail ? (
                        <ChevronDoubleRightIcon className="w-5 h-5" />
                    ) : (
                        <ChevronDoubleLeftIcon className="w-5 h-5" />
                    )}
                </button>
            </div>
        </div>
    );
}
