<?php
// index.php - Homepage with video grid and hero section
// This is the main landing page of Stralentech.ai

$page_title = "Home";
require_once 'db.php';
require_once 'includes/auth.php';

// Handle category filtering
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the main video query
$whereConditions = ["v.approved = 1"];
$queryParams = [];

if (!empty($categoryFilter)) {
    $whereConditions[] = "c.name = ?";
    $queryParams[] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(v.filename LIKE ? OR c.name LIKE ?)";
    $queryParams[] = "%$searchQuery%";
    $queryParams[] = "%$searchQuery%";
}

$whereClause = implode(" AND ", $whereConditions);

// Main query to fetch approved videos with category info and user info
$videoQuery = "
    SELECT v.*, c.name as category_name, u.email as uploader_email, u.is_premium as uploader_premium
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    JOIN users u ON v.user_id = u.id
    WHERE $whereClause
    ORDER BY v.upload_time DESC
    LIMIT 50
";

$stmt = mysqli_prepare($conn, $videoQuery);
if (!empty($queryParams)) {
    $types = str_repeat('s', count($queryParams));
    mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
}
mysqli_stmt_execute($stmt);
$videosResult = mysqli_stmt_get_result($stmt);

// Get categories for filter buttons
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get statistics for hero section
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM videos WHERE approved = 1) as total_videos,
        (SELECT COUNT(*) FROM users WHERE role = 'member') as total_users,
        (SELECT COUNT(*) FROM likes) as total_likes
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

// Function to determine video labels
function getVideoLabel($video) {
    // Check if it's a sponsored video
    if ($video['sponsor'] == 1) {
        return "🎯 Video Sponsor";
    }
    
    // Check for "Best of the Week" (most likes in last 7 days)
    // This would require a more complex query in production
    if ($video['likes'] > 10) { // Simplified condition
        return "🔥 Best of the Week";
    }
    
    // Check for "Top of the Month" 
    if ($video['likes'] > 5) { // Simplified condition
        return "⭐ Top of the Month";
    }
    
    return "";
}

// Function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan lalu';
    return floor($time/31536000) . ' tahun lalu';
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h2>Platform Komunitas Video Pendek AI</h2>
        <p>
            Bergabunglah dengan <?php echo number_format($stats['total_users']); ?>+ kreator dalam menciptakan 
            konten video AI inovatif. Upload, bagikan, dan nikmati <?php echo number_format($stats['total_videos']); ?>+ 
            video berkualitas tinggi dari komunitas global.
        </p>
        
        <?php if (!isLoggedIn()): ?>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="/register.php" class="cta-button">Mulai Sekarang</a>
                <a href="/login.php" class="btn btn-secondary btn-large">Masuk</a>
            </div>
        <?php else: ?>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <?php if (isAdmin()): ?>
                    <a href="/admin.php" class="cta-button">Panel Admin</a>
                <?php else: ?>
                    <a href="/member.php" class="cta-button">Upload Video</a>
                <?php endif; ?>
                
                <?php if (!isPremium() && !isAdmin()): ?>
                    <a href="/upgrade.php" class="btn btn-secondary btn-large">Upgrade Premium</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Search Bar -->
        <div style="max-width: 500px; margin: 2rem auto 0;">
            <form method="GET" action="index.php" style="display: flex; gap: 0.5rem;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari video atau kategori..." 
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-secondary); color: var(--text-primary);"
                >
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<!-- Category Filter Section -->
<section class="categories">
    <div class="container">
        <button onclick="window.location.href='index.php'" 
                class="<?php echo empty($categoryFilter) ? 'active' : ''; ?>">
            Semua Kategori
        </button>
        
        <?php if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0): ?>
            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                <button onclick="window.location.href='index.php?category=<?php echo urlencode($category['name']); ?>'"
                        class="<?php echo $categoryFilter === $category['name'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Video Grid Section -->
