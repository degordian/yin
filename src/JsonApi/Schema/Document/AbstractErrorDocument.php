<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\JsonApi\Schema\Document;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

abstract class AbstractErrorDocument extends AbstractDocument
{
    /**
     * @var Error[]
     */
    protected $errors = [];

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Includes a new error in the error document.
     *
     * @param Error $error
     * @return $this
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Returns the content as an array with all the provided members of the error document. You can also pass
     * additional meta information for the document in the $additionalMeta argument.
     */
    public function getContent(array $additionalMeta = []): array
    {
        $content = $this->transformBaseContent($additionalMeta);

        if (empty($this->errors) === false) {
            foreach ($this->errors as $error) {
                /** @var Error $error */
                $content["errors"][] = $error->transform();
            }
        }

        return $content;
    }

    public function getStatusCode(?int $statusCode = null): int
    {
        return $this->getResponseCode($statusCode);
    }

    /**
     * @deprecated since 3.1.0, will be removed in 4.0.0. Use AbstractErrorDocument::getStatusCode() instead.
     */
    public function getResponseCode(?int $statusCode = null): int
    {
        if ($statusCode !== null) {
            return $statusCode;
        }

        if (count($this->errors) === 1) {
            return (int) $this->errors[0]->getStatus();
        }

        $responseCode = 500;
        foreach ($this->errors as $error) {
            /** @var Error $error */
            $roundedStatusCode = (int) (((int)$error->getStatus()) / 100) * 100;

            if (abs($responseCode - $roundedStatusCode) >= 100) {
                $responseCode = $roundedStatusCode;
            }
        }

        return $responseCode;
    }
}
