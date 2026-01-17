<?php

const WIDTH = 10;
const HEIGHT = 20;
const FOOD_SIGN = '@';
const HEAD_SIGN = '&';
const BODY_SIGN = '*';

const DIRS = [
    'A' => [0, -1], // up
    'B' => [0,  1], // down
    'C' => [1,  0], // right
    'D' => [-1, 0], // left
];

class State
{
    public static int $score = 0;
    public static int $foodX = 0;
    public static int $foodY = 0;

    public static array $snake = [
        [
            'x' => 5,
            'y' => 5,
        ],
        [
            'x' => 4,
            'y' => 5,
        ]
    ];

    public static $dx = 1;
    public static $dy = 0;
}

$target = 20;

function clearScreen()
{
    echo "\033[2J\033[H";
}

function spawnFood()
{
    return [
        rand(0, HEIGHT - 1),
        rand(0, WIDTH - 1),
    ];
}

function draw()
{
    clearScreen();
    echo sprintf("Score: %s \n", State::$score);

    $map = array_fill(0, WIDTH, array_fill(0, HEIGHT, ' '));

    $map[State::$foodY][State::$foodX] = FOOD_SIGN;

    foreach (State::$snake as $key => $item) {
        $map[$item['y']][$item['x']] = $key === 0 ? HEAD_SIGN : BODY_SIGN;
    }

    foreach ($map as $row) {
        echo implode('', $row) . "\n";
    }
}

function move()
{
    $head = State::$snake[0];
    $head['x'] = ($head['x'] + State::$dx + HEIGHT) % HEIGHT;
    $head['y'] = ($head['y'] + State::$dy + WIDTH) % WIDTH;
    array_unshift(State::$snake, $head);

    if ($head['x'] === State::$foodX && $head['y'] === State::$foodY) {
        State::$score++;
        [State::$foodX, State::$foodY] = spawnFood();
    } else {
        array_pop(State::$snake);
    }
}

function getInput()
{
    $read = [STDIN];
    $write = null;
    $except = null;

    if (stream_select($read, $write, $except, 0, 0)) {
        $char = fgetc(STDIN);

        if ($char === "\033") {
            fgetc(STDIN);
            $arrow = fgetc(STDIN);

            if (isset(DIRS[$arrow])) {
                [State::$dx, State::$dy] = DIRS[$arrow];
            }
        }
    }
}

system('stty -icanon -echo');
stream_set_blocking(STDIN, false);
ob_implicit_flush(true);

try {
    [State::$foodX, State::$foodY] = spawnFood();

    while (State::$score < $target) {
        getInput();
        move();
        draw();
        usleep(200000);
    }

    clearScreen();
    echo sprintf("WIN! Score: %s \n", State::$score);
} catch (\Throwable $t) {
    var_dump($t->getMessage());
}
