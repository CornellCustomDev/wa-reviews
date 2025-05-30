<div>
    <h1>AI Chat</h1>

    @include('ai-agents.laragent-chat', [
        'description' => sprintf('This AI chatbot can answer questions related to web accessibility issues and the <a href="%s">Cornell Web Accessibility Testing Guidelines</a>. ', route('guidelines.md')),
    ])
</div>
