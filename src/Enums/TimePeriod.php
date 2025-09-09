<?php

namespace Treblle\OaaS\Enums;

enum TimePeriod: string
{
    case LAST_MINUTE = 'minute,1';
    case LAST_5_MINUTES = 'minute,5';
    case LAST_24_HOURS = 'hour,24';
    case LAST_72_HOURS = 'hour,72';
    case LAST_WEEK = 'week,1';
    case LAST_14_DAYS = 'day,14';
    case LAST_MONTH = 'month,1';
    case ALL = 'all';
}