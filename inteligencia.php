<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LendFind - Chat Mejorado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos CSS */
        :root {
            --primary-blue: #2c5aa0;
            --light-blue: #3a6bc0;
            --dark-gray: #2a2a2a;
            --medium-gray: #4a4a4a;
            --light-gray: #6a6a6a;
            --lighter-gray: #9a9a9a;
            --lightest-gray: #e0e0e0;
            --background-gray: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--dark-gray);
            color: white;
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--dark-gray);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }

        .logo span {
            color: var(--primary-blue);
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 1.5rem;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        nav ul li a:hover {
            color: var(--primary-blue);
        }

        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            padding: 0 20px;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 1rem 0;
        }

        .chat-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 1.2rem;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            background-color: #fafafa;
        }

        .message {
            max-width: 75%;
            padding: 0.9rem 1.2rem;
            margin-bottom: 1.2rem;
            border-radius: 12px;
            position: relative;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .user-message {
            align-self: flex-end;
            background-color: var(--dark-gray);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .ai-message {
            align-self: flex-start;
            background-color: white;
            color: var(--dark-gray);
            border: 1px solid var(--lightest-gray);
            border-bottom-left-radius: 4px;
        }

        .chat-input-container {
            display: flex;
            padding: 1.2rem;
            border-top: 1px solid var(--lightest-gray);
            background-color: white;
        }

        .chat-input {
            flex: 1;
            padding: 0.9rem 1.2rem;
            border: 1px solid var(--lighter-gray);
            border-radius: 8px;
            margin-right: 0.8rem;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background-color: white;
        }

        .send-button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 0.9rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .send-button:hover {
            background-color: var(--light-blue);
        }

        footer {
            background-color: var(--dark-gray);
            color: white;
            padding: 1.2rem 0;
            text-align: center;
            flex-shrink: 0;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-links {
            display: flex;
            margin: 0.8rem 0;
        }

        .footer-links a {
            color: white;
            margin: 0 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-links a:hover {
            color: var(--primary-blue);
        }

        /* Indicador de escritura */
        .typing-indicator {
            display: none;
            align-self: flex-start;
            background-color: white;
            padding: 0.9rem 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            border: 1px solid var(--lightest-gray);
            border-bottom-left-radius: 4px;
        }

        .typing-text {
            color: var(--light-gray);
            font-style: italic;
            margin-bottom: 0.5rem;
        }

        .typing-dots {
            display: flex;
        }

        .typing-dots span {
            height: 8px;
            width: 8px;
            background-color: var(--light-gray);
            border-radius: 50%;
            display: block;
            margin: 0 2px;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dots span:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-5px);
            }
        }

        /* Personalización de la barra de desplazamiento */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--lighter-gray);
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: var(--light-gray);
        }

        /* Estilos para mensajes de error y éxito */
        .error-message {
            color: #ff4444;
            background-color: #ffeaea;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #ffcccc;
            animation: fadeIn 0.3s ease-in;
        }

        .success-message {
            color: #00aa00;
            background-color: #eaffea;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #ccffcc;
            animation: fadeIn 0.3s ease-in;
        }

        /* Animación de aparición */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Estilos para el formulario */
        .chat-input-container form {
            display: flex;
            width: 100%;
        }

        /* Estilos para botón deshabilitado */
        .send-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .send-button:disabled:hover {
            background-color: #cccccc;
            transform: none;
        }

        .char-counter {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            background-color: white;
            padding: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 1rem;
            }
            
            nav ul li {
                margin: 0 0.8rem;
            }
            
            .message {
                max-width: 90%;
            }
            
            .footer-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .footer-links a {
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">Lend<span>Find</span></div>
            <nav>
                <ul>
                    <li><a href='admin.php'>Administrar</a></li> 
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <div class="main-container">
        <div class="chat-container">
            <div class="chat-header">
                Asistente Virtual
            </div>
            
            <!-- Mensajes de error y éxito -->
            <div id="errorMessage" class="error-message" style="display: none;"></div>
            <div id="successMessage" class="success-message" style="display: none;"></div>

            <div class="chat-messages" id="chatMessages">
                <div class="message ai-message">
                    ¡Hola! Soy tu asistente virtual. Estoy aquí para ayudarte con cualquier pregunta o tarea que tengas. ¿En qué puedo asistirte hoy?
                </div>
                
                <!-- Indicador de escritura -->
                <div class="typing-indicator" id="typingIndicator">
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>

            <div class="chat-input-container">
                <input type="text" 
                       class="chat-input" 
                       id="userInput" 
                       placeholder="Escribe tu mensaje aquí..."
                       maxlength="500">
                <button class="send-button" id="sendButton">Enviar</button>
            </div>
            
            <div class="char-counter">
                <span id="charCount">0</span>/500 caracteres
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="logo">Lend<span>Find</span></div>
            <div class="footer-links">
                <a href="#chat">Chat</a>
                <a href="#admin">Admin</a>
                <a href="#privacy">Privacidad</a>
                <a href="#terms">Términos</a>
                <a href="#help">Ayuda</a>
            </div>
            <p>&copy; 2025 LendFind. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Elementos del DOM
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const typingIndicator = document.getElementById('typingIndicator');
        const charCount = document.getElementById('charCount');

        // Respuestas predefinidas de la IA
        const aiResponses = {
            'hola': "¡Hola! Es un placer saludarte. ¿En qué puedo ayudarte hoy?",
            'buenos días': "¡Buenos días! Espero que tengas un excelente día. ¿En qué puedo asistirte?",
            'buenas tardes': "¡Buenas tardes! ¿Cómo va tu día? Estoy aquí para ayudarte.",
            'buenas noches': "¡Buenas noches! ¿Necesitas ayuda con algo antes de descansar?",
            'cómo estás': "¡Estoy funcionando perfectamente! Gracias por preguntar. ¿Hay algo específico en lo que pueda asistirte?",
            'qué puedes hacer': "Puedo ayudarte con una variedad de tareas: responder preguntas, analizar información, realizar cálculos, programar recordatorios y mucho más. ¿Qué necesitas específicamente?",
            'tus funciones': "Mis funciones incluyen: responder consultas, proporcionar información, ayudar con tareas específicas y asistirte en lo que necesites. ¿En qué área necesitas ayuda?",
            'gracias': "¡De nada! Estoy aquí para ayudarte siempre que lo necesites.",
            'adiós': "¡Hasta luego! Ha sido un placer ayudarte. No dudes en volver si necesitas más asistencia.",
            'chao': "¡Chao! Espero haberte sido de ayuda. ¡Vuelve pronto!",
            'hasta luego': "¡Hasta luego! Que tengas un excelente día.",
            'clima': "No tengo acceso a información meteorológica en tiempo real en esta demostración, pero en una implementación completa podría integrarme con servicios de pronóstico del tiempo.",
            'tiempo': "Para información meteorológica precisa, te recomiendo consultar servicios especializados. ¿Puedo ayudarte con algo más?",
            'noticias': "Puedo ayudarte a buscar noticias si integras una API de noticias en mi sistema. ¿Sobre qué tema te interesan las noticias?",
            'nombre': "Soy tu Asistente Virtual de LendFind. Puedes llamarme como prefieras. ¿Hay algo específico en lo que pueda ayudarte?",
            'ayuda': "Por supuesto, estoy aquí para ayudarte. Puedes preguntarme sobre cualquier tema, pedirme que realice cálculos, explicar conceptos o ayudarte con tareas específicas. ¿Qué necesitas?"
        };

        // Historial del chat almacenado en localStorage
        let chatHistory = JSON.parse(localStorage.getItem('chatHistory')) || [];

        // Función para inicializar el chat
        function initChat() {
            // Cargar historial del chat
            loadChatHistory();
            
            // Enfocar el campo de entrada
            userInput.focus();
            
            // Configurar eventos
            setupEventListeners();
        }

        // Función para cargar el historial del chat
        function loadChatHistory() {
            chatHistory.forEach(chat => {
                addMessageToChat('user', chat.user);
                addMessageToChat('ai', chat.ai);
            });
            
            // Scroll al final del chat
            scrollToBottom();
        }

        // Función para configurar los event listeners
        function setupEventListeners() {
            // Enviar mensaje al hacer clic en el botón
            sendButton.addEventListener('click', handleSendMessage);
            
            // Enviar mensaje al presionar Enter
            userInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    handleSendMessage();
                }
            });
            
            // Actualizar contador de caracteres
            userInput.addEventListener('input', updateCharCounter);
            
            // Inicializar contador de caracteres
            updateCharCounter();
        }

        // Función para actualizar el contador de caracteres
        function updateCharCounter() {
            const count = userInput.value.length;
            charCount.textContent = count;
            
            // Cambiar color si se acerca al límite
            if (count > 450) {
                charCount.style.color = '#d32f2f';
            } else if (count > 400) {
                charCount.style.color = '#ff9800';
            } else {
                charCount.style.color = '#666';
            }
        }

        // Función para manejar el envío de mensajes
        function handleSendMessage() {
            const message = userInput.value.trim();
            
            // Validar entrada
            if (!validateInput(message)) {
                return;
            }
            
            // Limpiar campo de entrada
            userInput.value = '';
            updateCharCounter();
            
            // Deshabilitar botón temporalmente
            sendButton.disabled = true;
            
            // Mostrar mensaje del usuario
            addMessageToChat('user', message);
            
            // Mostrar indicador de escritura
            showTypingIndicator();
            
            // Simular tiempo de procesamiento de la IA
            setTimeout(() => {
                // Ocultar indicador de escritura
                hideTypingIndicator();
                
                // Generar respuesta de la IA
                const aiResponse = generateAIResponse(message);
                
                // Mostrar respuesta de la IA
                addMessageToChat('ai', aiResponse);
                
                // Guardar en historial
                saveToHistory(message, aiResponse);
                
                // Habilitar botón nuevamente
                sendButton.disabled = false;
                
                // Enfocar campo de entrada
                userInput.focus();
            }, 1000 + Math.random() * 1000); // Tiempo de respuesta aleatorio entre 1-2 segundos
        }

        // Función para validar entrada
        function validateInput(input) {
            // Limpiar mensajes anteriores
            hideMessages();
            
            if (input === '') {
                showError('Por favor, escribe un mensaje.');
                return false;
            }
            
            if (input.length < 2) {
                showError('El mensaje debe tener al menos 2 caracteres.');
                return false;
            }
            
            if (input.length > 500) {
                showError('El mensaje no puede tener más de 500 caracteres.');
                return false;
            }
            
            // Validar caracteres permitidos
            const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,!?¿¡()\-:;]+$/;
            if (!regex.test(input)) {
                showError('El mensaje contiene caracteres no permitidos. Solo se permiten letras, números y signos de puntuación básicos.');
                return false;
            }
            
            return true;
        }

        // Función para generar respuesta de la IA
        function generateAIResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Buscar coincidencias en las respuestas predefinidas
            for (const [key, response] of Object.entries(aiResponses)) {
                if (lowerMessage.includes(key)) {
                    return response;
                }
            }
            
            // Respuesta por defecto
            return `He procesado tu mensaje: "${message}". En una implementación real, aquí conectaría con un modelo de IA para generar una respuesta más precisa y contextual.`;
        }

        // Función para añadir mensaje al chat
        function addMessageToChat(sender, message) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.classList.add(sender === 'user' ? 'user-message' : 'ai-message');
            messageElement.textContent = message;
            
            // Insertar antes del indicador de escritura
            chatMessages.insertBefore(messageElement, typingIndicator);
            
            // Scroll al final del chat
            scrollToBottom();
        }

        // Función para mostrar indicador de escritura
        function showTypingIndicator() {
            typingIndicator.style.display = 'block';
            scrollToBottom();
        }

        // Función para ocultar indicador de escritura
        function hideTypingIndicator() {
            typingIndicator.style.display = 'none';
        }

        // Función para hacer scroll al final del chat
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Función para mostrar mensaje de error
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        // Función para mostrar mensaje de éxito
        function showSuccess(message) {
            successMessage.textContent = message;
            successMessage.style.display = 'block';
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }

        // Función para ocultar todos los mensajes
        function hideMessages() {
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
        }

        // Función para guardar en el historial
        function saveToHistory(userMessage, aiMessage) {
            const chatEntry = {
                user: userMessage,
                ai: aiMessage,
                timestamp: new Date().toISOString()
            };
            
            chatHistory.push(chatEntry);
            
            // Guardar en localStorage (máximo 50 mensajes)
            if (chatHistory.length > 50) {
                chatHistory = chatHistory.slice(-50);
            }
            
            localStorage.setItem('chatHistory', JSON.stringify(chatHistory));
        }

        // Inicializar el chat cuando se carga la página
        document.addEventListener('DOMContentLoaded', initChat);
    </script>
</body>
</html>