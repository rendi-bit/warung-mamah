<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penjualan UMKM TOKO TIKA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
</head>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileTrigger = document.getElementById('profileTrigger');
    const profileMenu = document.getElementById('profileMenu');

    if (profileTrigger && profileMenu) {
        profileTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            profileMenu.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!document.getElementById('profileDropdown').contains(e.target)) {
                profileMenu.classList.remove('active');
            }
        });
    }
});
</script>
@php
    $whatsappNumber = '6281525874869';
    $whatsappMessage = urlencode('Halo admin TOKO TIKA, saya ingin bertanya tentang produk dan pemesanan.');
@endphp

<a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
   class="whatsapp-float"
   target="_blank"
   rel="noopener">
    <i class="fab fa-whatsapp"></i>
    <span>Chat WhatsApp</span>
</a>
<body>
    @php
        $isAdmin = auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'admin';
    @endphp
    <header class="site-header">
        <div class="container site-nav">
            <div class="brand">
                <span class="brand-badge">WM</span>
                <div>
                    <h1>TOKO TIKA</h1>
                    <p>UMKM Commerce Platform</p>
                </div>
            </div>

            <nav class="nav-menu">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Home</a>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'is-active' : '' }}">Produk</a>

                @auth
                    <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}">Pesanan Saya</a>
                    <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'is-active' : '' }}">Keranjang</a>

                    @if($isAdmin)
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'is-active' : '' }}">Admin</a>
                    @endif
                @endauth
            </nav>

            <div class="nav-actions">
    @auth
        <div class="profile-dropdown" id="profileDropdown">
            <button type="button" class="profile-trigger" id="profileTrigger">
            <div class="profile-avatar">
    @if(auth()->user()->avatar)
        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
    @else
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    @endif
</div>
            </button>

            <div class="profile-menu" id="profileMenu">
                <div class="profile-menu-header">
                <div class="profile-avatar large">
    @if(auth()->user()->avatar)
        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
    @else
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    @endif
</div>
                    <div>
                        <h4>{{ auth()->user()->name }}</h4>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="profile-menu-links">
                    <a href="{{ route('settings.index') }}">
                        <i class="fas fa-gear"></i>
                        <span>Pengaturan</span>
                    </a>

                     <a href="{{ route('wishlist.index') }}">
                        <i class="fas fa-heart" style="color:#ef4444;"></i>
                        <span>Favorit Saya</span>
                    </a>


                    @if(auth()->user()->role && auth()->user()->role->role_name === 'admin')
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    @endif

                    <a href="{{ route('orders.index') }}">
                        <i class="fas fa-bag-shopping"></i>
                        <span>Pesanan Saya</span>
                    </a>
                </div>

                <div class="profile-menu-footer">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="profile-logout-btn">
                            <i class="fas fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="btn btn-light">Login</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
    @endauth
</div>
        </div>
    </header>

    <aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
        <div class="mobile-drawer-header">
            <strong>Menu Navigasi</strong>
            <button type="button" id="navMobileClose" aria-label="Close menu">✕</button>
        </div>
        <nav class="mobile-drawer-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('products.index') }}">Produk</a>
            @auth
                <a href="{{ route('orders.index') }}">Pesanan Saya</a>
                <a href="{{ route('cart.index') }}">Keranjang</a>
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <a href="{{ route('profile.edit') }}">Profil</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </aside>
    <div class="mobile-drawer-backdrop" id="mobileDrawerBackdrop"></div>

    <main class="page-transition" id="pageTransitionRoot">
        <section class="topbar-insight">
            <div class="container topbar-insight-inner">
                <div class="insight-item">
                    <strong>Update Terakhir</strong>
                    <span>{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
            </div>
        </section>

        <div class="toast-wrap" id="toastWrap">
            @if(session('success'))
                <div class="toast toast-success" role="status">
                    <strong>Berhasil</strong>
                    <p>{{ session('success') }}</p>
                    <button type="button" class="toast-close" aria-label="Close">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="toast toast-error" role="alert">
                    <strong>Terjadi Kendala</strong>
                    <p>{{ session('error') }}</p>
                    <button type="button" class="toast-close" aria-label="Close">✕</button>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <button id="backToTopBtn" class="back-to-top-btn">↑</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const backToTopBtn = document.getElementById('backToTopBtn');

    window.addEventListener('scroll', function () {
        if (window.scrollY > 500) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    backToTopBtn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <h3>TOKO TIKA</h3>
                <p>
                    Toko UMKM modern yang menyediakan produk pilihan dengan kualitas terbaik
                    untuk kebutuhan harian masyarakat.
                </p>
            </div>

            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('products.index') }}">Produk</a>
                @auth
                    <a href="{{ route('orders.index') }}">Pesanan</a>
                @endauth
                @auth
                    <a href="{{ route('wishlist.index') }}">Favorit ❤️</a>
                @endauth
            </div>

            <div class="footer-col">
                <h4>Kontak</h4>
                <p>Email: rendiprano15@gmail.com</p>
                <p>Telepon: 0821-2505-2233</p>
                <p>Alamat: Pasar Rawa Kalong, Bekasi</p>
            </div>

            <div class="footer-col social-col">
                <h4>Ikuti Kami</h4>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <p>© 2026 TOKO TIKA. All rights reserved.</p>
        </div>
    </footer>

    <div class="chatbot-toggle" id="chatbotToggle">
    💬
</div>

    @auth
    <nav class="mobile-quick-actions">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('products.index') }}">Produk</a>
        <a href="{{ route('cart.index') }}">Keranjang</a>
        <a href="{{ route('orders.index') }}">Pesanan</a>
    </nav>
    @endauth

