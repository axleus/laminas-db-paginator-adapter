<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter\Exception;

use InvalidArgumentException as SplInvalidArgumentException;

final class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface {}
