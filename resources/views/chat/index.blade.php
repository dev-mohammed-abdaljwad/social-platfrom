@extends('layouts.app')

@section('title', 'Messages — SocialTime')

@push('styles')
    <style>
        /* ── ───────────────────────────────────────────
                       CHAT LAYOUT
                       ───────────────────────────────────────────── */
        .chat-layout-wrap {
            /* break out of the layout's max-w / padding */
            margin: -12px -8px 0;
            height: calc(100vh - 64px);
            display: flex;
            overflow: hidden;
            background: #f1f5f9;
        }

        /* ─── Sidebar ─── */
        .conv-sidebar {
            width: 340px;
            min-width: 340px;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .conv-list {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .conv-list::-webkit-scrollbar {
            width: 4px;
        }

        .conv-list::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }

        .conv-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
            position: relative;
        }

        .conv-item:hover {
            background: #f0f7ff;
        }

        .conv-item.is-active {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
            padding-left: 13px;
        }

        .conv-avatar {
            position: relative;
            flex-shrink: 0;
        }

        .conv-avatar img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .conv-item.is-active .conv-avatar img {
            border-color: #93c5fd;
        }

        .online-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .conv-body {
            flex: 1;
            min-width: 0;
        }

        .conv-name {
            font-weight: 600;
            font-size: 13.5px;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conv-preview {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .conv-preview.has-unread {
            color: #2563eb;
            font-weight: 600;
        }

        .conv-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
            flex-shrink: 0;
        }

        .conv-time {
            font-size: 11px;
            color: #9ca3af;
        }

        .unread-badge {
            min-width: 20px;
            height: 20px;
            background: #2563eb;
            color: #fff;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }

        /* ─── Chat Window ─── */
        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            overflow: hidden;
        }

        .chat-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
            min-height: 64px;
        }

        .chat-head .h-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dbeafe;
        }

        .chat-head .h-name {
            font-weight: 700;
            font-size: 15px;
            color: #111827;
        }

        .chat-head .h-status {
            font-size: 12px;
            color: #16a34a;
            font-weight: 500;
        }

        /* ─── Messages ─── */
        .msgs-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .msgs-area::-webkit-scrollbar {
            width: 4px;
        }

        .msgs-area::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .day-sep {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 12px 0 6px;
        }

        .day-sep::before,
        .day-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 7px;
            max-width: 78%;
            animation: msgPop 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .msg-row.mine {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .msg-row.other {
            align-self: flex-start;
        }

        @keyframes msgPop {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .msg-sender-img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .msg-bubble {
            padding: 9px 14px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }

        .msg-row.mine .msg-bubble {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 10px rgba(37, 99, 235, .28);
        }

        .msg-row.other .msg-bubble {
            background: #fff;
            color: #1f2937;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, .08);
            border: 1px solid #f0f0f0;
        }

        .msg-footer {
            display: flex;
            align-items: center;
            gap: 3px;
            margin-top: 3px;
        }

        .msg-row.mine .msg-footer {
            justify-content: flex-end;
        }

        .msg-row.other .msg-footer {
            justify-content: flex-start;
        }

        .msg-time {
            font-size: 10px;
            color: #9ca3af;
        }

        .msg-row.mine .msg-time {
            color: rgba(255, 255, 255, .6);
        }

        /* ─── Input bar ─── */
        .input-bar {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 10px 14px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .msg-textarea {
            flex: 1;
            padding: 10px 15px;
            border-radius: 22px;
            border: 1.5px solid #e5e7eb;
            background: #f8fafc;
            font-size: 14px;
            resize: none;
            outline: none;
            font-family: inherit;
            line-height: 1.45;
            min-height: 42px;
            max-height: 120px;
            transition: border-color .2s, background .2s;
        }

        .msg-textarea:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
        }

        .send-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .4);
            transition: transform .15s, box-shadow .15s;
        }

        .send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(37, 99, 235, .5);
        }

        .send-btn:active {
            transform: scale(0.94);
        }

        .send-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ─── Empty state ─── */
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px;
            background: #f8fafc;
        }

        .empty-icon {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe 0%, #ede9fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* ─── Responsive ─── */
        @media (max-width: 767px) {
            .conv-sidebar {
                position: fixed;
                top: 64px;
                left: 0;
                bottom: 0;
                z-index: 60;
                transform: translateX(-100%);
                transition: transform .3s ease;
                box-shadow: none;
            }

            .conv-sidebar.sidebar-open {
                transform: translateX(0);
                box-shadow: 6px 0 24px rgba(0, 0, 0, .15);
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 59;
                background: rgba(0, 0, 0, .35);
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }
    </style>
@endpush

@section('content')
    {{-- Full-bleed wrapper --}}
    <div class="chat-layout-wrap">

        {{-- Mobile backdrop --}}
        <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="closeSidebar()"></div>

        {{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
        <aside id="convSidebar" class="conv-sidebar">

            {{-- Gradient header --}}
            <div class="flex-shrink-0 p-4" style="background: linear-gradient(135deg,#2563eb,#4f46e5);">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-white font-bold text-lg inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Messages
                    </span>
                    {{-- Mobile close --}}
                    <button onclick="closeSidebar()"
                        class="md:hidden text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="relative">
                    <input id="convSearch" type="text" placeholder="Search conversations…" class="w-full pl-9 pr-3 py-2 text-sm rounded-xl bg-white/15 text-white
                                                  placeholder-white/55 border border-white/20 focus:outline-none
                                                  focus:ring-2 focus:ring-white/35">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-white/55 pointer-events-none" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- List --}}
            <div id="convList" class="conv-list">
                @forelse($conversations as $conv)
                    @php
                        $other = $conv->user_one_id == $currentUserId ? $conv->userTwo : $conv->userOne;
                        $last = $conv->latestMessage;
                        $active = isset($activeConversation) && $activeConversation == $conv->id;
                        $unread = 0; /* will be set client-side via Pusher */
                    @endphp
                    <a href="{{ route('chat.show', $conv->id) }}" class="conv-item {{ $active ? 'is-active' : '' }}"
                        data-cid="{{ $conv->id }}" data-name="{{ strtolower($other->name ?? '') }}" onclick="closeSidebar()">

                        <div class="conv-avatar">
                            <img src="{{ $other->avatar_url ?? 'https://ui-avatars.com/api/?name=U&background=3b82f6&color=fff' }}"
                                alt="{{ $other->name }}">
                            <div class="online-dot"></div>
                        </div>

                        <div class="conv-body">
                            <div class="conv-name">{{ $other->name ?? 'Unknown User' }}</div>
                            <div class="conv-preview" data-conv-preview="{{ $conv->id }}">
                                @if($last)
                                    @if($last->sender_id == $currentUserId)<span class="opacity-60">You:
                                    </span>@endif{{ Str::limit($last->body, 42) }}
                                @else
                                    <em class="opacity-50">Start the conversation!</em>
                                @endif
                            </div>
                        </div>

                        <div class="conv-meta">
                            @if($last)
                                <span class="conv-time" data-conv-time="{{ $conv->id }}">
                                    {{ $last->created_at->diffForHumans(null, true, true) }}
                                </span>
                            @endif
                            <span class="unread-badge hidden" data-conv-badge="{{ $conv->id }}"></span>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center py-20 px-6 text-center">
                        <svg class="w-16 h-16 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8
                                                                     a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72
                                                                     C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-400">No conversations yet</p>
                        <p class="text-xs text-gray-300 mt-1">Send a message from a friend's profile!</p>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- ═══════════════════════ MAIN WINDOW ═══════════════════════ --}}
        <section class="chat-window">

            @if(isset($activeConversation) && isset($messages) && isset($otherUser))

                {{-- Header --}}
                <header class="chat-head">
                    <button onclick="openSidebar()" class="md:hidden p-2 hover:bg-gray-100 rounded-lg text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <a href="{{ route('profile.show', $otherUser->id) }}" class="flex items-center gap-3 flex-1 min-w-0"
                        style="text-decoration:none;">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" class="h-avatar">
                            <div
                                style="position:absolute;bottom:-1px;right:-1px;width:11px;height:11px;
                                                                        background:#22c55e;border-radius:50%;border:2px solid #fff;">
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="h-name truncate">{{ $otherUser->name }}</div>
                            <div class="h-status">● Active now</div>
                        </div>
                    </a>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a href="{{ route('profile.show', $otherUser->id) }}"
                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                            title="View profile">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    </div>
                </header>

                {{-- Messages --}}
                <div id="msgsArea" class="msgs-area" data-conv="{{ $activeConversation }}"
                    data-has-more="{{ $messages->hasMorePages() ? '1' : '0' }}"
                    data-next-page="{{ $messages->currentPage() + 1 }}">


                    @php
                        $prevDate = null;
                        $allMsgs = $messages->reverse();
                    @endphp

                    @foreach($allMsgs as $msg)
                        @php
                            $d = $msg->created_at->toDateString();
                            $mine = $msg->sender_id == $currentUserId;
                        @endphp

                        @if($d !== $prevDate)
                            <div class="day-sep">
                                {{ $msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('M j, Y')) }}
                            </div>
                            @php $prevDate = $d; @endphp
                        @endif

                        <div class="msg-row {{ $mine ? 'mine' : 'other' }}" data-mid="{{ $msg->id }}">
                            @if(!$mine)
                                <img src="{{ $msg->sender->avatar_url ?? $otherUser->avatar_url }}" alt="" class="msg-sender-img">
                            @endif
                            <div class="min-w-0">
                                <div class="msg-bubble">{{ $msg->body }}</div>
                                <div class="msg-footer">
                                    <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                                    @if($mine && $msg->read_at)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)"
                                            stroke-width="2.5">
                                            <path d="M4 12l5 5L20 7" />
                                            <path d="M9 17l-2-2" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Typing indicator (hidden by default) --}}
                    <div id="typingIndicator" class="msg-row other" style="display:none;">
                        <img src="{{ $otherUser->avatar_url }}" alt="" class="msg-sender-img">
                        <div class="msg-bubble"
                            style="background:#fff;border:1px solid #f0f0f0;box-shadow:0 1px 5px rgba(0,0,0,.08);padding:10px 16px;">
                            <div style="display:flex;gap:5px;align-items:center;">
                                <span
                                    style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1.2s infinite;"></span>
                                <span
                                    style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1.2s infinite .2s;"></span>
                                <span
                                    style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1.2s infinite .4s;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Input bar --}}
                <div class="input-bar">
                    <button
                        class="p-2 text-gray-400 hover:text-yellow-500 rounded-xl hover:bg-gray-100 transition-colors flex-shrink-0"
                        title="Emoji">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <textarea id="msgInput" class="msg-textarea" rows="1"
                        placeholder="Type a message… (Enter to send, Shift+Enter for new line)"></textarea>
                    <button id="sendMsgBtn" class="send-btn" title="Send message">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>

            @else
                {{-- No conversation selected --}}
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg class="w-14 h-14 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8
                                                                     a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72
                                                                     C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Your Messages</h3>
                    <p class="text-gray-400 text-sm max-w-xs leading-relaxed mb-6">
                        Choose a conversation from the sidebar or tap
                        <strong class="text-blue-600">Send Message</strong> on any profile to get started.
                    </p>
                    <button onclick="openSidebar()" class="md:hidden px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600
                                                                   text-white text-sm font-semibold rounded-xl shadow-lg
                                                                   hover:shadow-xl transition-shadow">
                        View Conversations
                    </button>
                </div>
            @endif

        </section>

    </div>