<div class="chatbot-box" id="chatbotBox">
    <div class="chatbot-header">
        <h4>TOKO TIKA AI</h4>
        <button type="button" id="chatbotClose">✕</button>
    </div>

    <div class="chatbot-body" id="chatbotBody">
        <div class="chatbot-message bot">
            Halo 👋 Saya asisten AI TOKO TIKA. Ada yang bisa saya bantu?
        </div>
    </div>

    <form class="chatbot-form" id="chatbotForm">
        @csrf
        <input type="text" id="chatbotInput" placeholder="Tulis pertanyaan..." autocomplete="off">
        <button type="submit">Kirim</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');
    const navMobileToggle = document.getElementById('navMobileToggle');
    const navMobileClose = document.getElementById('navMobileClose');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const mobileDrawerBackdrop = document.getElementById('mobileDrawerBackdrop');
    const pageTransitionRoot = document.getElementById('pageTransitionRoot');
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotBox = document.getElementById('chatbotBox');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotBody = document.getElementById('chatbotBody');
    const toastWrap = document.getElementById('toastWrap');

    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('wm-theme', theme);
        if (themeToggle) {
            themeToggle.textContent = theme === 'dark' ? '☀️ Light' : '🌙 Dark';
        }
    };

    const storedTheme = localStorage.getItem('wm-theme');
    const preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    setTheme(storedTheme || preferredTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
            setTheme(activeTheme === 'dark' ? 'light' : 'dark');
        });
    }

    const toggleDrawer = (isOpen) => {
        if (!mobileDrawer || !mobileDrawerBackdrop) return;
        mobileDrawer.classList.toggle('active', isOpen);
        mobileDrawerBackdrop.classList.toggle('active', isOpen);
        document.body.classList.toggle('drawer-open', isOpen);
        mobileDrawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    };

    if (navMobileToggle) {
        navMobileToggle.addEventListener('click', function () {
            toggleDrawer(true);
        });
    }
    if (navMobileClose) {
        navMobileClose.addEventListener('click', function () {
            toggleDrawer(false);
        });
    }
    if (mobileDrawerBackdrop) {
        mobileDrawerBackdrop.addEventListener('click', function () {
            toggleDrawer(false);
        });
    }

    if (chatbotToggle && chatbotBox) {
        chatbotToggle.addEventListener('click', function () {
            chatbotBox.classList.toggle('active');
        });
    }

    if (chatbotClose && chatbotBox) {
        chatbotClose.addEventListener('click', function () {
            chatbotBox.classList.remove('active');
        });
    }

    if (chatbotForm && chatbotInput && chatbotBody) {
    chatbotForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const message = chatbotInput.value.trim();
        if (!message) return;

        const userBubble = document.createElement('div');
        userBubble.className = 'chatbot-message user';
        userBubble.textContent = message;
        chatbotBody.appendChild(userBubble);

        chatbotInput.value = '';

        const loadingBubble = document.createElement('div');
        loadingBubble.className = 'chatbot-message bot';
        loadingBubble.textContent = 'Sedang mengetik...';
        chatbotBody.appendChild(loadingBubble);

        chatbotBody.scrollTop = chatbotBody.scrollHeight;

        try {
            const response = await fetch('{{ route("chatbot.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();

            loadingBubble.remove();

            const botBubble = document.createElement('div');
            botBubble.className = 'chatbot-message bot';
            botBubble.textContent = data.reply || 'Maaf, saya belum bisa menjawab.';
            chatbotBody.appendChild(botBubble);
        } catch (error) {
            loadingBubble.remove();

            const errorBubble = document.createElement('div');
            errorBubble.className = 'chatbot-message bot';
            errorBubble.textContent = 'Terjadi kesalahan. Coba lagi ya.';
            chatbotBody.appendChild(errorBubble);
        }

        chatbotBody.scrollTop = chatbotBody.scrollHeight;
    });
    }

    document.querySelectorAll('.product-card-image img').forEach(function (img) {
        const wrapper = img.closest('.product-card-image');
        if (!wrapper) return;
        wrapper.classList.add('is-loading');
        if (img.complete) {
            wrapper.classList.remove('is-loading');
            return;
        }
        img.addEventListener('load', function () {
            wrapper.classList.remove('is-loading');
        });
        img.addEventListener('error', function () {
            wrapper.classList.remove('is-loading');
        });
    });

    if (toastWrap) {
        toastWrap.querySelectorAll('.toast').forEach(function (toast, index) {
            setTimeout(function () {
                toast.classList.add('show');
            }, 120 + index * 80);

            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    toast.classList.remove('show');
                    setTimeout(function () { toast.remove(); }, 220);
                });
            }

            setTimeout(function () {
                toast.classList.remove('show');
                setTimeout(function () { toast.remove(); }, 220);
            }, 4200);
        });
    }

    if (pageTransitionRoot) {
        requestAnimationFrame(function () {
            pageTransitionRoot.classList.add('is-visible');
        });
    }

    document.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            const url = link.getAttribute('href');
            const isInternal = !!url && (url.startsWith('/') || url.startsWith(window.location.origin));
            const isAnchor = url && url.startsWith('#');
            if (!isInternal || isAnchor || link.target === '_blank' || event.ctrlKey || event.metaKey) return;
            if (!pageTransitionRoot) return;
            event.preventDefault();
            pageTransitionRoot.classList.remove('is-visible');
            setTimeout(function () {
                window.location.href = url;
            }, 180);
        });
    });
});
</script>   
</body>
</html>