# PHP-snake-terminal

A simple Snake game for the terminal written in PHP.

## Rules:
- Impossible to lose.
- Win by reaching the target score.

## Code

Clear terminal screen
```
echo "\033[2J\033[H";
```

Switches terminal to raw-like mode: character-by-character input, no Enter needed, no automatic echo
```
system('stty -icanon -echo'); 
```

Makes STDIN non-blocking: reading input does not pause the game loop
```
stream_set_blocking(STDIN, false); 
```

Disables output buffering: every echo is sent to the terminal immediately
```
ob_implicit_flush(true); 
```

Handles non-blocking keyboard input: detects arrow key escape sequences (ESC = "\033"),
reads the final arrow code, and maps it to a movement direction using match

```
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
```
