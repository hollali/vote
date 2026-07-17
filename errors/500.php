<?php
session_start();
$page_title = 'Server Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - <?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-8xl font-bold text-rose-600">500</h1>
        <p class="text-xl text-white mt-4">Server Error</p>
        <p class="text-slate-400 mt-2">Something went wrong. Please try again later.</p>
        <a href="../" class="inline-block mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition">Go Home</a>
    </div>
</body>
</html>