<section class="video-grid">
    <div class="container">
        <?php if ($videosResult && mysqli_num_rows($videosResult) > 0): ?>
            <?php while ($video = mysqli_fetch_assoc($videosResult)): ?>
                <?php $label = getVideoLabel($video); ?>
                
                <div class="video-card">
                    <?php if (!empty($label)): ?>
                        <div class="video-label"><?php echo $label; ?></div>
                    <?php endif; ?>
                    
                    <!-- Video Element -->
                    <video width="100%" height="200" controls controlsList="nodownload" preload="metadata">
                        <source src="/uploads/<?php echo htmlspecialchars($video['filename']); ?>" type="video/mp4">
                        Browser Anda tidak mendukung tag video.
                    </video>
                    
                    <!-- Video Info -->
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--accent-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                <?php echo htmlspecialchars($video['category_name']); ?>
                            </span>
                            
                            <?php if ($video['uploader_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh <?php echo htmlspecialchars(explode('@', $video['uploader_email'])[0]); ?></span>
                            <span><?php echo timeAgo($video['upload_time']); ?></span>
                        </div>
                        
                        <!-- Video Actions -->
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <!-- Like Button -->
                                <?php if (isLoggedIn()): ?>
                                    <form method="POST" action="/like_process.php" style="display: inline;">
                                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                                            ❤️ <?php echo $video['likes']; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                        ❤️ <?php echo $video['likes']; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Comment Count -->
                                <?php
                                $commentCountQuery = "SELECT COUNT(*) as count FROM comments WHERE video_id = ?";
                                $commentStmt = mysqli_prepare($conn, $commentCountQuery);
                                mysqli_stmt_bind_param($commentStmt, "i", $video['id']);
                                mysqli_stmt_execute($commentStmt);
                                $commentResult = mysqli_stmt_get_result($commentStmt);
                                $commentCount = mysqli_fetch_assoc($commentResult)['count'];
                                ?>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 <?php echo $commentCount; ?>
                                </span>
                            </div>
                            
                            <!-- View Details Button -->
                            <a href="/video.php?id=<?php echo $video['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- No Videos Found -->
            <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
                <h3 style="color: var(--text-secondary); margin-bottom: 1rem;">
                    <?php if (!empty($categoryFilter) || !empty($searchQuery)): ?>
                        Tidak ada video ditemukan
                    <?php else: ?>
                        Belum ada video tersedia
                    <?php endif; ?>
                </h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">
                    <?php if (!empty($categoryFilter) || !empty($searchQuery)): ?>
                        Coba ubah filter atau kata kunci pencarian Anda.
                    <?php else: ?>
                        Jadilah yang pertama untuk mengupload video!
                    <?php endif; ?>
                </p>
                
                <?php if (!isLoggedIn()): ?>
                    <a href="/register.php" class="btn btn-primary">Daftar Sekarang</a>
                <?php elseif (!isAdmin()): ?>
                    <a href="/member.php" class="btn btn-primary">Upload Video</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Statistics Section -->
<?php if ($stats['total_videos'] > 0): ?>
<section style="background: var(--bg-secondary); padding: 3rem 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Statistik Platform</h2>
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_videos']); ?></div>
                <div class="stat-label">Total Video</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Total Member</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_likes']); ?></div>
                <div class="stat-label">Total Like</div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* Additional styles for index page */
.categories button.active {
    background: var(--accent-primary);
    border-color: var(--accent-primary);
    color: white;
}

.video-info {
    background: var(--bg-tertiary);
}

.video-card:hover .video-info {
    background: var(--bg-secondary);
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .hero h2 {
        font-size: 2rem;
    }
    
    .hero p {
        font-size: 1rem;
    }
    
    .video-grid .container {
        display: flex;
        overflow-x: auto;
        gap: 1rem;
        padding-bottom: 1rem;
        scroll-snap-type: x mandatory;
    }
    
    .video-card {
        flex: 0 0 280px;
        scroll-snap-align: start;
    }
}
</style>

<script>
// Auto-refresh video grid every 5 minutes to show new approved videos
setInterval(function() {
    // Only refresh if user is not interacting with the page
    if (document.hidden === false) {
        const currentTime = Date.now();
        const lastActivity = localStorage.getItem('lastActivity') || 0;
        
        // Refresh if no activity for 5 minutes
        if (currentTime - lastActivity > 300000) {
            window.location.reload();
        }
    }
}, 300000);

// Track user activity
document.addEventListener('mousemove', function() {
    localStorage.setItem('lastActivity', Date.now());
});

document.addEventListener('keypress', function() {
    localStorage.setItem('lastActivity', Date.now());
});

// Smooth scroll for category buttons
document.querySelectorAll('.categories button').forEach(button => {
    button.addEventListener('click', function() {
        // Add loading state
        this.style.opacity = '0.7';
        this.innerHTML += ' <span class="loading"></span>';
    });
});

// Video lazy loading
document.addEventListener('DOMContentLoaded', function() {
    const videos = document.querySelectorAll('video');
    
    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const video = entry.target;
                video.load(); // Load video when it comes into view
            }
        });
    });
    
    videos.forEach(video => {
        videoObserver.observe(video);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
