<?php
require_once __DIR__ . '/../actions/connect.php';

$token = $_GET['token'] ?? '';
$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="min-h-screen bg-[#0a0a0a] flex items-center justify-center px-4">
    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2"></div>

    <div class="w-full max-w-sm">
        <div class="text-center mb-8 icon-fade">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/[0.06] mb-4">
                <svg class="w-6 h-6 text-neutral-400 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-lg font-semibold text-neutral-200">Reset Password</h1>
        </div>

        <div class="card p-6 scale-in">
            <form action="../actions/reset-password.php" method="POST" class="space-y-3.5">
                <input type="hidden" name="token" value="<?= sanitize($token) ?>">
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1.5">New Password</label>
                    <input type="password" name="password" required
                        class="input-field" placeholder="Min 8 characters">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1.5">Confirm Password</label>
                    <input type="password" name="confirm_password" required
                        class="input-field" placeholder="Confirm password">
                </div>
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98]">
                    Reset Password
                </button>
            </form>
            <div class="mt-5 text-center border-t border-neutral-800/50 pt-4">
                <a href="../" class="text-xs text-neutral-500 hover:text-neutral-300 transition">
                    <svg class="w-3 h-3 inline mr-1 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
    <script>
        function showToast(t,m){var c=document.getElementById('toast-container');var cols={success:'bg-neutral-800 border border-neutral-700',error:'bg-neutral-800 border border-red-900/40'};var ic={success:'text-neutral-400',error:'text-red-500'};var sv={success:'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',error:'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'};var e=document.createElement('div');e.className='flex items-center gap-3 '+(cols[t]||cols.error)+' text-white px-4 py-3 rounded-lg shadow-xl transform transition-all duration-300 translate-x-full opacity-0';e.innerHTML='<svg class="w-4 h-4 flex-shrink-0 icon-pop '+(ic[t]||ic.error)+'" fill="none" stroke="currentColor" viewBox="0 0 24 24">'+(sv[t]||sv.error)+'</svg><span class="text-sm '+(t==='success'?'text-neutral-200':'text-red-400')+'">'+m+'</span>';c.appendChild(e);requestAnimationFrame(function(){e.classList.remove('translate-x-full','opacity-0')});setTimeout(function(){e.classList.add('translate-x-full','opacity-0');setTimeout(function(){e.remove()},300)},4000)}
        <?php $flash = get_flash(); if ($flash): ?>
        document.addEventListener('DOMContentLoaded', function() { showToast('<?= in_array($flash['type'], ['success','error']) ? $flash['type'] : 'error' ?>', '<?= sanitize($flash['message']) ?>'); });
        <?php endif; ?>
    </script>
</body>
</html>
