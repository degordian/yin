<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\JsonApi\Exception;

use Exception;
use WoohooLabs\Yin\JsonApi\Schema\Document\AbstractErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

abstract class AbstractJsonApiException extends Exception implements JsonApiExceptionInterface
{
    /**
     * @var AbstractErrorDocument
     */
    protected $errorDocument;

    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }

    protected function createErrorDocument(): AbstractErrorDocument
    {
        return new ErrorDocument();
    }

    /**
     * @return Error[]
     */
    abstract protected function getErrors(): array;

    public function getErrorDocument(): AbstractErrorDocument
    {
        $document = $this->createErrorDocument();
        foreach ($this->getErrors() as $error) {
            $document->addError($error);
        }

        return $document;
    }
}
