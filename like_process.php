<?php
// like_process.php - Process video like actions
// Handles adding/removing likes for videos

require_once 'db.php';
require_once 'includes/auth.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '/index.php';
    
    if ($video_id <= 0) {
        header("Location: $redirect_url?error=invalid_video");
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
    
    // Check if user has already liked this video
    $likeCheckQuery = "SELECT id FROM likes WHERE user_id = ? AND video_id = ?";
    $likeCheckStmt = mysqli_prepare($conn, $likeCheckQuery);
    mysqli_stmt_bind_param($likeCheckStmt, "ii", $user_id, $video_id);
    mysqli_stmt_execute($likeCheckStmt);
    $likeCheckResult = mysqli_stmt_get_result($likeCheckStmt);
    
    if (mysqli_num_rows($likeCheckResult) > 0) {
        // User has already liked, so remove the like (unlike)
        $deleteLikeQuery = "DELETE FROM likes WHERE user_id = ? AND video_id = ?";
        $deleteLikeStmt = mysqli_prepare($conn, $deleteLikeQuery);
        mysqli_stmt_bind_param($deleteLikeStmt, "ii", $user_id, $video_id);
        
        if (mysqli_stmt_execute($deleteLikeStmt)) {
            // Update video likes count
            $updateVideoQuery = "UPDATE videos SET likes = likes - 1 WHERE id = ?";
            $updateVideoStmt = mysqli_prepare($conn, $updateVideoQuery);
            mysqli_stmt_bind_param($updateVideoStmt, "i", $video_id);
            mysqli_stmt_execute($updateVideoStmt);
            
            // Log the unlike action
            error_log("User $user_id unliked video $video_id");
            
            header("Location: $redirect_url?message=unliked");
        } else {
            header("Location: $redirect_url?error=unlike_failed");
        }
    } else {
        // User hasn't liked yet, so add the like
        $insertLikeQuery = "INSERT INTO likes (user_id, video_id, liked_at) VALUES (?, ?, NOW())";
        $insertLikeStmt = mysqli_prepare($conn, $insertLikeQuery);
        mysqli_stmt_bind_param($insertLikeStmt, "ii", $user_id, $video_id);
        
        if (mysqli_stmt_execute($insertLikeStmt)) {
            // Update video likes count
            $updateVideoQuery = "UPDATE videos SET likes = likes + 1 WHERE id = ?";
            $updateVideoStmt = mysqli_prepare($conn, $updateVideoQuery);
            mysqli_stmt_bind_param($updateVideoStmt, "i", $video_id);
            mysqli_stmt_execute($updateVideoStmt);
            
            // Log the like action
            error_log("User $user_id liked video $video_id");
            
            header("Location: $redirect_url?message=liked");
        } else {
            header("Location: $redirect_url?error=like_failed");
        }
    }
} else {
    // Invalid request method
    header("Location: /index.php?error=invalid_request");
}

exit;
?>
