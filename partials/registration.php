<?php
require_once __DIR__ . '/../actions/connect.php';
$page_title = 'Register - Voting System';
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
<body class="min-h-screen bg-[#0a0a0a] flex items-center justify-center px-4 py-8">

    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2"></div>

    <div class="w-full max-w-sm">
        <div class="text-center mb-8 icon-fade">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/[0.06] mb-4">
                <svg class="w-6 h-6 text-neutral-400 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-lg font-semibold text-neutral-200 tracking-tight">Vote</h1>
            <p class="text-neutral-500 text-xs mt-1">Create your account</p>
        </div>

        <div class="card p-6 scale-in">
            <h2 class="text-sm font-semibold text-neutral-300 mb-4">Registration</h2>

            <!-- Step Indicator -->
            <div class="flex items-center gap-2 mb-6">
                <div class="flex items-center gap-1.5 flex-1">
                    <div id="step-dot-1" class="w-6 h-6 rounded-full bg-white/[0.1] text-neutral-300 text-[11px] font-bold flex items-center justify-center transition-all duration-300">1</div>
                    <span id="step-label-1" class="text-[11px] font-medium text-neutral-400 hidden sm:block">Account</span>
                </div>
                <div class="flex-1 h-px bg-neutral-800 overflow-hidden">
                    <div id="step-line-1" class="h-full bg-neutral-500 transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex items-center gap-1.5 flex-1 justify-center">
                    <div id="step-dot-2" class="w-6 h-6 rounded-full bg-neutral-800 text-neutral-600 text-[11px] font-bold flex items-center justify-center transition-all duration-300">2</div>
                    <span id="step-label-2" class="text-[11px] font-medium text-neutral-600 hidden sm:block">Security</span>
                </div>
                <div class="flex-1 h-px bg-neutral-800 overflow-hidden">
                    <div id="step-line-2" class="h-full bg-neutral-500 transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex items-center gap-1.5 flex-1 justify-end">
                    <span id="step-label-3" class="text-[11px] font-medium text-neutral-600 hidden sm:block">Profile</span>
                    <div id="step-dot-3" class="w-6 h-6 rounded-full bg-neutral-800 text-neutral-600 text-[11px] font-bold flex items-center justify-center transition-all duration-300">3</div>
                </div>
            </div>

            <form action="../actions/register.php" method="POST" enctype="multipart/form-data" data-loading>

                <!-- Step 1: Account -->
                <div id="step-1" class="step-content space-y-3">
                    <div>
                        <label for="username" class="block text-xs font-medium text-neutral-400 mb-1.5">Username</label>
                        <input type="text" name="username" id="username" required
                            class="input-field" placeholder="Choose a username">
                    </div>
                    <div>
                        <label for="idNum" class="block text-xs font-medium text-neutral-400 mb-1.5">Voter ID Number</label>
                        <input type="text" name="idNum" id="idNum" required maxlength="10" pattern="\d{10}"
                            class="input-field" placeholder="Enter 10-digit Voter ID">
                        <p class="text-[11px] text-neutral-600 mt-1">Exactly 10 digits</p>
                    </div>
                    <div>
                        <label for="std" class="block text-xs font-medium text-neutral-400 mb-1.5">Register as</label>
                        <select name="std" id="std" class="input-field appearance-none cursor-pointer">
                            <option value="voter">Voter</option>
                        </select>
                    </div>
                </div>

                <!-- Step 2: Security -->
                <div id="step-2" class="step-content space-y-3 hidden">
                    <div>
                        <label for="password" class="block text-xs font-medium text-neutral-400 mb-1.5">Password</label>
                        <input type="password" name="password" id="password" required minlength="8"
                            class="input-field" placeholder="Min 8 characters">
                    </div>
                    <div>
                        <label for="cpassword" class="block text-xs font-medium text-neutral-400 mb-1.5">Confirm Password</label>
                        <input type="password" name="cpassword" id="cpassword" required minlength="8"
                            class="input-field" placeholder="Confirm your password">
                        <p id="pw-match" class="text-[11px] mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Step 3: Profile -->
                <div id="step-3" class="step-content space-y-3 hidden">
                    <div>
                        <label for="email" class="block text-xs font-medium text-neutral-400 mb-1.5">Email <span class="text-neutral-600">(optional)</span></label>
                        <input type="email" name="email" id="email"
                            class="input-field" placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-400 mb-1.5">Profile Photo</label>
                        <div class="flex items-center gap-3">
                            <div id="photo-preview" class="w-12 h-12 rounded-lg bg-neutral-800 border border-dashed border-neutral-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label for="photo" class="text-xs text-neutral-400 hover:text-neutral-200 cursor-pointer font-medium transition">
                                    <svg class="w-3 h-3 inline mr-1 icon-hover-scale" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Upload photo
                                </label>
                                <p class="text-[11px] text-neutral-600 mt-0.5">JPG, PNG, GIF, WebP (max 2MB)</p>
                                <input type="file" name="photo" id="photo" class="hidden" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/[0.02] rounded-lg p-3 space-y-1.5 mt-2 border border-neutral-800/50">
                        <p class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Summary</p>
                        <div class="flex justify-between text-xs">
                            <span class="text-neutral-500">Username</span>
                            <span id="sum-username" class="text-neutral-300 font-medium">-</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-neutral-500">Voter ID</span>
                            <span id="sum-id" class="text-neutral-300 font-medium">-</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-neutral-500">Role</span>
                            <span id="sum-role" class="text-neutral-300 font-medium">-</span>
                        </div>
                    </div>
                </div>

                <?php echo csrf_field(); ?>

                <div class="flex items-center gap-2 mt-5">
                    <button type="button" id="btn-back"
                        class="hidden flex-1 bg-white/[0.04] hover:bg-white/[0.08] text-neutral-400 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98]">
                        Back
                    </button>
                    <button type="button" id="btn-next"
                        class="flex-1 bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98]">
                        Next
                    </button>
                    <button type="submit" id="btn-submit"
                        class="hidden flex-1 bg-white/[0.08] hover:bg-white/[0.14] text-neutral-200 font-medium py-2.5 rounded-lg transition text-sm cursor-pointer active:scale-[0.98]">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center border-t border-neutral-800/50 pt-4">
                <p class="text-xs text-neutral-500">
                    Already have an account?
                    <a href="../" class="text-neutral-300 hover:text-white font-medium transition">Login</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        var currentStep = 1;
        var totalSteps = 3;

        function updateStepUI() {
            document.querySelectorAll('.step-content').forEach(function(el) { el.classList.add('hidden'); });
            document.getElementById('step-' + currentStep).classList.remove('hidden');

            for (var i = 1; i <= totalSteps; i++) {
                var dot = document.getElementById('step-dot-' + i);
                var label = document.getElementById('step-label-' + i);
                if (i < currentStep) {
                    dot.className = 'w-6 h-6 rounded-full bg-white/[0.1] text-neutral-300 text-[11px] font-bold flex items-center justify-center transition-all duration-300';
                    dot.innerHTML = '<svg class="w-3 h-3 icon-pop" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                    if (label) label.className = 'text-[11px] font-medium text-neutral-400 hidden sm:block';
                } else if (i === currentStep) {
                    dot.className = 'w-6 h-6 rounded-full bg-white/[0.1] text-neutral-200 text-[11px] font-bold flex items-center justify-center transition-all duration-300 ring-2 ring-white/10';
                    dot.textContent = i;
                    if (label) label.className = 'text-[11px] font-medium text-neutral-300 hidden sm:block';
                } else {
                    dot.className = 'w-6 h-6 rounded-full bg-neutral-800 text-neutral-600 text-[11px] font-bold flex items-center justify-center transition-all duration-300';
                    dot.textContent = i;
                    if (label) label.className = 'text-[11px] font-medium text-neutral-600 hidden sm:block';
                }
            }

            document.getElementById('step-line-1').style.width = currentStep > 1 ? '100%' : '0%';
            document.getElementById('step-line-2').style.width = currentStep > 2 ? '100%' : '0%';

            document.getElementById('btn-back').classList.toggle('hidden', currentStep === 1);
            document.getElementById('btn-next').classList.toggle('hidden', currentStep === totalSteps);
            document.getElementById('btn-submit').classList.toggle('hidden', currentStep !== totalSteps);
        }

        function validateStep1() {
            var username = document.getElementById('username').value.trim();
            var idNum = document.getElementById('idNum').value.trim();
            if (!username || username.length < 3) { showToast('error', 'Username must be at least 3 characters'); return false; }
            if (!/^\d{10}$/.test(idNum)) { showToast('error', 'Voter ID must be exactly 10 digits'); return false; }
            return true;
        }

        function validateStep2() {
            var pw = document.getElementById('password').value;
            var cpw = document.getElementById('cpassword').value;
            if (pw.length < 8) { showToast('error', 'Password must be at least 8 characters'); return false; }
            if (pw !== cpw) { showToast('error', 'Passwords do not match'); return false; }
            return true;
        }

        function updateSummary() {
            document.getElementById('sum-username').textContent = document.getElementById('username').value.trim() || '-';
            document.getElementById('sum-id').textContent = document.getElementById('idNum').value.trim() || '-';
            document.getElementById('sum-role').textContent = document.getElementById('std').value === 'group' ? 'Candidate' : 'Voter';
        }

        document.getElementById('cpassword').addEventListener('input', function() {
            var pw = document.getElementById('password').value;
            var msg = document.getElementById('pw-match');
            if (this.value.length > 0) {
                msg.classList.remove('hidden');
                if (pw === this.value) {
                    msg.textContent = 'Passwords match';
                    msg.className = 'text-[11px] mt-1 text-neutral-400';
                } else {
                    msg.textContent = 'Passwords do not match';
                    msg.className = 'text-[11px] mt-1 text-red-400';
                }
            } else {
                msg.classList.add('hidden');
            }
        });

        document.getElementById('photo').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('photo-preview').innerHTML = '<img src="' + ev.target.result + '" class="w-full h-full object-cover" alt="">';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('btn-next').addEventListener('click', function() {
            if (currentStep === 1 && !validateStep1()) return;
            if (currentStep === 2 && !validateStep2()) return;
            if (currentStep === 2) updateSummary();
            if (currentStep < totalSteps) { currentStep++; updateStepUI(); }
        });

        document.getElementById('btn-back').addEventListener('click', function() {
            if (currentStep > 1) { currentStep--; updateStepUI(); }
        });

        function showToast(type, message) {
            var container = document.getElementById('toast-container');
            var cols = { success: 'bg-neutral-800 border border-neutral-700', error: 'bg-neutral-800 border border-red-900/40' };
            var ic = { success: 'text-neutral-400', error: 'text-red-500' };
            var svg = { success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>', error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' };
            var toast = document.createElement('div');
            toast.className = 'flex items-center gap-3 ' + (cols[type] || cols.error) + ' text-white px-4 py-3 rounded-lg shadow-xl transform transition-all duration-300 translate-x-full opacity-0';
            toast.innerHTML = '<svg class="w-4 h-4 flex-shrink-0 icon-pop ' + (ic[type] || ic.error) + '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' + (svg[type] || svg.error) + '</svg><span class="text-sm ' + (type === 'success' ? 'text-neutral-200' : 'text-red-400') + '">' + message + '</span>';
            container.appendChild(toast);
            requestAnimationFrame(function() { toast.classList.remove('translate-x-full', 'opacity-0'); });
            setTimeout(function() { toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(function() { toast.remove(); }, 300); }, 4000);
        }

        <?php $flash = get_flash(); if ($flash): ?>
        document.addEventListener('DOMContentLoaded', function() { showToast('<?= in_array($flash['type'], ['success','error']) ? $flash['type'] : 'error' ?>', '<?= sanitize($flash['message']) ?>'); });
        <?php endif; ?>

        document.querySelectorAll('form[data-loading]').forEach(function(f) { f.addEventListener('submit', function() { document.getElementById('loading-overlay')?.classList.remove('hidden'); }); });
    </script>
</body>
</html>
