<!-- chatbot_widget.php -->
<!-- Floating AI Icon -->
<div id="ai-chatbot-icon" onclick="toggleChat()">
    <span style="font-size: 28px;">🤖</span>
</div>
<!-- Chat Window -->
<div id="ai-chatbot-window" class="chatbot-window">
    <div class="chatbot-header">
        <h3><span>🤖</span> LearnOnAir AI Guide</h3>
        <span class="close-chatbot" onclick="toggleChat()">×</span>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="chat-bubble chat-ai">Hi! I'm LearnOnAir's virtual guide. I can help you find courses, understand
            your dashboard, and answer basic questions! 🚀</div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="chatInput" placeholder="Ask me something..."
            onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()">➤</button>
    </div>
</div>
<script>
    function toggleChat() {
        const chatWindow = document.getElementById('ai-chatbot-window');
        chatWindow.classList.toggle('active');
        if (chatWindow.classList.contains('active')) {
            document.getElementById('chatInput').focus();
        }
    }
    function addMessage(msg, sender) {
        const box = document.getElementById('chatMessages');
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-bubble chat-${sender}`;
        // simple parsing
        msg = msg.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        msg = msg.replace(/\n/g, '<br>');
        msgDiv.innerHTML = msg;
        box.appendChild(msgDiv);
        box.scrollTop = box.scrollHeight;
    }
    function showTyping() {
        const box = document.getElementById('chatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
        box.appendChild(typingDiv);
        box.scrollTop = box.scrollHeight;
    }
    function hideTyping() {
        const typingDiv = document.getElementById('typingIndicator');
        if (typingDiv) typingDiv.remove();
    }
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;
        // Add User message
        addMessage(message, 'user');
        input.value = '';
        // Show AI thinking
        showTyping();
        // Fetch to Backend AI Handler
        fetch('chatbot_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        })
            .then(res => res.json())
            .then(data => {
                hideTyping();
                addMessage(data.reply, 'ai');
            })
            .catch(err => {
                hideTyping();
                addMessage("Oops! My circuits got crossed. Please try asking again.", 'ai');
            });
    }
</script>