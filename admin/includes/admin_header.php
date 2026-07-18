<?php
$page_title = $page_title ?? 'Admin - Voting System';
require_once __DIR__ . '/../../actions/connect.php';
require_admin();

$admin_page = basename($_SERVER['PHP_SELF']);
$admin_dir = dirname($_SERVER['SCRIPT_NAME']);
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
    <title><?= $page_title ?></title>
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

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 w-56 bg-[#111] border-r border-neutral-800/80 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-200">
            <div class="p-4 border-b border-neutral-800/50">
                <a href="<?= $base_url ?>/admin/" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-white/[0.07] flex items-center justify-center group-hover:bg-white/[0.12] transition">
                        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-neutral-200 font-semibold text-sm tracking-tight block leading-tight">Admin</span>
                        <span class="text-neutral-600 text-[10px]">Voting System</span>
                    </div>
                </a>
            </div>

            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                <?php
                $nav_items = [
                    ['page' => 'index.php', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                    ['page' => 'elections.php', 'label' => 'Elections', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                    ['page' => 'candidates.php', 'label' => 'Candidates', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ['page' => 'voters.php', 'label' => 'Voters', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
                    ['page' => 'admins.php', 'label' => 'Admins', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
                    ['page' => 'audit.php', 'label' => 'Activity', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                ];
                foreach ($nav_items as $item):
                    $active = $admin_page === $item['page'];
                ?>
                <a href="<?= $base_url ?>/admin/<?= $item['page'] ?>"
                    class="flex items-center gap-2.5 text-xs font-medium px-3 py-2 rounded-md transition <?= $active ? 'bg-[var(--accent-light)] text-[var(--accent)]' : 'text-neutral-500 hover:text-neutral-300 hover:bg-white/[0.04]' ?>">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 <?= $active ? 'text-[var(--accent)]' : 'text-neutral-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $item['icon'] ?></svg>
                    <?= $item['label'] ?>
                </a>
                <?php endforeach; ?>
            </nav>

            <div class="p-3 border-t border-neutral-800/50 space-y-0.5">
                <a href="<?= $base_url ?>/partials/dashboard.php"
                    class="flex items-center gap-2.5 text-xs font-medium px-3 py-2 rounded-md text-neutral-600 hover:text-neutral-300 hover:bg-white/[0.04] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Back to Site
                </a>
                <a href="<?= $base_url ?>/partials/logout.php"
                    class="flex items-center gap-2.5 text-xs font-medium px-3 py-2 rounded-md text-neutral-600 hover:text-red-400 hover:bg-white/[0.04] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main content -->
        <div class="flex-1 md:ml-56">
            <!-- Top bar (mobile) -->
            <div class="sticky top-0 z-20 bg-[#111] border-b border-neutral-800/80 md:hidden">
                <div class="flex items-center justify-between h-12 px-4">
                    <button onclick="toggleSidebar()" class="text-neutral-500 hover:text-neutral-300 p-1.5 rounded-md hover:bg-white/[0.04] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="text-sm font-medium text-neutral-300">Admin Panel</span>
                    <div class="w-8"></div>
                </div>
            </div>

            <main class="p-4 sm:p-6 md:p-8 max-w-5xl">

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
