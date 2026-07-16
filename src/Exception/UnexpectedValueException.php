<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter\Exception;

use UnexpectedValueException as SplUnexpectedValueException;

final class UnexpectedValueException extends SplUnexpectedValueException implements ExceptionInterface {}
