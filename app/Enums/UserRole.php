<?php

namespace App\Enums;

enum UserRole: string
{
    case Citizen = 'citizen';
    case Operator = 'operator';
    case Executor = 'executor';
    case LandscapingSpecialist = 'landscaping_specialist';
}
