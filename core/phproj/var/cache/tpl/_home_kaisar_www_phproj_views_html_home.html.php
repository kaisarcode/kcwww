<!DOCTYPE html>








<html lang="<?php echo $app->lang ?>">
    <head>
        <?php call_user_func(function() 
use ($dd8e0a68) { 
extract($dd8e0a68, EXTR_SKIP); 
extract($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, EXTR_SKIP); 
$dd8e0a68['/head_ab1c08'] = array_merge($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, []); 
extract($dd8e0a68['/head_ab1c08'], EXTR_OVERWRITE); 
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Page Title -->
<?php if ($page->name == $app->name) : ?>
<title><?php echo $page->name ?></title>
<?php else: ?>
<title><?php echo $page->name ?> | <?php echo $app->name ?></title>
<?php endif ?>

<meta name="description" content="<?php echo $app->desc ?>">
<meta name="keywords" content="<?php echo $app->keywords ?>">

<!-- PWA Meta -->
<meta name="theme-color" content="<?php echo $app->color ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="<?php echo $app->name ?>">
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
<?php }); ?>
    </head>
    <body>
        <header>
            <?php call_user_func(function() 
use ($dd8e0a68) { 
extract($dd8e0a68, EXTR_SKIP); 
extract($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, EXTR_SKIP); 
$dd8e0a68['/header_5b8182'] = array_merge($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, []); 
extract($dd8e0a68['/header_5b8182'], EXTR_OVERWRITE); 
?>
<h1><a href="/">CORE <?php echo $app->name ?></a></h1>
<nav>
    <ul>
        <li><a href="/">Home</a></li>
    </ul>
</nav>
<?php }); ?>
        </header>
        <main>
            <?php call_user_func(function() 
use ($dd8e0a68) { 
extract($dd8e0a68, EXTR_SKIP); 
extract($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, EXTR_SKIP); 
$dd8e0a68['/main_55c0fd'] = array_merge($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, []); 
extract($dd8e0a68['/main_55c0fd'], EXTR_OVERWRITE); 
?>
<article>
    <header>
        <h2>Welcome to <?php echo $app->name ?></h2>
    </header>
    <section>
        <p><?php echo $app->desc ?></p>
    </section>
</article>
<?php }); ?>
        </main>
        <footer>
            <?php call_user_func(function() 
use ($dd8e0a68) { 
extract($dd8e0a68, EXTR_SKIP); 
extract($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, EXTR_SKIP); 
$dd8e0a68['/footer_f3058c'] = array_merge($dd8e0a68['dd8e0a68'] ?? $dd8e0a68, []); 
extract($dd8e0a68['/footer_f3058c'], EXTR_OVERWRITE); 
?>
<p>&copy; 2025 KaisarCode</p>
<?php }); ?>
        </footer>
    </body>
</html>


