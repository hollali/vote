<?php
require_once __DIR__ . '/../actions/connect.php';

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_url = preg_replace('#/(partials|admin)$#', '', $script_dir);

$active_election = get_active_election($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure online voting system">
    <title><?= $page_title ?? 'Voting System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= $base_url ?>/style.css">
    <script>
        function showToast(type, message) {
            var container = document.getElementById('toast-container');
            if (!container) return;
            var s = {
                success: { bg: 'bg-neutral-800 border border-neutral-700', text: 'text-neutral-200', icon: 'text-neutral-400', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>' },
                error: { bg: 'bg-neutral-800 border border-red-900/40', text: 'text-red-400', icon: 'text-red-500', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' },
                warning: { bg: 'bg-neutral-800 border border-yellow-900/40', text: 'text-yellow-400', icon: 'text-yellow-500', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>' },
                info: { bg: 'bg-neutral-800 border border-neutral-700', text: 'text-neutral-300', icon: 'text-neutral-400', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' }
            }[type] || { bg: 'bg-neutral-800 border border-neutral-700', text: 'text-neutral-300', icon: 'text-neutral-400', svg: '' };
            var toast = document.createElement('div');
            toast.className = 'flex items-center gap-3 ' + s.bg + ' rounded-lg px-4 py-3 shadow-xl transform transition-all duration-300 translate-x-full opacity-0';
            toast.innerHTML = '<svg class="w-4 h-4 flex-shrink-0 icon-pop ' + s.icon + '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' + s.svg + '</svg><span class="text-sm ' + s.text + '">' + message + '</span>';
            container.appendChild(toast);
            requestAnimationFrame(function() { toast.classList.remove('translate-x-full', 'opacity-0'); });
            setTimeout(function() { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(function() { toast.remove(); }, 300); }, 4000);
        }
        document.addEventListener('DOMContentLoaded', function() {
            var flash = document.getElementById('flash-data');
            if (flash && flash.dataset.type && flash.dataset.message) showToast(flash.dataset.type, flash.dataset.message);
        });
    </script>
</head>
<body class="min-h-screen bg-[#0a0a0a] text-neutral-200">

    <?php $flash = get_flash(); ?>
    <?php if ($flash): ?>
        <div id="flash-data" data-type="<?= $flash['type'] ?>" data-message="<?= sanitize($flash['message']) ?>" class="hidden"></div>
    <?php endif; ?>

    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2"></div>

    <nav class="bg-[#111] border-b border-neutral-800/80 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <a href="<?= $base_url ?>/partials/dashboard.php" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-white/[0.07] flex items-center justify-center group-hover:bg-white/[0.12] transition">
                        <svg class="w-4 h-4 text-neutral-400 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <span class="text-neutral-300 font-semibold text-sm tracking-tight hidden sm:block">Vote</span>
                </a>

                <div class="hidden md:flex items-center gap-0.5">
                    <a href="<?= $base_url ?>/partials/dashboard.php"
                        class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-md transition <?= $current_page === 'dashboard.php' ? 'bg-white/[0.08] text-white' : 'text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]' ?>">
                        <svg class="w-3.5 h-3.5 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="<?= $base_url ?>/partials/results.php"
                        class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-md transition <?= $current_page === 'results.php' ? 'bg-white/[0.08] text-white' : 'text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]' ?>">
                        <svg class="w-3.5 h-3.5 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Results
                    </a>
                    <a href="<?= $base_url ?>/partials/profile.php"
                        class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-md transition <?= $current_page === 'profile.php' ? 'bg-white/[0.08] text-white' : 'text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]' ?>">
                        <svg class="w-3.5 h-3.5 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <?php if (is_admin()): ?>
                    <a href="<?= $base_url ?>/admin/"
                        class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-md transition <?= $current_dir === 'admin' ? 'bg-white/[0.08] text-white' : 'text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]' ?>">
                        <svg class="w-3.5 h-3.5 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Admin
                    </a>
                    <?php endif; ?>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-neutral-800 overflow-hidden ring-1 ring-neutral-700">
                            <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($_SESSION['data']['photo'] ?? 'default.png') ?>" class="w-full h-full object-cover" alt="">
                        </div>
                        <span class="text-xs text-neutral-400 font-medium"><?= sanitize($_SESSION['data']['username'] ?? '') ?></span>
                    </div>
                    <a href="<?= $base_url ?>/partials/logout.php"
                        class="text-neutral-600 hover:text-red-400 transition p-1.5 rounded icon-hover-bounce">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>

                <button id="mobile-menu-btn" class="md:hidden text-neutral-500 hover:text-neutral-300 p-1.5 rounded-md hover:bg-white/[0.04] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="menu-icon-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                        <path id="menu-icon-close" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" class="hidden"/>
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="hidden md:hidden pb-3 space-y-0.5 border-t border-neutral-800/50 pt-2 mt-1">
                <a href="<?= $base_url ?>/partials/dashboard.php" class="flex items-center gap-2 text-xs px-3 py-2 rounded-md text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="<?= $base_url ?>/partials/results.php" class="flex items-center gap-2 text-xs px-3 py-2 rounded-md text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Results
                </a>
                <a href="<?= $base_url ?>/partials/profile.php" class="flex items-center gap-2 text-xs px-3 py-2 rounded-md text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <?php if (is_admin()): ?>
                <a href="<?= $base_url ?>/admin/" class="flex items-center gap-2 text-xs px-3 py-2 rounded-md text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin
                </a>
                <?php endif; ?>
                <hr class="border-neutral-800/50 my-1">
                <a href="<?= $base_url ?>/partials/logout.php" class="flex items-center gap-2 text-xs px-3 py-2 rounded-md text-neutral-600 hover:text-red-400 hover:bg-white/[0.04]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <?php if ($active_election && $current_page !== 'dashboard.php'): ?>
    <div class="bg-white/[0.02] border-b border-neutral-800/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-2 flex items-center gap-2">
            <span class="relative flex h-1.5 w-1.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neutral-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-neutral-500"></span>
            </span>
            <p class="text-xs text-neutral-500">
                <span class="text-neutral-400 font-medium"><?= sanitize($active_election['name']) ?></span>
                &middot; Ends <?= date('M d, Y g:i A', strtotime($active_election['end_time'])) ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            var menu = document.getElementById('mobile-menu');
            var iconOpen = document.getElementById('menu-icon-open');
            var iconClose = document.getElementById('menu-icon-close');
            menu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
        });
    </script>
