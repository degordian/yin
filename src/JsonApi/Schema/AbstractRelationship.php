<?php
namespace WoohooLabs\Yin\JsonApi\Schema;

use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

abstract class AbstractRelationship
{
    use LinksTrait;
    use MetaTrait;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface
     */
    protected $resourceTransformer;

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \WoohooLabs\Yin\JsonApi\Schema\Included $included
     * @param string $baseRelationshipPath
     * @param string $relationshipName
     * @param array $defaultRelationships
     * @return array
     */
    abstract protected function transformData(
        RequestInterface $request,
        Included $included,
        $baseRelationshipPath,
        $relationshipName,
        array $defaultRelationships
    );

    /**
     * @param array $meta
     * @param \WoohooLabs\Yin\JsonApi\Schema\Links|null $links
     * @param mixed $data
     * @param \WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface|null $resourceTransformer
     */
    public function __construct(
        array $meta = [],
        Links $links = null,
        $data = null,
        ResourceTransformerInterface $resourceTransformer = null
    ) {
        $this->meta = $meta;
        $this->links = $links;
        $this->data = $data;
        $this->resourceTransformer = $resourceTransformer;
    }

    /**
     * @param mixed $data
     * @param \WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface $resourceTransformer
     * @return $this
     */
    public function setData($data, ResourceTransformerInterface $resourceTransformer)
    {
        $this->data = $data;
        $this->resourceTransformer = $resourceTransformer;

        return $this;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \WoohooLabs\Yin\JsonApi\Schema\Included $included
     * @param string $resourceType
     * @param string $baseRelationshipPath
     * @param string $relationshipName
     * @param array $defaultRelationships
     * @return array|null
     */
    public function transform(
        RequestInterface $request,
        Included $included,
        $resourceType,
        $baseRelationshipPath,
        $relationshipName,
        array $defaultRelationships
    ) {
        $relationship = null;

        $data = $this->transformData(
            $request,
            $included,
            $baseRelationshipPath,
            $relationshipName,
            $defaultRelationships
        );

        if ($request->isIncludedField($resourceType, $relationshipName)) {
            $relationship = [];

            // LINKS
            if ($this->links !== null) {
                $relationship["links"] = $this->links->transform();
            }

            // META
            if (empty($this->meta) === false) {
                $relationship["meta"] = $this->meta;
            }

            // DATA
            $relationship["data"] = $data;
        }

        return $relationship;
    }

    /**
     * @param mixed $domainObject
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param \WoohooLabs\Yin\JsonApi\Schema\Included $included
     * @param string $baseRelationshipPath
     * @param string $relationshipName
     * @param array $defaultRelationships
     * @return array
     */
    protected function transformResource(
        $domainObject,
        RequestInterface $request,
        Included $included,
        $baseRelationshipPath,
        $relationshipName,
        array $defaultRelationships
    ) {
        if ($request->isIncludedRelationship($baseRelationshipPath, $relationshipName, $defaultRelationships)) {
            $included->addIncludedResource(
                $this->resourceTransformer->transformToResource(
                    $domainObject,
                    $request,
                    $included,
                    $baseRelationshipPath
                )
            );
        }

        return $this->resourceTransformer->transformToResourceIdentifier($domainObject);
    }
}
