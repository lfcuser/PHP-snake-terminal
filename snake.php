<?php

$width = 10;
$height = 10;
$targetScore = 10;
$tickDelay = 150000; // microseconds

$snake = [
    ['x' => 5, 'y' => 4], // head
    ['x' => 4, 'y' => 4], // body
];

$direction = '>'; // > < ^ v
$score = 0;

function clearScreen() {
    echo "\033[2J\033[H";
}

function draw($snake, $food, $score, $direction, $width, $height) {
    clearScreen();

    echo "Score: $score | Dirrection: $direction\n";

    $map = array_fill(0, $height, array_fill(0, $width, ' '));

    $map[$food['y']][$food['x']] = '@';

    foreach ($snake as $i => $seg) {
        $map[$seg['y']][$seg['x']] = $i === 0 ? '$' : '*';
    }

    foreach ($map as $row) {
        echo implode('', $row) . "\n";
    }
}


function spawnFood($snake, $width, $height) {
    return [
        'x' => rand(0, $width - 1),
        'y' => rand(0, $height - 1),
    ];
}

system('stty -icanon -echo');
stream_set_blocking(STDIN, false);
ob_implicit_flush(true);

$food = spawnFood($snake, $width, $height);

try {
    while ($score < $targetScore) {
        $read = [STDIN];
        $write = null;
        $except = null;
        if (stream_select($read, $write, $except, 0, 0)) {
            $char = fgetc(STDIN);

            if ($char === "\033") {
                fgetc(STDIN);
                $arrow = fgetc(STDIN);

                $direction = match ($arrow) {
                    'A' => '^',
                    'B' => 'v',
                    'C' => '>',
                    'D' => '<',
                    default => $direction
                };
            }
        }

        $head = $snake[0];

        match ($direction) {
            '>' => $head['x']++,
            '<' => $head['x']--,
            '^' => $head['y']--,
            'v' => $head['y']++,
        };

        $head['x'] = ($head['x'] + $width) % $width;
        $head['y'] = ($head['y'] + $height) % $height;

        array_unshift($snake, $head);

        if ($head['x'] === $food['x'] && $head['y'] === $food['y']) {
            $score++;
            $food = spawnFood($snake, $width, $height);
        } else {
            array_pop($snake);
        }

        draw($snake, $food, $score, $direction, $width, $height);
        usleep($tickDelay);
    }

    clearScreen();
    echo "WIN! Score: 10\n";
} finally {
    system('stty sane');
}
