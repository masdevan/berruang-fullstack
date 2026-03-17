import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';

interface SearchMenuProps {
    searchQuery: string;
    onSearchChange: (query: string) => void;
    placeholder?: string;
}

export default function SearchMenu({
    searchQuery,
    onSearchChange,
    placeholder = 'Search user...',
}: SearchMenuProps) {
    return (
        <div className="flex-1 relative">
            <input
                type="text"
                placeholder={placeholder}
                value={searchQuery}
                onChange={(e) => onSearchChange(e.target.value)}
                className="w-full px-4 py-2 pl-10 text-sm bg-[#111111] border-none focus:outline-none focus:ring-none text-white placeholder-gray-500"
            />
            <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
        </div>
    );
}
