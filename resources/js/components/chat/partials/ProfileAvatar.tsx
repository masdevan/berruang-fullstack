interface ProfileAvatarProps {
    name: string;
    avatar?: string;
    status?: 'online' | 'away' | 'offline';
    size?: 'sm' | 'md' | 'lg';
    showStatus?: boolean;
}

export default function ProfileAvatar({
    name,
    avatar,
    status,
    size = 'md',
    showStatus = false,
}: ProfileAvatarProps) {
    const sizeClasses = {
        sm: 'w-8 h-8 text-xs',
        md: 'w-10 h-10 text-sm',
        lg: 'w-12 h-12 text-base',
    };

    const statusSizeClasses = {
        sm: 'w-2 h-2',
        md: 'w-2.5 h-2.5',
        lg: 'w-3 h-3',
    };

    const imgSizeClasses = {
        sm: 'w-8 h-8',
        md: 'w-10 h-10',
        lg: 'w-12 h-12',
    };

    return (
        <div className="relative">
            <div
                className={`${sizeClasses[size]} rounded-full bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center text-white font-medium`}
            >
                {avatar ? (
                    <img
                        src={avatar}
                        alt={name}
                        className={`${imgSizeClasses[size]} rounded-full object-cover`}
                    />
                ) : (
                    name.charAt(0).toUpperCase()
                )}
            </div>
            {showStatus && status && (
                <span
                    className={`absolute bottom-0 right-0 ${statusSizeClasses[size]} rounded-full border-2 border-[#090909] ${
                        status === 'online'
                            ? 'bg-green-500'
                            : status === 'away'
                            ? 'bg-yellow-500'
                            : 'bg-gray-500'
                    }`}
                />
            )}
        </div>
    );
}