@endsection

@push('scripts')
    <style>
        @keyframes typingDot {

            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: .4;
            }

            40% {
                transform: translateY(-5px);
                opacity: 1;
            }
        }
    </style>

    {{-- Load modular chat JS (order matters: dependencies first) --}}
    <script src="{{ asset('js/chat/ScrollManager.js') }}"></script>
    <script src="{{ asset('js/chat/TypingManager.js') }}"></script>
    <script src="{{ asset('js/chat/ChatManager.js') }}"></script>

    <script>
        // Expose to PopupManager in app.blade.php — prevents auto-open for active conversation
        window.CURRENT_CONVERSATION_ID = {{ isset($activeConversation) ? $activeConversation : 'null' }};

        // Boot ChatManager with server-supplied context
        window.addEventListener('DOMContentLoaded', function () {
            window.chatPage = new ChatManager({
                currentUserId:       {{ $currentUserId }},
                activeConversationId: {{ isset($activeConversation) ? $activeConversation : 'null' }},
                csrf: document.querySelector('meta[name="csrf-token"]').content,
                @if(isset($otherUser))
                            otherUser: {
                        id:         {{ $otherUser->id }},
                        name: "{{ addslashes($otherUser->name) }}",
                        avatar_url: "{{ $otherUser->avatar_url }}"
                    },
                @endif
                });
            });
    </script>
@endpush