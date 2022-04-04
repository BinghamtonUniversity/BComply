<?php

namespace App\Policies;

use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class WorkshopPolicy
{
    use HandlesAuthorization;

}