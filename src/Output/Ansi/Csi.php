<?php declare(strict_types=1);

namespace Kirameki\Cli\Output\Ansi;

enum Csi: string
{
    case CursorUp = 'A';
    case CursorDown = 'B';
    case CursorForward = 'C';
    case CursorBack = 'D';
    case CursorNextLine = 'E';
    case CursorPrevLine = 'F';
    case CursorPosition = 'H';
    case EraseInDisplay = 'J';
    case EraseInLine = 'K';
    case ScrollUp = 'S';
    case ScrollDown = 'T';
    case Sgr = 'm';
    case DeviceStatusReport = '6n';
}
