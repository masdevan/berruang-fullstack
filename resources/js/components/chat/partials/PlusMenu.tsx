import { ChatBubbleLeftRightIcon, UserPlusIcon, UsersIcon } from '@heroicons/react/24/outline';

interface PlusMenuProps {
    showMenu: boolean;
    menuPosition: { top: number; left: number };
    onClose: () => void;
}

export default function PlusMenu({ showMenu, menuPosition, onClose }: PlusMenuProps) {
    if (!showMenu) return null;

    return (
        <>
            <div className="fixed inset-0 z-40" onClick={onClose} />
            <div
                className="fixed z-50 w-40"
                style={{
                    top: menuPosition.top,
                    left: menuPosition.left,
                }}
            >
                <button className="w-full bg-[#111111] border border-[#222222] rounded-lg shadow-xl overflow-hidden mb-2 cursor-pointer px-3 py-2 text-left text-white text-sm hover:bg-[#222222] flex items-center gap-2">
                    <ChatBubbleLeftRightIcon className="w-4 h-4" />
                    Add new user
                </button>
                <button className="w-full bg-[#111111] border border-[#222222] rounded-lg shadow-xl overflow-hidden mb-2 cursor-pointer px-3 py-2 text-left text-white text-sm hover:bg-[#222222] flex items-center gap-2">
                    <UserPlusIcon className="w-4 h-4" />
                    Join Group
                </button>
                <button className="w-full bg-[#111111] border border-[#222222] rounded-lg shadow-xl overflow-hidden px-3 cursor-pointer py-2 text-left text-white text-sm hover:bg-[#222222] flex items-center gap-2">
                    <UsersIcon className="w-4 h-4" />
                    Create group
                </button>
            </div>
        </>
    );
}
