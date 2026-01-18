<?php

const WIDTH = 20;
const HEIGHT = 10;
const HEAD_SIGN = '&';
const BODY_SIGN = '*';
const FOOD_SIGN = '@';
const TARGET_SCORE = 20;
const DIRS = [
    'A' => [0, -1], // up
    'B' => [0,  1], // down
    'C' => [1,  0], // right
    'D' => [-1, 0], // left
];

class State
{
    public static $score = 0;

    public static $food = [
        'x' => 0,
        'y' => 0,
    ];
    
    public static $snake = [
        [
            'x' => 5,
            'y' => 5,
        ],
        [
            'x' => 4,
            'y' => 5,
        ],
    ];
    
    public static $dx = 1;
    public static $dy = 0;
}

function clearScreen()
{
    echo "\033[2J\033[H";
}

function spawnFood() {
    return [
        'y' => rand(0, HEIGHT - 1),
        'x' => rand(0, WIDTH - 1),
    ];
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

function move()
{
    $head = State::$snake[0];
    $head['x'] = ($head['x'] + State::$dx + WIDTH) % WIDTH;
    $head['y'] = ($head['y'] + State::$dy + HEIGHT) % HEIGHT;
    array_unshift(State::$snake, $head);
    
    if ($head['x'] === State::$food['x'] && $head['y'] === State::$food['y']) {
        State::$score++;
        State::$food = spawnFood();
    } else {
        array_pop(State::$snake);
    }
}

function draw()
{
    clearScreen();
    echo sprintf("Score: %s \n", State::$score);
    $map = array_fill(0, HEIGHT, array_fill(0, WIDTH, ' '));
    $map[State::$food['y']][State::$food['x']] = FOOD_SIGN;
    
    foreach (State::$snake as $key => $item) {
        $map[$item['y']][$item['x']] = $key === 0 ? HEAD_SIGN : BODY_SIGN;
    }
    
    foreach ($map as $row) {
        echo implode('', $row)."\n";
    }
}

try {
    $sttyMode = shell_exec('stty -g');
    system('stty -icanon -echo');
    stream_set_blocking(STDIN, false);
    ob_implicit_flush(true);

    State::$food = spawnFood();
    
    while (State::$score < TARGET_SCORE) {
        getInput();
        move();
        draw();
        usleep(200000);
    }
    
    clearScreen();
    echo sprintf("Win %s\n", State::$score);
} catch (\Throwable $t) {
    var_dump($t->getMessage());
} finally {
    system("stty $sttyMode");
    stream_set_blocking(STDIN, true);
    echo "\033[?25h";
}
