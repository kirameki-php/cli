<?php declare(strict_types=1);

namespace Kirameki\Cli\Output\Ansi;

enum C0: string
{
    /**
     * Makes an audible noise
     */
    case Bell = "\x07";

    /**
     * Moves back the cursor
     */
    case Backspace = "\x08";

    /**
     * Moves the cursor right 8 times
     */
    case Tab = "\x09";

    /**
     * Move to next line and scrolls the display up if at bottom of the screen
     */
    case LineFeed = "\x0A";

    /**
     * Moves the cursor to column zero
     */
    case CarriageReturn = "\x0D";

    /**
     * Starts all the escape sequences
     */
    case Escape = "\x1B";
}
