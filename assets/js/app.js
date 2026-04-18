/**
 * app.js - Ana Site JavaScript
 * AJAX beğeni/liste/yorum, canlı arama, video sekmeler, animasyonlar
 */

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // NAVBAR
    // =====================================================
    const navbar = document.getElementById('mainNavbar');
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarLinks = document.getElementById('navbarLinks');

    // Scroll efekti
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }
    });

    // Mobil menü toggle
    navbarToggle?.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        navbarLinks.classList.toggle('active');
    });

    // Menü dışına tıklayınca kapat
    document.addEventListener('click', (e) => {
        if (navbarLinks?.classList.contains('active') &&
            !navbarLinks.contains(e.target) &&
            !navbarToggle.contains(e.target)) {
            navbarLinks.classList.remove('active');
            navbarToggle.classList.remove('active');
        }
    });

    // =====================================================
    // CANLI ARAMA
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    let searchTimer = null;

    searchInput?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            searchDropdown.classList.remove('active');
            return;
        }

        searchTimer = setTimeout(() => {
            fetch(`${SITE_URL}/api/search_api.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(item => {
                            html += `
                                <a href="${SITE_URL}/movie.php?id=${item.id}" class="search-result-item">
                                    <img src="${item.poster_url || 'https://via.placeholder.com/40x56/1a1a2e/666?text=Film'}" 
                                         alt="${escapeHtml(item.title)}" onerror="this.src='https://via.placeholder.com/40x56/1a1a2e/666?text=Film'">
                                    <div class="search-result-info">
                                        <h4>${escapeHtml(item.title)}</h4>
                                        <span>${item.year} • ${escapeHtml(item.director || '')}</span>
                                    </div>
                                </a>
                            `;
                        });
                        searchDropdown.innerHTML = html;
                        searchDropdown.classList.add('active');
                    } else {
                        searchDropdown.innerHTML = '<div class="search-result-item"><span>Sonuç bulunamadı</span></div>';
                        searchDropdown.classList.add('active');
                    }
                })
                .catch(err => console.error('Arama hatası:', err));
        }, 300);
    });

    // Arama dışına tıklayınca kapat
    document.addEventListener('click', (e) => {
        if (searchDropdown && !searchDropdown.contains(e.target) && e.target !== searchInput) {
            searchDropdown.classList.remove('active');
        }
    });

    // =====================================================
    // BEĞENİ SİSTEMİ (AJAX)
    // =====================================================
    document.addEventListener('click', function (e) {
        const likeBtn = e.target.closest('.btn-like');
        if (!likeBtn) return;

        e.preventDefault();

        if (!IS_LOGGED_IN) {
            showToast('Beğenmek için giriş yapmalısınız.', 'error');
            return;
        }

        const movieId = likeBtn.dataset.movieId;
        if (!movieId) return;

        likeBtn.classList.add('like-animation');
        setTimeout(() => likeBtn.classList.remove('like-animation'), 400);

        fetch(`${SITE_URL}/api/like.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `movie_id=${movieId}&csrf_token=${CSRF_TOKEN}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Tüm aynı film için olan beğeni butonlarını güncelle
                    document.querySelectorAll(`.btn-like[data-movie-id="${movieId}"]`).forEach(btn => {
                        const countSpan = btn.querySelector('.like-count');
                        if (countSpan) countSpan.textContent = data.like_count;

                        if (data.liked) {
                            btn.classList.add('active');
                            btn.querySelector('i')?.classList.replace('far', 'fas');
                        } else {
                            btn.classList.remove('active');
                            btn.querySelector('i')?.classList.replace('fas', 'far');
                        }
                    });
                } else {
                    showToast(data.message || 'Bir hata oluştu.', 'error');
                }
            })
            .catch(err => {
                console.error('Beğeni hatası:', err);
                showToast('Bir hata oluştu.', 'error');
            });
    });

    // =====================================================
    // İZLEME LİSTESİ (AJAX)
    // =====================================================
    document.addEventListener('click', function (e) {
        const watchlistBtn = e.target.closest('.btn-watchlist');
        if (!watchlistBtn) return;

        e.preventDefault();

        if (!IS_LOGGED_IN) {
            showToast('Listeye eklemek için giriş yapmalısınız.', 'error');
            return;
        }

        const movieId = watchlistBtn.dataset.movieId;
        if (!movieId) return;

        fetch(`${SITE_URL}/api/watchlist.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `movie_id=${movieId}&csrf_token=${CSRF_TOKEN}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll(`.btn-watchlist[data-movie-id="${movieId}"]`).forEach(btn => {
                        if (data.in_watchlist) {
                            btn.classList.add('active');
                            btn.querySelector('i')?.classList.replace('far', 'fas');
                            const text = btn.querySelector('.watchlist-text');
                            if (text) text.textContent = 'Listede';
                        } else {
                            btn.classList.remove('active');
                            btn.querySelector('i')?.classList.replace('fas', 'far');
                            const text = btn.querySelector('.watchlist-text');
                            if (text) text.textContent = 'Listeme Ekle';
                        }
                    });
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Bir hata oluştu.', 'error');
                }
            })
            .catch(err => {
                console.error('Watchlist hatası:', err);
                showToast('Bir hata oluştu.', 'error');
            });
    });

    // =====================================================
    // YORUM SİSTEMİ (AJAX)
    // =====================================================
    const commentForm = document.getElementById('commentForm');
    const commentsList = document.getElementById('commentsList');

    commentForm?.addEventListener('submit', function (e) {
        e.preventDefault();

        const content = this.querySelector('textarea[name="content"]').value.trim();
        const movieId = this.querySelector('input[name="movie_id"]').value;

        if (!content) {
            showToast('Lütfen bir yorum yazın.', 'error');
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner"></span> Gönderiliyor...';
        submitBtn.disabled = true;

        fetch(`${SITE_URL}/api/comment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&movie_id=${movieId}&content=${encodeURIComponent(content)}&csrf_token=${CSRF_TOKEN}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Yeni yorumu listeye ekle
                    const commentHtml = `
                        <div class="comment-item" data-comment-id="${data.comment.id}" style="animation: slideDown 0.3s ease">
                            <div class="comment-avatar">${data.comment.username.charAt(0).toUpperCase()}</div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-username">${escapeHtml(data.comment.username)}</span>
                                    <span class="comment-date">Az önce</span>
                                </div>
                                <p class="comment-text">${escapeHtml(data.comment.content)}</p>
                                <div class="comment-actions">
                                    <button class="comment-delete-btn" onclick="deleteComment(${data.comment.id})">
                                        <i class="fas fa-trash"></i> Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    if (commentsList) {
                        const noComments = commentsList.querySelector('.no-comments');
                        if (noComments) noComments.remove();
                        commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    }

                    // Formu temizle
                    this.querySelector('textarea[name="content"]').value = '';

                    // Yorum sayısını güncelle
                    const countEl = document.getElementById('commentCount');
                    if (countEl) {
                        countEl.textContent = parseInt(countEl.textContent || 0) + 1;
                    }

                    showToast('Yorumunuz eklendi!', 'success');
                } else {
                    showToast(data.message || 'Yorum gönderilemedi.', 'error');
                }
            })
            .catch(err => {
                console.error('Yorum hatası:', err);
                showToast('Bir hata oluştu.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // =====================================================
    // VİDEO SEKMELERİ
    // =====================================================
    const videoTabs = document.querySelectorAll('.video-tab');
    const videoTabContents = document.querySelectorAll('.video-tab-content');

    videoTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            videoTabs.forEach(t => t.classList.remove('active'));
            videoTabContents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(`tab-${target}`)?.classList.add('active');
        });
    });

    // =====================================================
    // VİDEO OYNATICI
    // =====================================================
    document.querySelectorAll('.video-item').forEach(item => {
        item.addEventListener('click', function () {
            const embedUrl = this.dataset.embedUrl;
            const player = document.getElementById('mainVideoPlayer');

            if (player && embedUrl) {
                player.src = embedUrl + '?autoplay=1&enablejsapi=1';

                // Aktif video öğesini güncelle
                document.querySelectorAll('.video-item').forEach(v => v.classList.remove('playing'));
                this.classList.add('playing');

                // Oynatıcıya smooth scroll
                document.querySelector('.video-player-wrapper')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    });

    // =====================================================
    // GALERİ LİGHTBOX
    // =====================================================
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxClose = document.getElementById('lightboxClose');

    document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function () {
            const imgSrc = this.querySelector('img')?.src;
            if (imgSrc && lightbox && lightboxImg) {
                lightboxImg.src = imgSrc;
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    lightboxClose?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', function (e) {
        if (e.target === this) closeLightbox();
    });

    function closeLightbox() {
        lightbox?.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ESC tuşu ile lightbox kapat
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });

    // =====================================================
    // PROFİL SEKMELERİ
    // =====================================================
    const profileTabs = document.querySelectorAll('.profile-tab');
    const profileTabContents = document.querySelectorAll('.profile-tab-content');

    profileTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            profileTabs.forEach(t => t.classList.remove('active'));
            profileTabContents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(`profile-${target}`)?.classList.add('active');
        });
    });

    // =====================================================
    // SCROLL ANİMASYONLARI
    // =====================================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
});

// =====================================================
// GLOBAL FONKSİYONLAR
// =====================================================

/**
 * Yorum silme fonksiyonu
 */
function deleteComment(commentId) {
    if (!confirm('Bu yorumu silmek istediğinize emin misiniz?')) return;

    fetch(`${SITE_URL}/api/comment.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&comment_id=${commentId}&csrf_token=${CSRF_TOKEN}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const commentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (commentEl) {
                    commentEl.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => commentEl.remove(), 300);
                }

                const countEl = document.getElementById('commentCount');
                if (countEl) {
                    countEl.textContent = Math.max(0, parseInt(countEl.textContent || 0) - 1);
                }

                showToast('Yorum silindi.', 'success');
            } else {
                showToast(data.message || 'Yorum silinemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Yorum silme hatası:', err);
            showToast('Bir hata oluştu.', 'error');
        });
}

/**
 * Toast bildirim göster
 */
function showToast(message, type = 'success') {
    // Eski toast varsa kaldır
    const oldToast = document.querySelector('.toast-notification');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    // Toast stilleri
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '30px',
        right: '30px',
        padding: '14px 24px',
        borderRadius: '12px',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        fontSize: '0.95rem',
        fontWeight: '500',
        zIndex: '3000',
        animation: 'slideUp 0.3s ease',
        boxShadow: '0 8px 30px rgba(0,0,0,0.4)',
        background: type === 'success' ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)',
        color: type === 'success' ? '#2ecc71' : '#e74c3c',
        border: `1px solid ${type === 'success' ? 'rgba(46, 204, 113, 0.3)' : 'rgba(231, 76, 60, 0.3)'}`,
        backdropFilter: 'blur(20px)',
        WebkitBackdropFilter: 'blur(20px)'
    });

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * HTML karakterlerini escape et
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// FadeOut animation (CSS'te yoksa ekle)
const fadeOutStyle = document.createElement('style');
fadeOutStyle.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(10px); }
    }
`;
document.head.appendChild(fadeOutStyle);
