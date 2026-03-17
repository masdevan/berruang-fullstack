import ChatLayout from '../components/layout/ChatLayout';
import type { UserInterface } from '../interface/UserInterface';
import type { MessageInterface } from '../interface/MessageInterface';
import type { GroupInterface } from '../interface/GroupInterface';

const users: UserInterface[] = [
    {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        status: 'online',
        lastMessage: 'Sure, I will send the documents tomorrow',
        lastMessageTime: '2:30 PM',
        unreadCount: 0,
    },
    {
        id: 2,
        name: 'Sarah Smith',
        email: 'sarah@example.com',
        status: 'online',
        lastMessage: 'Thanks for your help!',
        lastMessageTime: '1:15 PM',
        unreadCount: 3,
    },
    {
        id: 3,
        name: 'Michael Johnson',
        email: 'michael@example.com',
        status: 'away',
        lastMessage: 'Let me check and get back to you',
        lastMessageTime: '11:30 AM',
        unreadCount: 0,
    },
    {
        id: 4,
        name: 'Emily Brown',
        email: 'emily@example.com',
        status: 'offline',
        lastMessage: 'See you at the meeting tomorrow',
        lastMessageTime: 'Yesterday',
        unreadCount: 1,
    },
    {
        id: 5,
        name: 'David Wilson',
        email: 'david@example.com',
        status: 'online',
        lastMessage: 'The project is almost complete',
        lastMessageTime: 'Yesterday',
        unreadCount: 0,
    },
    {
        id: 6,
        name: 'Lisa Anderson',
        email: 'lisa@example.com',
        status: 'offline',
        lastMessage: 'Can we schedule a call?',
        lastMessageTime: 'Monday',
        unreadCount: 0,
    },
];

const groups: GroupInterface[] = [
    {
        id: 1,
        name: 'Work Team',
        tag: 'workteam',
        description: 'Team collaboration for work projects and updates',
        memberCount: 8,
        members: [
            { id: 1, name: 'John Doe', status: 'online' },
            { id: 2, name: 'Sarah Smith', status: 'online' },
            { id: 3, name: 'Michael Johnson', status: 'away' },
            { id: 4, name: 'Emily Brown', status: 'offline' },
            { id: 5, name: 'David Wilson', status: 'online' },
            { id: 6, name: 'Lisa Anderson', status: 'offline' },
        ],
    },
    {
        id: 2,
        name: 'Friends',
        tag: 'friendsclub',
        description: 'Stay connected with friends',
        memberCount: 12,
        members: [
            { id: 1, name: 'Alex', status: 'online' },
            { id: 2, name: 'Bob', status: 'online' },
            { id: 3, name: 'Charlie', status: 'away' },
        ],
    },
    {
        id: 3,
        name: 'Family',
        tag: 'family',
        description: 'Family group chat',
        memberCount: 6,
        members: [
            { id: 1, name: 'Mom', status: 'online' },
            { id: 2, name: 'Dad', status: 'away' },
            { id: 3, name: 'Sister', status: 'offline' },
        ],
    },
    {
        id: 4,
        name: 'Project Alpha',
        tag: 'projectalpha',
        description: 'Alpha project discussion and updates',
        memberCount: 5,
        members: [],
    },
];

const sampleMessages: MessageInterface[] = [
    {
        id: 1,
        senderId: 2,
        content: 'Hi! How are you doing today?',
        timestamp: '10:00 AM',
        isRead: true,
    },
    {
        id: 2,
        senderId: 1,
        content: "I'm doing great, thanks for asking! How about you?",
        timestamp: '10:05 AM',
        isRead: true,
    },
    {
        id: 3,
        senderId: 2,
        content: 'Pretty good! I wanted to ask about the project timeline.',
        timestamp: '10:10 AM',
        isRead: true,
    },
    {
        id: 4,
        senderId: 1,
        content: "Sure, what's your question?",
        timestamp: '10:15 AM',
        isRead: true,
    },
    {
        id: 5,
        senderId: 2,
        content: 'When do you think we can finish the first phase?',
        timestamp: '10:20 AM',
        isRead: true,
    },
    {
        id: 6,
        senderId: 1,
        content: 'Based on our current progress, I estimate about 2 more weeks.',
        timestamp: '10:25 AM',
        isRead: true,
    },
    {
        id: 7,
        senderId: 2,
        content: 'Sounds reasonable! I will inform the team.',
        timestamp: '10:30 AM',
        isRead: true,
    },
    {
        id: 8,
        senderId: 2,
        content: 'Thanks for your help!',
        timestamp: '1:15 PM',
        isRead: true,
    },
];

export default function Messages() {
    return (
        <ChatLayout
            title="Messages"
            users={users}
            selectedUserId={2}
            messages={sampleMessages}
            groups={groups}
        />
    );
}
