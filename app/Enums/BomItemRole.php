<?php

namespace App\Enums;

enum BomItemRole: string
{
    case Jumbo = 'jumbo';
    case Paper = 'paper';
    case Tube = 'tube';
    case Foil = 'foil';
    case Box = 'box';
    case Ink = 'ink';
    case Graphic = 'graphic';
    case Machine = 'machine';
    case Other = 'other';
}
