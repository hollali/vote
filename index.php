<?php
session_start();
require_once __DIR__ . '/actions/connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./style.css">
</head>
<body class="min-h-screen bg-[#0a0a0a] flex items-center justify-center px-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8 icon-fade">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/[0.06] mb-4">
                <svg class="w-6 h-6 text-neutral-400 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h1 class="text-lg font-semibold text-neutral-200 tracking-tight">Vote</h1>
            <p class="text-neutral-500 text-xs mt-1">Sign in to cast your vote</p>
        </div>

        <div class="card p-6 scale-in">
            <form action="./actions/login.php" method="POST" class="space-y-3.5">
                <div>
                    <label for="username" class="block text-xs font-medium text-neutral-400 mb-1.5">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" name="username" id="username" required
                            class="input-field pl-9" placeholder="Enter your username">
                    </div>
                </div>

                <div>
                    <label for="idNum" class="block text-xs font-medium text-neutral-400 mb-1.5">Voter ID Number</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                            </svg>
                        </span>
                        <input type="text" name="idNum" id="idNum" required maxlength="10" minlength="10"
                            class="input-field pl-9" placeholder="Enter your Voter ID">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-neutral-400 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="input-field pl-9" placeholder="Enter your password">
                    </div>
                </div>

                <div>
                    <label for="std" class="block text-xs font-medium text-neutral-400 mb-1.5">Sign in as</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <select name="std" id="std"
                            class="input-field pl-9 pr-10 appearance-none cursor-pointer">
                            <option value="group">Candidate</option>
                            <option value="voter">Voter</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98] mt-2">
                    Sign In
                </button>
            </form>

            <div class="mt-5 text-center border-t border-neutral-800/50 pt-4">
                <p class="text-xs text-neutral-500">
                    Don't have an account?
                    <a href="./partials/registration.php" class="text-neutral-300 hover:text-white transition font-medium">Register</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
