<?php
// demo.php - Demo version without database dependency
// This file demonstrates the UI/UX without requiring database setup

$page_title = "Demo - Stralentech.ai";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stralentech.ai - Platform Komunitas Video Pendek AI</title>
    <meta name="description" content="Platform komunitas video pendek AI - Stralentech.ai">
    <meta name="keywords" content="AI, video, komunitas, teknologi, artificial intelligence">
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="/assets/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo/Brand -->
                <div class="logo">
                    <a href="/demo.php">
                        <h1>Stralentech.ai</h1>
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="nav">
                    <div class="nav-links">
                        <a href="#register" class="btn btn-primary">Get Started</a>
                        <a href="#login" class="btn btn-secondary">Sign In</a>
                    </div>
                </nav>
                
                <!-- Mobile menu toggle -->
                <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-content">
            <div class="mobile-nav-links">
                <a href="#demo" class="mobile-nav-link">Home</a>
                <a href="#register" class="mobile-nav-link">Get Started</a>
                <a href="#login" class="mobile-nav-link">Sign In</a>
            </div>
        </div>
    </div>
    
    <!-- Main Content Container -->
    <main class="main-content">
        
        <!-- Demo Notice -->
        <div class="alert alert-warning" style="margin: 1rem auto; max-width: 1200px;">
            <strong>🚀 Demo Mode:</strong> Ini adalah demo UI/UX Stralentech.ai. 
            Untuk versi lengkap dengan database, silakan setup database MySQL terlebih dahulu.
        </div>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h2>Platform Komunitas Video Pendek AI</h2>
                <p>
                    Bergabunglah dengan 1,000+ kreator dalam menciptakan 
                    konten video AI inovatif. Upload, bagikan, dan nikmati 500+ 
                    video berkualitas tinggi dari komunitas global.
                </p>
                
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                    <a href="#register" class="cta-button">Mulai Sekarang</a>
                    <a href="#login" class="btn btn-secondary btn-large">Masuk</a>
                </div>
                
                <!-- Search Bar -->
                <div style="max-width: 500px; margin: 2rem auto 0;">
                    <form style="display: flex; gap: 0.5rem;">
                        <input 
                            type="text" 
                            placeholder="Cari video atau kategori..." 
                            style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-secondary); color: var(--text-primary);"
                        >
                        <button type="button" class="btn btn-primary">Cari</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Category Filter Section -->
        <section class="categories">
            <div class="container">
                <button class="active">Semua Kategori</button>
                <button>AI Humor</button>
                <button>Technology</button>
                <button>Short Movie</button>
            </div>
        </section>

        <!-- Video Grid Section -->
        <section class="video-grid">
            <div class="container">
                <!-- Demo Video Card 1 -->
                <div class="video-card">
                    <div class="video-label">🔥 Best of the Week</div>
                    
                    <!-- Placeholder Video -->
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #1a1a1a, #2a2a2a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎬</div>
                            <div>Demo Video AI</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">Technology Category</div>
                        </div>
                    </div>
                    
                    <!-- Video Info -->
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--accent-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                Technology
                            </span>
                            <span class="premium-badge">Premium</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh aiCreator</span>
                            <span>2 jam lalu</span>
                        </div>
                        
                        <!-- Video Actions -->
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 42
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 8
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Demo Video Card 2 -->
                <div class="video-card">
                    <div class="video-label">🎯 Video Sponsor</div>
                    
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #2a1a2a, #3a2a3a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🤖</div>
                            <div>AI Comedy Sketch</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">AI Humor Category</div>
                        </div>
                    </div>
                    
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--accent-secondary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                AI Humor
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh funnyAI</span>
                            <span>5 jam lalu</span>
                        </div>
                        
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 28
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 15
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Demo Video Card 3 -->
                <div class="video-card">
                    <div class="video-label">⭐ Top of the Month</div>
                    
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #1a2a1a, #2a3a2a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎭</div>
                            <div>AI Short Film</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">Short Movie Category</div>
                        </div>
                    </div>
                    
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                Short Movie
                            </span>
                            <span class="premium-badge">Premium</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh filmMaker</span>
                            <span>1 hari lalu</span>
                        </div>
                        
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 156
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 32
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Demo Video Card 4 -->
                <div class="video-card">
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #2a1a1a, #3a2a2a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚡</div>
                            <div>Tech Innovation</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">Technology Category</div>
                        </div>
                    </div>
                    
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--accent-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                Technology
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh techGuru</span>
                            <span>3 hari lalu</span>
                        </div>
                        
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 89
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 21
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Demo Video Card 5 -->
                <div class="video-card">
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #1a1a2a, #2a2a3a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">😂</div>
                            <div>AI Meme Generator</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">AI Humor Category</div>
                        </div>
                    </div>
                    
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--accent-secondary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                AI Humor
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh memeBot</span>
                            <span>1 minggu lalu</span>
                        </div>
                        
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 73
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 18
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Demo Video Card 6 -->
                <div class="video-card">
                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #2a2a1a, #3a3a2a); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); border-radius: 0.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎨</div>
                            <div>AI Art Creation</div>
                            <div style="font-size: 0.875rem; opacity: 0.7;">Short Movie Category</div>
                        </div>
                    </div>
                    
                    <div class="video-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <span class="video-category" style="background: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                Short Movie
                            </span>
                            <span class="premium-badge">Premium</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                            <span>oleh artAI</span>
                            <span>2 minggu lalu</span>
                        </div>
                        
                        <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    ❤️ 124
                                </span>
                                <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem;">
                                    💬 27
                                </span>
                            </div>
                            <button class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section style="background: var(--bg-secondary); padding: 3rem 0;">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 2rem;">Statistik Platform</h2>
                <div class="admin-stats">
                    <div class="stat-card">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Total Video</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">1,000+</div>
                        <div class="stat-label">Total Member</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">15,000+</div>
                        <div class="stat-label">Total Like</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section style="padding: 3rem 0;">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 3rem;">Fitur Platform</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎬</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Upload Video AI</h4>
                        <p style="color: var(--text-secondary);">
                            Upload video AI berkualitas tinggi dengan durasi maksimal 1 menit. 
                            Format MP4, ukuran maksimal 50MB.
                        </p>
                    </div>
                    
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">❤️</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Like & Comment</h4>
                        <p style="color: var(--text-secondary);">
                            Berinteraksi dengan komunitas melalui sistem like dan komentar. 
                            Satu user hanya bisa like sekali per video.
                        </p>
                    </div>
                    
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🏷️</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Kategori Beragam</h4>
                        <p style="color: var(--text-secondary);">
                            Jelajahi berbagai kategori: AI Humor, Technology, Short Movie. 
                            Filter berdasarkan minat Anda.
                        </p>
                    </div>
                    
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Premium Features</h4>
                        <p style="color: var(--text-secondary);">
                            Upgrade ke premium untuk upload unlimited, badge eksklusif, 
                            dan prioritas review dari admin.
                        </p>
                    </div>
                    
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🛡️</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Moderasi Ketat</h4>
                        <p style="color: var(--text-secondary);">
                            Semua video direview oleh admin sebelum dipublikasi. 
                            Konten berkualitas dan aman untuk semua.
                        </p>
                    </div>
                    
                    <div class="benefit-card" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
                        <h4 style="color: var(--text-primary); margin-bottom: 1rem;">Responsive Design</h4>
                        <p style="color: var(--text-secondary);">
                            Akses dari desktop, tablet, atau mobile. 
                            UI responsif dengan horizontal scroll di mobile.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Stralentech.ai</h3>
                    <p>Platform komunitas video pendek AI terdepan. Bergabunglah dengan ribuan kreator dalam menciptakan konten inovatif.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="#demo">Home</a></li>
                        <li><a href="#register">Daftar</a></li>
                        <li><a href="#login">Login</a></li>
                        <li><a href="#premium">Premium</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Kategori</h4>
                    <ul>
                        <li><a href="#ai-humor">AI Humor</a></li>
                        <li><a href="#technology">Technology</a></li>
                        <li><a href="#short-movie">Short Movie</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#panduan">Panduan Upload</a></li>
                        <li><a href="#aturan">Aturan Komunitas</a></li>
                        <li><a href="#kontak">Kontak</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2025 Stralentech.ai. All Rights Reserved.</p>
                    <div class="footer-links">
                        <a href="#privacy">Privacy Policy</a>
                        <a href="#terms">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle function
        function toggleMobileMenu() {
            const mobileNav = document.getElementById('mobileNav');
            mobileNav.classList.toggle('active');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileNav = document.getElementById('mobileNav');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (!mobileNav.contains(event.target) && !toggle.contains(event.target)) {
                mobileNav.classList.remove('active');
            }
        });

        // Category button interactions
        document.querySelectorAll('.categories button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.categories button').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
            });
        });

        // Video card hover effects
        document.querySelectorAll('.video-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-hide demo notice after 10 seconds
        setTimeout(function() {
            const demoNotice = document.querySelector('.alert-warning');
            if (demoNotice) {
                demoNotice.style.opacity = '0';
                setTimeout(function() {
                    demoNotice.style.display = 'none';
                }, 300);
            }
        }, 10000);
    </script>

    <style>
        /* Additional styles for demo */
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

        .benefit-card {
            transition: all 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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
</body>
</html>
