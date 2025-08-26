<?php

namespace Treblle\OaaS\Enums;

enum SortOrder: string
{
    case CREATED_AT_DESC = '-created_at';
    case CREATED_AT_ASC = 'created_at';
    case PATH_ASC = 'path';
    case PATH_DESC = '-path';
    case LOAD_TIME_ASC = 'load_time';
    case LOAD_TIME_DESC = '-load_time';
}