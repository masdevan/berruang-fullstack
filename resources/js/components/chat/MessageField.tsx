import { useState } from 'react';
import { ChatBubbleLeftRightIcon, CheckIcon } from '@heroicons/react/24/outline';
import type { UserInterface } from '../../interface/UserInterface';
import type { MessageInterface } from '../../interface/MessageInterface';
import ChatHeader from './partials/ChatHeader';
import MessageInput from './partials/MessageInput';

interface MessageFieldProps {
    user: UserInterface | undefined;
    messages: MessageInterface[];
    currentUserId?: number;
    onToggleDetail?: () => void;
    showDetail?: boolean;
}

export default function MessageField({
    user,
    messages,
    currentUserId = 1,
    onToggleDetail,
    showDetail,
}: MessageFieldProps) {
    const [messageInput, setMessageInput] = useState('');

    if (!user) {
        return (
            <main className="flex-1 flex flex-col bg-[#090909]">
                <div className="flex-1 flex items-center justify-center">
                    <div className="text-center">
                        <ChatBubbleLeftRightIcon className="w-16 h-16 mx-auto text-gray-600 mb-4" />
                        <p className="text-gray-500">
                            Select a conversation to start messaging
                        </p>
                    </div>
                </div>
            </main>
        );
    }

    return (
        <main className="flex-1 flex flex-col bg-[#090909]">
            <ChatHeader
                user={user}
                onToggleDetail={onToggleDetail}
                showDetail={showDetail}
            />

            <div className="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-3">
                {messages.length > 0 ? (
                    messages.map((message) => (
                        <div
                            key={message.id}
                            className={`flex ${
                                message.senderId === currentUserId
                                    ? 'justify-end'
                                    : 'justify-start'
                            }`}
                        >
                            <div
                                className={`max-w-[70%] px-3 py-1.5 rounded-2xl ${
                                    message.senderId === currentUserId
                                        ? 'bg-gray-700 text-white rounded-br-md'
                                        : 'bg-[#111111] text-white rounded-bl-md'
                                }`}
                            >
                                <p className="text-xs">{message.content}</p>
                                <div
                                    className={`flex items-center justify-end mt-0.5 space-x-1 ${
                                        message.senderId === currentUserId
                                            ? 'text-gray-400'
                                            : 'text-gray-500'
                                    }`}
                                >
                                    <span className="text-[10px]">{message.timestamp}</span>
                                    {message.senderId === currentUserId && (
                                        <span>
                                            <CheckIcon className="w-3 h-3" />
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="flex items-center justify-center h-full">
                        <p className="text-gray-500">
                            No messages yet. Start a conversation!
                        </p>
                    </div>
                )}
            </div>

            <MessageInput
                value={messageInput}
                onChange={setMessageInput}
            />
        </main>
    );
}
