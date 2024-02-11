<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class User extends Model implements AttachableContract
{
    use HasFactory;
    use User\Picturable;
    use User\Mentionee;
}
