<!DOCTYPE html>
{{@setblock head}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Page Title -->
{{@if ($page->name == $app->name) :}}
<title>{{page.name}}</title>
{{@else:}}
<title>{{page.name}} | {{app.name}}</title>
{{@endif}}

<meta name="description" content="{{app.desc}}">
<meta name="keywords" content="{{app.keywords}}">

<!-- PWA Meta -->
<meta name="theme-color" content="{{app.color}}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{app.name}}">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<!-- Icons -->
<link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
<link rel="icon" href="/favicon-192.png" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/apple-touch-icon-180.png">

<link rel="manifest" href="/manifest.json">

<!-- Assets -->
<link rel="preload" href="/styles.css" as="style">
<link rel="preload" href="/script.js" as="script">

<link rel="stylesheet" href="/styles.css">
<script src="/script.js" defer></script>
{{@endsetblock}}

{{@setblock header}}
<h1><a href="/">CORE {{app.name}}</a></h1>
<nav>
    <ul>
        <li><a href="/">Home</a></li>
    </ul>
</nav>
{{@endsetblock}}

{{@setblock main}}
{{@endsetblock}}

{{@setblock footer}}
<p>&copy; 2025 KaisarCode</p>
{{@endsetblock}}

<html lang="{{app.lang}}">
    <head>
        {{@block head}}
    </head>
    <body>
        <header>
            {{@block header}}
        </header>
        <main>
            {{@block main}}
        </main>
        <footer>
            {{@block footer}}
        </footer>
    </body>
</html>

{{@setblock main}}
<article>
    <header>
        <h2>Welcome to {{app.name}}</h2>
    </header>
    <section>
        <p>{{app.desc}}</p>
    </section>
</article>
{{@endsetblock}}
