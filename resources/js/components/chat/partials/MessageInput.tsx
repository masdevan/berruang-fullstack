import { useRef, useEffect } from 'react';
import { PhotoIcon, FaceSmileIcon, PaperAirplaneIcon } from '@heroicons/react/24/outline';

interface MessageInputProps {
    value: string;
    onChange: (value: string) => void;
}

export default function MessageInput({ value, onChange }: MessageInputProps) {
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${Math.min(textareaRef.current.scrollHeight, 120)}px`;
        }
    }, [value]);

    return (
        <div className="px-4 py-3 border-t border-[#111111]">
            <div className="flex items-start space-x-2">
                <button className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer">
                    <PhotoIcon className="w-4 h-4" />
                </button>
                <textarea
                    ref={textareaRef}
                    placeholder="Type a message..."
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    rows={2}
                    className="flex-1 px-3 py-1.5 text-xs bg-[#111111] border-none focus:outline-none focus:ring-2 focus:ring-gray-600 text-white placeholder-gray-500 resize-none custom-scrollbar"
                />
                <button className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer">
                    <FaceSmileIcon className="w-4 h-4" />
                </button>
                <button className="p-2 text-gray-500 hover:text-gray-300 hover:bg-[#111111] cursor-pointer">
                    <PaperAirplaneIcon className="w-4 h-4" />
                </button>
            </div>
        </div>
    );
}
