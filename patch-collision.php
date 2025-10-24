<?php

$printerFile = __DIR__.'/vendor/nunomaduro/collision/src/Adapters/Phpunit/Printers/DefaultPrinter.php';

if (! file_exists($printerFile)) {
    echo "Collision printer file not found.\n";
    exit(1);
}

$content = file_get_contents($printerFile);

// Find the testPrintedUnexpectedOutput method and modify it to suppress unexpected output
$search = 'public function testPrintedUnexpectedOutput(PrintedUnexpectedOutput $printedUnexpectedOutput): void
    {
        $this->output->write($printedUnexpectedOutput->output());
    }';

$replace = 'public function testPrintedUnexpectedOutput(PrintedUnexpectedOutput $printedUnexpectedOutput): void
    {
        // Completely suppress unexpected output to eliminate garbled x§x§x§x§ characters
        return;
    }';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($printerFile, $content);
    echo "Collision printer patched successfully.\n";
} else {
    echo "Could not find the target method in Collision printer. Patch may already be applied or version mismatch.\n";
}
