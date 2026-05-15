<?php
// search_redirect.php
$dir = new RecursiveDirectoryIterator('.');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.php$/', RegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    if (stripos($content, 'login.php') !== false || stripos($content, 'Location:') !== false) {
        echo "Found in: $path<br>";
        // Show the line
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (stripos($line, 'Location:') !== false || stripos($line, 'login.php') !== false) {
                echo "  Line " . ($num + 1) . ": " . htmlspecialchars(trim($line)) . "<br>";
            }
        }
        echo "<hr>";
    }
}
?>