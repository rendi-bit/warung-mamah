@php
    $isAdmin = auth()->check()
        && auth()->user()->role
        && auth()->user()->role->role_name === 'admin';

    $cartCount = 0;

    if (auth()->check() && !$isAdmin) {
        $cart = \App\Models\Cart::with('items')
            ->where('user_id', auth()->id())
            ->first();

        $cartCount = $cart ? $cart->items->sum('quantity') : 0;
    }
@endphp

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

<body>
    @php
        $isAdmin = auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'admin';
    @endphp
    <header class="site-header">
        <div class="container site-nav">
            <div class="brand">
                <span class="brand-badge brand-logo-image">
                    <img src="{{ asset('storage/avatars/mamah.jpeg') }}" alt="Logo Toko Tika">
                </span>
                <div>
                    <h1>TOKO TIKA</h1>
                    <p>UMKM Commerce Platform</p>
                </div>
            </div>

            <nav class="nav-menu">
                @auth
                    @if($isAdmin)
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                            Dashboard
                        </a>

                        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
                            Produk
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">
                            Home
                        </a>

                        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'is-active' : '' }}">
                            Produk
                        </a>

                        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}">
                            Pesanan Saya
                        </a>

                        <a href="{{ route('cart.index') }}" class="nav-cart-link {{ request()->routeIs('cart.*') ? 'is-active' : '' }}">
                            <span>Keranjang</span>

                            @if($cartCount > 0)
                                <span class="cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endif
                @else
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">
                        Home
                    </a>

                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'is-active' : '' }}">
                        Produk
                    </a>
                @endauth

                @if(!$isAdmin)
                    <div class="navbar-search">
                        <div class="navbar-search-box">
                            <i class="fas fa-search"></i>

                            <input
                                type="text"
                                id="navbarSearchInput"
                                placeholder="Cari produk atau kategori..."
                                autocomplete="off"
                            >
                        </div>

                        <div class="search-result-box" id="searchResultBox"></div>
                    </div>
                @endif
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
                    @if($isAdmin)
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('admin.products.index') }}">
                            <i class="fas fa-box-open"></i>
                            <span>Produk</span>
                        </a>
                    @else
                        <a href="{{ route('settings.index') }}">
                            <i class="fas fa-gear"></i>
                            <span>Pengaturan</span>
                        </a>

                        <a href="{{ route('wishlist.index') }}">
                            <i class="fas fa-heart"></i>
                            <span>Favorit Saya</span>                    
                        </a>
                        <a href="{{ route('orders.index') }}">
                            <i class="fas fa-bag-shopping"></i>
                            <span>Pesanan Saya</span>
                        </a>
                    @endif
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
           
        @auth
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a href="{{ route('admin.products.index') }}">Produk</a>
                @else
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('products.index') }}">Produk</a>
                    <a href="{{ route('orders.index') }}">Pesanan Saya</a>
                    <a href="{{ route('cart.index') }}" class="mobile-cart-link">
                        <span>Keranjang</span>
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('wishlist.index') }}">Favorit Saya</a>
                    <a href="{{ route('settings.index') }}">Pengaturan</a>
               
                    @endif
            @else
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('products.index') }}">Produk</a>
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
                    <div class="toast-icon">
                        <i class="fas fa-check"></i>
                    </div>

                    <div class="toast-content">
                        <strong>Berhasil</strong>
                        <p>{{ session('success') }}</p>
                    </div>

                    <button type="button" class="toast-close" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="toast toast-error" role="alert">
                    <div class="toast-icon">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>

                    <div class="toast-content">
                        <strong>Terjadi Kendala</strong>
                        <p>{{ session('error') }}</p>
                    </div>

                    <button type="button" class="toast-close" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="toast toast-info" role="status">
                    <div class="toast-icon">
                        <i class="fas fa-circle-info"></i>
                    </div>

                    <div class="toast-content">
                        <strong>Informasi</strong>
                        <p>{{ session('info') }}</p>
                    </div>

                    <button type="button" class="toast-close" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
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
    @if(!$isAdmin)
    <footer class="site-footer">
        <div class="container footer-grid footer-grid-modern">
            <div class="footer-brand">
                <div class="footer-logo-row">
                    <span class="footer-logo">
                        <img src="{{ asset('storage/avatars/mamah.jpeg') }}" alt="Logo Toko Tika">
                    </span>

                    <div>
                        <h3>TOKO TIKA</h3>
                        <small>UMKM Commerce Platform</small>
                    </div>
                </div>

                <p>
                    Toko UMKM modern yang menyediakan produk pilihan dengan kualitas terbaik
                    untuk kebutuhan harian masyarakat, khususnya area Bekasi Timur.
                </p>
            </div>

            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('products.index') }}">Produk</a>

                @auth
                    <a href="{{ route('orders.index') }}">Pesanan Saya</a>
                    <a href="{{ route('wishlist.index') }}">Favorit</a>
                @endauth

                @guest
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Daftar</a>
                @endguest
            </div>

            <div class="footer-col">
                <h4>Informasi</h4>
                <a href="{{ route('pages.about') }}">Tentang Kami</a>
                <a href="{{ route('pages.faq') }}">FAQ</a>
                <a href="{{ route('pages.how-to-shop') }}">Cara Belanja</a>
                <a href="{{ route('pages.privacy') }}">Kebijakan Privasi</a>
                <a href="{{ route('pages.terms') }}">Syarat & Ketentuan</a>
                <a href="{{ route('pages.contact') }}">Kontak Kami</a>
            </div>

            <div class="footer-col footer-contact">
                <h4>Kontak</h4>

                <div class="footer-contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <span>Email</span>
                        <strong>rendiprano15@gmail.com</strong>
                    </div>
                </div>

                <div class="footer-contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <span>WhatsApp</span>
                        <strong>0821-2505-2233</strong>
                    </div>
                </div>

                <div class="footer-contact-item">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <span>Alamat</span>
                        <strong>Pasar Rawa Kalong, Bekasi</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <p>© 2026 TOKO TIKA. All rights reserved.</p>

            <div class="footer-bottom-links">
                <a href="{{ route('pages.privacy') }}">Privasi</a>
                <a href="{{ route('pages.terms') }}">Ketentuan</a>
            </div>
        </div>
    </footer>
    @endif

    @if(!$isAdmin)
    <div class="chatbot-toggle" id="chatbotToggle">
        💬
    </div>
    @endif

    @auth
    <nav class="mobile-quick-actions {{ $isAdmin ? 'admin-quick-actions' : '' }}">
        @if($isAdmin)
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.products.index') }}">Produk</a>
        @else
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('products.index') }}">Produk</a>
            <a href="{{ route('cart.index') }}" class="mobile-quick-cart">
                Keranjang

                @if($cartCount > 0)
                    <span class="cart-badge mobile-cart-badge">{{ $cartCount }}</span>
                @endif
            </a>
            <a href="{{ route('orders.index') }}">Pesanan</a>
        @endif
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
const navbarSearchInput = document.getElementById('navbarSearchInput');
const searchResultBox = document.getElementById('searchResultBox');

