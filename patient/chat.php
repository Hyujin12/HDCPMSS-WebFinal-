<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use Dotenv\Dotenv;

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit();
}

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$messagesCollection = $db->selectCollection('Messages');
$usersCollection = $db->selectCollection('users');

$userEmail = $_SESSION['email'];
$username = $_SESSION['username'];

// Get user info
$user = $usersCollection->findOne(['email' => $userEmail]);
$userId = (string)$user['_id'];

// Handle sending new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $messageText = trim($_POST['message']);
    
    if (!empty($messageText)) {
        $messagesCollection->insertOne([
            'userId' => $userId,
            'userEmail' => $userEmail,
            'username' => $username,
            'message' => $messageText,
            'sender' => 'patient',
            'isRead' => false,
            'createdAt' => new UTCDateTime()
        ]);
        
        header("Location: chat.php");
        exit();
    }
}

// Fetch all messages for this user (both sent and received)
$messages = $messagesCollection->find(
    ['userId' => $userId],
    ['sort' => ['createdAt' => 1]]
);

// Mark admin messages as read
$messagesCollection->updateMany(
    ['userId' => $userId, 'sender' => 'admin', 'isRead' => false],
    ['$set' => ['isRead' => true]]
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat with Admin - Halili Dental Clinic</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  .gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  .gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .chat-container {
    height: calc(100vh - 250px);
    min-height: 400px;
  }
  .message-bubble {
    max-width: 70%;
    word-wrap: break-word;
  }
</style>
</head>
<body class="bg-gray-50">

  <!-- Header -->
  <header class="gradient-bg text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <img src="/images/newlogohalili.png" alt="Logo" class="w-12 h-12">
        <div>
          <h1 class="text-xl font-bold">Halili Dental Clinic</h1>
          <p class="text-sm opacity-90">Chat with Admin</p>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-sm">Welcome, <?= htmlspecialchars($username) ?></span>
        <a href="dashboard.php" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
          Back to Dashboard
        </a>
      </div>
    </div>
  </header>

  <!-- Chat Container -->
  <div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
      
      <!-- Chat Header -->
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <h2 class="font-bold text-lg">Admin Support</h2>
          <p class="text-xs opacity-90">We typically respond within 24 hours</p>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="chat-container overflow-y-auto p-6 space-y-4 bg-gray-50" id="chatMessages">
        <?php 
        $hasMessages = false;
        foreach ($messages as $msg): 
          $hasMessages = true;
          $isPatient = $msg['sender'] === 'patient';
          $timestamp = $msg['createdAt']->toDateTime()->setTimezone(new DateTimeZone('Asia/Manila'));
        ?>
          <div class="flex <?= $isPatient ? 'justify-end' : 'justify-start' ?>">
            <div class="message-bubble">
              <?php if (!$isPatient): ?>
                <p class="text-xs text-gray-500 mb-1 font-semibold">Admin</p>
              <?php endif; ?>
              
              <div class="<?= $isPatient ? 'bg-purple-600 text-white' : 'bg-white border border-gray-200' ?> rounded-2xl px-4 py-3 shadow">
                <p class="<?= $isPatient ? 'text-white' : 'text-gray-800' ?>"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
              </div>
              
              <p class="text-xs text-gray-400 mt-1 <?= $isPatient ? 'text-right' : 'text-left' ?>">
                <?= $timestamp->format('M d, Y g:i A') ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (!$hasMessages): ?>
          <div class="flex items-center justify-center h-full">
            <div class="text-center text-gray-400">
              <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              <p class="text-lg font-medium">No messages yet</p>
              <p class="text-sm">Start a conversation with the admin</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Message Input -->
      <div class="bg-white border-t border-gray-200 p-4">
        <form method="POST" class="flex space-x-3">
          <input 
            type="text" 
            name="message" 
            placeholder="Type your message here..." 
            required
            class="flex-1 p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            autocomplete="off"
          >
          <button 
            type="submit"
            class="gradient-bg text-white px-6 py-3 rounded-full font-semibold hover:opacity-90 transition duration-300 shadow-md flex items-center space-x-2"
          >
            <span>Send</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
          </button>
        </form>
      </div>

    </div>

    <!-- Help Text -->
    <div class="mt-6 text-center text-gray-600 text-sm">
      <p>💬 Need help? Send us a message and our admin team will get back to you soon!</p>
    </div>
  </div>

  <script>
    // Auto-scroll to bottom of chat on page load
    window.addEventListener('load', function() {
      const chatMessages = document.getElementById('chatMessages');
      chatMessages.scrollTop = chatMessages.scrollHeight;
    });

    // Optional: Auto-refresh messages every 30 seconds
    setInterval(function() {
      location.reload();
    }, 30000);
  </script>

</body>
</html>