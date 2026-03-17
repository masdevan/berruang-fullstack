import { useState, useRef, useEffect } from 'react';
import { ChatBubbleLeftRightIcon, PlusIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import type { GroupInterface } from '../../interface/GroupInterface';
import { ProfileAvatar, GroupTooltip, PlusMenu } from './partials';

interface GroupListProps {
    groups: GroupInterface[];
    selectedGroupId?: number;
    onSelectGroup: (groupId: number) => void;
    onSelectDirectMessage?: () => void;
    isDirectMessageSelected?: boolean;
}

export default function GroupList({
    groups,
    selectedGroupId,
    onSelectGroup,
    onSelectDirectMessage,
    isDirectMessageSelected,
}: GroupListProps) {
    const [hoveredGroupId, setHoveredGroupId] = useState<number | null>(null);
    const [tooltipPosition, setTooltipPosition] = useState({ top: 0, left: 0 });
    const [showMenu, setShowMenu] = useState(false);
    const [menuPosition, setMenuPosition] = useState({ top: 0, left: 0 });
    const [isExpanded, setIsExpanded] = useState(false);
    const groupRefs = useRef<{ [key: number]: HTMLDivElement | null }>({});
    const plusButtonRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (hoveredGroupId && groupRefs.current[hoveredGroupId]) {
            const rect = groupRefs.current[hoveredGroupId]?.getBoundingClientRect();
            if (rect) {
                setTooltipPosition({
                    top: rect.bottom - 18,
                    left: rect.left + 48,
                });
            }
        }
    }, [hoveredGroupId]);

    useEffect(() => {
        if (showMenu && plusButtonRef.current) {
            const rect = plusButtonRef.current.getBoundingClientRect();
            setMenuPosition({
                top: rect.top - 88,
                left: rect.right + 10,
            });
        }
    }, [showMenu]);

    return (
        <aside className={`flex flex-col bg-[#090909] border-r border-[#111111] transition-all duration-300 ${isExpanded ? 'w-64' : 'w-16'}`}>
            <div className={`h-[60px] flex items-center border-b border-[#111111] shrink-0 ${isExpanded ? 'justify-start px-2' : 'justify-center'}`}>
                <button
                    className="p-2 text-gray-500 hover:text-white hover:bg-[#111111] cursor-pointer"
                    onClick={() => setIsExpanded(!isExpanded)}
                >
                    <ChevronRightIcon className={`w-6 h-6 transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}`} />
                </button>
                {isExpanded && (
                    <span className="text-sm font-medium text-white">Group List</span>
                )}
            </div>
            <div className="flex-1 overflow-y-auto custom-scrollbar overflow-x-hidden">
                <div className="flex flex-col items-center py-2 space-y-4">
                    {onSelectDirectMessage && (
                        <button
                            onClick={onSelectDirectMessage}
                            className={`cursor-pointer flex items-center w-full ${isExpanded ? 'px-2 py-2 hover:bg-[#111111]' : 'justify-center'}`}
                            title="Direct Message"
                        >
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center ${isDirectMessageSelected ? 'bg-yellow-700' : 'bg-yellow-900 hover:bg-yellow-800'}`}>
                                <ChatBubbleLeftRightIcon className="w-5 h-5 text-white" />
                            </div>
                            {isExpanded && (
                                <div className="ml-2 text-left">
                                    <p className="text-sm font-medium text-white">Direct Message</p>
                                    <p className="text-xs text-gray-500">Start a new conversation</p>
                                </div>
                            )}
                        </button>
                    )}
                    {groups.map((group) => (
                        <div
                            key={group.id}
                            ref={(el) => { groupRefs.current[group.id] = el; }}
                            className="relative w-full"
                            onMouseEnter={() => setHoveredGroupId(group.id)}
                            onMouseLeave={() => setHoveredGroupId(null)}
                        >
                            <button
                                onClick={() => onSelectGroup(group.id)}
                                className={`cursor-pointer flex items-center w-full ${isExpanded ? 'px-2 py-2 hover:bg-[#111111]' : 'justify-center'}`}
                            >
                                <ProfileAvatar
                                    name={group.name}
                                    avatar={group.avatar}
                                    size="md"
                                />
                                {isExpanded && (
                                    <div className="ml-2 text-left min-w-0 flex-1">
                                        <p className="text-sm font-medium text-white truncate">{group.name}</p>
                                        <p className="text-xs text-gray-500 truncate">{group.description || 'No description'}</p>
                                    </div>
                                )}
                            </button>
                        </div>
                    ))}
                </div>
            </div>
            <div className={`h-[60px] flex items-center border-t border-[#111111] shrink-0 ${isExpanded ? 'justify-start' : 'justify-center'}`}>
                <button
                    ref={plusButtonRef}
                    onClick={() => setShowMenu(!showMenu)}
                    className={`cursor-pointer flex items-center w-full ${isExpanded ? 'px-2 py-2 hover:bg-[#111111]' : 'justify-center'}`}
                    title="Add new"
                >
                    <div className="w-10 h-10 rounded-full bg-yellow-900 hover:bg-yellow-800 flex items-center justify-center">
                        <PlusIcon className="w-5 h-5 text-white" />
                    </div>
                    {isExpanded && (
                        <div className="ml-2 text-left">
                            <p className="text-sm font-medium text-white">Add new</p>
                            <p className="text-xs text-gray-500">Create or join group</p>
                        </div>
                    )}
                </button>
            </div>
            {!isExpanded && hoveredGroupId && groups.find(g => g.id === hoveredGroupId) && (
                <div
                    className="fixed z-[100]"
                    style={{
                        top: tooltipPosition.top,
                        left: tooltipPosition.left,
                    }}
                >
                    <GroupTooltip group={groups.find(g => g.id === hoveredGroupId)!} />
                </div>
            )}
            {showMenu && (
                <PlusMenu
                    showMenu={showMenu}
                    menuPosition={menuPosition}
                    onClose={() => setShowMenu(false)}
                />
            )}
        </aside>
    );
}
