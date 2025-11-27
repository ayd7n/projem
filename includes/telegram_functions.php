<?php
// includes/telegram_functions.php

/**
 * Sends a file to a Telegram chat.
 *
 * @param string $filePath The absolute path to the file to send.
 * @param string $caption The caption for the file.
 * @param string $botToken The Telegram Bot API token.
 * @param string $chatId The ID of the chat to send the file to.
 * @return bool True on success, false on failure.
 */
function sendTelegramFile($filePath, $caption, $botToken, $chatId) {
    if (empty($botToken) || empty($chatId)) {
        error_log('Telegram bot token or chat ID is not configured.');
        return false;
    }

    $url = "https://api.telegram.org/bot" . $botToken . "/sendDocument";

    if (!file_exists($filePath)) {
        error_log("File not found to send to Telegram: " . $filePath);
        return false;
    }

    // Using cURL to send the file
    $ch = curl_init();
    $post_fields = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'document' => new CURLFile($filePath)
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout for the entire cURL execution

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code !== 200 || !$response_data['ok']) {
        error_log("Telegram API Error: " . ($response_data['description'] ?? $curl_error));
        return false;
    }

    return true;
}

/**
 * Sends a message to a Telegram chat.
 *
 * @param string $message The message to send.
 * @param string $botToken The Telegram Bot API token.
 * @param string $chatId The ID of the chat to send the message to.
 * @return bool True on success, false on failure.
 */
function sendTelegramMessage($message, $botToken, $chatId)
{
    if (empty($botToken) || empty($chatId)) {
        error_log('Telegram bot token or chat ID is not configured.');
        return false;
    }

    $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $post_fields = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    $response_data = json_decode($response, true);

    if ($http_code !== 200 || !$response_data['ok']) {
        error_log("Telegram API Error: " . ($response_data['description'] ?? $curl_error));
        return false;
    }
    return true;
}

/**
 * Fetches Telegram settings from the database.
 *
 * @param mysqli $connection The database connection object.
 * @return array An associative array with 'bot_token' and 'chat_id'.
 */
function get_telegram_settings($connection) {
    $settings = ['bot_token' => '', 'chat_id' => ''];
    
    $token_result = $connection->query("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'telegram_bot_token'");
    if ($token_result && $token_row = $token_result->fetch_assoc()) {
        $settings['bot_token'] = $token_row['ayar_deger'];
    }

    $chat_id_result = $connection->query("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'telegram_chat_id'");
    if ($chat_id_result && $chat_id_row = $chat_id_result->fetch_assoc()) {
        $settings['chat_id'] = $chat_id_row['ayar_deger'];
    }

    return $settings;
}