<?php 

namespace VestiaireCollective\Enum;


enum HttpCode: int {
    case OK = 200;

    case BadRequest = 400;
    case NotFound = 404;

    case InternalServerError = 500;
}