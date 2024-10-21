<?php

namespace App\Enum;

enum NoteState: string
{
    case NORMAL = 'normal';
    case TRASHED = 'trashed';
    case ARCHIVED = 'archived';
}
