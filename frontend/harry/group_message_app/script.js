const BASE_URL = 'http://localhost:8000/api';
let isLoginMode = true;
let pollingInterval = null;

const authView = document.getElementById('auth-view');
const chatView = document.getElementById('chat-view');
const authForm = document.getElementById('auth-form');
const authTitle = document.getElementById('auth-title');
const authBtn = document.getElementById('auth-btn');
const toggleAuthBtn = document.getElementById('toggle-auth');
const authError = document.getElementById('auth-error');
const messageContainer = document.getElementById('message-container');
const chatForm = document.getElementById('chat-form');
const messageInput = document.getElementById('message-input');

document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('api_token');
    const userId = localStorage.getItem('user_id');
    if (token && userId) showChatView();
    else showAuthView();
});

toggleAuthBtn.addEventListener('click', (e) => {
    e.preventDefault();
    isLoginMode = !isLoginMode;
    authTitle.innerText = isLoginMode ? "Login" : "Register";
    authBtn.innerText = isLoginMode ? "Login" : "Register";
    toggleAuthBtn.innerHTML = isLoginMode 
        ? "Don't have an account? <span style='color: #4f46e5;'>Register here</span>" 
        : "Already have an account? <span style='color: #4f46e5;'>Login here</span>";
    authError.classList.add('d-none');
});

authForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    authError.classList.add('d-none');
    
    const endpoint = isLoginMode ? '/LoginApi.php' : '/RegisterApi.php';
    const formData = new URLSearchParams();
    formData.append('name', document.getElementById('username').value);
    formData.append('password', document.getElementById('password').value);

    try {
        const response = await fetch(`${BASE_URL}${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });

        const rawText = await response.text(); 
        try {
            const data = JSON.parse(rawText); 
            if (data.status === 'success') {
                localStorage.setItem('api_token', data.api_token);
                localStorage.setItem('user_id', data.user_id);
                authForm.reset();
                showChatView();
            } else {
                showError(data.message); 
            }
        } catch (parseError) {
            console.error("Backend PHP Error Raw Output:", rawText);
            showError("Backend crashed. Check console.");
        }
    } catch (err) {
        showError("Network blocked request. Check CORS.");
    }
});

async function fetchMessages(forceScroll = false) {
    try {
        const response = await fetch(`${BASE_URL}/GetMessagesApi.php`);
        const result = await response.json();

        if (result.status === 'success') {
            renderMessages(result.data);
            if (forceScroll) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        }
    } catch (err) {
        console.error("Failed to fetch messages", err);
    }
}

// --- UPDATED RENDER FUNCTION FOR NEW DESIGN ---
function renderMessages(messagesArray) {
    messageContainer.innerHTML = ''; 
    const currentUserId = parseInt(localStorage.getItem('user_id'));

    messagesArray.forEach(msg => {
        const isMe = msg.user_id === currentUserId;
        const alignClass = isMe ? 'me' : 'them';
        
        // Use "Y" for You, or "U" for unknown user as the avatar letter
        const initial = isMe ? 'Y' : 'U'; 
        const displayName = isMe ? 'You' : `User #${msg.user_id}`;
        
        const timeString = new Date(msg.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const messageHTML = `
            <div class="msg-wrapper ${alignClass}">
                <div class="avatar shadow-sm">${initial}</div>
                <div class="msg-content">
                    <div class="msg-meta">${displayName} • ${timeString}</div>
                    <div class="msg-bubble">${msg.message}</div>
                </div>
            </div>
        `;
        messageContainer.insertAdjacentHTML('beforeend', messageHTML);
    });
}

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const messageText = messageInput.value.trim();
    if (!messageText) return;

    const token = localStorage.getItem('api_token');
    const formData = new URLSearchParams();
    formData.append('api_token', token);
    formData.append('message', messageText);

    try {
        const response = await fetch(`${BASE_URL}/SendMessageApi.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });

        const data = await response.json();
        if (data.status === 'success') {
            messageInput.value = ''; 
            fetchMessages(true); 
        } else if (data.status === 'error' && data.message.includes('Unauthorized')) {
            logout();
            showError("Session expired. Please log in again.");
        }
    } catch (err) {
        console.error("Failed to send message", err);
    }
});

function showChatView() {
    authView.classList.add('d-none');
    chatView.classList.remove('d-none');
    fetchMessages(true);
    pollingInterval = setInterval(() => fetchMessages(false), 5000);
}

function showAuthView() {
    chatView.classList.add('d-none');
    authView.classList.remove('d-none');
}

function logout() {
    localStorage.removeItem('api_token');
    localStorage.removeItem('user_id');
    if (pollingInterval) clearInterval(pollingInterval);
    showAuthView();
}

function showError(msg) {
    authError.innerText = msg;
    authError.classList.remove('d-none');
}