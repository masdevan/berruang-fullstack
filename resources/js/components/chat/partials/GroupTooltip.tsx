import type { GroupInterface, GroupMember } from '../../../interface/GroupInterface';
import ProfileAvatar from './ProfileAvatar';

interface GroupTooltipProps {
    group: GroupInterface;
}

export default function GroupTooltip({ group }: GroupTooltipProps) {
    const members = group.members || [];
    const onlineMembers = members.filter((m: GroupMember) => m.status === 'online');
    const awayMembers = members.filter((m: GroupMember) => m.status === 'away');
    const offlineMembers = members.filter((m: GroupMember) => m.status === 'offline');

    return (
        <div className="w-72 bg-[#111111] border border-[#222222] rounded-lg shadow-xl overflow-hidden">
            <div
                className="h-20 bg-gradient-to-br from-gray-700 to-gray-900 relative"
                style={{
                    backgroundImage: group.background ? `url(${group.background})` : undefined,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                }}
            >
                <div className="absolute inset-0 bg-black bg-opacity-40" />
            </div>

            <div className="px-4 pb-4">
                <div className="relative -mt-10 mb-2">
                    <ProfileAvatar
                        name={group.name}
                        avatar={group.avatar}
                        size="lg"
                    />
                </div>

                <h3 className="text-white font-semibold text-lg">{group.name}</h3>

                {group.tag && (
                    <p className="text-yellow-600 text-sm font-medium">@{group.tag}</p>
                )}

                {group.description && (
                    <p className="text-gray-500 text-xs mt-1">{group.description}</p>
                )}

                <div className="mt-3 flex items-center gap-4 text-gray-400 text-sm">
                    <span>{group.memberCount} members</span>
                </div>

                <div className="mt-3 space-y-1.5">
                    {onlineMembers.length > 0 && (
                        <div className="flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-green-500" />
                            <span className="text-gray-400 text-xs">
                                {onlineMembers.length} active
                            </span>
                        </div>
                    )}
                    {awayMembers.length > 0 && (
                        <div className="flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-yellow-500" />
                            <span className="text-gray-400 text-xs">
                                {awayMembers.length} away
                            </span>
                        </div>
                    )}
                    {offlineMembers.length > 0 && (
                        <div className="flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-gray-500" />
                            <span className="text-gray-400 text-xs">
                                {offlineMembers.length} inactive
                            </span>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
