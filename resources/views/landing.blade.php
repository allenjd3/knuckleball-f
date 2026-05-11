<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knuckleball - The Modern Home for TTM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <!-- MailerLite Universal -->
    <script>
        (function(w,d,e,u,f,l,n){w[f]=w[f]||function(){(w[f].q=w[f].q||[])
        .push(arguments);},l=d.createElement(e),l.async=1,l.src=u,
        n=d.getElementsByTagName(e)[0],n.parentNode.insertBefore(l,n);})
        (window,document,'script','https://assets.mailerlite.com/js/universal.js','ml');
        ml('account', '1340045');
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes scroll {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-scroll { animation: scroll 30s linear infinite; }
        .animate-scroll:hover { animation-play-state: paused; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen bg-white font-sans selection:bg-red-100 selection:text-red-900">

    <!-- NAVBAR -->
    <nav class="bg-[#CB504B] text-white">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="text-2xl font-black tracking-tighter">
                KNUCKLEBALL
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold tracking-wider">
                <a href="#features" class="hover:text-red-200 transition-colors">FEATURES</a>
                <a href="{{ route('players.index') }}" class="hover:text-red-200 transition-colors">TTM DATABASE</a>
                <a href="#contact" class="hover:text-red-200 transition-colors">CONTACT</a>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-red-200 transition-colors hidden sm:block">Log In</a>
                <a href="{{ route('register') }}" class="bg-white text-[#CB504B] hover:bg-gray-50 text-sm font-bold px-5 py-2.5 rounded-full transition-colors shadow-sm">
                    Join Free
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="bg-[#CB504B] text-white pt-16 pb-24 lg:pt-24 lg:pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">

            <div class="text-center lg:text-left z-10">
                <div class="inline-block border border-white/30 bg-white/10 rounded-full px-4 py-1.5 text-xs font-bold tracking-widest uppercase mb-6 backdrop-blur-sm">
                    Registration Now Open
                </div>
                <h1 class="text-5xl lg:text-7xl font-black tracking-tighter leading-[0.95] mb-6">
                    THE MODERN, 100% FREE HOME FOR TTM.
                </h1>
                <p class="text-lg lg:text-xl text-red-100 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                    Leave the clunky spreadsheets and outdated forums behind. Track your sends, find verified addresses, and show off your collection with the fastest growing community in the hobby.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-white text-[#CB504B] text-base font-bold px-8 py-4 rounded-full hover:bg-gray-50 transition-colors shadow-lg flex items-center justify-center gap-2">
                        Create Free Account
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <p class="text-sm text-red-200 font-medium mt-2 sm:mt-0">Takes 30 seconds. No credit card required.</p>
                </div>
            </div>

            <div class="relative mt-12 lg:mt-0 lg:ml-10">
                <div class="absolute inset-0 bg-red-600 blur-3xl opacity-50 rounded-full scale-75"></div>
                <div class="relative z-10">

                    <!-- Mini Celebration Card (Hero Graphic) -->
                    <div class="bg-white border-t-4 border-t-[#CB504B] rounded-xl p-5 shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500 w-full max-w-md mx-auto text-gray-900">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img src="{{ asset('images/hero-avatar.png') }}"
                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=R+N&background=CB504B&color=fff';"
                                         class="w-10 h-10 rounded-full object-cover" alt="User" />
                                    <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#CB504B]"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm text-gray-500">
                                        <span class="font-semibold text-gray-900">@raulnino</span> got a return from <span class="font-semibold text-gray-900">Rollie Fingers</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Turnaround: <span class="font-semibold text-gray-900">14 Days</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="w-full aspect-[3/4] bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center relative overflow-hidden group p-2">
                                <img src="{{ asset('images/hero-card.png') }}"
                                     onerror="this.onerror=null;this.src='https://placehold.co/400x533/e5e7eb/6b7280?text=Card+Return';"
                                     alt="Signed Card Return"
                                     class="w-full h-full object-contain rounded shadow-sm group-hover:scale-105 transition-transform duration-500" />
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded text-sm text-[#CB504B] bg-red-50 font-medium">
                                <span>🔥</span><span>12</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded text-sm text-gray-500 bg-gray-50">
                                <span>🤩</span><span>4</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- LIVE TICKER -->
    <div class="bg-gray-900 text-white py-3 overflow-hidden whitespace-nowrap border-b border-gray-800 relative flex items-center">
        <div class="absolute left-0 w-16 h-full bg-gradient-to-r from-gray-900 to-transparent z-10"></div>
        <div class="absolute right-0 w-16 h-full bg-gradient-to-l from-gray-900 to-transparent z-10"></div>

        <div class="inline-block animate-scroll text-sm font-medium">
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-orange-400">🔥</span> <span class="font-bold">Mail day for @james_ttm:</span> Peyton Manning (8 Days)</span>
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-yellow-400">🏆</span> <span class="font-bold">@bailey_collects secured:</span> Briana Scurry (22 Days)</span>
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-orange-400">🔥</span> <span class="font-bold">Mail day for @raulnino:</span> Sid Bream (14 Days)</span>
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-green-400">📬</span> <span class="font-bold">@david_cards sent:</span> Tim Tebow (Football)</span>
            {{-- Duplicate for seamless scroll loop --}}
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-orange-400">🔥</span> <span class="font-bold">Mail day for @james_ttm:</span> Peyton Manning (8 Days)</span>
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-yellow-400">🏆</span> <span class="font-bold">@bailey_collects secured:</span> Briana Scurry (22 Days)</span>
            <span class="mx-4 text-gray-400">•</span>
            <span><span class="text-orange-400">🔥</span> <span class="font-bold">Mail day for @raulnino:</span> Sid Bream (14 Days)</span>
        </div>
    </div>

    <!-- FEATURES SECTION -->
    <section id="features" class="py-24 bg-gray-50 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-sm font-bold text-[#CB504B] tracking-widest uppercase mb-3">Features</h2>
                <h3 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight leading-tight">
                    Everything you need to grow your collection.
                </h3>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">

                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-50 text-[#CB504B] rounded-xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Smart Tracking</h4>
                    <p class="text-gray-500 text-sm leading-relaxed">Log your sends and let Knuckleball automatically calculate your turnaround times, success rates, and pending inventory in real-time.</p>
                </div>

                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-50 text-[#CB504B] rounded-xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">The Mail Day Feed</h4>
                    <p class="text-gray-500 text-sm leading-relaxed">Stop collecting in a vacuum. Share your wins, react to crazy returns with hobby-specific emojis, and chat with the community.</p>
                </div>

                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-50 text-[#CB504B] rounded-xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Player Tags & Intel</h4>
                    <p class="text-gray-500 text-sm leading-relaxed">Stop guessing. Access verified addresses, exact fee requirements, and community-driven tags before you send.</p>
                </div>

                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-50 text-[#CB504B] rounded-xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Curated Packs</h4>
                    <p class="text-gray-500 text-sm leading-relaxed">Build, share, and follow custom player lists. Discover new targets through community-created packs like "90s Olympians" or "HOF Signers".</p>
                </div>

            </div>
        </div>
    </section>

    <!-- WHAT IS TTM SECTION -->
    <section class="py-24 bg-white px-6 border-t border-gray-100">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center gap-12">
            <div class="w-full md:w-1/2">
                <div class="relative w-full max-w-[280px] mx-auto aspect-[3/4] mt-8 md:mt-0">
                    <div class="absolute inset-0 transform -rotate-6 translate-x-4 translate-y-4 bg-white p-2 rounded-xl shadow-md border border-gray-200 transition-all duration-500 hover:-rotate-12 hover:-translate-x-2 hover:translate-y-2 z-10 cursor-pointer">
                        <img src="{{ asset('images/ttm-card-back.png') }}"
                             onerror="this.onerror=null;this.src='https://placehold.co/400x533/e5e7eb/6b7280?text=Football+Card';"
                             alt="Signed Football Card" class="w-full h-full object-cover rounded" />
                    </div>
                    <div class="absolute inset-0 transform rotate-3 -translate-x-2 -translate-y-2 bg-white p-2 rounded-xl shadow-2xl border border-gray-200 transition-all duration-500 hover:rotate-6 hover:-translate-y-4 z-20 cursor-pointer">
                        <img src="{{ asset('images/ttm-card-front.png') }}"
                             onerror="this.onerror=null;this.src='https://placehold.co/400x533/e5e7eb/6b7280?text=Baseball+Card';"
                             alt="Vintage Baseball Card" class="w-full h-full object-cover rounded" />
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-4">WHAT IS TTM?</h2>
                <p class="text-gray-600 mb-8 leading-relaxed">
                    TTM (Through The Mail) is a popular method for sports fans and collectors to acquire autographs by mailing cards and requests directly to athletes. It's a fantastic way to connect with your favorite players and build your dream collection from home.
                </p>
                <a href="{{ route('players.index') }}" class="inline-block border-2 border-[#CB504B] text-[#CB504B] font-bold px-8 py-3 rounded-full hover:bg-red-50 transition-colors">
                    Get In The Game
                </a>
            </div>
        </div>
    </section>

    <!-- COMMUNITY SHOWCASE CAROUSEL -->
    <section class="py-24 bg-gray-900 border-t border-gray-800 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 mb-12 flex flex-col md:flex-row justify-between items-end gap-6">
            <div>
                <h2 class="text-sm font-bold text-[#CB504B] tracking-widest uppercase mb-3">The Vault</h2>
                <h3 class="text-3xl md:text-5xl font-black text-white tracking-tight">Recent Grails.</h3>
            </div>
            <div class="flex gap-3">
                <button id="prevBtn" aria-label="Previous image" class="w-12 h-12 rounded-full border border-gray-700 bg-gray-800 text-white flex items-center justify-center hover:bg-[#CB504B] hover:border-[#CB504B] transition-all focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button id="nextBtn" aria-label="Next image" class="w-12 h-12 rounded-full border border-gray-700 bg-gray-800 text-white flex items-center justify-center hover:bg-[#CB504B] hover:border-[#CB504B] transition-all focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative">
            <div class="overflow-hidden rounded-2xl shadow-2xl bg-black border border-gray-800 relative aspect-[16/9] md:aspect-[21/9]">
                <div id="carouselTrack" class="flex w-full h-full transition-transform duration-700 ease-in-out">
                    <img src="{{ asset('images/vault/mark-grace.png') }}"       class="w-full h-full object-cover flex-shrink-0" alt="Mark Grace Return" />
                    <img src="{{ asset('images/vault/wade-boggs.png') }}"       class="w-full h-full object-cover flex-shrink-0" alt="Wade Boggs Return" />
                    <img src="{{ asset('images/vault/ted-kazanski.png') }}"     class="w-full h-full object-cover flex-shrink-0" alt="Ted Kazanski Return" />
                    <img src="{{ asset('images/vault/vern-law.png') }}"         class="w-full h-full object-cover flex-shrink-0" alt="Vern Law Return" />
                    <img src="{{ asset('images/vault/bill-mazeroski.png') }}"   class="w-full h-full object-cover flex-shrink-0" alt="Bill Mazeroski Return" />
                    <img src="{{ asset('images/vault/jim-palmer.png') }}"       class="w-full h-full object-cover flex-shrink-0" alt="Jim Palmer Return" />
                    <img src="{{ asset('images/vault/bill-bradley.jpg') }}"     class="w-full h-full object-cover flex-shrink-0" alt="Bill Bradley Return" />
                    <img src="{{ asset('images/vault/bobby-richardson.jpg') }}" class="w-full h-full object-cover flex-shrink-0" alt="Bobby Richardson Return" />
                </div>
            </div>
        </div>
    </section>

    <!-- FREE BANNER SECTION -->
    <section class="bg-gray-50 py-16 px-6 border-t border-gray-200">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mb-6">
                <span class="text-3xl font-black">$0</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-4">
                No Paywalls for Outdated Data.
            </h2>
            <p class="text-gray-600 text-lg mb-8 max-w-2xl mx-auto">
                Other databases charge premium subscriptions for clunky interfaces that haven't been updated since 2008. Knuckleball is modern, lightning-fast, and completely free—save your money for stamps and top loaders.
            </p>
            <a href="{{ route('register') }}" class="inline-block bg-[#CB504B] text-white text-base font-bold px-10 py-4 rounded-full hover:bg-red-800 transition-colors shadow-lg">
                Claim Your Free Account
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="contact" class="bg-gray-900 text-gray-400 py-12 px-6 border-t border-gray-800 text-sm">
        <div class="max-w-7xl mx-auto flex flex-col items-center">
            <div class="text-2xl font-black tracking-tighter text-white mb-8">
                KNUCKLEBALL
            </div>

            <p class="text-center max-w-lg mb-8">
                Are you a player or agent? Reach out to us directly for profile verification and ownership requests. We're here to help ensure accurate representation.
            </p>

            <a href="mailto:hello@knuckleball.app?subject=Player%20Verification&body=Hi%20Knuckleball"
               class="border border-gray-600 hover:border-gray-400 text-white px-6 py-2 rounded-full transition-colors mb-12 font-medium inline-block">
                Drop Us A Line
            </a>

            <div class="flex gap-4 mb-8">
                <a href="https://www.facebook.com/profile.php?id=61570261658450" aria-label="Facebook"
                   class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-gray-800 hover:border-gray-500 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 group-hover:text-white transition-colors"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://www.instagram.com/knuckleballapp/" aria-label="Instagram"
                   class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-gray-800 hover:border-gray-500 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 group-hover:text-white transition-colors"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                </a>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8 border-t border-gray-800 pt-8 w-full justify-center">
                <p>Copyright &copy; 2026 Knuckleball | All rights reserved | Made in the USA</p>
                <a href="#" class="hover:text-white transition-colors">DMCA Policy</a>
            </div>
        </div>
    </footer>

    <!-- CAROUSEL SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track    = document.getElementById('carouselTrack');
            const prevBtn  = document.getElementById('prevBtn');
            const nextBtn  = document.getElementById('nextBtn');
            const slides   = track.children;
            const slideCount = slides.length;
            let currentIndex = 0;
            let intervalId;

            const updateCarousel = () => {
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
            };

            const nextSlide = () => {
                currentIndex = (currentIndex + 1) % slideCount;
                updateCarousel();
            };

            const prevSlide = () => {
                currentIndex = (currentIndex - 1 + slideCount) % slideCount;
                updateCarousel();
            };

            const startAutoPlay  = () => { intervalId = setInterval(nextSlide, 4000); };
            const resetAutoPlay  = () => { clearInterval(intervalId); startAutoPlay(); };

            nextBtn.addEventListener('click', () => { nextSlide(); resetAutoPlay(); });
            prevBtn.addEventListener('click', () => { prevSlide(); resetAutoPlay(); });

            track.addEventListener('mouseenter', () => clearInterval(intervalId));
            track.addEventListener('mouseleave', startAutoPlay);

            startAutoPlay();
        });
    </script>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-899C14WKNP"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-899C14WKNP');
    </script>

</body>
</html>
