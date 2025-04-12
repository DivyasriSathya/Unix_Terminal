<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

$data = json_decode(file_get_contents('php://input'), true);

// Detect OS and set shell path accordingly
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows
    $shell = "/bin/bash -c ";
} else {
    // Linux or Mac
    $shell = "/bin/bash -c ";
}

if (isset($data['command'])) {
    $command = trim($data['command']);

    if ($command === "clear") {
        echo "CLEAR_SCREEN";
        exit;
    }

    $escapedCommand = escapeshellarg($command);
    $fullCommand = $shell . $escapedCommand;

    $output = '';
    $errorOutput = '';

    $descriptorSpec = [
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w'], // stderr
    ];

    $process = proc_open($fullCommand, $descriptorSpec, $pipes);

    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if (!empty($output)) echo $output;
        if ($exitCode !== 0) {
            echo "Error: $errorOutput";
            echo "Command failed with exit code $exitCode.";
        }

        if (empty($output) && empty($errorOutput)) {
            echo "No output generated. Please check your command.";
        }
    } else {
        echo "Failed to open process.";
    }
} else {
    echo "Error: No command provided.";
}
?>
