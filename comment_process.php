<?php
// comment_process.php - Process video comment submissions
// Handles adding comments to videos

require_once 'db.php';
require_once 'includes/auth.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '/index.php';
    
    // Validation
    if ($video_id <= 0) {
        header("Location: $redirect_url?error=invalid_video");
        exit;
    }
    
    if (empty($comment)) {
        header("Location: $redirect_url?error=empty_comment");
        exit;
    }
    
    if (strlen($comment) > 1000) {
        header("Location: $redirect_url?error=comment_too_long");
        exit;
    }
    
    // Check if video exists and is approved
    $videoCheckQuery = "SELECT id FROM videos WHERE id = ? AND approved = 1";
    $videoCheckStmt = mysqli_prepare($conn, $videoCheckQuery);
    mysqli_stmt_bind_param($videoCheckStmt, "i", $video_id);
    mysqli_stmt_execute($videoCheckStmt);
    $videoCheckResult = mysqli_stmt_get_result($videoCheckStmt);
    
    if (mysqli_num_rows($videoCheckResult) == 0) {
        header("Location: $redirect_url?error=video_not_found");
        exit;
    }
    
    // Check for spam (prevent multiple comments in short time)
    $spamCheckQuery = "SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    $spamCheckStmt = mysqli_prepare($conn, $spamCheckQuery);
    mysqli_stmt_bind_param($spamCheckStmt, "i", $user_id);
    mysqli_stmt_execute($spamCheckStmt);
    $spamCheckResult = mysqli_stmt_get_result($spamCheckStmt);
    $spamCount = mysqli_fetch_assoc($spamCheckResult)['count'];
    
    if ($spamCount >= 3) {
        header("Location: $redirect_url?error=comment_spam");
        exit;
    }
    
    // Basic content filtering (you can expand this)
    $bannedWords = ['spam', 'scam', 'fake', 'bot'];
    $commentLower = strtolower($comment);
    
    foreach ($bannedWords as $word) {
        if (strpos($commentLower, $word) !== false) {
            header("Location: $redirect_url?error=inappropriate_content");
            exit;
        }
    }
    
    // Insert comment
    $insertCommentQuery = "INSERT INTO comments (user_id, video_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $insertCommentStmt = mysqli_prepare($conn, $insertCommentQuery);
    mysqli_stmt_bind_param($insertCommentStmt, "iis", $user_id, $video_id, $comment);
    
    if (mysqli_stmt_execute($insertCommentStmt)) {
        // Log the comment action
        error_log("User $user_id commented on video $video_id");
        
        header("Location: $redirect_url?message=comment_added");
    } else {
        header("Location: $redirect_url?error=comment_failed");
    }
} else {
    // Invalid request method
    header("Location: /index.php?error=invalid_request");
}

exit;
?>
