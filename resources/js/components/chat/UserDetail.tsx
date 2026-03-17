import { EnvelopeIcon, PhoneIcon, ExclamationTriangleIcon, UserCircleIcon } from '@heroicons/react/24/outline';
import type { UserInterface } from '../../interface/UserInterface';

interface UserDetailProps {
    user: UserInterface | undefined;
}

export default function UserDetail({ user }: UserDetailProps) {
    if (!user) {
        return (
            <aside className="w-64 bg-[#090909] border-l border-[#111111]">
                <div className="flex items-center justify-center h-full">
                    <div className="text-center p-4">
                        <UserCircleIcon className="w-12 h-12 mx-auto text-gray-600 mb-3" />
                        <p className="text-gray-500 text-sm">
                            Select a user to view details
                        </p>
                    </div>
                </div>
            </aside>
        );
    }

    return (
        <aside className="w-64 bg-[#090909] border-l border-[#111111]">
            <div className="p-4 border-b border-[#111111]">
                <div className="flex flex-col items-center">
                    <div className="w-16 h-16 rounded-full bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center text-white text-xl font-medium mb-3">
                        {user.name.charAt(0).toUpperCase()}
                    </div>
                    <h2 className="text-base font-semibold text-white">
                        {user.name}
                    </h2>
                    <p className="text-xs text-gray-500 mt-1">
                        {user.email}
                    </p>
                    <div className="flex items-center mt-2">
                        <span
                            className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#111111] text-gray-300`}
                        >
                            <span
                                className={`w-1.5 h-1.5 rounded-full mr-1.5 ${
                                    user.status === 'online'
                                        ? 'bg-green-500'
                                        : user.status === 'away'
                                        ? 'bg-yellow-500'
                                        : 'bg-gray-500'
                                }`}
                            />
                            {user.status === 'online'
                                ? 'Online'
                                : user.status === 'away'
                                ? 'Away'
                                : 'Offline'}
                        </span>
                    </div>
                </div>
            </div>

            <div className="p-4 space-y-4 overflow-y-auto custom-scrollbar" style={{ maxHeight: 'calc(100vh - 250px)' }}>
                <div>
                    <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        About
                    </h3>
                    <p className="text-xs text-white">
                        Hey there! I'm using this chat app.
                    </p>
                </div>

                <div>
                    <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        Shared Media
                    </h3>
                    <div className="grid grid-cols-3 gap-1.5">
                        <div className="aspect-square bg-[#111111] flex items-center justify-center cursor-pointer hover:bg-[#1a1a1a]">
                            <svg
                                className="w-4 h-4 text-gray-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div className="aspect-square bg-[#111111] flex items-center justify-center cursor-pointer hover:bg-[#1a1a1a]">
                            <svg
                                className="w-4 h-4 text-gray-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div className="aspect-square bg-[#111111] flex items-center justify-center cursor-pointer hover:bg-[#1a1a1a]">
                            <svg
                                className="w-4 h-4 text-gray-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="space-y-1">
                    <button className="w-full flex items-center px-3 py-1.5 text-xs text-gray-300 hover:bg-[#111111] transition-colors cursor-pointer">
                        <EnvelopeIcon className="w-4 h-4 mr-2 text-gray-500" />
                        Send Email
                    </button>
                    <button className="w-full flex items-center px-3 py-1.5 text-xs text-gray-300 hover:bg-[#111111] transition-colors cursor-pointer">
                        <PhoneIcon className="w-4 h-4 mr-2 text-gray-500" />
                        Call
                    </button>
                    <button className="w-full flex items-center px-3 py-1.5 text-xs text-red-500 hover:bg-[#111111] transition-colors cursor-pointer">
                        <ExclamationTriangleIcon className="w-4 h-4 mr-2" />
                        Block User
                    </button>
                </div>
            </div>
        </aside>
    );
}
