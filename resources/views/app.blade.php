<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="description"
        content="BlogVerse Pro adalah platform untuk membaca, menulis, dan berbagi artikel tentang teknologi, pendidikan, gaya hidup, bisnis, opini, dan pengetahuan."
    >

    <meta name="theme-color" content="#ffffff">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <title inertia>
        {{ config('app.name', 'BlogVerse Pro') }}
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
        rel="stylesheet"
    >

    @viteReactRefresh

    @vite([
        'resources/css/app.css',
        'resources/js/app.jsx'
    ])

    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>
</html>