let searchTimer = null;

if (navbarSearchInput && searchResultBox) {
    navbarSearchInput.addEventListener('input', function () {
        const keyword = this.value.trim();

        clearTimeout(searchTimer);

        if (keyword.length < 2) {
            searchResultBox.classList.remove('active');
            searchResultBox.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(async function () {
            try {
                const response = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(keyword)}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const products = await response.json();

                if (!products.length) {
                    searchResultBox.innerHTML = `
                        <div class="search-empty">
                            Produk tidak ditemukan.
                        </div>
                    `;
                    searchResultBox.classList.add('active');
                    return;
                }

                searchResultBox.innerHTML = products.map(function (product) {
                    const image = product.image
                        ? `<img src="${product.image}" alt="${product.name}">`
                        : `<div class="search-product-placeholder"><i class="fas fa-box"></i></div>`;

                    return `
                        <a href="${product.url}" class="search-result-item">
                            <div class="search-result-image">
                                ${image}
                            </div>

                            <div class="search-result-content">
                                <strong>${product.name}</strong>
                                <span>${product.category}</span>
                                <small>${product.price}</small>
                            </div>
                        </a>
                    `;
                }).join('');

                searchResultBox.classList.add('active');
            } catch (error) {
                searchResultBox.innerHTML = `
                    <div class="search-empty">
                        Terjadi kesalahan saat mencari produk.
                    </div>
                `;
                searchResultBox.classList.add('active');
            }
        }, 300);
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.navbar-search')) {
            searchResultBox.classList.remove('active');
        }
    });

    navbarSearchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            const keyword = navbarSearchInput.value.trim();

            if (keyword.length > 0) {
                window.location.href = `{{ route('products.index') }}?search=${encodeURIComponent(keyword)}`;
            }
        }
    });
}
</script>   
</body>
</html>